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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;

/**
 * @deprecated
 * @see \pocketmine\block\FlowerPot
 */
class FlowerPot extends Spawnable{
	private const TAG_ITEM = "item";
	private const TAG_ITEM_DATA = "mData";

	private ?Block $plant = null;

	public function readSaveData(CompoundTag $nbt) : void{
		if(($itemIdTag = $nbt->getTag(self::TAG_ITEM)) instanceof ShortTag && ($itemMetaTag = $nbt->getTag(self::TAG_ITEM_DATA)) instanceof IntTag){
			try{
				$this->setPlant(BlockFactory::getInstance()->get($itemIdTag->getValue(), $itemMetaTag->getValue()));
			}catch(\InvalidArgumentException $e){
				//noop
			}
		}else{
			//TODO: new PlantBlock tag
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->plant !== null){
			$nbt->setShort(self::TAG_ITEM, $this->plant->getId());
			$nbt->setInt(self::TAG_ITEM_DATA, $this->plant->getMeta());
		}
	}

	public function getPlant() : ?Block{
		return $this->plant !== null ? clone $this->plant : null;
	}

	public function setPlant(?Block $plant) : void{
		if($plant === null || $plant instanceof Air){
			$this->plant = null;
		}else{
			$this->plant = clone $plant;
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->plant !== null){
			$nbt->setShort(self::TAG_ITEM, $this->plant->getId());
			$nbt->setInt(self::TAG_ITEM_DATA, $this->plant->getMeta());
		}
	}
}
