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

use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\item\Book;
use pocketmine\item\Item;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;
use function array_keys;
use function array_map;

class ChiseledBookshelf extends Spawnable{

	public const TAG_ITEMS = "Items";

	/** @var (WritableBookBase|Book)[] $items */
	private array $items = [];

	public function __construct(World $world, Vector3 $pos) {
		parent::__construct($world, $pos);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{

	}

	public function readSaveData(CompoundTag $nbt) : void{
		$itemsTag = $nbt->getListTag(self::TAG_ITEMS);
		if($itemsTag !== null && $itemsTag->getTagType() === NBT::TAG_Compound){
			/** @var CompoundTag $itemNBT */
			foreach($itemsTag->getValue() as $itemNBT){
				/** @var WritableBookBase|Book $item */
				$item = Item::nbtDeserialize($itemNBT);
				$this->items[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = $item;
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setTag(self::TAG_ITEMS, new ListTag(array_map(fn(WritableBookBase|Book $item, int $index) => $item->nbtSerialize($index), $this->items, array_keys($this->items)), NBT::TAG_Compound));
	}

	/**
	 * @return (WritableBookBase|Book)[]
	 */
	public function getBooks() : array{
		return $this->items;
	}

	/**
	 * @param (WritableBookBase|Book)[] $items
	 */
	public function setBooks(array $items) : void{
		$this->items = $items;
	}
}
