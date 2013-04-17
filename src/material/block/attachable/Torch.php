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

class TorchBlock extends FlowableBlock{
	public function __construct($meta = 0){
		parent::__construct(TORCH, $meta, "Torch");
	}
	
	public function onUpdate(BlockAPI $level, $type){
		if($type === BLOCK_UPDATE_NORMAL){
			$side = $this->getMetadata();
			$faces = array(
					1 => 3,
					2 => 2,
					3 => 5,
					4 => 4,
					5 => 0,
					6 => 0,
					0 => 0,
			);
			if($level->getBlockFace($this, $faces[$side])->isTransparent === true){
				$level->drop($this, BlockAPI::getItem($this->id));
				$level->setBlock($this, AIR, 0, false);
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			if($target->isTransparent === false and $face !== 0){
				$faces = array(
					1 => 5,
					2 => 4,
					3 => 3,
					4 => 2,
					5 => 1,
				);
				$level->setBlock($block, $this->id, $faces[$face]);
				return true;
			}elseif($level->getBlockFace($block, 0)->isTransparent === false){
				$level->setBlock($block, $this->id, 5);
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