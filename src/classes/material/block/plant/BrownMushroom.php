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

class BrownMushroomBlock extends FlowableBlock{
	public function __construct(){
		parent::__construct(BROWN_MUSHROOM, 0, "Brown Mushroom");
		$this->isFlowable = true;
	}
	
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$down = $level->getBlockFace($block, 0);
			if($down->isTransparent === false){
				$level->setBlock($block, $this->id, $this->getMetadata());
				return true;
			}
		}
		return false;
	}		
}