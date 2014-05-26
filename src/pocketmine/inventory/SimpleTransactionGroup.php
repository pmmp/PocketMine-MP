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

use pocketmine\item\Item;

/**
 * This TransactionGroup only allows doing Transaction between one / two inventories
 */
class SimpleTransactionGroup implements TransactionGroup{
	private $creationTime;
	protected $hasExecuted = false;

	/** @var Inventory[] */
	protected $inventories = [];

	/** @var Transaction[] */
	protected $transactions = [];

	public function __construct(){
		$this->creationTime = microtime(true);
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function getInventories(){
		return $this->inventories;
	}

	public function getTransactions(){
		return $this->transactions;
	}

	public function addTransaction(Transaction $transaction){
		$this->transactions[spl_object_hash($transaction)] = $transaction;
		$this->inventories[spl_object_hash($transaction->getInventory())] = $transaction->getInventory();
	}

	public function canExecute(){
		/** @var Item[] $needItems */
		$needItems = [];
		/** @var Item[] $haveItems */
		$haveItems = [];
		foreach($this->transactions as $key => $ts){
			$needItems[] = $ts->getTargetItem();
			$checkSourceItem = $ts->getInventory()->getItem($ts->getSlot());
			$sourceItem = $ts->getSourceItem();
			if(!$checkSourceItem->equals($sourceItem, true) or $sourceItem->getCount() !== $checkSourceItem->getCount()){
				return false;
			}
			$haveItems[] = $sourceItem;
		}

		foreach($needItems as $i => $needItem){
			foreach($haveItems as $j => $haveItem){
				if($needItem->equals($haveItem, true)){
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

		return count($haveItems) === 0 and count($needItems) === 0 and count($this->transactions) > 0;
	}

	public function execute(){
		if($this->hasExecuted() or !$this->canExecute()){
			return false;
		}
		foreach($this->transactions as $transaction){
			$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem());
		}

		return true;
	}

	public function hasExecuted(){
		return $this->hasExecuted;
	}
}