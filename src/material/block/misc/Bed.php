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
		parent::__construct(BED_BLOCK, $type, "Bed Block");
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
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){//Checks if the block was in the world or not. Just in case
			$blockNorth = $level->getBlockFace($this, 2); //Gets the blocks around them
			$blockSouth = $level->getBlockFace($this, 3);
			$blockEast = $level->getBlockFace($this, 5);
			$blockWest = $level->getBlockFace($this, 4);
			
			if(($this->meta & 0x08) === 0x08){ //This is the Top part of bed			
				if($blockNorth->getID() === $this->id and $blockNorth->meta !== 0x08){ //Checks if the block ID and meta are right
					$level->setBlock($blockNorth, 0, 0);
				}elseif($blockSouth->getID() === $this->id and $blockSouth->meta !== 0x08){
					$level->setBlock($blockSouth, 0, 0);
				}elseif($blockEast->getID() === $this->id and $blockEast->meta !== 0x08){
					$level->setBlock($blockEast, 0, 0);
				}elseif($blockWest->getID() === $this->id and $blockWest->meta !== 0x08){
					$level->setBlock($blockWest, 0, 0);
				}else{
					return false;
				}
				return true;
			}else{ //Bottom Part of Bed
				if($blockNorth->getID() === $this->id and ($blockNorth->meta & 0x08) === 0x08){
					$level->setBlock($blockNorth, 0, 0);
				}elseif($blockSouth->getID() === $this->id and ($blockSouth->meta & 0x08) === 0x08){
					$level->setBlock($blockSouth, 0, 0);
				}elseif($blockEast->getID() === $this->id and ($blockEast->meta & 0x08) === 0x08){
					$level->setBlock($blockEast, 0, 0);
				}elseif($blockWest->getID() === $this->id and ($blockWest->meta & 0x08) === 0x08){
					$level->setBlock($blockWest, 0, 0);
				}else{
					return false;
				}
				return true;
			}
			return false;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(BED, 0, 1),
		);
	}
	
}