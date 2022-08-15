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

use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

/**
 * This class facilitates generating SlotChangeActions to build an inventory transaction.
 * It wraps around the inventory you want to modify under transaction, and generates a diff of changes.
 * This allows you to use the normal Inventory API methods like addItem() and so on to build a transaction, without
 * modifying the original inventory.
 */
final class TransactionBuilderInventory extends BaseInventory{

	/**
	 * @var \SplFixedArray|(Item|null)[]
	 * @phpstan-var \SplFixedArray<Item|null>
	 */
	private \SplFixedArray $changedSlots;

	public function __construct(
		private Inventory $actualInventory
	){
		parent::__construct();
		$this->changedSlots = new \SplFixedArray($this->actualInventory->getSize());
	}

	protected function internalSetContents(array $items) : void{
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if(!isset($items[$i])){
				$this->clear($i);
			}else{
				$this->setItem($i, $items[$i]);
			}
		}
	}

	protected function internalSetItem(int $index, Item $item) : void{
		if(!$item->equalsExact($this->actualInventory->getItem($index))){
			$this->changedSlots[$index] = $item->isNull() ? VanillaItems::AIR() : clone $item;
		}
	}

	public function getSize() : int{
		return $this->actualInventory->getSize();
	}

	public function getItem(int $index) : Item{
		return $this->changedSlots[$index] !== null ? clone $this->changedSlots[$index] : $this->actualInventory->getItem($index);
	}

	public function getContents(bool $includeEmpty = false) : array{
		$contents = $this->actualInventory->getContents($includeEmpty);
		foreach($this->changedSlots as $index => $item){
			if($item !== null){
				if($includeEmpty || !$item->isNull()){
					$contents[$index] = clone $item;
				}else{
					unset($contents[$index]);
				}
			}
		}
		return $contents;
	}

	/**
	 * @return SlotChangeAction[]
	 */
	public function generateActions() : array{
		$result = [];
		foreach($this->changedSlots as $index => $newItem){
			if($newItem !== null){
				$oldItem = $this->actualInventory->getItem($index);
				if(!$newItem->equalsExact($oldItem)){
					$result[] = new SlotChangeAction($this->actualInventory, $index, $oldItem, $newItem);
				}
			}
		}
		return $result;
	}
}
