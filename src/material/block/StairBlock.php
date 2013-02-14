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

class StairBlock extends TransparentBlock{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}

	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$faces = array(
				0 => 0,
				1 => 2,
				2 => 1,
				3 => 3,
			);
			$this->meta = $faces[$player->entity->getDirection()] & 0x03;
			if(($fy > 0.5 and $face !== 1) or $face === 0){
				$this->meta |= 0x04; //Upside-down stairs
			}
			$level->setBlock($block, $this->id, $this->meta);
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}