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

use pocketmine\item\Item;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\Player;

class DroppedItem extends Entity{

	protected $age = 0;
	protected $owner = null;
	protected $thrower = null;
	protected $pickupDelay = 0;
	/** @var Item */
	protected $item;

	protected function initEntity(){
		//TODO: upgrade old numeric entity ids
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
		$this->item = Item::get($this->namedtag->Item["id"], $this->namedtag->Item["Damage"], min(64, $this->namedtag->Item["Count"]));
	}

	public function attack($damage, $source = "generic"){

	}

	public function heal($amount, $source = "generic"){

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
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => 0),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		];
	}

	/**
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
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
		$pk->metadata = $this->getData();
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}