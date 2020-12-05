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

use Ds\Set;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use function array_map;
use function array_slice;
use function count;
use function max;
use function min;
use function spl_object_id;

abstract class BaseInventory implements Inventory{

	/** @var int */
	protected $maxStackSize = Inventory::MAX_STACK;
	/**
	 * @var \SplFixedArray|(Item|null)[]
	 * @phpstan-var \SplFixedArray<Item|null>
	 */
	protected $slots;
	/** @var Player[] */
	protected $viewers = [];
	/**
	 * @var InventoryListener[]|Set
	 * @phpstan-var Set<InventoryListener>
	 */
	protected $listeners;

	public function __construct(int $size){
		$this->slots = new \SplFixedArray($size);
		$this->listeners = new Set();
	}

	/**
	 * Returns the size of the inventory.
	 */
	public function getSize() : int{
		return $this->slots->getSize();
	}

	public function getMaxStackSize() : int{
		return $this->maxStackSize;
	}

	public function getItem(int $index) : Item{
		return $this->slots[$index] !== null ? clone $this->slots[$index] : ItemFactory::air();
	}

	/**
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array{
		$contents = [];

		foreach($this->slots as $i => $slot){
			if($slot !== null){
				$contents[$i] = clone $slot;
			}elseif($includeEmpty){
				$contents[$i] = ItemFactory::air();
			}
		}

		return $contents;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items) : void{
		if(count($items) > $this->getSize()){
			$items = array_slice($items, 0, $this->getSize(), true);
		}

		$oldContents = array_map(function(?Item $item) : Item{
			return $item ?? ItemFactory::air();
		}, $this->slots->toArray());

		$listeners = $this->listeners->toArray();
		$this->listeners->clear();
		$viewers = $this->viewers;
		$this->viewers = [];

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if(!isset($items[$i])){
				$this->clear($i);
			}else{
				$this->setItem($i, $items[$i]);
			}
		}

		$this->listeners->add(...$listeners); //don't directly write, in case listeners were added while operation was in progress
		foreach($viewers as $id => $viewer){
			$this->viewers[$id] = $viewer;
		}

		foreach($this->listeners as $listener){
			$listener->onContentChange($this, $oldContents);
		}

		foreach($this->getViewers() as $viewer){
			$viewer->getNetworkSession()->getInvManager()->syncContents($this);
		}
	}

	public function setItem(int $index, Item $item) : void{
		if($item->isNull()){
			$item = ItemFactory::air();
		}else{
			$item = clone $item;
		}

		$oldItem = $this->getItem($index);

		$this->slots[$index] = $item->isNull() ? null : $item;
		$this->onSlotChange($index, $oldItem);
	}

	public function contains(Item $item) : bool{
		$count = max(1, $item->getCount());
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();
		foreach($this->getContents() as $i){
			if($item->equals($i, $checkDamage, $checkTags)){
				$count -= $i->getCount();
				if($count <= 0){
					return true;
				}
			}
		}

		return false;
	}

	public function all(Item $item) : array{
		$slots = [];
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();
		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage, $checkTags)){
				$slots[$index] = $i;
			}
		}

		return $slots;
	}

	public function remove(Item $item) : void{
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();

		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage, $checkTags)){
				$this->clear($index);
			}
		}
	}

	public function first(Item $item, bool $exact = false) : int{
		$count = $exact ? $item->getCount() : max(1, $item->getCount());
		$checkDamage = $exact || !$item->hasAnyDamageValue();
		$checkTags = $exact || $item->hasNamedTag();

		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage, $checkTags) and ($i->getCount() === $count or (!$exact and $i->getCount() > $count))){
				return $index;
			}
		}

		return -1;
	}

	public function firstEmpty() : int{
		foreach($this->slots as $i => $slot){
			if($slot === null or $slot->isNull()){
				return $i;
			}
		}

		return -1;
	}

	public function isSlotEmpty(int $index) : bool{
		return $this->slots[$index] === null or $this->slots[$index]->isNull();
	}

	public function canAddItem(Item $item) : bool{
		$count = $item->getCount();
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$slot = $this->getItem($i);
			if($item->equals($slot)){
				if(($diff = min($slot->getMaxStackSize(), $item->getMaxStackSize()) - $slot->getCount()) > 0){
					$count -= $diff;
				}
			}elseif($slot->isNull()){
				$count -= min($this->getMaxStackSize(), $item->getMaxStackSize());
			}

			if($count <= 0){
				return true;
			}
		}

		return false;
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

		$emptySlots = [];

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$item = $this->getItem($i);
			if($item->isNull()){
				$emptySlots[] = $i;
			}

			foreach($itemSlots as $index => $slot){
				if($slot->equals($item) and $item->getCount() < $item->getMaxStackSize()){
					$amount = min($item->getMaxStackSize() - $item->getCount(), $slot->getCount(), $this->getMaxStackSize());
					if($amount > 0){
						$slot->setCount($slot->getCount() - $amount);
						$item->setCount($item->getCount() + $amount);
						$this->setItem($i, $item);
						if($slot->getCount() <= 0){
							unset($itemSlots[$index]);
						}
					}
				}
			}

			if(count($itemSlots) === 0){
				break;
			}
		}

		if(count($itemSlots) > 0 and count($emptySlots) > 0){
			foreach($emptySlots as $slotIndex){
				//This loop only gets the first item, then goes to the next empty slot
				foreach($itemSlots as $index => $slot){
					$amount = min($slot->getMaxStackSize(), $slot->getCount(), $this->getMaxStackSize());
					$slot->setCount($slot->getCount() - $amount);
					$item = clone $slot;
					$item->setCount($amount);
					$this->setItem($slotIndex, $item);
					if($slot->getCount() <= 0){
						unset($itemSlots[$index]);
					}
					break;
				}
			}
		}

		return $itemSlots;
	}

	public function removeItem(Item ...$slots) : array{
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach($slots as $slot){
			if(!$slot->isNull()){
				$itemSlots[] = clone $slot;
			}
		}

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$item = $this->getItem($i);
			if($item->isNull()){
				continue;
			}

			foreach($itemSlots as $index => $slot){
				if($slot->equals($item, !$slot->hasAnyDamageValue(), $slot->hasNamedTag())){
					$amount = min($item->getCount(), $slot->getCount());
					$slot->setCount($slot->getCount() - $amount);
					$item->setCount($item->getCount() - $amount);
					$this->setItem($i, $item);
					if($slot->getCount() <= 0){
						unset($itemSlots[$index]);
					}
				}
			}

			if(count($itemSlots) === 0){
				break;
			}
		}

		return $itemSlots;
	}

	public function clear(int $index) : void{
		$this->setItem($index, ItemFactory::air());
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

	public function setMaxStackSize(int $size) : void{
		$this->maxStackSize = $size;
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
			$viewer->getNetworkSession()->getInvManager()->syncSlot($this, $index);
		}
	}

	public function slotExists(int $slot) : bool{
		return $slot >= 0 and $slot < $this->slots->getSize();
	}

	public function getListeners() : Set{
		return $this->listeners;
	}
}
