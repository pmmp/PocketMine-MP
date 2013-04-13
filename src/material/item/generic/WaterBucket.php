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

class WaterBucketItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(WATER_BUCKET, 0, $count, "Water Bucket");
		$this->isActivable = true;
		$this->maxStackSize = 1;
	}
	
	public function onActivate(BlockAPI $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->getID() === AIR){
			$level->setBlock($target, STILL_WATER, 0);
			$player->removeItem($this->getID(), $this->getMetadata(), $this->count);
			$player->addItem(BUCKET, 0, 1);
			return true;
		}
		return false;
	}
}