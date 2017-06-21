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
use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class ItemFrame extends Spawnable{

	public function __construct(Level $level, CompoundTag $nbt){
		$itemRotation = $nbt->getTag("ItemRotation");
		if(!($itemRotation instanceof ByteTag)){
			$nbt->setTag(new ByteTag("ItemRotation", 0));
		}

		$itemDropChance = $nbt->getTag("ItemDropChance");
		if(!($itemDropChance instanceof FloatTag)){
			$nbt->setTag(new FloatTag("ItemDropChance", 1.0));
		}

		parent::__construct($level, $nbt);
	}

	public function hasItem() : bool{
		return $this->getItem()->getId() !== Item::AIR;
	}

	public function getItem() : Item{
		$itemTag = $this->namedtag->getCompoundTag("Item");
		return $itemTag !== null ? Item::nbtDeserialize($itemTag) : Item::get(Item::AIR, 0, 0);
	}

	public function setItem(Item $item = null){
		if($item !== null and $item->getId() !== Item::AIR){
			$this->namedtag->setTag($item->nbtSerialize(-1, "Item"));
		}else{
			$this->namedtag->remove("Item");
		}
		$this->onChanged();
	}

	public function getItemRotation() : int{
		return $this->namedtag->getTag("ItemRotation")->getValue();
	}

	public function setItemRotation(int $rotation){
		$this->namedtag->setTag(new ByteTag("ItemRotation", $rotation));
		$this->onChanged();
	}

	public function getItemDropChance() : float{
		return $this->namedtag->getTag("ItemDropChance")->getValue();
	}

	public function setItemDropChance(float $chance){
		$this->namedtag->setTag(new FloatTag("ItemDropChance", $chance));
		$this->onChanged();
	}

	public function getSpawnCompound(){
		$tag = new CompoundTag("", [
			new StringTag("id", Tile::ITEM_FRAME),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag->getTag("ItemDropChance"),
			$this->namedtag->getTag("ItemRotation"),
		]);
		if($this->hasItem()){
			$tag->setTag(clone $this->namedtag->getCompoundTag("Item"));
		}
		return $tag;
	}

}