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

class WallSignBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(WALL_SIGN, $meta, "Wall Sign");
	}
	
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true and $face !== 0){
			if($face !== 0){
				$faces = array(
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
				);
				if(!isset($faces[$face])){
					$level->setBlock($block, SIGN_POST, 0);
					return true;
				}else{
					$level->setBlock($block, WALL_SIGN, $faces[$face]);
					return true;
				}
			}
		}
		return false;
	}
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){
			$level->setBlock($this, AIR, 0, true, true);
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(SIGN, 0, 1),
		);
	}
	
}