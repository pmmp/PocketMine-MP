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
	public static function growTree(BlockAPI $level, Block $block){
		switch($block->getMetadata() & 0x03){
			case SaplingBlock::SPRUCE:
				if(mt_rand(0,1) == 1){
					$tree = new SpruceTreeObject();
				}else{
					$tree = new PineTreeObject();
				}
				break;
			case SaplingBlock::BIRCH:
				$tree = new SmallTreeObject();
				$tree->type = SaplingBlock::BIRCH;
				break;
			default:
			case SaplingBlock::OAK:
				if(mt_rand(0,9) === 0){
					$tree = new BigTreeObject();
				}else{
					$tree = new SmallTreeObject();
				}
				break;
		}
		if($tree->canPlaceObject($level, $block->x, $block->y, $block->z)){
			$tree->placeObject($level, $block->x, $block->y, $block->z);
		}
	}
}