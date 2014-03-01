<?php

/**
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

abstract class Entity extends Position{
	public static $entityCount = 1;
	public static $list = array();
	public static $needUpdate = array();
	
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
	
	public $closed;
	
	public static function get($entityID){
		return isset(Entity::$list[$entityID]) ? Entity::$list[$entityID]:false;
	}
	
	public static function getAll(){
		return Entity::$list;
	}
	
	
	public function __construct(Level $level, NBTTag_Compound $nbt){
		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->closed = false;
		$this->namedtag = $nbt;
		$this->level = $level;

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->setPositionAndRotation(new Vector3($this->namedtag->Pos[0], $this->namedtag->Pos[1], $this->namedtag->Pos[2]), $this->namedtag->Rotation[0], $this->namedtag->Rotation[1]);
		$this->setMotion(new Vector3($this->namedtag->Motion[0], $this->namedtag->Motion[1], $this->namedtag->Motion[2]));
		
		$this->fallDistance = $this->namedtag->FallDistance;
		$this->fireTicks = $this->namedtag->Fire;
		$this->airTicks = $this->namedtag->Air;
		$this->onGround = $this->namedtag->OnGround > 0 ? true:false;
		$this->invulnerable = $this->namedtag->Invulnerable > 0 ? true:false;
		
		$index = PMFLevel::getIndex($this->x >> 4, $this->z >> 4);
		$this->chunkIndex = $index;
		Entity::$list[$this->id] = $this;
		$this->level->entities[$this->id] = $this;
		$this->level->chunkEntities[$this->chunkIndex][$this->id] = $this;
		$this->lastUpdate = microtime(true);
		$this->initEntity();
		$this->server->api->dhandle("entity.add", $this);
	}
	
	public function saveNBT(){
		$this->namedtag->Pos[0] = $this->x;
		$this->namedtag->Pos[1] = $this->y;
		$this->namedtag->Pos[2] = $this->z;
		
		$this->namedtag->Motion[0] = $this->motionX;
		$this->namedtag->Motion[1] = $this->motionY;
		$this->namedtag->Motion[2] = $this->motionZ;
		
		$this->namedtag->Rotation[0] = $this->yaw;
		$this->namedtag->Rotation[1] = $this->pitch;
		
		$this->namedtag->FallDistance = $this->fallDistance;
		$this->namedtag->Fire = $this->fireTicks;
		$this->namedtag->Air = $this->airTicks;
		$this->namedtag->OnGround = $this->onGround == true ? 1:0;
		$this->namedtag->Invulnerable = $this->invulnerable == true ? 1:0;
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
	
	public function onUpdate(){
		if($this->closed !== false){
			return false;
		}
		$timeNow = microtime(true);
		$this->ticksLived += ($now - $this->lastUpdate) * 20;
		$this->lastUpdate = $timeNow;
		
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
		return false;
	}
	
	public final function scheduleUpdate(){
		Entity::$needUpdate[$this->id] = $this;
	}
	
	public abstract function getMetadata();
	
	public function setOnFire($seconds){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
	}
	
	public function extinguish(){
		$this->fireTicks = 0;
	}
	
	public function moveEntity(Vector3 $displacement){ //TODO
	
	}
	
	public function canTriggerWalking(){
		return true;
	}
	
	protected function updateFallState($distanceThisTick, $onGround){
		if($onGround === true){
			if($this->fallDistance > 0){
				if($this instanceof EntityLiving){
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
	
	public function setPositionAndRotation(Vector3 $pos, $yaw, $pitch){ //TODO
		if($this->setPosition($pos) === true){
			$this->yaw = $yaw;
			$this->pitch = $pitch;
			return true;
		}
		return false;
	}
	
	public function onCollideWithPlayer(EntityPlayer $entityPlayer){
	
	}
	
	protected function switchLevel(Level $targetLevel){
		if($this->level instanceof Level){
			if(EventHandler::callEvent(new EntityLevelChangeEvent($this, $this->level, $targetLevel)) === BaseEvent::DENY){
				return false;
			}
			unset($this->level->entities[$this->id]);
			unset($this->level->chunkEntities[$this->chunkIndex][$this->id]);
			$this->despawnFromAll();
			if($this instanceof Player){
				foreach($this->chunksLoaded as $index => $Yndex){
					if($Yndex !== 0xff){
						$X = null;
						$Z = null;
						PMFLevel::getXZ($index, $X, $Z);
						foreach($this->level->getChunkEntities($X, $Z) as $entity){
							$entity->despawnFrom($this);
						}
					}
				}
				$this->level->freeAllChunks($this);
			}
		}
		$this->level = $targetLevel;
		$this->level->entities[$this->id] = $this;
		if($this instanceof Player){
			$this->chunksLoaded = array();
			$pk = new SetTimePacket;
			$pk->time = $this->level->getTime();
			$this->dataPacket($pk);		
		}
		$this->spawnToAll();
		$this->chunkIndex = false;
	}
	
	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}
	
	public function setPosition(Vector3 $pos){
		if($pos instanceof Position and $pos->level instanceof Level and $pos->level !== $this->level){
			if($this->switchLevel($pos->level) === false){
				return false;
			}
		}
		if(EventHandler::callEvent(new EntityMoveEvent($this, $pos)) === BaseEvent::DENY){
			return false;
		}
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;
		
		$radius = $this->width / 2;
		if(($index = PMFLevel::getIndex($this->x >> 4, $this->z >> 4)) !== $this->chunkIndex){
			if($this->chunkIndex !== false){
				unset($this->level->chunkEntities[$this->chunkIndex][$this->id]);
			}
			$this->chunkIndex = $index;
			$this->level->loadChunk($this->x >> 4, $this->z >> 4);
			$this->level->chunkEntities[$this->chunkIndex][$this->id] = $this;
		}
		$this->boundingBox->setBounds($pos->x - $radius, $pos->y, $pos->z - $radius, $pos->x + $radius, $pos->y + $this->height, $pos->z + $radius);
		
		if($this instanceof HumanEntity){
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
		foreach($this->hasSpawned as $p){
			$p->dataPacket(clone $pk);
		}
		return true;
	}
	
	public function getMotion(){
		return new Vector3($this->motionX, $this->motionY, $this->motionZ);
	}
	
	public function setMotion(Vector3 $motion){
		if(EventHandler::callEvent(new EntityMotionEvent($this, $motion)) === BaseEvent::DENY){
			return false;
		}
		$this->motionX = $motion->x;
		$this->motionY = $motion->y;
		$this->motionZ = $motion->z;
	}
	
	public function isOnGround(){
		return $this->onGround === true;
	}
	
	public function kill(){
		$this->dead = true;
	}
	
	public function getLevel(){
		return $this->level;
	}
	
	public function teleport(Position $pos, $yaw = false, $pitch = false){
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
		foreach($this->level->getPlayers() as $player){
			if($player->eid !== false or $player->spawned !== true){
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
			unset($this->level->entities[$this->id]);
			unset($this->level->chunkEntities[$this->chunkIndex][$this->id]);	
			unset(Entity::$list[$this->id]);
			$this->despawnFromAll();
			$this->server->api->dhandle("entity.remove", $this);
		}	
	}
	
	public function __destruct(){
		$this->close();
	}
	
}

/***REM_START***/
require_once("entity/DamageableEntity.php");
require_once("entity/ProjectileSourceEntity.php");
require_once("entity/RideableEntity.php");
require_once("entity/TameableEntity.php");
require_once("entity/AttachableEntity.php");
require_once("entity/AgeableEntity.php");
require_once("entity/ExplosiveEntity.php");
require_once("entity/ColorableEntity.php");

require_once("entity/LivingEntity.php");
require_once("entity/CreatureEntity.php");
require_once("entity/MonsterEntity.php");
require_once("entity/AnimalEntity.php");
require_once("entity/HumanEntity.php");
require_once("entity/ProjectileEntity.php");
require_once("entity/VehicleEntity.php");
require_once("entity/HangingEntity.php");

require_once("entity/HumanEntity.php");
require_once("entity/PlayerEntity.php");
/***REM_END***/