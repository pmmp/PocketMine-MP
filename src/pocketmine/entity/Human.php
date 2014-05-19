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

use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerEquipmentPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\Network;
use pocketmine\Player;
use pocketmine\Server;

class Human extends Creature implements ProjectileSource, InventorySource{

	protected $nameTag = "TESTIFICATE";
	protected $inventory = array();
	public $slot;
	protected $hotbar = array();
	protected $armor = array();

	protected function initEntity(){
		if(isset($this->namedtag->NameTag)){
			$this->nameTag = $this->namedtag["NameTag"];
		}
		$this->hotbar = array(-1, -1, -1, -1, -1, -1, -1, -1, -1);
		$this->armor = array(
			0 => Item::get(Item::AIR, 0, 0),
			1 => Item::get(Item::AIR, 0, 0),
			2 => Item::get(Item::AIR, 0, 0),
			3 => Item::get(Item::AIR, 0, 0)
		);

		foreach($this->namedtag->Inventory as $item){
			if($item["Slot"] >= 0 and $item["Slot"] < 9){ //Hotbar
				$this->hotbar[$item["Slot"]] = isset($item["TrueSlot"]) ? $item["TrueSlot"] : -1;
			}elseif($item["Slot"] >= 100 and $item["Slot"] < 104){ //Armor
				$this->armor[$item["Slot"] - 100] = Item::get($item["id"], $item["Damage"], $item["Count"]);
			}else{
				$this->inventory[$item["Slot"] - 9] = Item::get($item["id"], $item["Damage"], $item["Count"]);
			}
		}
		$this->slot = $this->hotbar[0];

		$this->height = 1.8; //Or 1.62?
		$this->width = 0.6;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Inventory = new Enum("Inventory", array());
		$this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
		for($slot = 0; $slot < 9; ++$slot){
			if(isset($this->hotbar[$slot]) and $this->hotbar[$slot] !== -1){
				$item = $this->getSlot($this->hotbar[$slot]);
				if($item->getID() !== 0 and $item->getCount() > 0){
					$this->namedtag->Inventory[$slot] = new Compound(false, array(
						new Byte("Count", $item->getCount()),
						new Short("Damage", $item->getMetadata()),
						new Byte("Slot", $slot),
						new Byte("TrueSlot", $this->hotbar[$slot]),
						new Short("id", $item->getID()),
					));
					continue;
				}
			}
			$this->namedtag->Inventory[$slot] = new Compound(false, array(
				new Byte("Count", 0),
				new Short("Damage", 0),
				new Byte("Slot", $slot),
				new Byte("TrueSlot", -1),
				new Short("id", 0),
			));
		}

		//Normal inventory
		$slotCount = Player::SURVIVAL_SLOTS + 9;
		//$slotCount = (($this instanceof Player and ($this->gamemode & 0x01) === 1) ? Player::CREATIVE_SLOTS : Player::SURVIVAL_SLOTS) + 9;
		for($slot = 9; $slot < $slotCount; ++$slot){
			$item = $this->getSlot($slot - 9);
			$this->namedtag->Inventory[$slot] = new Compound(false, array(
				new Byte("Count", $item->getCount()),
				new Short("Damage", $item->getMetadata()),
				new Byte("Slot", $slot),
				new Short("id", $item->getID()),
			));
		}

		//Armor
		for($slot = 100; $slot < 104; ++$slot){
			$item = $this->armor[$slot - 100];
			if($item instanceof Item){
				$this->namedtag->Inventory[$slot] = new Compound(false, array(
					new Byte("Count", $item->getCount()),
					new Short("Damage", $item->getMetadata()),
					new Byte("Slot", $slot),
					new Short("id", $item->getID()),
				));
			}
		}
	}

	public function spawnTo(Player $player){
		if($player !== $this and !isset($this->hasSpawned[$player->getID()])){
			$this->hasSpawned[$player->getID()] = $player;

			$pk = new AddPlayerPacket;
			$pk->clientID = 0;
			$pk->username = $this->nameTag;
			$pk->eid = $this->id;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = 0;
			$pk->pitch = 0;
			$pk->unknown1 = 0;
			$pk->unknown2 = 0;
			$pk->metadata = $this->getData();
			$player->dataPacket($pk);

			$pk = new SetEntityMotionPacket;
			$pk->eid = $this->id;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			$player->dataPacket($pk);

			$this->sendCurrentEquipmentSlot($player);

			$this->sendArmor($player);
		}
	}

	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getID()])){
			$pk = new RemovePlayerPacket;
			$pk->eid = $this->id;
			$pk->clientID = 0;
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getID()]);
		}
	}

	public function setEquipmentSlot($equipmentSlot, $inventorySlot){
		$this->hotbar[$equipmentSlot] = $inventorySlot;
		if($equipmentSlot === $this->slot){
			foreach($this->hasSpawned as $p){
				$this->sendCurrentEquipmentSlot($p);
			}
		}
	}

	public function getEquipmentSlot($equipmentSlot){
		if(isset($this->hotbar[$equipmentSlot])){
			return $this->hotbar[$equipmentSlot];
		}

		return -1;
	}

	public function setCurrentEquipmentSlot($slot){
		if(isset($this->hotbar[$slot])){
			$this->slot = (int) $slot;
			foreach($this->hasSpawned as $p){
				$this->sendCurrentEquipmentSlot($p);
			}
		}
	}

	public function getCurrentEquipmentSlot(){
		return $this->slot;
	}

	public function getCurrentEquipment(){
		if($this->slot > -1) {
			return $this->hotbar[$this->slot];
		}
	}

	public function sendCurrentEquipmentSlot(Player $player){
		$pk = new PlayerEquipmentPacket;
		$pk->eid = $this->id;
		$pk->item = $this->getSlot($this->slot)->getID();
		$pk->meta = $this->getSlot($this->slot)->getMetadata();
		$pk->slot = 0;
		$player->dataPacket($pk);
	}

	public function setArmorSlot($slot, Item $item){
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this, $this->getArmorSlot($slot), $item, $slot));
		if($ev->isCancelled()){
			return false;
		}
		$this->armor[(int) $slot] = $ev->getNewItem();
		foreach($this->hasSpawned as $p){
			$this->sendArmor($p);
		}
		if($this instanceof Player){
			$this->sendArmor();
		}

		return true;
	}

	public function getArmorSlot($slot){
		$slot = (int) $slot;
		if(!isset($this->armor[$slot])){
			$this->armor[$slot] = Item::get(Item::AIR, 0, 0);
		}

		return $this->armor[$slot];
	}

	public function sendArmor($player = null){
		$slots = array();
		for($i = 0; $i < 4; ++$i){
			if(isset($this->armor[$i]) and ($this->armor[$i] instanceof Item) and $this->armor[$i]->getID() > Item::AIR){
				$slots[$i] = $this->armor[$i]->getID() !== Item::AIR ? $this->armor[$i]->getID() - 256 : 0;
			}else{
				$this->armor[$i] = Item::get(Item::AIR, 0, 0);
				$slots[$i] = 255;
			}
		}
		if($player instanceof Player){
			$pk = new PlayerArmorEquipmentPacket();
			$pk->eid = $this->id;
			$pk->slots = $slots;
			$player->dataPacket($pk);
		}elseif($this instanceof Player){
			$pk = new ContainerSetContentPacket;
			$pk->windowid = 0x78; //Armor window id
			$pk->slots = $this->armor;
			$this->dataPacket($pk);
		}
	}

	public function getData(){ //TODO
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1 : 0;
		//$flags |= ($this->crouched === true ? 0b10:0) << 1;
		//$flags |= ($this->inAction === true ? 0b10000:0);
		$d = array(
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => $this->airTicks),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		);

		/*if($this->class === ENTITY_MOB and $this->type === MOB_SHEEP){
			if(!isset($this->data["Sheared"])){
				$this->data["Sheared"] = 0;
				$this->data["Color"] = mt_rand(0,15);
			}
			$d[16]["value"] = (($this->data["Sheared"] == 1 ? 1:0) << 4) | ($this->data["Color"] & 0x0F);
		}elseif($this->type === OBJECT_PRIMEDTNT){
			$d[16]["value"] = (int) max(0, $this->data["fuse"] - (microtime(true) - $this->spawntime) * 20);
		}elseif($this->class === ENTITY_PLAYER){
			if($this->player->sleeping !== false){
				$d[16]["value"] = 2;
				$d[17]["value"] = array($this->player->sleeping->x, $this->player->sleeping->y, $this->player->sleeping->z);
			}
		}*/

		return $d;
	}

	public function attack($damage, $source = "generic"){

	}

	public function heal($amount, $source = "generic"){

	}

	public function hasItem(Item $item, $checkDamage = true){
		foreach($this->inventory as $s => $i){
			if($i->equals($item, $checkDamage)){
				return $i;
			}
		}

		return false;
	}

	public function canAddItem(Item $item){
		$inv = $this->inventory;
		while($item->getCount() > 0){
			$add = 0;
			foreach($inv as $s => $i){
				if($i->getID() === Item::AIR){
					$add = min($i->getMaxStackSize(), $item->getCount());
					$inv[$s] = clone $item;
					$inv[$s]->setCount($add);
					break;
				}elseif($i->equals($item)){
					$add = min($i->getMaxStackSize() - $i->getCount(), $item->getCount());
					if($add <= 0){
						continue;
					}
					$inv[$s] = clone $item;
					$inv[$s]->setCount($i->getCount() + $add);
					break;
				}
			}
			if($add <= 0){
				return false;
			}
			$item->setCount($item->getCount() - $add);
		}

		return true;
	}

	public function addItem(Item $item){
		while($item->getCount() > 0){
			$add = 0;
			foreach($this->inventory as $s => $i){
				if($i->getID() === Item::AIR){
					$add = min($i->getMaxStackSize(), $item->getCount());
					$i2 = clone $item;
					$i2->setCount($add);
					$this->setSlot($s, $i2);
					break;
				}elseif($i->equals($item)){
					$add = min($i->getMaxStackSize() - $i->getCount(), $item->getCount());
					if($add <= 0){
						continue;
					}
					$i2 = clone $item;
					$i2->setCount($i->getCount() + $add);
					$this->setSlot($s, $i2);
					break;
				}
			}
			if($add <= 0){
				return false;
			}
			$item->setCount($item->getCount() - $add);
		}

		return true;
	}

	public function canRemoveItem(Item $item, $checkDamage = true){
		return $this->hasItem($item, $checkDamage);
	}

	public function removeItem(Item $item, $checkDamage = true){
		while($item->getCount() > 0){
			$remove = 0;
			foreach($this->inventory as $s => $i){
				if($i->equals($item, $checkDamage)){
					$remove = min($item->getCount(), $i->getCount());
					if($item->getCount() < $i->getCount()){
						$i->setCount($i->getCount() - $item->getCount());
						$this->setSlot($s, $i);
					}else{
						$this->setSlot($s, Item::get(Item::AIR, 0, 0));
					}
					break;
				}
			}
			if($remove <= 0){
				return false;
			}
			$item->setCount($item->getCount() - $remove);
		}

		return true;
	}

	public function setSlot($slot, Item $item){
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this, $this->getSlot($slot), $item, $slot));
		if($ev->isCancelled()){
			return false;
		}
		$this->inventory[(int) $slot] = $ev->getNewItem();

		return true;
	}

	/**
	 * @param int $slot
	 *
	 * @return Item
	 */
	public function getSlot($slot){
		$slot = (int) $slot;
		if(!isset($this->inventory[$slot])){
			$this->inventory[$slot] = Item::get(Item::AIR, 0, 0);
		}

		return $this->inventory[$slot];
	}

	public function getAllSlots(){
		return $this->inventory;
	}

	public function getSlotCount(){
		return count($this->inventory);
	}
}
