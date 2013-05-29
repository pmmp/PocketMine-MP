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

class SugarcaneBlock extends TransparentBlock{
	public function __construct(){
		parent::__construct(SUGARCANE_BLOCK, 0, "Sugarcane");
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(SUGARCANE, 0, 1),
		);
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isFlowable === true){ //Replace wit common break method
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem(SUGARCANE));
				$this->level->setBlock($this, new AirBlock(), false);
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->getID() === SUGARCANE_BLOCK){
				$this->level->setBlock($block, new SugarcaneBlock());
				return true;
			}elseif($down->getID() === GRASS or $down->getID() === DIRT or $down->getID() === SAND){
				$block0 = $this->getSide(2);
				$block1 = $this->getSide(3);
				$block2 = $this->getSide(4);
				$block3 = $this->getSide(5);
				if($block0->getID() === WATER or $block0->getID() === STILL_WATER
				or $block1->getID() === WATER or $block1->getID() === STILL_WATER
				or $block2->getID() === WATER or $block2->getID() === STILL_WATER
				or $block3->getID() === WATER or $block3->getID() === STILL_WATER){
					$this->level->setBlock($block, new SugarcaneBlock());
					return true;
				}
			}
		return false;
	}
	
}