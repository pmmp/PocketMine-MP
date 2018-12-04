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

use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;
use pocketmine\utils\Utils;

abstract class BaseInventory implements Inventory{

	/** @var int */
	protected $maxStackSize = Inventory::MAX_STACK;
	/** @var string */
	protected $name;
	/** @var string */
	protected $title;
	/** @var \SplFixedArray|Item[] */
	protected $slots = [];
	/** @var Player[] */
	protected $viewers = [];
	/** @var \Closure */
	protected $slotChangeListener;

	/**
	 * @param Item[] $items
	 * @param int    $size
	 * @param string $title
	 */
	public function __construct(array $items = [], int $size = null, string $title = null){
		$this->slots = new \SplFixedArray($size ?? $this->getDefaultSize());
		$this->title = $title ?? $this->getName();

		$this->setContents($items, false);
	}

	abstract public function getName() : string;

	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * Returns the size of the inventory.
	 * @return int
	 */
	public function getSize() : int{
		return $this->slots->getSize();
	}

	/**
	 * Sets the new size of the inventory.
	 * WARNING: If the size is smaller, any items past the new size will be lost.
	 *
	 * @param int $size
	 */
	public function setSize(int $size){
		$this->slots->setSize($size);
	}

	abstract public function getDefaultSize() : int;

	public function getMaxStackSize() : int{
		return $this->maxStackSize;
	}

	public function getItem(int $index) : Item{
		return $this->slots[$index] !== null ? clone $this->slots[$index] : ItemFactory::get(Item::AIR, 0, 0);
	}

	/**
	 * @param bool $includeEmpty
	 *
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array{
		$contents = [];
		$air = null;

		foreach($this->slots as $i => $slot){
			if($slot !== null){
				$contents[$i] = clone $slot;
			}elseif($includeEmpty){
				$contents[$i] = $air ?? ($air = ItemFactory::get(Item::AIR, 0, 0));
			}
		}

		return $contents;
	}

	/**
	 * @param Item[] $items
	 * @param bool   $send
	 */
	public function setContents(array $items, bool $send = true) : void{
		if(count($items) > $this->getSize()){
			$items = array_slice($items, 0, $this->getSize(), true);
		}

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if(!isset($items[$i])){
				if($this->slots[$i] !== null){
					$this->clear($i, false);
				}
			}else{
				if(!$this->setItem($i, $items[$i], false)){
					$this->clear($i, false);
				}
			}
		}

		if($send){
			$this->sendContents($this->getViewers());
		}
	}

	/**
	 * Drops the contents of the inventory into the specified Level at the specified position and clears the inventory
	 * contents.
	 *
	 * @param Level   $level
	 * @param Vector3 $position
	 */
	public function dropContents(Level $level, Vector3 $position) : void{
		foreach($this->getContents() as $item){
			$level->dropItem($position, $item);
		}

		$this->clearAll();
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool{
		if($item->isNull()){
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}else{
			$item = clone $item;
		}

		$oldItem = $this->getItem($index);
		if($this->slotChangeListener !== null){
			$newItem = ($this->slotChangeListener)($this, $index, $oldItem, $item);
			if($newItem === null){
				return false;
			}
		}else{
			$newItem = $item;
		}

		$this->slots[$index] = $newItem->isNull() ? null : $newItem;
		$this->onSlotChange($index, $oldItem, $send);

		return true;
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
		$item = clone $item;
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$slot = $this->getItem($i);
			if($item->equals($slot, $checkDamage, $checkTags)){
				if(($diff = $slot->getMaxStackSize() - $slot->getCount()) > 0){
					$item->setCount($item->getCount() - $diff);
				}
			}elseif($slot->isNull()){
				$item->setCount($item->getCount() - $this->getMaxStackSize());
			}

			if($item->getCount() <= 0){
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

	public function clear(int $index, bool $send = true) : bool{
		return $this->setItem($index, ItemFactory::get(Item::AIR, 0, 0), $send);
	}

	public function clearAll(bool $send = true) : void{
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$this->clear($i, false);
		}

		if($send){
			$this->sendContents($this->getViewers());
		}
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return $this->viewers;
	}

	/**
	 * Removes the inventory window from all players currently viewing it.
	 *
	 * @param bool $force Force removal of permanent windows such as the player's own inventory. Used internally.
	 */
	public function removeAllViewers(bool $force = false) : void{
		foreach($this->viewers as $hash => $viewer){
			$viewer->removeWindow($this, $force);
			unset($this->viewers[$hash]);
		}
	}

	public function setMaxStackSize(int $size) : void{
		$this->maxStackSize = $size;
	}

	public function open(Player $who) : bool{
		$ev = new InventoryOpenEvent($this, $who);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->onOpen($who);

		return true;
	}

	public function close(Player $who) : void{
		$this->onClose($who);
	}

	public function onOpen(Player $who) : void{
		$this->viewers[spl_object_hash($who)] = $who;
	}

	public function onClose(Player $who) : void{
		unset($this->viewers[spl_object_hash($who)]);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		if($send){
			$this->sendSlot($index, $this->getViewers());
		}
	}


	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventoryContentPacket();
		$pk->items = $this->getContents(true);

		foreach($target as $player){
			if(($id = $player->getWindowId($this)) === ContainerIds::NONE){
				$this->close($player);
				continue;
			}
			$pk->windowId = $id;
			$player->sendDataPacket($pk);
		}
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventorySlotPacket();
		$pk->inventorySlot = $index;
		$pk->item = $this->getItem($index);

		foreach($target as $player){
			if(($id = $player->getWindowId($this)) === ContainerIds::NONE){
				$this->close($player);
				continue;
			}
			$pk->windowId = $id;
			$player->sendDataPacket($pk);
		}
	}

	public function slotExists(int $slot) : bool{
		return $slot >= 0 and $slot < $this->slots->getSize();
	}

	public function getSlotChangeListener() : ?\Closure{
		return $this->slotChangeListener;
	}

	public function setSlotChangeListener(?\Closure $eventProcessor) : void{
		if($eventProcessor !== null){
			Utils::validateCallableSignature(function(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item{}, $eventProcessor);
		}
		$this->slotChangeListener = $eventProcessor;
	}
}
