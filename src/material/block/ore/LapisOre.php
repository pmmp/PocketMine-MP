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

class LapisOreBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(LAPIS_ORE, 0, "Lapis Ore");
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 2){
			return array(
				array(351, 4, mt_rand(4, 8)),
			);
		}else{
			return array();
		}
	}
	
}