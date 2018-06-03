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

class ItemFrame extends Spawnable{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	/** @var Item */
	private $item;
	/** @var int */
	private $itemRotation;
	/** @var float */
	private $itemDropChance;

	public function __construct(Level $level, CompoundTag $nbt){
		if(($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null){
			$this->item = Item::nbtDeserialize($itemTag);
		}else{
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->itemRotation = $nbt->getByte(self::TAG_ITEM_ROTATION, 0, true);
		$this->itemDropChance = $nbt->getFloat(self::TAG_ITEM_DROP_CHANCE, 1.0, true);
		$nbt->removeTag(self::TAG_ITEM, self::TAG_ITEM_ROTATION, self::TAG_ITEM_DROP_CHANCE);

		parent::__construct($level, $nbt);
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$this->namedtag->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		$this->namedtag->setTag($this->item->nbtSerialize(-1, self::TAG_ITEM));
	}

	public function hasItem() : bool{
		return !$this->item->isNull();
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function setItem(Item $item = null){
		if($item !== null and !$item->isNull()){
			$this->item = clone $item;
		}else{
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->onChanged();
	}

	public function getItemRotation() : int{
		return $this->itemRotation;
	}

	public function setItemRotation(int $rotation){
		$this->itemRotation = $rotation;
		$this->onChanged();
	}

	public function getItemDropChance() : float{
		return $this->itemDropChance;
	}

	public function setItemDropChance(float $chance){
		$this->itemDropChance = $chance;
		$this->onChanged();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		$nbt->setTag($this->item->nbtSerialize(-1, self::TAG_ITEM));
	}
}
