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

class BigTreeObject extends TreeObject{
	private $trunkHeightMultiplier = 0.618;
	private $trunkHeight;
	private $leafAmount = 1;
	private $leafDistanceLimit = 5;
	private $widthScale = 1;
	private $branchSlope = 0.381;

	private $totalHeight = 6;
	private $leavesHeight = 3;
	protected $radiusIncrease = 0;
	private $addLeavesVines = false;
	private $addLogVines = false;
	private $addCocoaPlants = false;

	public function canPlaceObject(BlockAPI $level, $x, $y, $z){
		return false;
	}

	public function placeObject(BlockAPI $level, $x, $y, $z, $type){

		$this->trunkHeight = (int) ($this->totalHeight * $this->trunkHeightMultiplier);
		$leaves = $this->getLeafGroupPoints($level, $x, $y, $z);
		foreach($leaves as $leafGroup){
			$groupX = $leafGroup->getBlockX();
			$groupY = $leafGrou->getBlockY();
			$groupZ = $leafGroup->getBlockZ();
			for ($yy = $groupY; $yy < $groupY + $this->leafDistanceLimit; ++$yy) {
				$this->generateGroupLayer($level, $groupX, $yy, $groupZ, $this->getLeafGroupLayerSize($yy - $groupY));
			}
		}
		/*final BlockIterator trunk = new BlockIterator(new Point(w, x, y - 1, z), new Point(w, x, y + trunkHeight, z));
		while (trunk.hasNext()) {
			trunk.next().setMaterial(VanillaMaterials.LOG, logMetadata);
		}
		generateBranches(w, x, y, z, leaves);

		$level->setBlock($x, $y - 1, $z, 3, 0);
		$this->totalHeight += mt_rand(-1, 3);
		$this->leavesHeight += mt_rand(0, 1);
		for($yy = ($this->totalHeight - $this->leavesHeight); $yy < ($this->totalHeight + 1); ++$yy){
			$yRadius = ($yy - $this->totalHeight);
			$xzRadius = (int) (($this->radiusIncrease + 1) - $yRadius / 2);
			for($xx = -$xzRadius; $xx < ($xzRadius + 1); ++$xx){
				for($zz = -$xzRadius; $zz < ($xzRadius + 1); ++$zz){
					if((abs($xx) != $xzRadius or abs($zz) != $xzRadius) and $yRadius != 0){
						$level->setBlock($x + $xx, $y + $yy, $z + $zz, 18, $type);
					}
				}
			}
		}
		for($yy = 0; $yy < ($this->totalHeight - 1); ++$yy){
			$level->setBlock($x, $y + $yy, $z, 17, $type);
		}
		*/
	}


}