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
	private $id;
	
	//public $passenger = null;
	//public $vehicle = null;
	
	public $lastX;
	public $lastY;
	public $lastZ;
	public $velocity;
	public $yaw;
	public $pitch;
	public $lastYaw;
	public $lastPitch;
	public $boundingBox;
	public $onGround;
	public $positionChanged;
	public $velocityChanged;
	public $dead;
	public $height;
	public $width;
	public $length;
	public $fallDistance;
	public $ticksLived;
	public $lastUpdate;
	public $maxFireTicks;
	public $fireTicks;
	protected $inWater;
	public $noDamageTicks;
	private $justCreated;
	protected $fireProof;
	private $invulnerable;
	
	public static function getEntity($entityID){
		return isset(Entity::$list[$entityID]) ? Entity::$list[$entityID]:false;
	}
	
	
	public function __construct(Level $level){
		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->level = $level;
		$this->setPosition(new Vector3(0, 0, 0));
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		Entity::$list[$this->id] = $this;
		$this->lastUpdate = microtime(true);
		$this->initEntity();
	}
	
	protected abstract function initEntity();
	
	public abstract function spawnTo(Player $player);
	
	abstract function attackEntity($damage, $source = "generic");
	
	public function onEntityUpdate(){
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
	}
	
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
	
	}
	
	public function onCollideWithPlayer(EntityPlayer $entityPlayer){
	
	}
	
	
	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}
	
	public function setPosition(Vector3 $pos){
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;
	}
	
	public function setVelocity(Vector3 $velocity){
		$this->velocity = clone $velocity;
	}
	
	public function getVelocity(){
		return clone $this->velocity;
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
	
	public function teleport(Position $pos){
	
	}
	
	public function equals($object){
		return $object instanceof Entity ? $object->getID() === $this->id : false;
	}
	
	public function getID(){
		return $this->id;
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
/***REM_END***/