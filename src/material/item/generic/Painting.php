<?php

/**
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

class PaintingItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(PAINTING, 0, $count, "Painting");
		$this->isActivable = true;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->isTransparent === false and $face > 1 and $block->isSolid === false){
			$server = ServerAPI::request();
			$faces = array(
				2 => 1,
				3 => 3,
				4 => 0,
				5 => 2,
			
			);
			$motives = array(
				// Motive Width Height
				array("Kebab", 1, 1),
				array("Aztec", 1, 1),
				array("Alban", 1, 1),
				array("Aztec2", 1, 1),
				array("Bomb", 1, 1),
				array("Plant", 1, 1),
				array("Wasteland", 1, 1),
				array("Wanderer", 1, 2),
				array("Graham", 1, 2),
				array("Pool", 2, 1),
				array("Courbet", 2, 1),
				array("Sunset", 2, 1),
				array("Sea", 2, 1),
				array("Creebet", 2, 1),
				array("Match", 2, 2),
				array("Bust", 2, 2),
				array("Stage", 2, 2),
				array("Void", 2, 2),
				array("SkullAndRoses", 2, 2),
				//array("Wither", 2, 2),
				array("Fighters", 4, 2),
				array("Skeleton", 4, 3),
				array("DonkeyKong", 4, 3),
				array("Pointer", 4, 4),
				array("Pigscene", 4, 4),
				array("Flaming Skull", 4, 4),
			);
			$motive = $motives[mt_rand(0, count($motives) - 1)];
			$data = array(
				"x" => $target->x,
				"y" => $target->y,
				"z" => $target->z,
				"yaw" => $faces[$face] * 90,
				"Motive" => $motive[0],
			);
			$e = $server->api->entity->add($level, ENTITY_OBJECT, OBJECT_PAINTING, $data);
			$server->api->entity->spawnToAll($e);
			if(($player->gamemode & 0x01) === 0x00){
				$player->removeItem($this->getID(), $this->getMetadata(), 1, false);
			}
			return true;
		}
		return false;
	}

}