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

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\World;

/**
 * @deprecated
 * @see \pocketmine\block\ItemFrame
 */
class ItemFrame extends Spawnable{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	private Item $item;
	private int $itemRotation = 0;
	private float $itemDropChance = 1.0;

	public function __construct(World $world, Vector3 $pos){
		$this->item = VanillaItems::AIR();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null){
			$this->item = Item::nbtDeserialize($itemTag);
		}
		if($nbt->getTag(self::TAG_ITEM_ROTATION) instanceof FloatTag){
			$this->itemRotation = (int) ($nbt->getFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45) / 45);
		} else {
			$this->itemRotation = $nbt->getByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		}
		$this->itemDropChance = $nbt->getFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45);
		if(!$this->item->isNull()){
			$nbt->setTag(self::TAG_ITEM, $this->item->nbtSerialize());
		}
	}

	public function hasItem() : bool{
		return !$this->item->isNull();
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function setItem(?Item $item) : void{
		if($item !== null && !$item->isNull()){
			$this->item = clone $item;
		}else{
			$this->item = VanillaItems::AIR();
		}
	}

	public function getItemRotation() : int{
		return $this->itemRotation;
	}

	public function setItemRotation(int $rotation) : void{
		$this->itemRotation = $rotation;
	}

	public function getItemDropChance() : float{
		return $this->itemDropChance;
	}

	public function setItemDropChance(float $chance) : void{
		$this->itemDropChance = $chance;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setFloat(self::TAG_ITEM_ROTATION, $this->itemRotation * 45);
		if(!$this->item->isNull()){
			$nbt->setTag(self::TAG_ITEM, TypeConverter::getInstance()->getItemTranslator()->toNetworkNbt($this->item));
		}
	}
}
