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

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;
use pocketmine\utils\Utils;
use function array_slice;
use function count;
use function max;
use function min;
use function spl_object_id;

/**
 * This class provides everything needed to implement an inventory, minus the underlying storage system.
 *
 * @phpstan-import-type SlotValidators from SlotValidatedInventory
 */
abstract class BaseInventory implements Inventory, SlotValidatedInventory{
	protected int $maxStackSize = Inventory::MAX_STACK;
	/**
	 * @var Player[]
	 * @phpstan-var array<int, Player>
	 */
	protected array $viewers = [];
	/**
	 * @var InventoryListener[]|ObjectSet
	 * @phpstan-var ObjectSet<InventoryListener>
	 */
	protected ObjectSet $listeners;
	/** @phpstan-var SlotValidators */
	protected ObjectSet $validators;

	public function __construct(){
		$this->listeners = new ObjectSet();
		$this->validators = new ObjectSet();
	}

	public function getMaxStackSize() : int{
		return $this->maxStackSize;
	}

	public function setMaxStackSize(int $size) : void{
		$this->maxStackSize = $size;
	}

	abstract protected function internalSetItem(int $index, Item $item) : void;

	public function setItem(int $index, Item $item) : void{
		if($item->isNull()){
			$item = VanillaItems::AIR();
		}else{
			$item = clone $item;
		}

		$oldItem = $this->getItem($index);

		$this->internalSetItem($index, $item);
		$this->onSlotChange($index, $oldItem);
	}

	/**
	 * @param Item[] $items
	 * @phpstan-param array<int, Item> $items
	 */
	abstract protected function internalSetContents(array $items) : void;

	/**
	 * @param Item[] $items
	 * @phpstan-param array<int, Item> $items
	 */
	public function setContents(array $items) : void{
		Utils::validateArrayValueType($items, function(Item $item) : void{});
		if(count($items) > $this->getSize()){
			$items = array_slice($items, 0, $this->getSize(), true);
		}

		$oldContents = $this->getContents(true);

		$listeners = $this->listeners->toArray();
		$this->listeners->clear();
		$viewers = $this->viewers;
		$this->viewers = [];

		$this->internalSetContents($items);

		$this->listeners->add(...$listeners); //don't directly write, in case listeners were added while operation was in progress
		foreach($viewers as $id => $viewer){
			$this->viewers[$id] = $viewer;
		}

		$this->onContentChange($oldContents);
	}

	/**
	 * Helper for utility functions which search the inventory.
	 * TODO: make this abstract instead of providing a slow default implementation (BC break)
	 */
	protected function getMatchingItemCount(int $slot, Item $test, bool $checkTags) : int{
		$item = $this->getItem($slot);
		return $item->equals($test, true, $checkTags) ? $item->getCount() : 0;
	}

	public function contains(Item $item) : bool{
		$count = max(1, $item->getCount());
		$checkTags = $item->hasNamedTag();
		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			$slotCount = $this->getMatchingItemCount($i, $item, $checkTags);
			if($slotCount > 0){
				$count -= $slotCount;
				if($count <= 0){
					return true;
				}
			}
		}

		return false;
	}

	public function all(Item $item) : array{
		$slots = [];
		$checkTags = $item->hasNamedTag();
		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			if($this->getMatchingItemCount($i, $item, $checkTags) > 0){
				$slots[$i] = $this->getItem($i);
			}
		}

		return $slots;
	}

	public function first(Item $item, bool $exact = false) : int{
		$count = $exact ? $item->getCount() : max(1, $item->getCount());
		$checkTags = $exact || $item->hasNamedTag();

		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			$slotCount = $this->getMatchingItemCount($i, $item, $checkTags);
			if($slotCount > 0 && ($slotCount === $count || (!$exact && $slotCount > $count))){
				return $i;
			}
		}

		return -1;
	}

	public function firstEmpty() : int{
		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			if($this->isSlotEmpty($i)){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * TODO: make this abstract and force implementations to implement it properly (BC break)
	 * This default implementation works, but is slow.
	 */
	public function isSlotEmpty(int $index) : bool{
		return $this->getItem($index)->isNull();
	}

	public function canAddItem(Item $item) : bool{
		return $this->getAddableItemQuantity($item) === $item->getCount();
	}

	public function getAddableItemQuantity(Item $item) : int{
		$count = $item->getCount();
		$maxStackSize = min($this->getMaxStackSize(), $item->getMaxStackSize());

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if($this->isSlotEmpty($i)){
				$count -= $maxStackSize;
			}else{
				$slotCount = $this->getMatchingItemCount($i, $item, true);
				if($slotCount > 0 && ($diff = $maxStackSize - $slotCount) > 0){
					$count -= $diff;
				}
			}

			if($count <= 0){
				return $item->getCount();
			}
		}

		return $item->getCount() - $count;
	}

	public function addItem(Item ...$slots) : array{
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach($slots as $slot){
			if(!$slot->isNull()){
				$itemSlots[] = clone $slot;
			}
		}

		/** @var Item[] $returnSlots */
		$returnSlots = [];

		foreach($itemSlots as $item){
			$leftover = $this->internalAddItem($item);
			if(!$leftover->isNull()){
				$returnSlots[] = $leftover;
			}
		}

		return $returnSlots;
	}

	private function internalAddItem(Item $newItem) : Item{
		$emptySlots = [];

		$maxStackSize = min($this->getMaxStackSize(), $newItem->getMaxStackSize());

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if($this->isSlotEmpty($i)){
				$emptySlots[] = $i;
				continue;
			}
			$slotCount = $this->getMatchingItemCount($i, $newItem, true);
			if($slotCount === 0){
				continue;
			}

			if($slotCount < $maxStackSize){
				$amount = min($maxStackSize - $slotCount, $newItem->getCount());
				if($amount > 0){
					$newItem->setCount($newItem->getCount() - $amount);
					$slotItem = $this->getItem($i);
					$slotItem->setCount($slotItem->getCount() + $amount);
					$this->setItem($i, $slotItem);
					if($newItem->getCount() <= 0){
						break;
					}
				}
			}
		}

		if(count($emptySlots) > 0){
			foreach($emptySlots as $slotIndex){
				$amount = min($maxStackSize, $newItem->getCount());
				$newItem->setCount($newItem->getCount() - $amount);
				$slotItem = clone $newItem;
				$slotItem->setCount($amount);
				$this->setItem($slotIndex, $slotItem);
				if($newItem->getCount() <= 0){
					break;
				}
			}
		}

		return $newItem;
	}

	public function remove(Item $item) : void{
		$checkTags = $item->hasNamedTag();

		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			if($this->getMatchingItemCount($i, $item, $checkTags) > 0){
				$this->clear($i);
			}
		}
	}

	public function removeItem(Item ...$slots) : array{
		$searchItems = [];
		foreach($slots as $slot){
			if(!$slot->isNull()){
				$searchItems[] = clone $slot;
			}
		}

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if($this->isSlotEmpty($i)){
				continue;
			}

			foreach($searchItems as $index => $search){
				$slotCount = $this->getMatchingItemCount($i, $search, $search->hasNamedTag());
				if($slotCount > 0){
					$amount = min($slotCount, $search->getCount());
					$search->setCount($search->getCount() - $amount);

					$slotItem = $this->getItem($i);
					$slotItem->setCount($slotItem->getCount() - $amount);
					$this->setItem($i, $slotItem);
					if($search->getCount() <= 0){
						unset($searchItems[$index]);
					}
				}
			}

			if(count($searchItems) === 0){
				break;
			}
		}

		return $searchItems;
	}

	public function clear(int $index) : void{
		$this->setItem($index, VanillaItems::AIR());
	}

	public function clearAll() : void{
		$this->setContents([]);
	}

	public function swap(int $slot1, int $slot2) : void{
		$i1 = $this->getItem($slot1);
		$i2 = $this->getItem($slot2);
		$this->setItem($slot1, $i2);
		$this->setItem($slot2, $i1);
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return $this->viewers;
	}

	/**
	 * Removes the inventory window from all players currently viewing it.
	 */
	public function removeAllViewers() : void{
		foreach($this->viewers as $hash => $viewer){
			if($viewer->getCurrentWindow() === $this){ //this might not be the case for the player's own inventory
				$viewer->removeCurrentWindow();
			}
			unset($this->viewers[$hash]);
		}
	}

	public function onOpen(Player $who) : void{
		$this->viewers[spl_object_id($who)] = $who;
	}

	public function onClose(Player $who) : void{
		unset($this->viewers[spl_object_id($who)]);
	}

	protected function onSlotChange(int $index, Item $before) : void{
		foreach($this->listeners as $listener){
			$listener->onSlotChange($this, $index, $before);
		}
		foreach($this->viewers as $viewer){
			$invManager = $viewer->getNetworkSession()->getInvManager();
			if($invManager === null){
				continue;
			}
			$invManager->onSlotChange($this, $index);
		}
	}

	/**
	 * @param Item[] $itemsBefore
	 * @phpstan-param array<int, Item> $itemsBefore
	 */
	protected function onContentChange(array $itemsBefore) : void{
		foreach($this->listeners as $listener){
			$listener->onContentChange($this, $itemsBefore);
		}

		foreach($this->getViewers() as $viewer){
			$invManager = $viewer->getNetworkSession()->getInvManager();
			if($invManager === null){
				continue;
			}
			$invManager->syncContents($this);
		}
	}

	public function slotExists(int $slot) : bool{
		return $slot >= 0 && $slot < $this->getSize();
	}

	public function getListeners() : ObjectSet{
		return $this->listeners;
	}

	public function getSlotValidators() : ObjectSet{
		return $this->validators;
	}
}
