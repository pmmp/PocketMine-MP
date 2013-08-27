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

class SpawnEggItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(SPAWN_EGG, 0, $count, "Spawn Egg");
		$this->meta = $meta;
		$this->isActivable = true;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		switch($this->meta){
			case MOB_CHICKEN:
			case MOB_SHEEP:
			case MOB_COW:
			case MOB_PIG:
				$data = array(
					"x" => $block->x + 0.5,
					"y" => $block->y,
					"z" => $block->z + 0.5,
				);
				$e = ServerAPI::request()->api->entity->add($block->level, ENTITY_MOB, $this->meta, $data);
				ServerAPI::request()->api->entity->spawnToAll($e);
				if(($player->gamemode & 0x01) === 0){
					--$this->count;
				}
				return true;
				break;
		}
		return false;
	}
}