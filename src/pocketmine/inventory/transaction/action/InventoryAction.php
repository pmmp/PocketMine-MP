<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Represents an action involving a change that applies in some way to an inventory or other item-source.
 */
abstract class InventoryAction{
	/** @var Item */
	protected $sourceItem;
	/** @var Item */
	protected $targetItem;

	public function __construct(Item $sourceItem, Item $targetItem){
		$this->sourceItem = $sourceItem;
		$this->targetItem = $targetItem;
	}

	/**
	 * Returns the item that was present before the action took place.
	 * @return Item
	 */
	public function getSourceItem() : Item{
		return clone $this->sourceItem;
	}

	/**
	 * Returns the item that the action attempted to replace the source item with.
	 * @return Item
	 */
	public function getTargetItem() : Item{
		return clone $this->targetItem;
	}

	/**
	 * Returns whether this action is currently valid. This should perform any necessary sanity checks.
	 *
	 * @param Player $source
	 *
	 * @return bool
	 */
	abstract public function isValid(Player $source) : bool;

	/**
	 * Called when the action is added to the specified InventoryTransaction.
	 *
	 * @param InventoryTransaction $transaction
	 */
	public function onAddToTransaction(InventoryTransaction $transaction) : void{

	}

	/**
	 * Called by inventory transactions before any actions are processed. If this returns false, the transaction will
	 * be cancelled.
	 *
	 * @param Player $source
	 *
	 * @return bool
	 */
	public function onPreExecute(Player $source) : bool{
		return true;
	}

	/**
	 * Performs actions needed to complete the inventory-action server-side. Returns if it was successful. Will return
	 * false if plugins cancelled events. This will only be called if the transaction which it is part of is considered
	 * valid.
	 *
	 * @param Player $source
	 *
	 * @return bool
	 */
	abstract public function execute(Player $source) : bool;

	/**
	 * Performs additional actions when this inventory-action completed successfully.
	 *
	 * @param Player $source
	 */
	abstract public function onExecuteSuccess(Player $source) : void;

	/**
	 * Performs additional actions when this inventory-action did not complete successfully.
	 *
	 * @param Player $source
	 */
	abstract public function onExecuteFail(Player $source) : void;

}
