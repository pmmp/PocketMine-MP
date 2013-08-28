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

class RedstoneOreBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(REDSTONE_ORE, 0, "Redstone Ore");
	}
	
	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL or $type === BLOCK_UPDATE_TOUCH){
			$this->level->setBlock($this, BlockAPI::get(GLOWING_REDSTONE_ORE, $this->meta), false);
			$this->level->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), Utils::getRandomUpdateTicks(), BLOCK_UPDATE_RANDOM);
			return BLOCK_UPDATE_WEAK;
		}
		return false;
	}

	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 2){
			return array(
				//array(331, 4, mt_rand(4, 5)),
			);
		}else{
			return array();
		}
	}
}