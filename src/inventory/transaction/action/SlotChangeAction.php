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
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Represents an action causing a change in an inventory slot.
 */
class SlotChangeAction extends InventoryAction{

	/** @var Inventory */
	protected $inventory;
	private int $inventorySlot;

	public function __construct(Inventory $inventory, int $inventorySlot, Item $sourceItem, Item $targetItem){
		parent::__construct($sourceItem, $targetItem);
		$this->inventory = $inventory;
		$this->inventorySlot = $inventorySlot;
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
	}

	/**
	 * Adds this action's target inventory to the transaction's inventory list.
	 */
	public function onAddToTransaction(InventoryTransaction $transaction) : void{
		$transaction->addInventory($this->inventory);
	}

	/**
	 * Sets the item into the target inventory.
	 */
	public function execute(Player $source) : void{
		$this->inventory->setItem($this->inventorySlot, $this->targetItem);
	}
}
