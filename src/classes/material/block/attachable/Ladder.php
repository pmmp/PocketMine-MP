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

class LadderBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(LADDER, $meta, "Ladder");
	}
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			if($target->isTransparent === false){
				$faces = array(
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
				);
				$level->setBlock($block, $this->id, $faces[$face]);
				return true;
			}
		}
		return false;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}		
}