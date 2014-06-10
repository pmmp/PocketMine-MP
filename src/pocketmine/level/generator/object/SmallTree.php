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

namespace pocketmine\level\generator\object;

use pocketmine\item\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class SmallTree extends Tree{
	public $type = 0;
	private $trunkHeight = 5;
	private static $leavesHeight = 4; // All trees appear to be 4 tall
	private static $leafRadii = array(1, 1.41, 2.83, 2.24);

	private $addLeavesVines = false;
	private $addLogVines = false;
	private $addCocoaPlants = false;

	public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
		$radiusToCheck = 0;
		for($yy = 0; $yy < $this->trunkHeight + 3; ++$yy){
			if($yy == 1 or $yy === $this->trunkHeight){
				++$radiusToCheck;
			}
			for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx){
				for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz){
					if(!isset($this->overridable[$level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz)])){
						return false;
					}
				}
			}
		}

		return true;
	}

	public function placeObject(ChunkManager $level, $x, $y, $z, Random $random){
		// The base dirt block
		$level->setBlockIdAt($x, $y, $z, Block::DIRT);

		// Adjust the tree trunk's height randomly
		//    plot [-14:11] int( x / 8 ) + 5
		//    - min=4 (all leaves are 4 tall, some trunk must show)
		//    - max=6 (top leaves are within ground-level whacking range
		//             on all small trees)
		$heightPre = $random->nextRange(-14, 11);
		$this->trunkHeight = intval($heightPre / 8) + 5;

		// Adjust the starting leaf density using the trunk height as a
		// starting position (tall trees with skimpy leaves don't look
		// too good)
		$leafPre = $random->nextRange($this->trunkHeight, 10) / 20; // (TODO: seed may apply)

		// Now build the tree (from the top down)
		$leaflevel = 0;
		for($yy = ($this->trunkHeight + 1); $yy >= 0; --$yy){
			if($leaflevel < self::$leavesHeight){
				// The size is a slight variation on the trunkheight
				$radius = self::$leafRadii[$leaflevel] + $leafPre;
				$bRadius = 3;
				for($xx = -$bRadius; $xx <= $bRadius; ++$xx){
					for($zz = -$bRadius; $zz <= $bRadius; ++$zz){
						if(sqrt(($xx * $xx) + ($zz * $zz)) <= $radius){
							$level->setBlockIdAt($x + $xx, $y + $yy, $z + $zz, Block::LEAVES);
							$level->setBlockDataAt($x + $xx, $y + $yy, $z + $zz, $this->type);
						}
					}
				}
				$leaflevel++;
			}

			// Place the trunk last
			if($leaflevel > 1){
				$level->setBlockIdAt($x, $y + $yy, $z, Block::TRUNK);
				$level->setBlockDataAt($x, $y + $yy, $z, $this->type);
			}
		}
	}
}