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

/***REM_START***/
require_once("src/world/generator/object/tree/TreeObject.php");
/***REM_END***/

class PineTreeObject extends TreeObject{
	var $type = 1;
	private $totalHeight = 8;
	private $leavesSizeY = -1;
	private $leavesAbsoluteMaxRadius = -1;

	public function canPlaceObject(Level $level, Vector3 $pos, Random $random){
		$this->findRandomLeavesSize($random);
		$checkRadius = 0;
		for($yy = 0; $yy < $this->totalHeight; ++$yy) {
			if($yy === $this->leavesSizeY) {
				$checkRadius = $this->leavesAbsoluteMaxRadius;
			}
			for($xx = -$checkRadius; $xx < ($checkRadius + 1); ++$xx){
				for($zz = -$checkRadius; $zz < ($checkRadius + 1); ++$zz){
					if(!isset($this->overridable[$level->level->getBlockID($pos->x + $xx, $pos->y + $yy, $pos->z + $zz)])){
						return false;
					}
				}
			}
		}
		return true;
	}

	private function findRandomLeavesSize(Random $random){
		$this->totalHeight += $random->nextRange(-1, 2);
		$this->leavesSizeY = 1 + $random->nextRange(0, 2);
		$this->leavesAbsoluteMaxRadius = 2 + $random->nextRange(0, 1);
	}

	public function placeObject(Level $level, Vector3 $pos, Random $random){
		if($this->leavesSizeY === -1 or $this->leavesAbsoluteMaxRadius === -1) {
			$this->findRandomLeavesSize($random);
		}
		$level->setBlockRaw(new Vector3($pos->x, $pos->y - 1, $pos->z), new DirtBlock());
		$leavesRadius = 0;
		$leavesMaxRadius = 1;
		$leavesBottomY = $this->totalHeight - $this->leavesSizeY;
		$firstMaxedRadius = false;
		for($leavesY = 0; $leavesY <= $leavesBottomY; ++$leavesY) {
			$yy = $this->totalHeight - $leavesY;
			for ($xx = -$leavesRadius; $xx <= $leavesRadius; ++$xx) {
				for ($zz = -$leavesRadius; $zz <= $leavesRadius; ++$zz) {
					if (abs($xx) != $leavesRadius or abs($zz) != $leavesRadius or $leavesRadius <= 0) {
						$level->setBlockRaw(new Vector3($pos->x + $xx, $pos->y + $yy, $pos->z + $zz), new LeavesBlock($this->type));
					}
				}
			}
			if ($leavesRadius >= $leavesMaxRadius) {
				$leavesRadius = $firstMaxedRadius ? 1 : 0;
				$firstMaxedRadius = true;
				if (++$leavesMaxRadius > $this->leavesAbsoluteMaxRadius) {
					$leavesMaxRadius = $this->leavesAbsoluteMaxRadius;
				}
			}else{
				++$leavesRadius;
			}
		}
		$trunkHeightReducer = $random->nextRange(0, 3);
		for($yy = 0; $yy < ($this->totalHeight - $trunkHeightReducer); ++$yy){
			$level->setBlockRaw(new Vector3($pos->x, $pos->y + $yy, $pos->z), new WoodBlock($this->type));
		}
	}


}