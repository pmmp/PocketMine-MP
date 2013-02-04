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

class CactusBlock extends TransparentBlock{
	public function __construct(){
		parent::__construct(CACTUS, 0, "Cactus");
	}
	
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$down = $level->getBlockFace($block, 0);
			if($down->getID() === SAND or $down->getID() === CACTUS){
				$block0 = $level->getBlockFace($block, 2);
				$block1 = $level->getBlockFace($block, 3);
				$block2 = $level->getBlockFace($block, 4);
				$block3 = $level->getBlockFace($block, 5);
				if($block0->isFlowable === true and $block1->isFlowable === true and $block2->isFlowable === true and $block3->isFlowable === true){
					$level->setBlock($block, $this->id, 0);
					return true;
				}
			}
		}
		return false;
	}
	
}