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


class TreeObject{
	var $overridable = array(
		0 => true,
		6 => true,
		17 => true,
		18 => true,
	);
	public static function growTree(LevelAPI $level, $block, $type){
		switch($type){
			case Sapling::SPRUCE:
				if(mt_rand(0,1) == 1){
					$tree = new SpruceTreeObject();
				}else{
					$tree = new PineTreeObject();
				}
				break;
			case Sapling::BIRCH:
				$tree = new SmallTreeObject();
				$tree->type = Sapling::BIRCH;
				break;
			default:
			case Sapling::OAK:
				if(mt_rand(0,9) === 0){
					$tree = new BigTreeObject();
				}else{
					$tree = new SmallTreeObject();
				}
				break;
		}
		if($tree->canPlaceObject($level, $block[2][0], $block[2][1], $block[2][2])){
			$tree->placeObject($level, $block[2][0], $block[2][1], $block[2][2]);
		}
	}
}