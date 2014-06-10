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

class SpruceTree extends Tree{
	var $type = 1;
	private $totalHeight = 8;
	private $leavesBottomY = -1;
	private $leavesMaxRadius = -1;

	public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
		$this->findRandomLeavesSize($random);
		$checkRadius = 0;
		for($yy = 0; $yy < $this->totalHeight + 2; ++$yy){
			if($yy === $this->leavesBottomY){
				$checkRadius = $this->leavesMaxRadius;
			}
			for($xx = -$checkRadius; $xx < ($checkRadius + 1); ++$xx){
				for($zz = -$checkRadius; $zz < ($checkRadius + 1); ++$zz){
					if(!isset($this->overridable[$level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz)])){
						return false;
					}
				}
			}
		}

		return true;
	}

	private function findRandomLeavesSize(Random $random){
		$this->totalHeight += $random->nextRange(-1, 2);
		$this->leavesBottomY = (int) ($this->totalHeight - $random->nextRange(1, 2) - 3);
		$this->leavesMaxRadius = 1 + $random->nextRange(0, 1);
	}

	public function placeObject(ChunkManager $level, $x, $y, $z, Random $random){
		if($this->leavesBottomY === -1 or $this->leavesMaxRadius === -1){
			$this->findRandomLeavesSize($random);
		}
		$level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);
		$leavesRadius = 0;
		for($yy = $this->totalHeight; $yy >= $this->leavesBottomY; --$yy){
			for($xx = -$leavesRadius; $xx <= $leavesRadius; ++$xx){
				for($zz = -$leavesRadius; $zz <= $leavesRadius; ++$zz){
					if(abs($xx) != $leavesRadius or abs($zz) != $leavesRadius or $leavesRadius <= 0){
						$level->setBlockIdAt($x + $xx, $y + $yy, $z + $zz, Block::LEAVES);
						$level->setBlockDataAt($x + $xx, $y + $yy, $z + $zz, $this->type);
					}
				}
			}
			if($leavesRadius > 0 and $yy === ($y + $this->leavesBottomY + 1)){
				--$leavesRadius;
			}elseif($leavesRadius < $this->leavesMaxRadius){
				++$leavesRadius;
			}
		}
		for($yy = 0; $yy < ($this->totalHeight - 1); ++$yy){
			$level->setBlockIdAt($x, $y + $yy, $z, Block::TRUNK);
			$level->setBlockDataAt($x, $y + $yy, $z, $this->type);
		}
	}


}