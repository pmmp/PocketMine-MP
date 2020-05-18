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
use pocketmine\item\Item;
use pocketmine\Player;
use function spl_object_hash;

/**
 * Represents an action causing a change in an inventory slot.
 */
class SlotChangeAction extends InventoryAction{

	/** @var Inventory */
	protected $inventory;
	/** @var int */
	private $inventorySlot;

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
	 */
	public function isValid(Player $source) : bool{
		return (
			$this->inventory->slotExists($this->inventorySlot) and
			$this->inventory->getItem($this->inventorySlot)->equalsExact($this->sourceItem)
		);
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
	public function execute(Player $source) : bool{
		return $this->inventory->setItem($this->inventorySlot, $this->targetItem, false);
	}

	/**
	 * Sends slot changes to other viewers of the inventory. This will not send any change back to the source Player.
	 */
	public function onExecuteSuccess(Player $source) : void{
		$viewers = $this->inventory->getViewers();
		unset($viewers[spl_object_hash($source)]);
		$this->inventory->sendSlot($this->inventorySlot, $viewers);
	}

	/**
	 * Sends the original slot contents to the source player to revert the action.
	 */
	public function onExecuteFail(Player $source) : void{
		$this->inventory->sendSlot($this->inventorySlot, $source);
	}
}
