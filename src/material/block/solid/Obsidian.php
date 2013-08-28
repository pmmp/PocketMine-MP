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

class ObsidianBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(OBSIDIAN, 0, "Obsidian");
		
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		if($item->isPickaxe() >= 5){
			return 9.4;
		}else{
			return 250;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 5){
			return array(
				array(OBSIDIAN, 0, 1),
			);
		}else{
			return array();
		}
	}
}