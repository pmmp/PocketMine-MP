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

class SpruceTreeObject extends TreeObject{
	var $type = 1;
	private $totalHeight = 8;
	private $leavesBottomY = -1;
	private $leavesMaxRadius = -1;

	public function canPlaceObject(LevelAPI $level, $x, $y, $z){
		$this->findRandomLeavesSize();
		$checkRadius = 0;
		for($yy = 0; $yy < $this->totalHeight + 2; ++$yy) {
			if($yy === $this->leavesBottomY) {
				$checkRadius = $this->leavesMaxRadius;
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
		$this->leavesBottomY = (int) ($this->totalHeight - mt_rand(1,2) - 3);
		$this->leavesMaxRadius = 1 + mt_rand(0, 1);
	}

	public function placeObject(LevelAPI $level, $x, $y, $z){
		if($this->leavesBottomY === -1 or $this->leavesMaxRadius === -1) {
			$this->findRandomLeavesSize();
		}
		$level->setBlock($x, $y - 1, $z, 3, 0);
		$leavesRadius = 0;
		for($yy = $this->totalHeight; $yy >= $this->leavesBottomY; --$yy){
			for ($xx = -$leavesRadius; $xx < ($leavesRadius + 1); ++$xx) {
				for ($zz = -$leavesRadius; $zz < ($leavesRadius + 1); ++$zz) {
					if (abs($xx) != $leavesRadius or abs($zz) != $leavesRadius or $leavesRadius <= 0) {
						$level->setBlock($x + $xx, $y + $yy, $z + $zz, 18, $this->type);
					}
				}
			}
			if ($leavesRadius > 0 and $yy === ($y + $this->leavesBottomY + 1)) {
				--$leavesRadius;
			}elseif($leavesRadius < $this->leavesMaxRadius){
				++$leavesRadius;
			}
		}
		for($yy = 0; $yy < ($this->totalHeight - 1); ++$yy){
			$level->setBlock($x, $y + $yy, $z, 17, $this->type);
		}
	}


}