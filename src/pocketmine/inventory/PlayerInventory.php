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

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerEquipmentPacket;
use pocketmine\Player;

class PlayerInventory extends BaseInventory{

	protected $itemInHandIndex = 0;
	/** @var int[] */
	protected $hotbar;

	public function __construct(Human $player){
		$this->hotbar = array_fill(0, $this->getHotbarSize(), -1);
		parent::__construct($player, InventoryType::get(InventoryType::PLAYER));
	}

	public function getSize(){
		return parent::getSize() - 4; //Remove armor slots
	}

	public function getHotbarSlotIndex($index){
		return ($index >= 0 and $index < $this->getHotbarSize()) ? $this->hotbar[$index] : -1;
	}

	public function setHotbarSlotIndex($index, $slot){
		if($index >= 0 and $index < $this->getHotbarSize() and $slot >= -1 and $slot < $this->getSize()){
			$this->hotbar[$index] = $slot;
		}
	}

	public function getHeldItemIndex(){
		return $this->itemInHandIndex;
	}

	public function setHeldItemIndex($index){
		if($index >= 0 and $index < $this->getHotbarSize()){
			$this->itemInHandIndex = $index;
			$item = $this->getItemInHand();

			$pk = new PlayerEquipmentPacket;
			$pk->eid = $this->getHolder()->getID();
			$pk->item = $item->getID();
			$pk->meta = $item->getDamage();
			$pk->slot = $this->getHeldItemIndex();

			foreach($this->getHolder()->getViewers() as $player){
				$player->dataPacket(clone $pk);
			}
		}
	}

	public function getItemInHand(){
		$item = $this->getItem($this->getHeldItemSlot());
		if($item instanceof Item){
			return $item;
		}else{
			return Item::get(Item::AIR);
		}
	}

	public function setItemInHand(Item $item){
		$this->setItem($this->getHeldItemSlot(), $item);
	}

	public function getHeldItemSlot(){
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	public function setHeldItemSlot($slot){
		if($slot >= 0 and $slot < $this->getSize()){
			$this->setHotbarSlotIndex($this->itemInHandIndex, $slot);
			$item = $this->getItemInHand();

			$pk = new PlayerEquipmentPacket;
			$pk->eid = $this->getHolder()->getID();
			$pk->item = $item->getID();
			$pk->meta = $item->getDamage();
			$pk->slot = 0;

			foreach($this->getHolder()->getViewers() as $player){
				$player->dataPacket(clone $pk);
			}
		}
	}

	public function onSlotChange($index, $before){
		parent::onSlotChange($index, $before);

		if($index >= $this->getSize()){
			$armor = $this->getArmorContents();
			$slots = [];

			foreach($armor as $i => $slot){
				if($slot->getID() === Item::AIR){
					$slots[$i] = 255;
				}else{
					$slots[$i] = $slot->getID();
				}
			}

			$pk = new PlayerArmorEquipmentPacket;
			$pk->eid = $this->getHolder()->getID();
			$pk->slots = $slots;

			if($index >= $this->getSize()){ //Armor change
				foreach($this->getHolder()->getViewers() as $player){
					if($player === $this->getHolder()){
						/** @var Player $player */
						$pk2 = new ContainerSetContentPacket;
						$pk2->windowid = 0x78; //Armor window id constant
						$pk2->slots = $armor;
						$player->dataPacket($pk2);
					}else{
						$player->dataPacket(clone $pk);
					}
				}
			}
		}
	}

	public function getHotbarSize(){
		return 9;
	}

	public function getHelmet(){
		return $this->getItem($this->getSize() + 3);
	}

	public function getChestplate(){
		return $this->getItem($this->getSize() + 2);
	}

	public function getLeggings(){
		return $this->getItem($this->getSize() + 1);
	}

	public function getBoots(){
		return $this->getItem($this->getSize());
	}

	public function setHelmet(Item $helmet){
		$this->setItem($this->getSize() + 3, $helmet);
	}

	public function setChestplate(Item $chestplate){
		$this->setItem($this->getSize() + 2, $chestplate);
	}

	public function setLeggings(Item $leggings){
		$this->setItem($this->getSize() + 1, $leggings);
	}

	public function setBoots(Item $boots){
		$this->setItem($this->getSize(), $boots);
	}

	/**
	 * @return Item[]
	 */
	public function getArmorContents(){
		$armor = [];

		for($i = 0; $i < 4; ++$i){
			$armor[$i] = $this->getItem($this->getSize() + $i);
		}

		return $armor;
	}

	/**
	 * @param Item[] $items
	 */
	public function setArmorContents(array $items){
		for($i = 0; $i < 4; ++$i){
			if(!isset($items[$i]) or !($items[$i] instanceof Item)){
				$items[$i] = Item::get(Item::AIR, null, 0);
			}

			if($items[$i]->getID() === Item::AIR){
				$this->clear($this->getSize() + $i);
			}else{
				$this->setItem($this->getSize() + $i, $items[$i]);
			}
		}
	}

	/**
	 * @return Human
	 */
	public function getHolder(){
		return parent::getHolder();
	}

}