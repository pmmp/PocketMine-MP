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

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\Server;

class PlayerInventory extends BaseInventory{

	/** @var Human */
	protected $holder;

	protected $itemInHandIndex = 0;
	/** @var \SplFixedArray<int> */
	protected $hotbar;

	public function __construct(Human $player){
		$this->resetHotbar(false);
		parent::__construct($player);
	}

	public function getNetworkType() : int{
		return WindowTypes::INVENTORY;
	}

	public function getName() : string{
		return "Player";
	}

	public function getDefaultSize() : int{
		return 40; //36 inventory, 4 armor
	}

	public function getSize() : int{
		return parent::getSize() - 4; //Remove armor slots
	}

	public function setSize(int $size){
		parent::setSize($size + 4);
		$this->sendContents($this->getViewers());
	}

	/**
	 * Called when a client equips a hotbar slot. This method should not be used by plugins.
	 * This method will call PlayerItemHeldEvent.
	 *
	 * @param int $hotbarSlot Number of the hotbar slot to equip.
	 * @param int|null $inventorySlot Inventory slot to map to the specified hotbar slot. Supply null to make no change to the link.
	 *
	 * @return bool if the equipment change was successful, false if not.
	 */
	public function equipItem(int $hotbarSlot, int $inventorySlot = null) : bool{
		if($inventorySlot === null){
			$inventorySlot = $this->getHotbarSlotIndex($hotbarSlot);
		}

		if($hotbarSlot < 0 or $hotbarSlot >= $this->getHotbarSize() or $inventorySlot < -1 or $inventorySlot >= $this->getSize()){
			$this->sendContents($this->getHolder());
			return false;
		}

		if($inventorySlot === -1){
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}else{
			$item = $this->getItem($inventorySlot);
		}

		$this->getHolder()->getLevel()->getServer()->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this->getHolder(), $item, $inventorySlot, $hotbarSlot));

		if($ev->isCancelled()){
			$this->sendContents($this->getHolder());
			return false;
		}

		$this->setHotbarSlotIndex($hotbarSlot, $inventorySlot);
		$this->setHeldItemIndex($hotbarSlot, false);

		return true;
	}

	/**
	 * Returns the index of the inventory slot mapped to the specified hotbar slot, or -1 if the hotbar slot does not exist.
	 *
	 * @param int $index
	 *
	 * @return int
	 */
	public function getHotbarSlotIndex(int $index) : int{
		return $this->hotbar[$index] ?? -1;
	}

	/**
	 * Links a hotbar slot to the specified slot in the main inventory. -1 links to no slot and will clear the hotbar slot.
	 * This method is intended for use in network interaction with clients only.
	 *
	 * NOTE: Do not change hotbar slot mapping with plugins, this will cause myriad client-sided bugs, especially with desktop GUI clients.
	 *
	 * @param int $hotbarSlot
	 * @param int $inventorySlot
	 *
	 * @throws \RuntimeException if the hotbar slot is out of range
	 * @throws \InvalidArgumentException if the inventory slot is out of range
	 */
	public function setHotbarSlotIndex(int $hotbarSlot, int $inventorySlot){
		if($inventorySlot < -1 or $inventorySlot >= $this->getSize()){
			throw new \InvalidArgumentException("Inventory slot index \"$inventorySlot\" is out of range");
		}

		if($inventorySlot !== -1 and ($alreadyEquippedIndex = array_search($inventorySlot, $this->getHotbar(), true)) !== false){
			/* Swap the slots
			 * This assumes that the equipped slot can only be equipped in one other slot
			 * it will not account for ancient bugs where the same slot ended up linked to several hotbar slots.
			 * Such bugs will require a hotbar reset to default.
			 */
			$this->hotbar[$alreadyEquippedIndex] = $this->hotbar[$hotbarSlot];
		}

		$this->hotbar[$hotbarSlot] = $inventorySlot;
	}

	/**
	 * Returns the item in the slot linked to the specified hotbar slot, or Air if the slot is not linked to any hotbar slot.
	 * @param int $hotbarSlotIndex
	 *
	 * @return Item
	 */
	public function getHotbarSlotItem(int $hotbarSlotIndex) : Item{
		$inventorySlot = $this->getHotbarSlotIndex($hotbarSlotIndex);
		if($inventorySlot !== -1){
			return $this->getItem($inventorySlot);
		}else{
			return ItemFactory::get(Item::AIR, 0, 0);
		}
	}

	public function getHotbar() : array{
		return $this->hotbar->toArray();
	}

	/**
	 * Resets hotbar links to their original defaults.
	 * @param bool $send Whether to send changes to the holder.
	 */
	public function resetHotbar(bool $send = true){
		$this->hotbar = \SplFixedArray::fromArray(range(0, $this->getHotbarSize() - 1, 1));
		if($send){
			$this->sendContents($this->getHolder());
		}
	}

	public function sendHotbar(){
		$pk = new PlayerHotbarPacket();
		$pk->windowId = ContainerIds::INVENTORY;
		$pk->selectedSlot = $this->getHeldItemIndex();
		$pk->slots = array_map(function(int $link){ return $link + $this->getHotbarSize(); }, $this->getHotbar());
		$this->getHolder()->dataPacket($pk);
	}

	/**
	 * Returns the hotbar slot number the holder is currently holding.
	 * @return int
	 */
	public function getHeldItemIndex() : int{
		return $this->itemInHandIndex;
	}

	/**
	 * Sets which hotbar slot the player is currently loading.
	 *
	 * @param int  $hotbarSlot 0-8 index of the hotbar slot to hold
	 * @param bool $send Whether to send updates back to the inventory holder. This should usually be true for plugin calls.
	 *                    It should only be false to prevent feedback loops of equipment packets between client and server.
	 *
	 * @throws \InvalidArgumentException if the hotbar slot is out of range
	 */
	public function setHeldItemIndex(int $hotbarSlot, bool $send = true){
		if($hotbarSlot >= 0 and $hotbarSlot < $this->getHotbarSize()){
			$this->itemInHandIndex = $hotbarSlot;

			if($this->getHolder() instanceof Player and $send){
				$this->sendHeldItem($this->getHolder());
			}

			$this->sendHeldItem($this->getHolder()->getViewers());
		}else{
			throw new \InvalidArgumentException("Hotbar slot index \"$hotbarSlot\" is out of range");
		}
	}

	/**
	 * Returns the currently-held item.
	 *
	 * @return Item
	 */
	public function getItemInHand() : Item{
		return $this->getHotbarSlotItem($this->itemInHandIndex);
	}

	/**
	 * Sets the item in the currently-held slot to the specified item.
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItemInHand(Item $item) : bool{
		return $this->setItem($this->getHeldItemSlot(), $item);
	}

	/**
	 * Returns the hotbar slot number currently held.
	 * @return int
	 */
	public function getHeldItemSlot() : int{
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	/**
	 * Sets the hotbar slot link of the currently-held hotbar slot.
	 * @deprecated Do not change hotbar slot mapping with plugins, this will cause myriad client-sided bugs, especially with desktop GUI clients.
	 *
	 * @param int $slot
	 */
	public function setHeldItemSlot(int $slot){
		if($slot >= -1 and $slot < $this->getSize()){
			$this->setHotbarSlotIndex($this->getHeldItemIndex(), $slot);
		}
	}

	/**
	 * Sends the currently-held item to specified targets.
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target){
		$item = $this->getItemInHand();

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->item = $item;
		$pk->inventorySlot = $this->getHeldItemSlot();
		$pk->hotbarSlot = $this->getHeldItemIndex();
		$pk->windowId = ContainerIds::INVENTORY;

		if(!is_array($target)){
			$target->dataPacket($pk);
			if($this->getHeldItemSlot() !== -1 and $target === $this->getHolder()){
				$this->sendSlot($this->getHeldItemSlot(), $target);
			}
		}else{
			$this->getHolder()->getLevel()->getServer()->broadcastPacket($target, $pk);
			if($this->getHeldItemSlot() !== -1 and in_array($this->getHolder(), $target, true)){
				$this->sendSlot($this->getHeldItemSlot(), $this->getHolder());
			}
		}
	}

	public function onSlotChange(int $index, Item $before, bool $send){
		$holder = $this->getHolder();
		if($holder instanceof Player and !$holder->spawned){
			return;
		}

		if($index >= $this->getSize()){
			$this->sendArmorSlot($index, $this->getViewers());
			$this->sendArmorSlot($index, $this->getHolder()->getViewers());
		}else{
			//Do not send armor by accident here.
			parent::onSlotChange($index, $before, $send);
		}
	}

	/**
	 * Returns the number of slots in the hotbar.
	 * @return int
	 */
	public function getHotbarSize() : int{
		return 9;
	}

	public function getArmorItem(int $index) : Item{
		return $this->getItem($this->getSize() + $index);
	}

	public function setArmorItem(int $index, Item $item) : bool{
		return $this->setItem($this->getSize() + $index, $item);
	}

	public function getHelmet() : Item{
		return $this->getItem($this->getSize());
	}

	public function getChestplate() : Item{
		return $this->getItem($this->getSize() + 1);
	}

	public function getLeggings() : Item{
		return $this->getItem($this->getSize() + 2);
	}

	public function getBoots() : Item{
		return $this->getItem($this->getSize() + 3);
	}

	public function setHelmet(Item $helmet) : bool{
		return $this->setItem($this->getSize(), $helmet);
	}

	public function setChestplate(Item $chestplate) : bool{
		return $this->setItem($this->getSize() + 1, $chestplate);
	}

	public function setLeggings(Item $leggings) : bool{
		return $this->setItem($this->getSize() + 2, $leggings);
	}

	public function setBoots(Item $boots) : bool{
		return $this->setItem($this->getSize() + 3, $boots);
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool{
		if($item->isNull()){
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}else{
			$item = clone $item;
		}

		if($index >= $this->getSize()){ //Armor change
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled() and $this->getHolder() instanceof Human){
				$this->sendArmorSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}else{
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}

		$old = $this->getItem($index);
		$this->slots[$index] = $item;
		$this->onSlotChange($index, $old, $send);

		return true;
	}

	public function clearAll(){
		parent::clearAll();

		for($i = $this->getSize(), $m = $i + 4; $i < $m; ++$i){
			$this->clear($i, false);
		}

		$this->sendArmorContents($this->getViewers());
	}

	/**
	 * @return Item[]
	 */
	public function getArmorContents() : array{
		$armor = [];

		for($i = 0; $i < 4; ++$i){
			$armor[$i] = $this->getItem($this->getSize() + $i);
		}

		return $armor;
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendArmorContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new InventoryContentPacket();
				$pk2->windowId = ContainerIds::ARMOR;
				$pk2->items = $armor;
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
				$items[$i] = ItemFactory::get(Item::AIR, 0, 0);
			}

			$this->setItem($this->getSize() + $i, $items[$i], false);
		}

		$this->sendArmorContents($this->getViewers());
	}


	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendArmorSlot(int $index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */

				$pk2 = new InventorySlotPacket();
				$pk2->windowId = ContainerIds::ARMOR;
				$pk2->inventorySlot = $index - $this->getSize();
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

		$pk = new InventoryContentPacket();

		for($i = 0; $i < $this->getSize(); ++$i){ //Do not send armor by error here
			$pk->items[$i] = $this->getItem($i);
		}

		foreach($target as $player){
			if(($id = $player->getWindowId($this)) === -1 or $player->spawned !== true){
				$this->close($player);
				continue;
			}
			$pk->windowId = $id;
			$player->dataPacket(clone $pk);

			if($player === $this->getHolder()){
				$this->sendHotbar();
			}
		}
	}

	public function sendCreativeContents(){
		$pk = new InventoryContentPacket();
		$pk->windowId = ContainerIds::CREATIVE;
		if($this->getHolder()->getGamemode() === Player::CREATIVE){
			foreach(Item::getCreativeItems() as $i => $item){
				$pk->items[$i] = clone $item;
			}
		}

		$this->getHolder()->dataPacket($pk);
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventorySlotPacket();
		$pk->inventorySlot = $index;
		$pk->item = clone $this->getItem($index);

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				$pk->windowId = ContainerIds::INVENTORY;
				$player->dataPacket(clone $pk);
			}else{
				if(($id = $player->getWindowId($this)) === -1){
					$this->close($player);
					continue;
				}
				$pk->windowId = $id;
				$player->dataPacket(clone $pk);
			}
		}
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Human|Player
	 */
	public function getHolder(){
		return $this->holder;
	}

}
