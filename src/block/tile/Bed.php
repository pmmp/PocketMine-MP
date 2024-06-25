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

use pocketmine\block\utils\DyeColor;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

class Bed extends Spawnable{
	public const TAG_COLOR = "color";

	private DyeColor $color = DyeColor::RED;

	public function getColor() : DyeColor{
		return $this->color;
	}

	public function setColor(DyeColor $color) : void{
		$this->color = $color;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(
			($colorTag = $nbt->getTag(self::TAG_COLOR)) instanceof ByteTag &&
			($color = DyeColorIdMap::getInstance()->fromId($colorTag->getValue())) !== null
		){
			$this->color = $color;
		}else{
			$this->color = DyeColor::RED; //TODO: this should be an error, but we don't have the systems to handle it yet
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_COLOR, DyeColorIdMap::getInstance()->toId($this->color));
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_COLOR, DyeColorIdMap::getInstance()->toId($this->color));
	}
}
