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

class StoneBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(STONE, 0, "Stone");
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 1){
			return array(
				array(COBBLESTONE, 0, 1),
			);
		}else{
			return array();
		}
	}
	
}