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
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;

class ItemFrame extends Spawnable{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	public function __construct(Level $level, CompoundTag $nbt){
		if(!$nbt->hasTag(self::TAG_ITEM_ROTATION, ByteTag::class)){
			$nbt->setByte(self::TAG_ITEM_ROTATION, 0, true);
		}

		if(!$nbt->hasTag(self::TAG_ITEM_DROP_CHANCE, FloatTag::class)){
			$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, 1.0, true);
		}

		parent::__construct($level, $nbt);
	}

	public function hasItem() : bool{
		return !$this->getItem()->isNull();
	}

	public function getItem() : Item{
		$c = $this->namedtag->getCompoundTag(self::TAG_ITEM);
		if($c !== null){
			return Item::nbtDeserialize($c);
		}

		return ItemFactory::get(Item::AIR, 0, 0);
	}

	public function setItem(Item $item = null){
		if($item !== null and !$item->isNull()){
			$this->namedtag->setTag($item->nbtSerialize(-1, self::TAG_ITEM));
		}else{
			$this->namedtag->removeTag(self::TAG_ITEM);
		}
		$this->onChanged();
	}

	public function getItemRotation() : int{
		return $this->namedtag->getByte(self::TAG_ITEM_ROTATION);
	}

	public function setItemRotation(int $rotation){
		$this->namedtag->setByte(self::TAG_ITEM_ROTATION, $rotation);
		$this->onChanged();
	}

	public function getItemDropChance() : float{
		return $this->namedtag->getFloat(self::TAG_ITEM_DROP_CHANCE);
	}

	public function setItemDropChance(float $chance){
		$this->namedtag->setFloat(self::TAG_ITEM_DROP_CHANCE, $chance);
		$this->onChanged();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag($this->namedtag->getTag(self::TAG_ITEM_DROP_CHANCE));
		$nbt->setTag($this->namedtag->getTag(self::TAG_ITEM_ROTATION));

		if($this->hasItem()){
			$nbt->setTag($this->namedtag->getTag(self::TAG_ITEM));
		}
	}
}
