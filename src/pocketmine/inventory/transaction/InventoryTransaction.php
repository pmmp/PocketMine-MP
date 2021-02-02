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

namespace pocketmine\inventory\transaction;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\Player;
use function array_keys;
use function array_values;
use function assert;
use function count;
use function get_class;
use function min;
use function shuffle;
use function spl_object_hash;

/**
 * This is the basic type for an inventory transaction. This is used for moving items between inventories, dropping
 * items and more. It allows transactions with multiple inputs and outputs.
 *
 * Validation **does not** depend on ordering. This means that the actions can appear in any order and still be valid.
 * The only validity requirement for this transaction type is that the balance of items must add up to zero. This means:
 * - No new outputs without matching input amounts
 * - No inputs without matching output amounts
 * - No userdata changes (item state, NBT, etc)
 *
 * A transaction is composed of "actions", which are pairs of inputs and outputs which target a specific itemstack in
 * a specific location. There are multiple types of inventory actions which might be involved in a transaction.
 *
 * @see InventoryAction
 */
class InventoryTransaction{
	/** @var bool */
	protected $hasExecuted = false;
	/** @var Player */
	protected $source;

	/** @var Inventory[] */
	protected $inventories = [];

	/** @var InventoryAction[] */
	protected $actions = [];

	/**
	 * @param InventoryAction[] $actions
	 */
	public function __construct(Player $source, array $actions = []){
		$this->source = $source;
		foreach($actions as $action){
			$this->addAction($action);
		}
	}

	public function getSource() : Player{
		return $this->source;
	}

	/**
	 * @return Inventory[]
	 */
	public function getInventories() : array{
		return $this->inventories;
	}

	/**
	 * Returns an **unordered** set of actions involved in this transaction.
	 *
	 * WARNING: This system is **explicitly designed NOT to care about ordering**. Any order seen in this set has NO
	 * significance and should not be relied on.
	 *
	 * @return InventoryAction[]
	 */
	public function getActions() : array{
		return $this->actions;
	}

	public function addAction(InventoryAction $action) : void{
		if(!isset($this->actions[$hash = spl_object_hash($action)])){
			$this->actions[$hash] = $action;
			$action->onAddToTransaction($this);
		}else{
			throw new \InvalidArgumentException("Tried to add the same action to a transaction twice");
		}
	}

	/**
	 * Shuffles actions in the transaction to prevent external things relying on any implicit ordering.
	 */
	private function shuffleActions() : void{
		$keys = array_keys($this->actions);
		shuffle($keys);
		$actions = [];
		foreach($keys as $key){
			$actions[$key] = $this->actions[$key];
		}
		$this->actions = $actions;
	}

	/**
	 * @internal This method should not be used by plugins, it's used to add tracked inventories for InventoryActions
	 * involving inventories.
	 */
	public function addInventory(Inventory $inventory) : void{
		if(!isset($this->inventories[$hash = spl_object_hash($inventory)])){
			$this->inventories[$hash] = $inventory;
		}
	}

	/**
	 * @param Item[] $needItems
	 * @param Item[] $haveItems
	 *
	 * @throws TransactionValidationException
	 */
	protected function matchItems(array &$needItems, array &$haveItems) : void{
		$needItems = [];
		$haveItems = [];
		foreach($this->actions as $key => $action){
			if(!$action->getTargetItem()->isNull()){
				$needItems[] = $action->getTargetItem();
			}

			if(!$action->isValid($this->source)){
				throw new TransactionValidationException("Action " . get_class($action) . " is not valid in the current transaction");
			}

			if(!$action->getSourceItem()->isNull()){
				$haveItems[] = $action->getSourceItem();
			}
		}

		foreach($needItems as $i => $needItem){
			foreach($haveItems as $j => $haveItem){
				if($needItem->equals($haveItem)){
					$amount = min($needItem->getCount(), $haveItem->getCount());
					$needItem->setCount($needItem->getCount() - $amount);
					$haveItem->setCount($haveItem->getCount() - $amount);
					if($haveItem->getCount() === 0){
						unset($haveItems[$j]);
					}
					if($needItem->getCount() === 0){
						unset($needItems[$i]);
						break;
					}
				}
			}
		}
		$needItems = array_values($needItems);
		$haveItems = array_values($haveItems);
	}

	/**
	 * Iterates over SlotChangeActions in this transaction and compacts any which refer to the same slot in the same
	 * inventory so they can be correctly handled.
	 *
	 * Under normal circumstances, the same slot would never be changed more than once in a single transaction. However,
	 * due to the way things like the crafting grid are "implemented" in MCPE 1.2 (a.k.a. hacked-in), we may get
	 * multiple slot changes referring to the same slot in a single transaction. These multiples are not even guaranteed
	 * to be in the correct order (slot splitting in the crafting grid for example, causes the actions to be sent in the
	 * wrong order), so this method also tries to chain them into order.
	 */
	protected function squashDuplicateSlotChanges() : void{
		/** @var SlotChangeAction[][] $slotChanges */
		$slotChanges = [];
		/** @var Inventory[] $inventories */
		$inventories = [];
		/** @var int[] $slots */
		$slots = [];

		foreach($this->actions as $key => $action){
			if($action instanceof SlotChangeAction){
				$slotChanges[$h = (spl_object_hash($action->getInventory()) . "@" . $action->getSlot())][] = $action;
				$inventories[$h] = $action->getInventory();
				$slots[$h] = $action->getSlot();
			}
		}

		foreach($slotChanges as $hash => $list){
			if(count($list) === 1){ //No need to compact slot changes if there is only one on this slot
				continue;
			}

			$inventory = $inventories[$hash];
			$slot = $slots[$hash];
			if(!$inventory->slotExists($slot)){ //this can get hit for crafting tables because the validation happens after this compaction
				throw new TransactionValidationException("Slot $slot does not exist in inventory " . get_class($inventory));
			}
			$sourceItem = $inventory->getItem($slot);

			$targetItem = $this->findResultItem($sourceItem, $list);
			if($targetItem === null){
				throw new TransactionValidationException("Failed to compact " . count($list) . " duplicate actions");
			}

			foreach($list as $action){
				unset($this->actions[spl_object_hash($action)]);
			}

			if(!$targetItem->equalsExact($sourceItem)){
				//sometimes we get actions on the crafting grid whose source and target items are the same, so dump them
				$this->addAction(new SlotChangeAction($inventory, $slot, $sourceItem, $targetItem));
			}
		}
	}

	/**
	 * @param SlotChangeAction[] $possibleActions
	 */
	protected function findResultItem(Item $needOrigin, array $possibleActions) : ?Item{
		assert(count($possibleActions) > 0);

		$candidate = null;
		$newList = $possibleActions;
		foreach($possibleActions as $i => $action){
			if($action->getSourceItem()->equalsExact($needOrigin)){
				if($candidate !== null){
					/*
					 * we found multiple possible actions that match the origin action
					 * this means that there are multiple ways that this chain could play out
					 * if we cared so much about this, we could build all the possible chains in parallel and see which
					 * variation managed to complete the chain, but this has an extremely high complexity which is not
					 * worth the trouble for this scenario (we don't usually expect to see chains longer than a couple
					 * of actions in here anyway), and might still result in multiple possible results.
					 */
					return null;
				}
				$candidate = $action;
				unset($newList[$i]);
			}
		}
		if($candidate === null){
			//chaining is not possible with this origin, none of the actions are valid
			return null;
		}

		if(count($newList) === 0){
			return $candidate->getTargetItem();
		}
		return $this->findResultItem($candidate->getTargetItem(), $newList);
	}

	/**
	 * Verifies that the transaction can execute.
	 *
	 * @throws TransactionValidationException
	 */
	public function validate() : void{
		$this->squashDuplicateSlotChanges();

		$haveItems = [];
		$needItems = [];
		$this->matchItems($needItems, $haveItems);
		if(count($this->actions) === 0){
			throw new TransactionValidationException("Inventory transaction must have at least one action to be executable");
		}

		if(count($haveItems) > 0){
			throw new TransactionValidationException("Transaction does not balance (tried to destroy some items)");
		}
		if(count($needItems) > 0){
			throw new TransactionValidationException("Transaction does not balance (tried to create some items)");
		}
	}

	protected function sendInventories() : void{
		foreach($this->inventories as $inventory){
			$inventory->sendContents($this->source);
		}
	}

	protected function callExecuteEvent() : bool{
		$ev = new InventoryTransactionEvent($this);
		$ev->call();
		return !$ev->isCancelled();
	}

	/**
	 * Executes the group of actions, returning whether the transaction executed successfully or not.
	 *
	 * @throws TransactionValidationException
	 */
	public function execute() : bool{
		if($this->hasExecuted()){
			$this->sendInventories();
			return false;
		}

		$this->shuffleActions();

		try{
			$this->validate();
		}catch(TransactionValidationException $e){
			$this->sendInventories();
			throw $e;
		}

		if(!$this->callExecuteEvent()){
			$this->sendInventories();
			return false;
		}

		foreach($this->actions as $action){
			if(!$action->onPreExecute($this->source)){
				$this->sendInventories();
				return false;
			}
		}

		foreach($this->actions as $action){
			if($action->execute($this->source)){
				$action->onExecuteSuccess($this->source);
			}else{
				$action->onExecuteFail($this->source);
			}
		}

		$this->hasExecuted = true;

		return true;
	}

	public function hasExecuted() : bool{
		return $this->hasExecuted;
	}
}
