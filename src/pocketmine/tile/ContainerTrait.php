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

namespace pocketmine\tile;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait{

	/**
	 * @return int
	 */
	abstract public function getSize() : int;

	abstract public function getNBT() : CompoundTag;

	/**
	 * @return Inventory
	 */
	abstract public function getRealInventory();

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex(int $index) : int{
		foreach($this->getNBT()->getListTag(Container::TAG_ITEMS) as $i => $slot){
			/** @var CompoundTag $slot */
			if($slot->getByte("Slot") === $index){
				return (int) $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem(int $index) : Item{
		$i = $this->getSlotIndex($index);
		/** @var CompoundTag|null $itemTag */
		$itemTag = $this->getNBT()->getListTag(Container::TAG_ITEMS)[$i] ?? null;
		if($itemTag !== null){
			return Item::nbtDeserialize($itemTag);
		}

		return ItemFactory::get(Item::AIR, 0, 0);
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 */
	public function setItem(int $index, Item $item) : void{
		$i = $this->getSlotIndex($index);

		$d = $item->nbtSerialize($index);

		$items = $this->getNBT()->getListTag(Container::TAG_ITEMS);
		assert($items instanceof ListTag);

		if($item->isNull()){
			if($i >= 0){
				unset($items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($items[$i])){
					break;
				}
			}
			$items[$i] = $d;
		}else{
			$items[$i] = $d;
		}

		$this->getNBT()->setTag($items);
	}

	protected function loadItems() : void{
		if(!$this->getNBT()->hasTag(Container::TAG_ITEMS, ListTag::class)){
			$this->getNBT()->setTag(new ListTag(Container::TAG_ITEMS, [], NBT::TAG_Compound));
		}

		$inventory = $this->getRealInventory();
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$inventory->setItem($i, $this->getItem($i));
		}
	}

	protected function saveItems() : void{
		$this->getNBT()->setTag(new ListTag(Container::TAG_ITEMS, [], NBT::TAG_Compound));

		$inventory = $this->getRealInventory();
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$this->setItem($i, $inventory->getItem($i));
		}
	}
}
