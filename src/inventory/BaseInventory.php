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
 */
abstract class BaseInventory implements Inventory{
	/** @var int */
	protected $maxStackSize = Inventory::MAX_STACK;
	/** @var Player[] */
	protected $viewers = [];
	/**
	 * @var InventoryListener[]|ObjectSet
	 * @phpstan-var ObjectSet<InventoryListener>
	 */
	protected $listeners;

	public function __construct(){
		$this->listeners = new ObjectSet();
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

	public function first(Item $item, bool $exact = false) : int{
		$count = $exact ? $item->getCount() : max(1, $item->getCount());
		$checkDamage = $exact || !$item->hasAnyDamageValue();
		$checkTags = $exact || $item->hasNamedTag();

		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage, $checkTags) && ($i->getCount() === $count || (!$exact && $i->getCount() > $count))){
				return $index;
			}
		}

		return -1;
	}

	public function firstEmpty() : int{
		foreach($this->getContents(true) as $i => $slot){
			if($slot->isNull()){
				return $i;
			}
		}

		return -1;
	}

	public function isSlotEmpty(int $index) : bool{
		return $this->getItem($index)->isNull();
	}

	public function canAddItem(Item $item) : bool{
		return $this->getAddableItemQuantity($item) === $item->getCount();
	}

	public function getAddableItemQuantity(Item $item) : int{
		$count = $item->getCount();
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$slot = $this->getItem($i);
			if($item->canStackWith($slot)){
				if(($diff = min($slot->getMaxStackSize(), $item->getMaxStackSize()) - $slot->getCount()) > 0){
					$count -= $diff;
				}
			}elseif($slot->isNull()){
				$count -= min($this->getMaxStackSize(), $item->getMaxStackSize());
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

	private function internalAddItem(Item $slot) : Item{
		$emptySlots = [];

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$item = $this->getItem($i);
			if($item->isNull()){
				$emptySlots[] = $i;
			}

			if($slot->canStackWith($item) && $item->getCount() < $item->getMaxStackSize()){
				$amount = min($item->getMaxStackSize() - $item->getCount(), $slot->getCount(), $this->getMaxStackSize());
				if($amount > 0){
					$slot->setCount($slot->getCount() - $amount);
					$item->setCount($item->getCount() + $amount);
					$this->setItem($i, $item);
					if($slot->getCount() <= 0){
						break;
					}
				}
			}
		}

		if(count($emptySlots) > 0){
			foreach($emptySlots as $slotIndex){
				$amount = min($slot->getMaxStackSize(), $slot->getCount(), $this->getMaxStackSize());
				$slot->setCount($slot->getCount() - $amount);
				$item = clone $slot;
				$item->setCount($amount);
				$this->setItem($slotIndex, $item);
				if($slot->getCount() <= 0){
					break;
				}
			}
		}

		return $slot;
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
			$invManager->syncSlot($this, $index);
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
}
