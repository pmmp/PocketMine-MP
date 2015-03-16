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
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\Network;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Human extends Creature implements ProjectileSource, InventoryHolder{

	protected $nameTag = "TESTIFICATE";
	/** @var PlayerInventory */
	protected $inventory;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

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
				$this->inventory->setItem($this->inventory->getSize() + $item["Slot"] - 100, ItemItem::get($item["id"], $item["Damage"], $item["Count"]), $this);
			}else{
				$this->inventory->setItem($item["Slot"] - 9, ItemItem::get($item["id"], $item["Damage"], $item["Count"]), $this);
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
					if($item->getId() !== 0 and $item->getCount() > 0){
						$this->namedtag->Inventory[$slot] = new Compound(false, [
							new Byte("Count", $item->getCount()),
							new Short("Damage", $item->getDamage()),
							new Byte("Slot", $slot),
							new Byte("TrueSlot", $hotbarSlot),
							new Short("id", $item->getId()),
						]);
						continue;
					}
				}
				$this->namedtag->Inventory[$slot] = new Compound(false, [
					new Byte("Count", 0),
					new Short("Damage", 0),
					new Byte("Slot", $slot),
					new Byte("TrueSlot", -1),
					new Short("id", 0),
				]);
			}

			//Normal inventory
			$slotCount = Player::SURVIVAL_SLOTS + 9;
			//$slotCount = (($this instanceof Player and ($this->gamemode & 0x01) === 1) ? Player::CREATIVE_SLOTS : Player::SURVIVAL_SLOTS) + 9;
			for($slot = 9; $slot < $slotCount; ++$slot){
				$item = $this->inventory->getItem($slot - 9);
				$this->namedtag->Inventory[$slot] = new Compound(false, [
					new Byte("Count", $item->getCount()),
					new Short("Damage", $item->getDamage()),
					new Byte("Slot", $slot),
					new Short("id", $item->getId()),
				]);
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->inventory->getItem($this->inventory->getSize() + $slot - 100);
				if($item instanceof ItemItem and $item->getId() !== ItemItem::AIR){
					$this->namedtag->Inventory[$slot] = new Compound(false, [
						new Byte("Count", $item->getCount()),
						new Short("Damage", $item->getDamage()),
						new Byte("Slot", $slot),
						new Short("id", $item->getId()),
					]);
				}
			}
		}
	}

	public function spawnTo(Player $player){
		if($player !== $this and !isset($this->hasSpawned[$player->getId()])){
			$this->hasSpawned[$player->getId()] = $player;

			$pk = new AddPlayerPacket();
			$pk->clientID = 0;
			if($player->getRemoveFormat()){
				$pk->username = TextFormat::clean($this->nameTag);
			}else{
				$pk->username = $this->nameTag;
			}
			$pk->eid = $this->getId();
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$item = $this->getInventory()->getItemInHand();
			$pk->item = $item->getId();
			$pk->meta = $item->getDamage();
			$pk->metadata = $this->getData();
			$player->dataPacket($pk);

			$player->addEntityMotion($this->getId(), $this->motionX, $this->motionY, $this->motionZ);

			$this->inventory->sendArmorContents($player);
		}
	}

	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getId()])){
			$pk = new RemovePlayerPacket();
			$pk->eid = $this->id;
			$pk->clientID = 0;
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getId()]);
		}
	}

	public function getData(){ //TODO
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1 : 0;
		$flags |= $this->hasEffect(Effect::INVISIBILITY) ? 1 << 5 : 0;
		//$flags |= ($this->crouched === true ? 0b10:0) << 1;
		//$flags |= ($this->inAction === true ? 0b10000:0);
		$d = [
			0 => ["type" => 0, "value" => $flags],
			1 => ["type" => 1, "value" => $this->airTicks],
			3 => ["type" => 0, "value" => $this->hasEffect(Effect::INVISIBILITY) ? 0 : 1],
			16 => ["type" => 0, "value" => 0],
			17 => ["type" => 6, "value" => [0, 0, 0]],
		];

		return $d;
	}

	public function close(){
		if(!$this->closed){
			if(!($this instanceof Player) or $this->loggedIn){
				foreach($this->inventory->getViewers() as $viewer){
					$viewer->removeWindow($this->inventory);
				}
			}
			parent::close();
		}
	}

}
