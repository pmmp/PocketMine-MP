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

class IronDoorBlock extends DoorBlock{
	public function __construct($meta = 0){
		parent::__construct(IRON_DOOR_BLOCK, $meta, "Iron Door Block");
		//$this->isActivable = true;
	}
	
	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getID() === AIR){ //Replace wit common break method
				$this->level->setBlock($this, new AirBlock(), false);
				if($this->getSide(1) instanceof DoorBlock){
					$this->level->setBlock($this->getSide(1), new AirBlock(), false);
				}
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}		
		switch($item->isPickaxe()){
			case 5:
				return 0.95;
			case 4:
				return 1.25;
			case 3:
				return 1.9;
			case 2:
				return 0.65;
			case 1:
				return 3.75;
			default:
				return 25;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 1){
			return array(
				array(IRON_DOOR, 0, 1),
			);
		}else{
			return array();
		}
	}
}