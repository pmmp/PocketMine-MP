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

class TNTBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(TNT, 0, "TNT");
	}
	public function getDrops(Item $item, Player $player){
		if($this->inWorld === true){
			$player->dataPacket(MC_EXPLOSION, array(
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z,
				"radius" => 2,
				"records" => array(),
			));
		}
		return array(
			array(TNT, 0, 1),
		);
	}	
}