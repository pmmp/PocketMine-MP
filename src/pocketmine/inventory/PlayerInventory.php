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
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;
use function in_array;
use function is_array;

class PlayerInventory extends BaseInventory{

	/** @var Human */
	protected $holder;

	/** @var int */
	protected $itemInHandIndex = 0;

	/**
	 * @param Human $player
	 */
	public function __construct(Human $player){
		$this->holder = $player;
		parent::__construct(36);
	}

	public function isHotbarSlot(int $slot) : bool{
		return $slot >= 0 and $slot <= $this->getHotbarSize();
	}

	/**
	 * @param int $slot
	 *
	 * @throws \InvalidArgumentException
	 */
	private function throwIfNotHotbarSlot(int $slot) : void{
		if(!$this->isHotbarSlot($slot)){
			throw new \InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getHotbarSize() - 1) . ")");
		}
	}

	/**
	 * Returns the item in the specified hotbar slot.
	 *
	 * @param int $hotbarSlot
	 *
	 * @return Item
	 *
	 * @throws \InvalidArgumentException if the hotbar slot index is out of range
	 */
	public function getHotbarSlotItem(int $hotbarSlot) : Item{
		$this->throwIfNotHotbarSlot($hotbarSlot);
		return $this->getItem($hotbarSlot);
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
	public function setHeldItemIndex(int $hotbarSlot, bool $send = true) : void{
		$this->throwIfNotHotbarSlot($hotbarSlot);

		$this->itemInHandIndex = $hotbarSlot;

		if($this->getHolder() instanceof Player and $send){
			$this->sendHeldItem($this->getHolder());
		}

		$this->sendHeldItem($this->getHolder()->getViewers());
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
		if($this->setItem($this->getHeldItemIndex(), $item)){
			$this->sendHeldItem($this->holder->getViewers());
			return true;
		}

		return false;
	}

	/**
	 * Sends the currently-held item to specified targets.
	 *
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target) : void{
		$item = $this->getItemInHand();

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->item = $item;
		$pk->inventorySlot = $pk->hotbarSlot = $this->getHeldItemIndex();
		$pk->windowId = ContainerIds::INVENTORY;

		if(!is_array($target)){
			$target->sendDataPacket($pk);
			if($target === $this->getHolder()){
				$this->sendSlot($this->getHeldItemIndex(), $target);
			}
		}else{
			$this->getHolder()->getLevel()->getServer()->broadcastPacket($target, $pk);
			if(in_array($this->getHolder(), $target, true)){
				$this->sendSlot($this->getHeldItemIndex(), $this->getHolder());
			}
		}
	}

	/**
	 * Returns the number of slots in the hotbar.
	 * @return int
	 */
	public function getHotbarSize() : int{
		return 9;
	}

	public function sendCreativeContents() : void{
		//TODO: this mess shouldn't be in here
		$holder = $this->getHolder();
		if(!($holder instanceof Player)){
			throw new \LogicException("Cannot send creative inventory contents to non-player inventory holder");
		}
		$pk = new InventoryContentPacket();
		$pk->windowId = ContainerIds::CREATIVE;

		if(!$holder->isSpectator()){ //fill it for all gamemodes except spectator
			foreach(Item::getCreativeItems() as $i => $item){
				$pk->items[$i] = clone $item;
			}
		}

		$holder->sendDataPacket($pk);
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Human|Player
	 */
	public function getHolder(){
		return $this->holder;
	}
}
