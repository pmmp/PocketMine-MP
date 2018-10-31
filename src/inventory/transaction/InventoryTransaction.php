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

/**
 * This InventoryTransaction only allows doing Transaction between one / two inventories
 */
class InventoryTransaction{
	protected $hasExecuted = false;
	/** @var Player */
	protected $source;

	/** @var Inventory[] */
	protected $inventories = [];

	/** @var InventoryAction[] */
	protected $actions = [];

	/**
	 * @param Player            $source
	 * @param InventoryAction[] $actions
	 */
	public function __construct(Player $source, array $actions = []){
		$this->source = $source;
		foreach($actions as $action){
			$this->addAction($action);
		}
	}

	/**
	 * @return Player
	 */
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
	 * @return InventoryAction[]
	 */
	public function getActions() : array{
		return $this->actions;
	}

	/**
	 * @param InventoryAction $action
	 */
	public function addAction(InventoryAction $action) : void{
		if(!isset($this->actions[$hash = spl_object_hash($action)])){
			$this->actions[$hash] = $action;
			$action->onAddToTransaction($this);
		}else{
			throw new \InvalidArgumentException("Tried to add the same action to a transaction twice");
		}
	}

	/**
	 * @internal This method should not be used by plugins, it's used to add tracked inventories for InventoryActions
	 * involving inventories.
	 *
	 * @param Inventory $inventory
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
	 * @param Item               $needOrigin
	 * @param SlotChangeAction[] $possibleActions
	 *
	 * @return null|Item
	 */
	protected function findResultItem(Item $needOrigin, array $possibleActions) : ?Item{
		assert(!empty($possibleActions));

		foreach($possibleActions as $i => $action){
			if($action->getSourceItem()->equalsExact($needOrigin)){
				$newList = $possibleActions;
				unset($newList[$i]);
				if(empty($newList)){
					return $action->getTargetItem();
				}
				$result = $this->findResultItem($action->getTargetItem(), $newList);
				if($result !== null){
					return $result;
				}
			}
		}

		return null;
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
	 * @return bool
	 *
	 * @throws TransactionValidationException
	 */
	public function execute() : bool{
		if($this->hasExecuted()){
			$this->sendInventories();
			return false;
		}

		$this->validate();

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

	/**
	 * @return bool
	 */
	public function hasExecuted() : bool{
		return $this->hasExecuted;
	}
}
