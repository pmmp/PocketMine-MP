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
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\Player;

class DroppedItem extends Entity{

	protected $owner = null;
	protected $thrower = null;
	protected $pickupDelay = 0;
	/** @var Item */
	protected $item;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.04;
	protected $drag = 0.02;

	protected function initEntity(){
		$this->namedtag->id = new String("id", "Item");
		$this->setMaxHealth(5);
		$this->setHealth(@$this->namedtag["Health"]);
		if(isset($this->namedtag->Age)){
			$this->age = $this->namedtag["Age"];
		}
		if(isset($this->namedtag->PickupDelay)){
			$this->pickupDelay = $this->namedtag["PickupDelay"];
		}
		if(isset($this->namedtag->Owner)){
			$this->owner = $this->namedtag["Owner"];
		}
		if(isset($this->namedtag->Thrower)){
			$this->thrower = $this->namedtag["Thrower"];
		}
		$this->item = Item::get($this->namedtag->Item["id"], $this->namedtag->Item["Damage"], $this->namedtag->Item["Count"]);
	}

	public function onUpdate(){
		$this->entityBaseTick();

		if($this->closed !== false){
			return false;
		}

		if($this->pickupDelay > 0 and $this->pickupDelay < 32767){ //Infinite delay
			--$this->pickupDelay;
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

		if($this->age > 6000){
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
		$this->namedtag->Item = new Compound("Item", [
			"id" => new Short("id", $this->item->getID()),
			"Damage" => new Short("Damage", $this->item->getDamage()),
			"Count" => new Byte("Count", $this->item->getCount())
		]);
		$this->namedtag->Health = new Short("Health", $this->getHealth());
		$this->namedtag->Age = new Short("Age", $this->age);
		$this->namedtag->PickupDelay = new Short("PickupDelay", $this->pickupDelay);
		if($this->owner !== null){
			$this->namedtag->Owner = new String("Owner", $this->owner);
		}
		if($this->thrower !== null){
			$this->namedtag->Thrower = new String("Thrower", $this->thrower);
		}
	}

	public function getData(){
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1 : 0;

		return [
			0 => array("type" => 0, "value" => $flags)
		];
	}

	/**
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	/**
	 * @return int
	 */
	public function getPickupDelay(){
		return $this->pickupDelay;
	}

	/**
	 * @param int $delay
	 */
	public function setPickupDelay($delay){
		$this->pickupDelay = $delay;
	}

	/**
	 * @return string
	 */
	public function getOwner(){
		return $this->owner;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner($owner){
		$this->owner = $owner;
	}

	/**
	 * @return string
	 */
	public function getThrower(){
		return $this->thrower;
	}

	/**
	 * @param string $thrower
	 */
	public function setThrower($thrower){
		$this->thrower = $thrower;
	}

	public function spawnTo(Player $player){
		$pk = new AddItemEntityPacket();
		$pk->eid = $this->getID();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->roll = 0;
		$pk->item = $this->getItem();
		$player->dataPacket($pk);

		$pk = new SetEntityMotionPacket;
		$pk->entities = [
			[$this->getID(), $this->motionX, $this->motionY, $this->motionZ]
		];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}