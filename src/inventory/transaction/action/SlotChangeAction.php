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

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\SlotValidatedInventory;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Represents an action causing a change in an inventory slot.
 */
class SlotChangeAction extends InventoryAction{
	public function __construct(
		protected Inventory $inventory,
		private int $inventorySlot,
		Item $sourceItem,
		Item $targetItem
	){
		parent::__construct($sourceItem, $targetItem);
	}

	/**
	 * Returns the inventory involved in this action.
	 */
	public function getInventory() : Inventory{
		return $this->inventory;
	}

	/**
	 * Returns the slot in the inventory which this action modified.
	 */
	public function getSlot() : int{
		return $this->inventorySlot;
	}

	/**
	 * Checks if the item in the inventory at the specified slot is the same as this action's source item.
	 *
	 * @throws TransactionValidationException
	 */
	public function validate(Player $source) : void{
		if(!$this->inventory->slotExists($this->inventorySlot)){
			throw new TransactionValidationException("Slot does not exist");
		}
		if(!$this->inventory->getItem($this->inventorySlot)->equalsExact($this->sourceItem)){
			throw new TransactionValidationException("Slot does not contain expected original item");
		}
		if($this->targetItem->getCount() > $this->targetItem->getMaxStackSize()){
			throw new TransactionValidationException("Target item exceeds item type max stack size");
		}
		if($this->targetItem->getCount() > $this->inventory->getMaxStackSize()){
			throw new TransactionValidationException("Target item exceeds inventory max stack size");
		}
		if($this->inventory instanceof SlotValidatedInventory && !$this->targetItem->isNull()){
			foreach($this->inventory->getSlotValidators() as $validator){
				$ret = $validator->validate($this->inventory, $this->targetItem, $this->inventorySlot);
				if($ret !== null){
					throw new TransactionValidationException("Target item is not accepted by the inventory at slot #" . $this->inventorySlot . ": " . $ret->getMessage(), 0, $ret);
				}
			}
		}
	}

	/**
	 * Sets the item into the target inventory.
	 */
	public function execute(Player $source) : void{
		$this->inventory->setItem($this->inventorySlot, $this->targetItem);
	}
}
