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

class PondPopulator extends Populator{
	private $waterOdd = 4;
	private $lavaOdd = 4;
	private $lavaSurfaceOdd = 4;
	public function populate(Level $level, $chunkX, $chunkZ, Random $random){
		if($random->nextRange(0, $this->waterOdd) === 0){
			$v = new Vector3(
				$random->nextRange($chunkX << 4, ($chunkX << 4) + 16),
				$random->nextRange(0, 128),
				$random->nextRange($chunkZ << 4, ($chunkZ << 4) + 16)
			);
			$pond = new PondObject($random, new WaterBlock());
			if($pond->canPlaceObject($level, $v)){
				$pond->placeObject($level, $v);
			}
		}
	}
	
	public function setWaterOdd($waterOdd){
		$this->waterOdd = $waterOdd;
	}

	public function setLavaOdd($lavaOdd){
		$this->lavaOdd = $lavaOdd;
	}

	public function setLavaSurfaceOdd($lavaSurfaceOdd){
		$this->lavaSurfaceOdd = $lavaSurfaceOdd;
	}
}