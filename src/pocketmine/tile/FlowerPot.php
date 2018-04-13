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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;

class FlowerPot extends Spawnable{
	public const TAG_ITEM = "item";
	public const TAG_ITEM_DATA = "mData";

	public function __construct(Level $level, CompoundTag $nbt){
		//TODO: check PC format
		if(!$nbt->hasTag(self::TAG_ITEM, ShortTag::class)){
			$nbt->setShort(self::TAG_ITEM, 0, true);
		}
		if(!$nbt->hasTag(self::TAG_ITEM_DATA, IntTag::class)){
			$nbt->setInt(self::TAG_ITEM_DATA, 0, true);
		}
		parent::__construct($level, $nbt);
	}

	public function canAddItem(Item $item) : bool{
		if(!$this->isEmpty()){
			return false;
		}
		switch($item->getId()){
			/** @noinspection PhpMissingBreakStatementInspection */
			case Item::TALL_GRASS:
				if($item->getDamage() === 1){
					return false;
				}
			case Item::SAPLING:
			case Item::DEAD_BUSH:
			case Item::DANDELION:
			case Item::RED_FLOWER:
			case Item::BROWN_MUSHROOM:
			case Item::RED_MUSHROOM:
			case Item::CACTUS:
				return true;
			default:
				return false;
		}
	}

	public function getItem() : Item{
		return ItemFactory::get($this->namedtag->getShort(self::TAG_ITEM), $this->namedtag->getInt(self::TAG_ITEM_DATA), 1);
	}

	public function setItem(Item $item){
		$this->namedtag->setShort(self::TAG_ITEM, $item->getId());
		$this->namedtag->setInt(self::TAG_ITEM_DATA, $item->getDamage());
		$this->onChanged();
	}

	public function removeItem(){
		$this->setItem(ItemFactory::get(Item::AIR, 0, 0));
	}

	public function isEmpty() : bool{
		return $this->getItem()->isNull();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag($this->namedtag->getTag(self::TAG_ITEM));
		$nbt->setTag($this->namedtag->getTag(self::TAG_ITEM_DATA));
	}
}
