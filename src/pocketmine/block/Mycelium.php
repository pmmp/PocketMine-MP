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
use pocketmine\level\Position;

class Mycelium extends Solid{
	public function __construct(){
		parent::__construct(self::MYCELIUM, 0, "Mycelium");
		$this->hardness = 2.5;
	}

	public function getDrops(Item $item){
		return [
			[Item::DIRT, 0, 1],
		];
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_RANDOM){
			//TODO: light levels
			$x = mt_rand($this->x - 1, $this->x + 1);
			$y = mt_rand($this->y - 2, $this->y + 2);
			$z = mt_rand($this->z - 1, $this->z + 1);
			$block = $this->getLevel()->getBlockIdAt($x, $y, $z);
			if($block === Block::DIRT){
				$block = Block::get($block, $this->getLevel()->getBlockDataAt($x, $y, $z), new Position($x, $y, $z, $this->getLevel()));
				if($block->getSide(1) instanceof Transparent){
					$this->getLevel()->setBlock($block, new Mycelium());
				}
			}
		}
	}
}
