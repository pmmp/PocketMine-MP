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

class ContainerBlock extends SolidBlock{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){
			$server = ServerAPI::request();
			$t = $server->api->tileentity->get($this);
			if($t !== false){
				if(is_array($t)){
					foreach($t as $ts){
						if($ts->class === TILE_CHEST){
							$server->api->tileentity->remove($ts->id);
						}
					}
				}elseif($t->class === TILE_CHEST){
					$server->api->tileentity->remove($t->id);
				}
			}
			$level->setBlock($this, 0, 0);
			return true;
		}
		return false;
	}
	
	public function onActivate(BlockAPI $level, Item $item, Player $player){
		return false;
	}
}