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

class WoodDoorBlock extends DoorBlock{
	public function __construct($meta = 0){
		parent::__construct(WOOD_DOOR_BLOCK, $meta, "Wood Door Block");
		$this->isActivable = true;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(WOODEN_DOOR, 0, 1),
		);
	}
}