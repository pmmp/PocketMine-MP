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

use PocketMine\Block\Sapling as Sapling;
use PocketMine\Level\Level as Level;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine;

class Tree{
	public $overridable = array(
		0 => true,
		2 => true,
		3 => true,
		6 => true,
		17 => true,
		18 => true,
	);

	public static function growTree(Level $level, Vector3 $pos, Level\Random $random, $type = 0){
		switch($type & 0x03){
			case SaplingBlock::SPRUCE:
				if($random->nextRange(0, 1) === 1){
					$tree = new SpruceTree();
				} else{
					$tree = new PineTree();
				}
				break;
			case SaplingBlock::BIRCH:
				$tree = new SmallTree();
				$tree->type = Sapling::BIRCH;
				break;
			case SaplingBlock::JUNGLE:
				$tree = new SmallTree();
				$tree->type = Sapling::JUNGLE;
				break;
			case SaplingBlock::OAK:
			default:
				/*if($random->nextRange(0, 9) === 0){
					$tree = new BigTree();
				}else{*/
				$tree = new SmallTree();
				//}
				break;
		}
		if($tree->canPlaceObject($level, $pos, $random)){
			$tree->placeObject($level, $pos, $random);
		}
	}
}