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

/**
 * An inventory which is backed by another inventory, and acts as a proxy to that inventory.
 */
class DelegateInventory extends BaseInventory{
	private InventoryListener $inventoryListener;

	public function __construct(
		private Inventory $backingInventory
	){
		parent::__construct();
		$weakThis = \WeakReference::create($this);
		$this->backingInventory->getListeners()->add($this->inventoryListener = new CallbackInventoryListener(
			static function(Inventory $unused, int $slot, Item $oldItem) use ($weakThis) : void{
				if(($strongThis = $weakThis->get()) !== null){
					$strongThis->onSlotChange($slot, $oldItem);
				}
			},
			static function(Inventory $unused, array $oldContents) use ($weakThis) : void{
				if(($strongThis = $weakThis->get()) !== null){
					$strongThis->onContentChange($oldContents);
				}
			}
		));
	}

	public function __destruct(){
		$this->backingInventory->getListeners()->remove($this->inventoryListener);
	}

	public function getSize() : int{
		return $this->backingInventory->getSize();
	}

	public function getItem(int $index) : Item{
		return $this->backingInventory->getItem($index);
	}

	protected function internalSetItem(int $index, Item $item) : void{
		$this->backingInventory->setItem($index, $item);
	}

	public function getContents(bool $includeEmpty = false) : array{
		return $this->backingInventory->getContents($includeEmpty);
	}

	protected function internalSetContents(array $items) : void{
		$this->backingInventory->setContents($items);
	}
}
