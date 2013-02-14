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

class MelonStemBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(MELON_STEM, $meta, "Melon Stem");
		$this->isActivable = true;
	}
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$down = $level->getBlockFace($block, 0);
			if($down->getID() === FARMLAND){
				$level->setBlock($block, $this->id, $this->getMetadata());
				return true;
			}
		}
		return false;
	}
	
	public function onActivate(BlockAPI $level, Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			$level->setBlock($this, $this->id, 0x07);
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(MELON_SEEDS, 0, mt_rand(0, 2)),
		);
	}
}