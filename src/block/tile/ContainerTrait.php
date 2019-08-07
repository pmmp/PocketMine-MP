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

namespace pocketmine\block\tile;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\Position;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait{
	/** @var string|null */
	private $lock = null;

	/**
	 * @return Inventory
	 */
	abstract public function getRealInventory();

	protected function loadItems(CompoundTag $tag) : void{
		if($tag->hasTag(Container::TAG_ITEMS, ListTag::class)){
			$inventoryTag = $tag->getListTag(Container::TAG_ITEMS);

			$inventory = $this->getRealInventory();
			$listeners = $inventory->getChangeListeners();
			$inventory->removeChangeListeners(...$listeners); //prevent any events being fired by initialization
			$inventory->clearAll();
			/** @var CompoundTag $itemNBT */
			foreach($inventoryTag as $itemNBT){
				$inventory->setItem($itemNBT->getByte("Slot"), Item::nbtDeserialize($itemNBT));
			}
			$inventory->addChangeListeners(...$listeners);
		}

		if($tag->hasTag(Container::TAG_LOCK, StringTag::class)){
			$this->lock = $tag->getString(Container::TAG_LOCK);
		}
	}

	protected function saveItems(CompoundTag $tag) : void{
		$items = [];
		foreach($this->getRealInventory()->getContents() as $slot => $item){
			$items[] = $item->nbtSerialize($slot);
		}

		$tag->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));

		if($this->lock !== null){
			$tag->setString(Container::TAG_LOCK, $this->lock);
		}
	}

	/**
	 * @see Container::canOpenWith()
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function canOpenWith(string $key) : bool{
		return $this->lock === null or $this->lock === $key;
	}

	/**
	 * @see Position::asPosition()
	 * @return Position
	 */
	abstract protected function getPos() : Position;

	/**
	 * @see Tile::onBlockDestroyedHook()
	 */
	protected function onBlockDestroyedHook() : void{
		$inv = $this->getRealInventory();
		$pos = $this->getPos();

		foreach($inv->getContents() as $k => $item){
			$pos->world->dropItem($pos->add(0.5, 0.5, 0.5), $item);
		}
		$inv->clearAll(false);
	}
}
