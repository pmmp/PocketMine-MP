<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

/**
 * All the entity classes
 */
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\MoveEntityPacket_PosRot;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\Network;
use pocketmine\Player;
use pocketmine\level\format\pmf\LevelFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

abstract class Entity extends Position implements Metadatable{
	public static $entityCount = 1;

	/**
	 * @var Entity[]
	 */
	public static $list = array();

	/**
	 * @var Entity[]
	 */
	public static $needUpdate = array();

	/**
	 * @var Player[]
	 */
	protected $hasSpawned = array();

	protected $id;

	public $passenger = null;
	public $vehicle = null;

	public $chunkIndex;

	public $lastX;
	public $lastY;
	public $lastZ;

	public $motionX;
	public $motionY;
	public $motionZ;

	public $yaw;
	public $pitch;
	public $lastYaw;
	public $lastPitch;

	public $boundingBox;
	public $onGround;
	public $positionChanged;
	public $motionChanged;
	public $dead;

	public $height;
	public $width;
	public $length;

	/** @var int */
	private $health = 20;
	private $maxHealth = 20;

	public $fallDistance;
	public $ticksLived;
	public $lastUpdate;
	public $maxFireTicks;
	public $fireTicks;
	public $airTicks;
	public $namedtag;

	protected $inWater;
	public $noDamageTicks;
	private $justCreated;
	protected $fireProof;
	private $invulnerable;

	/** @var Server */
	protected $server;

	public $closed;

	public static function get($entityID){
		return isset(Entity::$list[$entityID]) ? Entity::$list[$entityID] : false;
	}

	public static function getAll(){
		return Entity::$list;
	}


	public function __construct(Level $level, Compound $nbt){
		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->closed = false;
		$this->namedtag = $nbt;
		$this->setLevel($level, true); //Create a hard reference
		$this->server = Server::getInstance();

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->setPositionAndRotation(new Vector3($this->namedtag["Pos"][0], $this->namedtag["Pos"][1], $this->namedtag["Pos"][2]), $this->namedtag->Rotation[0], $this->namedtag->Rotation[1]);
		$this->setMotion(new Vector3($this->namedtag["Motion"][0], $this->namedtag["Motion"][1], $this->namedtag["Motion"][2]));

		$this->fallDistance = $this->namedtag["FallDistance"];
		$this->fireTicks = $this->namedtag["Fire"];
		$this->airTicks = $this->namedtag["Air"];
		$this->onGround = $this->namedtag["OnGround"] > 0 ? true : false;
		$this->invulnerable = $this->namedtag["Invulnerable"] > 0 ? true : false;

		$index = LevelFormat::getIndex($this->x >> 4, $this->z >> 4);
		$this->chunkIndex = $index;
		Entity::$list[$this->id] = $this;
		$this->getLevel()->entities[$this->id] = $this;
		$this->getLevel()->chunkEntities[$this->chunkIndex][$this->id] = $this;
		$this->lastUpdate = microtime(true);
		$this->initEntity();
		Server::getInstance()->getPluginManager()->callEvent(new EntitySpawnEvent($this));
	}

	public function saveNBT(){
		$this->namedtag["Pos"][0] = $this->x;
		$this->namedtag["Pos"][1] = $this->y;
		$this->namedtag["Pos"][2] = $this->z;

		$this->namedtag["Motion"][0] = $this->motionX;
		$this->namedtag["Motion"][1] = $this->motionY;
		$this->namedtag["Motion"][2] = $this->motionZ;

		$this->namedtag["Rotation"][0] = $this->yaw;
		$this->namedtag["Rotation"][1] = $this->pitch;

		$this->namedtag["FallDistance"] = $this->fallDistance;
		$this->namedtag["Fire"] = $this->fireTicks;
		$this->namedtag["Air"] = $this->airTicks;
		$this->namedtag["OnGround"] = $this->onGround == true ? 1 : 0;
		$this->namedtag["Invulnerable"] = $this->invulnerable == true ? 1 : 0;
	}

	protected abstract function initEntity();

	public function spawnTo(Player $player){
		if(!isset($this->hasSpawned[$player->getID()]) and $player->chunksLoaded[$this->chunkIndex] !== 0xff){
			$this->hasSpawned[$player->getID()] = $player;
		}
	}

	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getID()])){
			$pk = new RemoveEntityPacket;
			$pk->eid = $this->id;
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getID()]);
		}
	}

	abstract function attack($damage, $source = "generic");

	abstract function heal($amount, $source = "generic");

	/**
	 * @return int
	 */
	public function getHealth(){
		return $this->health;
	}

	/**
	 * Sets the health of the Entity. This won't send any update to the players
	 *
	 * @param int $amount
	 */
	public function setHealth($amount){
		if($amount < 0){
			$this->health = 0;
			$this->dead = true;
		}elseif($amount > $this->getMaxHealth()){
			$this->health = $this->getMaxHealth();
		}else{
			$this->health = (int) $amount;
		}
	}

	/**
	 * @return int
	 */
	public function getMaxHealth(){
		return $this->maxHealth;
	}

	/**
	 * @param int $amount
	 */
	public function setMaxHealth($amount){
		$this->maxHealth = (int) $amount;
		$this->health = (int) min($this->health, $this->maxHealth);
	}

	public function onUpdate(){
		if($this->closed !== false){
			return false;
		}

		$timeNow = microtime(true);
		$this->ticksLived += ($timeNow - $this->lastUpdate) * 20;

		if($this->handleWaterMovement()){
			$this->fallDistance = 0;
			$this->inWater = true;
			$this->extinguish();
		}else{
			$this->inWater = false;
		}

		if($this->fireTicks > 0){
			if($this->fireProof === true){
				$this->fireTicks -= 4;
				if($this->fireTicks < 0){
					$this->fireTicks = 0;
				}
			}else{
				if(($this->fireTicks % 20) === 0){
					$this->attackEntity(1, "onFire");
				}
				--$this->fireTicks;
			}
		}

		if($this->handleLavaMovement()){
			$this->attackEntity(4, "lava");
			$this->setOnFire(15);
			$this->fallDistance *= 0.5;
		}

		if($this->y < -64){
			$this->kill();
		}

		if($this->x !== $this->lastX or $this->y !== $this->lastY or $this->z !== $this->lastZ or $this->yaw !== $this->lastYaw or $this->pitch !== $this->lastPitch){
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			if($this instanceof Human){
				$pk = new MovePlayerPacket;
				$pk->eid = $this->id;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->yaw = $this->yaw;
				$pk->pitch = $this->pitch;
				$pk->bodyYaw = $this->yaw;
			}else{
				$pk = new MoveEntityPacket_PosRot;
				$pk->eid = $this->id;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->yaw = $this->yaw;
				$pk->pitch = $this->pitch;
			}
			Player::broadcastPacket($this->hasSpawned, $pk);
		}

		if($this->motionChanged === true){
			$this->motionChanged = false;

			$pk = new SetEntityMotionPacket;
			$pk->eid = $this->id;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			Player::broadcastPacket($this->hasSpawned, $pk);
		}

		$this->lastUpdate = $timeNow;

		return false;
	}

	public final function scheduleUpdate(){
		Entity::$needUpdate[$this->id] = $this;
	}

	public function setOnFire($seconds){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
	}

	public function getDirection(){
		$rotation = ($this->yaw - 90) % 360;
		if($rotation < 0){
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2; //North
		}elseif(45 <= $rotation and $rotation < 135){
			return 3; //East
		}elseif(135 <= $rotation and $rotation < 225){
			return 0; //South
		}elseif(225 <= $rotation and $rotation < 315){
			return 1; //West
		}else{
			return null;
		}
	}

	public function extinguish(){
		$this->fireTicks = 0;
	}

	public function canTriggerWalking(){
		return true;
	}

	protected function updateFallState($distanceThisTick, $onGround){
		if($onGround === true){
			if($this->fallDistance > 0){
				if($this instanceof Living){
					//TODO
				}

				$this->fall($this->fallDistance);
				$this->fallDistance = 0;
			}
		}elseif($distanceThisTick < 0){
			$this->fallDistance -= $distanceThisTick;
		}
	}

	public function getBoundingBox(){
		return $this->boundingBox;
	}

	public function fall($fallDistance){ //TODO

	}

	public function handleWaterMovement(){ //TODO

	}

	public function handleLavaMovement(){ //TODO

	}

	public function getEyeHeight(){
		return 0;
	}

	public function moveFlying(){ //TODO

	}

	public function onCollideWithPlayer(Human $entityPlayer){

	}

	protected function switchLevel(Level $targetLevel){
		if($this->isValid()){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityLevelChangeEvent($this, $this->getLevel(), $targetLevel));
			if($ev->isCancelled()){
				return false;
			}
			unset($this->getLevel()->entities[$this->id]);
			unset($this->getLevel()->chunkEntities[$this->chunkIndex][$this->id]);
			$this->despawnFromAll();
			if($this instanceof Player){
				foreach($this->chunksLoaded as $index => $Yndex){
					if($Yndex !== 0xff){
						$X = null;
						$Z = null;
						LevelFormat::getXZ($index, $X, $Z);
						foreach($this->getLevel()->getChunkEntities($X, $Z) as $entity){
							$entity->despawnFrom($this);
						}
					}
				}
				$this->getLevel()->freeAllChunks($this);
			}
		}
		$this->setLevel($targetLevel, true); //Hard reference
		$this->getLevel()->entities[$this->id] = $this; //Oops, TODO!!
		if($this instanceof Player){
			$this->chunksLoaded = array();
			$pk = new SetTimePacket();
			$pk->time = $this->getLevel()->getTime();
			$pk->started = $this->getLevel()->stopTime == false;
			$this->dataPacket($pk);
		}
		$this->spawnToAll();
		$this->chunkIndex = false;
	}

	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	public function move(Vector3 $displacement){
		if($displacement->x == 0 and $displacement->y == 0 and $displacement->z == 0){
			return;
		}

		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		$this->scheduleUpdate();
	}

	public function setPositionAndRotation(Vector3 $pos, $yaw, $pitch){
		if($this->setPosition($pos) === true){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	public function setRotation($yaw, $pitch){
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->scheduleUpdate();
	}

	public function setPosition(Vector3 $pos){
		if($pos instanceof Position and $pos->getLevel() instanceof Level and $pos->getLevel() !== $this->getLevel()){
			if($this->switchLevel($pos->getLevel()) === false){
				return false;
			}
		}
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityMoveEvent($this, $pos));
		if($ev->isCancelled()){
			return false;
		}
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$radius = $this->width / 2;
		if(($index = LevelFormat::getIndex($this->x >> 4, $this->z >> 4)) !== $this->chunkIndex){
			if($this->chunkIndex !== false){
				unset($this->getLevel()->chunkEntities[$this->chunkIndex][$this->id]);
			}
			$this->chunkIndex = $index;
			$this->getLevel()->loadChunk($this->x >> 4, $this->z >> 4);

			$newChunk = $this->getLevel()->getUsingChunk($this->x >> 4, $this->z >> 4);
			foreach($this->hasSpawned as $player){
				if(!isset($newChunk[$player->CID])){
					$this->despawnFrom($player);
				}else{
					unset($newChunk[$player->CID]);
				}
			}
			foreach($newChunk as $player){
				$this->spawnTo($player);
			}

			$this->getLevel()->chunkEntities[$this->chunkIndex][$this->id] = $this;
		}
		$this->boundingBox->setBounds($pos->x - $radius, $pos->y, $pos->z - $radius, $pos->x + $radius, $pos->y + $this->height, $pos->z + $radius);

		$this->scheduleUpdate();

		return true;
	}

	public function getMotion(){
		return new Vector3($this->motionX, $this->motionY, $this->motionZ);
	}

	public function setMotion(Vector3 $motion){
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
		if($ev->isCancelled()){
			return false;
		}
		$this->motionX = $motion->x;
		$this->motionY = $motion->y;
		$this->motionZ = $motion->z;
		$this->scheduleUpdate();
	}

	public function isOnGround(){
		return $this->onGround === true;
	}

	public function kill(){
		$this->dead = true;
	}

	public function teleport(Vector3 $pos, $yaw = false, $pitch = false){
		$this->setMotion(new Vector3(0, 0, 0));
		if($this->setPositionAndRotation($pos, $yaw === false ? $this->yaw : $yaw, $pitch === false ? $this->pitch : $pitch) !== false){
			if($this instanceof Player){
				$this->airTicks = 300;
				$this->fallDistance = 0;
				$this->orderChunks();
				$this->getNextChunk(true);
				$this->forceMovement = $pos;

				$pk = new MovePlayerPacket;
				$pk->eid = 0;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->bodyYaw = $this->yaw;
				$pk->pitch = $this->pitch;
				$pk->yaw = $this->yaw;
				$this->dataPacket($pk);
			}

			return true;
		}

		return false;
	}

	public function getID(){
		return $this->id;
	}

	public function spawnToAll(){
		foreach($this->getLevel()->getPlayers() as $player){
			if(isset($player->id) and $player->spawned === true){
				$this->spawnTo($player);
			}
		}
	}

	public function despawnFromAll(){
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}

	public function close(){
		if($this->closed === false){
			$this->closed = true;
			unset(Entity::$needUpdate[$this->id]);
			unset($this->getLevel()->entities[$this->id]);
			unset($this->getLevel()->chunkEntities[$this->chunkIndex][$this->id]);
			unset(Entity::$list[$this->id]);
			$this->despawnFromAll();
			Server::getInstance()->getPluginManager()->callEvent(new EntityDespawnEvent($this));
		}
	}

	abstract public function getData();

	public function __destruct(){
		$this->close();
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

}