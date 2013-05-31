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

class FallableBlock extends SolidBlock{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
		$this->hasPhysics = true;
	}
	
	public function onUpdate($type){
		if($this->getSide(0)->getID() === AIR){
			$data = array(
				"x" => $this->x + 0.5,
				"y" => $this->y + 0.5,
				"z" => $this->z + 0.5,
				"Tile" => $this->id,
			);
			$server = ServerAPI::request();
			$this->level->setBlock($this, new AirBlock());
			$e = $server->api->entity->add($this->level, ENTITY_FALLING, FALLING_SAND, $data);
			$server->api->entity->spawnToAll($this->level, $e->eid);
		}
	}
}