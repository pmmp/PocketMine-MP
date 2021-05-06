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
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;
use function array_map;
use function array_slice;
use function count;
use function spl_object_id;

abstract class BaseInventory implements Inventory{
	use InventoryHelpersTrait;

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
	 * @var InventoryListener[]|ObjectSet
	 * @phpstan-var ObjectSet<InventoryListener>
	 */
	protected $listeners;

	public function __construct(int $size){
		$this->slots = new \SplFixedArray($size);
		$this->listeners = new ObjectSet();
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

		$this->onContentChange($oldContents);
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

	/**
	 * @param Item[] $itemsBefore
	 * @phpstan-param array<int, Item> $itemsBefore
	 */
	protected function onContentChange(array $itemsBefore) : void{
		foreach($this->listeners as $listener){
			$listener->onContentChange($this, $itemsBefore);
		}

		foreach($this->getViewers() as $viewer){
			$viewer->getNetworkSession()->getInvManager()->syncContents($this);
		}
	}

	public function slotExists(int $slot) : bool{
		return $slot >= 0 and $slot < $this->slots->getSize();
	}

	public function getListeners() : ObjectSet{
		return $this->listeners;
	}
}
