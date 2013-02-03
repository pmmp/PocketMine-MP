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

class DiamondOreBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(DIAMOND_ORE, 0, "Diamond Ore");
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 3){
			return array(
				array(264, 0, 1),
			);
		}else{
			return array();
		}
	}	
}