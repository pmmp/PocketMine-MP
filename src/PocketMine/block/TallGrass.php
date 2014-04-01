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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;

class TallGrass extends Flowable{
	public function __construct($meta = 1){
		parent::__construct(self::TALL_GRASS, $meta, "Tall Grass");
		$this->isReplaceable = true;
		$names = array(
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 0;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent === true){ //Replace with common break method
				$this->level->setBlock($this, new Air(), false, false, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		$drops = array();
		$possibleDrops = array(
			array(Item::WHEAT_SEEDS, 0, 1),
			array(Item::CARROT, 0, 1),
			array(Item::POTATO, 0, 1),
			array(Item::BEETROOT_SEEDS, 0, 1),
			array(Item::MELON_SEEDS, 0, 1),
			array(Item::PUMPKIN_SEEDS, 0, 1),
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
		);
		if(($item = $possibleDrops[mt_rand(0, count($possibleDrops) - 1)]) !== 0){
			$drops[] = $item;
		}

		return $drops;
	}

}