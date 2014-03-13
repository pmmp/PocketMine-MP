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

namespace PocketMine\Level\Generator\Object;

use PocketMine\Block\Block;
use PocketMine\Level\Level;
use PocketMine;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\Utils\Random;

class TallGrass{
	public static function growGrass(Level $level, Vector3 $pos, Random $random, $count = 15, $radius = 10){
		$arr = array(
			Block::get(Block::DANDELION, 0),
			Block::get(Block::CYAN_FLOWER, 0),
			Block::get(Block::TALL_GRASS, 1),
			Block::get(Block::TALL_GRASS, 1),
			Block::get(Block::TALL_GRASS, 1),
			Block::get(Block::TALL_GRASS, 1)
		);
		$arrC = count($arr) - 1;
		for($c = 0; $c < $count; ++$c){
			$x = $random->nextRange($pos->x - $radius, $pos->x + $radius);
			$z = $random->nextRange($pos->z - $radius, $pos->z + $radius);
			if($level->level->getBlockID($x, $pos->y + 1, $z) === Block::AIR and $level->level->getBlockID($x, $pos->y, $z) === Block::GRASS){
				$t = $arr[$random->nextRange(0, $arrC)];
				$level->setBlockRaw(new Vector3($x, $pos->y + 1, $z), $t);
			}
		}
	}
}