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

class DirtBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(DIRT, 0, "Dirt");
		$this->isActivable = true;
	}

	public function onActivate(Item $item, Player $player){
		if($item->isHoe()){
			if(($player->gamemode & 0x01) === 0){
				$item->useOn($this);
			}
			$this->level->setBlock($this, BlockAPI::get(FARMLAND, 0));
			return true;
		}
		return false;
	}
}