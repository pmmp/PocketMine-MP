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

class BucketItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(BUCKET, 0, $count, "Empty Bucket");
		$this->isActivable = true;
		$this->maxStackSize = 1;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->getID() === STILL_WATER or $target->getID() === STILL_LAVA){
			$level->setBlock($target, new AirBlock());
			$player->removeItem($this->getID(), $this->getMetadata(), $this->count);
			$player->addItem(($target->getID() === STILL_LAVA ? LAVA_BUCKET:WATER_BUCKET), 0, 1);
			return true;
		}
		return false;
	}
}