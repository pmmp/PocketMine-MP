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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Bed extends Spawnable{

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->color) or !($nbt->color instanceof ByteTag)){
			$nbt->color = new ByteTag("color", 14); //default to old red
		}
		parent::__construct($level, $nbt);
	}

	public function getColor() : int{
		return $this->namedtag->color->getValue();
	}

	public function setColor(int $color){
		$this->namedtag->color->setValue($color & 0x0f);
		$this->onChanged();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		$nbt->color = $this->namedtag->color;
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->color = new ByteTag("color", $item !== null ? $item->getDamage() : 14); //default red
	}
}