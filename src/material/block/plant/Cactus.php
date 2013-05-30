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
		$this->isFullBlock = false;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0)->getID();
			if($down !== SAND and $down !== CACTUS){ //Replace wit common break method
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem($this->id));
				$this->level->setBlock($this, new AirBlock(), false);
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === SAND or $down->getID() === CACTUS){
			$block0 = $this->getSide(2);
			$block1 = $this->getSide(3);
			$block2 = $this->getSide(4);
			$block3 = $this->getSide(5);
			if($block0->isTransparent === true and $block1->isTransparent === true and $block2->isTransparent === true and $block3->isTransparent === true){
				$this->level->setBlock($block, $this);
				return true;
			}
		}
		return false;
	}
	
}