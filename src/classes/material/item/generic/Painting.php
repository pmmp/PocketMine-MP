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

class PaintingItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(PAINTING, 0, $count, "Painting");
		$this->isActivable = true;
	}
	
	public function onActivate(BlockAPI $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->isTransparent === false and $face > 1){
			$faces = array(
				2 => 1,
				3 => 3,
				4 => 0,
				5 => 2,
			
			);
			$motives = array(
				"Kebab",
				"Aztec",
				"Alban",
				"Aztec2",
				"Bomb",
				"Plant",
				"Wasteland",
			);

			$data = array(
				"x" => $target->x,
				"y" => $target->y,
				"z" => $target->z,
				"yaw" => $faces[$face] * 90,
				"Motive" => $motives[mt_rand(0, count($motives) - 1)],
			);
			$server = ServerAPI::request();
			$e = $server->api->entity->add(ENTITY_OBJECT, OBJECT_PAINTING, $data);
			$server->api->entity->spawnToAll($e->eid);
			return true;
		}
		return false;
	}

}