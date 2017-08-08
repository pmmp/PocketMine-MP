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

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

/**
 * This TransactionGroup only allows doing Transaction between one / two inventories
 */
class SimpleTransactionGroup implements TransactionGroup{
	/** @var float */
	private $creationTime;
	protected $hasExecuted = false;
	/** @var Player */
	protected $source = null;

	/** @var Inventory[] */
	protected $inventories = [];

	/** @var Transaction[] */
	protected $transactions = [];

	/**
	 * @param Player $source
	 */
	public function __construct(Player $source = null){
		$this->creationTime = microtime(true);
		$this->source = $source;
	}

	/**
	 * @return Player
	 */
	public function getSource() : Player{
		return $this->source;
	}

	public function getCreationTime() : float{
		return $this->creationTime;
	}

	/**
	 * @return Inventory[]
	 */
	public function getInventories() : array{
		return $this->inventories;
	}

	/**
	 * @return Transaction[]
	 */
	public function getTransactions() : array{
		return $this->transactions;
	}

	public function addTransaction(Transaction $transaction){
		if(isset($this->transactions[spl_object_hash($transaction)])){
			return;
		}
		foreach($this->transactions as $hash => $tx){
			if($tx->getInventory() === $transaction->getInventory() and $tx->getSlot() === $transaction->getSlot()){
				if($transaction->getCreationTime() >= $tx->getCreationTime()){
					unset($this->transactions[$hash]);
				}else{
					return;
				}
			}
		}
		$this->transactions[spl_object_hash($transaction)] = $transaction;
		$this->inventories[spl_object_hash($transaction->getInventory())] = $transaction->getInventory();
	}

	/**
	 * @param Item[] $needItems
	 * @param Item[] $haveItems
	 *
	 * @return bool
	 */
	protected function matchItems(array &$needItems, array &$haveItems) : bool{
		foreach($this->transactions as $key => $ts){
			if($ts->getTargetItem()->getId() !== Item::AIR){
				$needItems[] = $ts->getTargetItem();
			}
			$checkSourceItem = $ts->getInventory()->getItem($ts->getSlot());
			$sourceItem = $ts->getSourceItem();
			if(!$checkSourceItem->equals($sourceItem) or $sourceItem->getCount() !== $checkSourceItem->getCount()){
				return false;
			}
			if($sourceItem->getId() !== Item::AIR){
				$haveItems[] = $sourceItem;
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

		return true;
	}

	public function canExecute() : bool{
		$haveItems = [];
		$needItems = [];

		return $this->matchItems($needItems, $haveItems) and count($this->transactions) > 0 and ((count($haveItems) === 0 and count($needItems) === 0) or $this->source->isCreative(true));
	}

	/**
	 * @return bool
	 */
	public function execute() : bool{
		if($this->hasExecuted() or !$this->canExecute()){
			return false;
		}

		Server::getInstance()->getPluginManager()->callEvent($ev = new InventoryTransactionEvent($this));
		if($ev->isCancelled()){
			foreach($this->inventories as $inventory){
				if($inventory instanceof PlayerInventory){
					$inventory->sendArmorContents($this->getSource());
				}
				$inventory->sendContents($this->getSource());
			}

			return false;
		}

		foreach($this->transactions as $transaction){
			$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem());
		}

		$this->hasExecuted = true;

		return true;
	}

	public function hasExecuted() : bool{
		return $this->hasExecuted;
	}
}