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

namespace pocketmine\entity;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\Player;

class Arrow extends Projectile{
	const NETWORK_ID = 80;

	public $width = 0.5;
	public $length = 0.5;
	public $height = 0.5;
	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected function initEntity(){
		$this->setMaxHealth(1);
		$this->setHealth(1);
		if(isset($this->namedtag->Age)){
			$this->age = $this->namedtag["Age"];
		}


	}

	public function onUpdate(){
		$this->entityBaseTick();

		if($this->closed !== false){
			return false;
		}

		$this->motionY -= $this->gravity;

		$this->inBlock = $this->checkObstruction($this->x, ($this->boundingBox->minY + $this->boundingBox->maxY) / 2, $this->z);
		$this->move($this->motionX, $this->motionY, $this->motionZ);

		$friction = 1 - $this->drag;

		if($this->onGround){
			$friction = $this->getLevel()->getBlock(new Vector3($this->getFloorX(), $this->getFloorY() - 1, $this->getFloorZ()))->frictionFactor * $friction;
		}

		$this->motionX *= $friction;
		$this->motionY *= 1 - $this->drag;
		$this->motionZ *= $friction;

		if($this->onGround){
			$this->motionY *= -0.5;
		}

		if(abs($this->motionX) < 0.01){
			$this->motionX = 0;
		}
		if(abs($this->motionZ) < 0.01){
			$this->motionZ = 0;
		}

		if($this->age > 1200){
			$this->kill();
		}
		$this->updateMovement();

		//TODO: handle scheduled updates
		return true;
	}

	public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){

	}

	public function heal($amount){

	}

	public function saveNBT(){
		$this->namedtag->Age = new Short("Age", $this->age);
	}

	public function getData(){
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1 : 0;

		return [
			0 => array("type" => 0, "value" => $flags)
		];
	}

	public function canCollideWith(Entity $entity){
		return $entity instanceof Living;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Arrow::NETWORK_ID;
		$pk->eid = $this->getID();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->did = 0; //TODO: send motion here
		$player->dataPacket($pk);

		$pk = new SetEntityMotionPacket();
		$pk->entities = [
			[$this->getID(), $this->motionX, $this->motionY, $this->motionZ]
		];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}