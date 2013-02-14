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

/***REM_START***/
require_once("src/world/generator/object/tree/TreeObject.php");
/***REM_END***/

class SmallTreeObject extends TreeObject{
	var $type = 0;
	private $totalHeight = 6;
	private $leavesHeight = 3;
	protected $radiusIncrease = 0;
	private $addLeavesVines = false;
	private $addLogVines = false;
	private $addCocoaPlants = false;

	public function canPlaceObject(BlockAPI $level, $x, $y, $z){
		$radiusToCheck = $this->radiusIncrease;
		for ($yy = 0; $yy < $this->totalHeight + 2; ++$yy) {
			if ($yy == 1 or $yy === $this->totalHeight - 1) {
				++$radiusToCheck;
			}
			for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx){
				for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz){
					$block = $level->getBlock(new Vector3($x + $xx, $y + $yy, $z + $zz));
					if(!isset($this->overridable[$block->getID()])){
						return false;
					}
				}
			}
		}
		return true;
	}

	public function placeObject(BlockAPI $level, $x, $y, $z){
		$level->setBlock(new Vector3($x, $y - 1, $z), 3, 0);
		$this->totalHeight += mt_rand(-1, 3);
		$this->leavesHeight += mt_rand(0, 1);
		for($yy = ($this->totalHeight - $this->leavesHeight); $yy < ($this->totalHeight + 1); ++$yy){
			$yRadius = ($yy - $this->totalHeight);
			$xzRadius = (int) (($this->radiusIncrease + 1) - $yRadius / 2);
			for($xx = -$xzRadius; $xx < ($xzRadius + 1); ++$xx){
				for($zz = -$xzRadius; $zz < ($xzRadius + 1); ++$zz){
					if((abs($xx) != $xzRadius or abs($zz) != $xzRadius) and $yRadius != 0){
						$level->setBlock(new Vector3($x + $xx, $y + $yy, $z + $zz), 18, $this->type);
					}
				}
			}
		}
		for($yy = 0; $yy < ($this->totalHeight - 1); ++$yy){
			$level->setBlock(new Vector3($x, $y + $yy, $z), 17, $this->type);
		}
	}


}