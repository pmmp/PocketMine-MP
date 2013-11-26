<?php

/**
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


class TallGrassObject{
	public static function growGrass(Level $level, Vector3 $pos, Random $random, $count = 15, $radius = 10){
		$arr = array(
			BlockAPI::get(DANDELION, 0),
			BlockAPI::get(CYAN_FLOWER, 0),
			BlockAPI::get(TALL_GRASS, 1),
			BlockAPI::get(TALL_GRASS, 1),
			BlockAPI::get(TALL_GRASS, 1),
			BlockAPI::get(TALL_GRASS, 1)
		);
		$arrC = count($arr) - 1;
		for($c = 0; $c < $count; ++$c){
			$x = $random->nextRange($pos->x - $radius, $pos->x + $radius);
			$z = $random->nextRange($pos->z - $radius, $pos->z + $radius);
			if($level->level->getBlockID($x, $pos->y + 1, $z) === AIR and $level->level->getBlockID($x, $pos->y, $z) === GRASS){
				$t = $arr[$random->nextRange(0, $arrC)];
				$level->setBlockRaw(new Vector3($x, $pos->y + 1, $z), $t);
			}
		}
	}
}