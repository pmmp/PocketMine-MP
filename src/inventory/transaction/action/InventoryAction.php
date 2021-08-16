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

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\player\Player;

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
	 */
	public function getSourceItem() : Item{
		return clone $this->sourceItem;
	}

	/**
	 * Returns the item that the action attempted to replace the source item with.
	 */
	public function getTargetItem() : Item{
		return clone $this->targetItem;
	}

	/**
	 * Returns whether this action is currently valid. This should perform any necessary sanity checks.
	 *
	 * @throws TransactionValidationException
	 */
	abstract public function validate(Player $source) : void;

	/**
	 * Called when the action is added to the specified InventoryTransaction.
	 */
	public function onAddToTransaction(InventoryTransaction $transaction) : void{

	}

	/**
	 * Called by inventory transactions before any actions are processed. If this returns false, the transaction will
	 * be cancelled.
	 */
	public function onPreExecute(Player $source) : bool{
		return true;
	}

	/**
	 * Performs actions needed to complete the inventory-action server-side. This will only be called if the transaction
	 * which it is part of is considered valid.
	 */
	abstract public function execute(Player $source) : void;
}
