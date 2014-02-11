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
	private static $entityCount = 1;
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
	//public $boundingBox;
	public $onGround;
	public $positionChanged;
	public $velocityChanged;
	public $dead;
	public $height;
	public $width;
	public $length;
	public $fallDistance;
	public $ticksLived;
	public $maxFireTicks;
	public $fireTicks;
	protected $inWater;
	public $noDamageTicks;
	private $justCreated;
	protected $fireProof;
	private $invulnerable;
	
	
	public function __construct(Level $level){
		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->level = $level;
	}
	
	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
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
require_once("entity/AttachableEntity.php");
require_once("entity/ExplosiveEntity.php");
require_once("entity/ColorableEntity.php");

require_once("entity/LivingEntity.php");
require_once("entity/CreatureEntity.php");
require_once("entity/MonsterEntity.php");
require_once("entity/AgeableEntity.php");
require_once("entity/AnimalEntity.php");
require_once("entity/HumanEntity.php");
require_once("entity/ProjectileEntity.php");
require_once("entity/VehicleEntity.php");
require_once("entity/HangingEntity.php");
/***REM_END***/