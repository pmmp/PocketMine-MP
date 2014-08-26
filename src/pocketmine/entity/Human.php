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

use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\Network;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Human extends Creature implements ProjectileSource, InventoryHolder{

	protected $nameTag = "TESTIFICATE";
	/** @var PlayerInventory */
	protected $inventory;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

	public function getInventory(){
		return $this->inventory;
	}

	protected function initEntity(){

		$this->inventory = new PlayerInventory($this);
		if($this instanceof Player){
			$this->addWindow($this->inventory, 0);
		}


		if(isset($this->namedtag->NameTag)){
			$this->nameTag = $this->namedtag["NameTag"];
		}

		foreach($this->namedtag->Inventory as $item){
			if($item["Slot"] >= 0 and $item["Slot"] < 9){ //Hotbar
				$this->inventory->setHotbarSlotIndex($item["Slot"], isset($item["TrueSlot"]) ? $item["TrueSlot"] : -1);
			}elseif($item["Slot"] >= 100 and $item["Slot"] < 104){ //Armor
				$this->inventory->setItem($this->inventory->getSize() + $item["Slot"] - 100, Item::get($item["id"], $item["Damage"], $item["Count"]), $this);
			}else{
				$this->inventory->setItem($item["Slot"] - 9, Item::get($item["id"], $item["Damage"], $item["Count"]), $this);
			}
		}

		parent::initEntity();
	}

	public function getName(){
		return $this->nameTag;
	}

	public function getDrops(){
		$drops = [];
		if($this->inventory instanceof PlayerInventory){
			foreach($this->inventory->getContents() as $item){
				$drops[] = $item;
			}

			foreach($this->inventory->getArmorContents() as $item){
				$drops[] = $item;
			}
		}

		return $drops;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Inventory = new Enum("Inventory", []);
		$this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
		if($this->inventory instanceof PlayerInventory){
			for($slot = 0; $slot < 9; ++$slot){
				$hotbarSlot = $this->inventory->getHotbarSlotIndex($slot);
				if($hotbarSlot !== -1){
					$item = $this->inventory->getItem($hotbarSlot);
					if($item->getID() !== 0 and $item->getCount() > 0){
						$this->namedtag->Inventory[$slot] = new Compound(false, array(
							new Byte("Count", $item->getCount()),
							new Short("Damage", $item->getDamage()),
							new Byte("Slot", $slot),
							new Byte("TrueSlot", $hotbarSlot),
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
				$item = $this->inventory->getItem($slot - 9);
				$this->namedtag->Inventory[$slot] = new Compound(false, array(
					new Byte("Count", $item->getCount()),
					new Short("Damage", $item->getDamage()),
					new Byte("Slot", $slot),
					new Short("id", $item->getID()),
				));
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->inventory->getItem($this->inventory->getSize() + $slot - 100);
				if($item instanceof Item and $item->getID() !== Item::AIR){
					$this->namedtag->Inventory[$slot] = new Compound(false, array(
						new Byte("Count", $item->getCount()),
						new Short("Damage", $item->getDamage()),
						new Byte("Slot", $slot),
						new Short("id", $item->getID()),
					));
				}
			}
		}
	}

	public function spawnTo(Player $player){
		if($player !== $this and !isset($this->hasSpawned[$player->getID()])){
			$this->hasSpawned[$player->getID()] = $player;

			$pk = new AddPlayerPacket;
			$pk->clientID = 0;
			if($player->getRemoveFormat()){
				$pk->username = TextFormat::clean($this->nameTag);
			}else{
				$pk->username = $this->nameTag;
			}
			$pk->eid = $this->getID();
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->unknown1 = 0;
			$pk->unknown2 = 0;
			$pk->metadata = $this->getData();
			$player->dataPacket($pk);

			$pk = new SetEntityMotionPacket;
			$pk->entities = [
				[$this->getID(), $this->motionX, $this->motionY, $this->motionZ]
			];
			$player->dataPacket($pk);

			$this->inventory->sendHeldItem($player);

			$this->inventory->sendArmorContents($player);
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

}
