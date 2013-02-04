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

class DoorBlock extends TransparentBlock{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}

	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true and $face === 1){
			$blockUp = $level->getBlockFace($block, 1);
			$blockDown = $level->getBlockFace($block, 0);
			if($blockUp->isReplaceable === false or $blockDown->isTransparent === true){
				return false;
			}
			$direction = $player->entity->getDirection();
			$face = array(
				0 => 3,
				1 => 4,
				2 => 2,
				3 => 5,
			);
			$next = $level->getBlockFace($block, $face[(($direction + 2) % 4)]);
			$next2 = $level->getBlockFace($block, $face[$direction]);
			$metaUp = 0x08;
			if($next->getID() === $this->id or ($next2->isTransparent === false and $next->isTransparent === true)){ //Door hinge
				$metaUp |= 0x01;
			}
			$level->setBlock($blockUp, $this->id, $metaUp); //Top
			
			$this->meta = $direction & 0x03;
			$level->setBlock($block, $this->id, $this->meta); //Bottom
			return true;			
		}
		return false;
	}
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){
			if(($this->meta & 0x08) === 0x08){
				$down = $level->getBlockFace($this, 0);
				if($down->getID() === $this->id){
					$level->setBlock($down, 0, 0);
				}
			}else{
				$up = $level->getBlockFace($this, 1);
				if($up->getID() === $this->id){
					$level->setBlock($up, 0, 0);
				}
			}
			$level->setBlock($this, 0, 0);
			return true;
		}
		return false;
	}
	
	public function onActivate(BlockAPI $level, Item $item, Player $player){
		if(($this->meta & 0x08) === 0x08){ //Top
			$down = $level->getBlockFace($this, 0);
			if($down->getID() === $this->id){
				$meta = $down->getMetadata() ^ 0x04;
				$level->setBlock($down, $this->id, $meta);
				return true;
			}
			return false;
		}else{
			$this->meta ^= 0x04;
			$level->setBlock($this, $this->id, $this->meta);
		}
		return true;
	}
}