<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/


require_once("classes/world/generator/object/tree/TreeObject.php");

class PineTreeObject extends TreeObject{
	var $type = 1;
	private $totalHeight = 8;
	private $leavesSizeY = -1;
	private $leavesAbsoluteMaxRadius = -1;

	public function canPlaceObject(LevelAPI $level, $x, $y, $z){
		$this->findRandomLeavesSize();
		$checkRadius = 0;
		for($yy = 0; $yy < $this->totalHeight; ++$yy) {
			if($yy === $this->leavesSizeY) {
				$checkRadius = $this->leavesAbsoluteMaxRadius;
			}
			for($xx = -$checkRadius; $xx < ($checkRadius + 1); ++$xx){
				for($zz = -$checkRadius; $zz < ($checkRadius + 1); ++$zz){
					$block = $level->getBlock($x + $xx, $y + $yy, $z + $zz);
					if(!isset($this->overridable[$block[0]])){
						return false;
					}
				}
			}
		}
		return true;
	}

	private function findRandomLeavesSize(){
		$this->totalHeight += mt_rand(-1, 2);
		$this->leavesSizeY = 1 + mt_rand(0,2);
		$this->leavesAbsoluteMaxRadius = 2 + mt_rand(0, 2);
	}

	public function placeObject(LevelAPI $level, $x, $y, $z){
		if($this->leavesSizeY === -1 or $this->leavesAbsoluteMaxRadius === -1) {
			$this->findRandomLeavesSize();
		}
		$level->setBlock($x, $y - 1, $z, 3, 0);
		$leavesRadius = mt_rand(0,2);
		$leavesMaxRadius = 1;
		$leavesBottomY = $this->totalHeight - $this->leavesSizeY;
		$firstMaxedRadius = false;
		for($leavesY = 0; $leavesY < ($leavesBottomY + 1); ++$leavesY) {
			$yy = $this->totalHeight - $leavesY;
			for ($xx = -$leavesRadius; $xx < ($leavesRadius + 1); ++$xx) {
				for ($zz = -$leavesRadius; $zz < ($leavesRadius + 1); ++$zz) {
					if (abs($xx) != $leavesRadius or abs($zz) != $leavesRadius or $leavesRadius <= 0) {
						$level->setBlock($x + $xx, $y + $yy, $z + $zz, 18, $this->type);
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
		$trunkHeightReducer = mt_rand(0,3);
		for($yy = 0; $yy < ($this->totalHeight - $trunkHeightReducer); ++$yy){
			$level->setBlock($x, $y + $yy, $z, 17, $this->type);
		}
	}


}