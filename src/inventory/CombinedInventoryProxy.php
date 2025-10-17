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
use pocketmine\player\InventoryWindow;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function spl_object_id;

/**
 * Allows interacting with several separate inventories via a unified interface
 * Mainly used for double chests, but could be used for other custom use cases
 */
final class CombinedInventoryProxy extends BaseInventory{

	private readonly int $size;

	/**
	 * @var Inventory[]
	 * @phpstan-var array<int, Inventory>
	 */
	private array $backingInventories = [];
	/**
	 * @var Inventory[]
	 * @phpstan-var array<int, Inventory>
	 */
	private array $slotToInventoryMap = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $inventoryToOffsetMap = [];

	private InventoryListener $backingInventoryListener;
	private bool $modifyingBackingInventory = false;

	/**
	 * @phpstan-param Inventory[] $backingInventories
	 */
	public function __construct(
		array $backingInventories
	){
		parent::__construct();
		foreach($backingInventories as $backingInventory){
			$this->backingInventories[spl_object_id($backingInventory)] = $backingInventory;
		}
		$combinedSize = 0;
		foreach($this->backingInventories as $inventory){
			$size = $inventory->getSize();

			$this->inventoryToOffsetMap[spl_object_id($inventory)] = $combinedSize;
			for($slot = 0; $slot < $size; $slot++){
				$this->slotToInventoryMap[$combinedSize + $slot] = $inventory;
			}

			$combinedSize += $size;
		}
		$this->size = $combinedSize;

		$weakThis = \WeakReference::create($this);
		$this->backingInventoryListener = new CallbackInventoryListener(
			static fn(Inventory $inventory, int $slot, Item $oldItem) => $weakThis->get()?->onBackingSlotChange($inventory, $slot, $oldItem),
			static fn(Inventory $inventory, array $oldContents) => $weakThis->get()?->onBackingContentChange($inventory, $oldContents)
		);
		foreach($this->backingInventories as $inventory){
			$inventory->getListeners()->add($this->backingInventoryListener);
		}
	}

	private function onBackingSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void{
		if($this->modifyingBackingInventory){
			return;
		}

		$offset = $this->inventoryToOffsetMap[spl_object_id($inventory)];
		$this->onSlotChange($offset + $slot, $oldItem);
	}

	/**
	 * @param Item[] $oldContents
	 */
	private function onBackingContentChange(Inventory $inventory, array $oldContents) : void{
		if($this->modifyingBackingInventory){
			return;
		}

		if(count($this->backingInventories) === 1){
			$this->onContentChange($oldContents);
		}else{
			$offset = $this->inventoryToOffsetMap[spl_object_id($inventory)];
			for($slot = 0, $limit = $inventory->getSize(); $slot < $limit; $slot++){
				$this->onSlotChange($offset + $slot, $oldContents[$slot] ?? VanillaItems::AIR());
			}
		}
	}

	public function __destruct(){
		foreach($this->backingInventories as $inventory){
			$inventory->getListeners()->remove($this->backingInventoryListener);
		}
	}

	/**
	 * @phpstan-return array{Inventory, int}
	 */
	private function getInventory(int $slot) : array{
		$inventory = $this->slotToInventoryMap[$slot] ?? throw new \InvalidArgumentException("Invalid combined inventory slot $slot");
		$actualSlot = $slot - $this->inventoryToOffsetMap[spl_object_id($inventory)];
		return [$inventory, $actualSlot];
	}

	protected function internalSetItem(int $index, Item $item) : void{
		[$inventory, $actualSlot] = $this->getInventory($index);

		//Make sure our backing listener doesn't dispatch double updates to our own listeners
		$this->modifyingBackingInventory = true;
		try{
			$inventory->setItem($actualSlot, $item);
		}finally{
			$this->modifyingBackingInventory = false;
		}
	}

	protected function internalSetContents(array $items) : void{
		$contentsByInventory = array_fill_keys(array_keys($this->backingInventories), []);
		foreach($items as $i => $item){
			[$inventory, $actualSlot] = $this->getInventory($i);
			$contentsByInventory[spl_object_id($inventory)][$actualSlot] = $item;
		}
		foreach($contentsByInventory as $splObjectId => $backingInventoryContents){
			$backingInventory = $this->backingInventories[$splObjectId];

			//Make sure our backing listener doesn't dispatch double updates to our own listeners
			$this->modifyingBackingInventory = true;
			try{
				$backingInventory->setContents($backingInventoryContents);
			}finally{
				$this->modifyingBackingInventory = false;
			}
		}
	}

	public function getSize() : int{
		return $this->size;
	}

	public function getItem(int $index) : Item{
		[$inventory, $actualSlot] = $this->getInventory($index);
		return $inventory->getItem($actualSlot);
	}

	public function getContents(bool $includeEmpty = false) : array{
		$result = [];
		foreach($this->backingInventories as $inventory){
			$offset = $this->inventoryToOffsetMap[spl_object_id($inventory)];
			foreach($inventory->getContents($includeEmpty) as $i => $item){
				$result[$offset + $i] = $item;
			}
		}

		return $result;
	}

	public function getMatchingItemCount(int $slot, Item $test, bool $checkTags) : int{
		[$inventory, $actualSlot] = $this->getInventory($slot);
		return $inventory->getMatchingItemCount($actualSlot, $test, $checkTags);
	}

	public function isSlotEmpty(int $index) : bool{
		[$inventory, $actualSlot] = $this->getInventory($index);
		return $inventory->isSlotEmpty($actualSlot);
	}

	public function onOpen(InventoryWindow $window) : void{
		foreach($this->backingInventories as $inventory){
			$inventory->onOpen($window);
		}
	}

	public function onClose(InventoryWindow $window) : void{
		foreach($this->backingInventories as $inventory){
			$inventory->onClose($window);
		}
	}

	public function removeAllWindows() : void{
		foreach($this->backingInventories as $inventory){
			$inventory->removeAllWindows();
		}
	}

	public function getViewers() : array{
		return array_merge(...array_map(fn(Inventory $inventory) => $inventory->getViewers(), $this->backingInventories));
	}
}
