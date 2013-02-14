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

class BedBlock extends TransparentBlock{
	public function __construct($type = 0){
		parent::__construct(BED_BLOCK, $type, "Bed");
		$this->isActivable = true;
	}
	
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$down = $level->getBlockFace($block, 0);
			if($down->isTransparent === false){
				$faces = array(
					0 => 3,
					1 => 4,
					2 => 2,
					3 => 5,
				);
				$d = $player->entity->getDirection();
				$next = $level->getBlockFace($block, $faces[(($d + 3) % 4)]);
				$downNext = $level->getBlockFace($next, 0);
				if($next->isReplaceable === true and $downNext->isTransparent === false){
					$meta = (($d + 3) % 4) & 0x03;
					$level->setBlock($block, $this->id, $meta);
					$level->setBlock($next, $this->id, $meta | 0x08);
					return true;
				}
			}
		}
		return false;
	}	
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(BED, 0, 1),
		);
	}
	
}