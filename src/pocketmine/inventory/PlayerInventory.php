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
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerEquipmentPacket;
use pocketmine\Player;
use pocketmine\Server;

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

			$pk = new PlayerEquipmentPacket();
			$pk->eid = $this->getHolder()->getId();
			$pk->item = $item->getId();
			$pk->meta = $item->getDamage();
			$pk->slot = $this->getHeldItemIndex();

			Server::broadcastPacket($this->getHolder()->getViewers(), $pk);
		}
	}

	public function getItemInHand(){
		$item = $this->getItem($this->getHeldItemSlot());
		if($item instanceof Item){
			return $item;
		}else{
			return Item::get(Item::AIR, 0, 0);
		}
	}

	/**
	 * @param Item $item
	 * @param      $source
	 *
	 * @return bool
	 */
	public function setItemInHand(Item $item, $source = null){
		return $this->setItem($this->getHeldItemSlot(), $item, $source);
	}

	public function getHeldItemSlot(){
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	public function setHeldItemSlot($slot){
		if($slot >= -1 and $slot < $this->getSize()){
			$item = $this->getItem($slot);

			$itemIndex = $this->getHeldItemIndex();

			for($i = 0; $i < $this->getHotbarSize(); ++$i){
				if($this->getHotbarSlotIndex($i) === $slot){
					$itemIndex = $i;
					break;
				}
			}

			if($this->getHolder() instanceof Player){
				Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this->getHolder(), $item, $slot, $itemIndex));
				if($ev->isCancelled()){
					$this->sendHeldItem($this->getHolder());
					return;
				}
			}

			$this->setHotbarSlotIndex($itemIndex, $slot);
			$this->setHeldItemIndex($itemIndex);
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$item = $this->getItemInHand();

		$pk = new PlayerEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->item = $item->getId();
		$pk->meta = $item->getDamage();
		$pk->slot = 0;
		$pk->isEncoded = true;
		$pk->encode();

		foreach($target as $player){
			if($player === $this->getHolder()){
				$this->sendSlot($this->getHeldItemSlot());
			}else{
				$player->dataPacket($pk);
			}
		}
	}

	public function onSlotChange($index, $before, $source = null){
		parent::onSlotChange($index, $before, $source);

		if($index >= $this->getSize()){
			$this->sendArmorSlot($index, $this->getViewers($source));
			$this->sendArmorSlot($index, $this->getHolder()->getViewers());
		}
	}

	public function getHotbarSize(){
		return 9;
	}

	public function getArmorItem($index){
		return $this->getItem($this->getSize() + $index);
	}

	public function setArmorItem($index, Item $item, $source = null){
		return $this->setItem($this->getSize() + $index, $item, $source);
	}

	public function getHelmet(){
		return $this->getItem($this->getSize());
	}

	public function getChestplate(){
		return $this->getItem($this->getSize() + 1);
	}

	public function getLeggings(){
		return $this->getItem($this->getSize() + 2);
	}

	public function getBoots(){
		return $this->getItem($this->getSize() + 3);
	}

	public function setHelmet(Item $helmet){
		return $this->setItem($this->getSize(), $helmet);
	}

	public function setChestplate(Item $chestplate){
		return $this->setItem($this->getSize() + 1, $chestplate);
	}

	public function setLeggings(Item $leggings){
		return $this->setItem($this->getSize() + 2, $leggings);
	}

	public function setBoots(Item $boots){
		return $this->setItem($this->getSize() + 3, $boots);
	}

	public function setItem($index, Item $item, $source = null){
		if($index < 0 or $index >= $this->size){
			return false;
		}elseif($item->getId() === 0 or $item->getCount() <= 0){
			return $this->clear($index, $source);
		}

		if($index >= $this->getSize()){ //Armor change
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled() and $this->getHolder() instanceof Player){
				if($index >= $this->size){
					$this->sendArmorSlot($index, $this->getViewers());
				}else{
					$this->sendSlot($index, $this->getViewers());
				}
				return false;
			}
			$item = $ev->getNewItem();
		}else{
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				if($index >= $this->size){
					$this->sendArmorSlot($index, $this->getViewers());
				}else{
					$this->sendSlot($index, $this->getViewers());
				}
				return false;
			}
			$item = $ev->getNewItem();
		}


		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old, $source);

		return true;
	}

	public function clear($index, $source = null){
		if(isset($this->slots[$index])){
			$item = Item::get(Item::AIR, null, 0);
			$old = $this->slots[$index];
			if($index >= $this->getSize() and $index < $this->size){ //Armor change
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}else{
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getId() !== Item::AIR){
				$this->slots[$index] = clone $item;
			}else{
				unset($this->slots[$index]);
			}

			$this->onSlotChange($index, $old, $source);
		}

		return true;
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

	public function clearAll(){
		$limit = $this->getSize() + 4;
		for($index = 0; $index < $limit; ++$index){
			$this->clear($index);
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendArmorContents($target){
		if($target instanceof Player){
			$target = [$target];
		}
		$armor = $this->getArmorContents();
		$slots = [];

		foreach($armor as $i => $slot){
			if($slot->getId() === Item::AIR){
				$slots[$i] = 255;
			}else{
				$slots[$i] = $slot->getId();
			}
		}

		$pk = new PlayerArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $slots;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				//$pk2 = clone $pk;
				//$pk2->eid = 0;

				$pk2 = new ContainerSetContentPacket();
				$pk2->windowid = 0x78; //Armor window id constant
				$pk2->slots = $armor;
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 * @param Item[] $items
	 */
	public function setArmorContents(array $items){
		for($i = 0; $i < 4; ++$i){
			if(!isset($items[$i]) or !($items[$i] instanceof Item)){
				$items[$i] = Item::get(Item::AIR, null, 0);
			}

			if($items[$i]->getId() === Item::AIR){
				$this->clear($this->getSize() + $i);
			}else{
				$this->setItem($this->getSize() + $i, $items[$i]);
			}
		}
	}


	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendArmorSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();
		$slots = [];

		foreach($armor as $i => $slot){
			if($slot->getId() === Item::AIR){
				$slots[$i] = 255;
			}else{
				$slots[$i] = $slot->getId();
			}
		}

		$pk = new PlayerArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $slots;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				$pk2 = new ContainerSetSlotPacket();
				$pk2->windowid = 0x78; //Armor window id constant
				$pk2->slot = $index;
				$pk2->item = $this->getItem($index);
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetContentPacket();
		$pk->slots = [];
		for($i = 0; $i < $this->getSize(); ++$i){ //Do not send armor by error here
			$pk->slots[$i] = $this->getItem($i);
		}

		foreach($target as $player){
			$pk->hotbar = [];
			if($player === $this->getHolder()){
				for($i = 0; $i < $this->getHotbarSize(); ++$i){
					$index = $this->getHotbarSlotIndex($i);
					$pk->hotbar[] = $index <= -1 ? -1 : $index + 9;
				}
			}
			if(($id = $player->getWindowId($this)) === -1 or $player->spawned !== true){
				$this->close($player);
				continue;
			}
			$pk->windowid = $id;
			$player->dataPacket(clone $pk);
		}
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetSlotPacket();
		$pk->slot = $index;
		$pk->item = clone $this->getItem($index);

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				$pk->windowid = 0;
				$player->dataPacket(clone $pk);
			}else{
				if(($id = $player->getWindowId($this)) === -1){
					$this->close($player);
					continue;
				}
				$pk->windowid = $id;
				$player->dataPacket(clone $pk);
			}
		}
	}

	/**
	 * @return Human|Player
	 */
	public function getHolder(){
		return parent::getHolder();
	}

}