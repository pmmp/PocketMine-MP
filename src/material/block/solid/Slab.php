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

class SlabBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(SLAB, $meta, "Slab");
		$names = array(
			0 => "Stone",
			1 => "Sandstone",
			2 => "Wooden",
			3 => "Cobblestone",
			4 => "Brick",
			5 => "Stone Brick",
			6 => "Nether Brick",
			7 => "Quartz",
		);
		$this->name = (($this->meta & 0x08) === 0x08 ? "Upper ":"") . $names[$this->meta & 0x07] . " Slab";
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$this->meta &= 0x07;
			if($face === 0){
				if($target->getID() === SLAB and ($target->getMetadata() & 0x08) === 0x08 and ($target->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($target, BlockAPI::get(DOUBLE_SLAB, $this->meta));
					return true;
				}else{
					$this->meta |= 0x08;
				}
			}elseif($face === 1){
				if($target->getID() === SLAB and ($target->getMetadata() & 0x08) === 0 and ($target->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($target, BlockAPI::get(DOUBLE_SLAB, $this->meta));
					return true;
				}
			}elseif(!$player->entity->inBlock($block->x, $block->y, $block->z)){
				if($block->getID() === SLAB){
					if(($block->getMetadata() & 0x07) === ($this->meta & 0x07)){
						$this->level->setBlock($block, BlockAPI::get(DOUBLE_SLAB, $this->meta));
						return true;
					}
					return false;
				}else{
					if($fy > 0.5){
						$this->meta |= 0x08;
					}
				}
			}else{
				return false;
			}
			if($block->getID() === SLAB and ($target->getMetadata() & 0x07) !== ($this->meta & 0x07)){
				return false;
			}
			$this->level->setBlock($block, $this);
			return true;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, $this->meta & 0x07, 1),
		);
	}	
}