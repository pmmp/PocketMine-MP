<?php

/*
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

namespace PocketMine\Block;

use PocketMine;
use PocketMine\Item\Item;

class TNT extends Solid{
	public function __construct(){
		parent::__construct(self::TNT, 0, "TNT");
		$this->hardness = 0;
		$this->isActivable = true;
	}

	public function onActivate(Item $item, PocketMine\Player $player){
		if($item->getID() === Item::FLINT_STEEL){
			if(($player->gamemode & 0x01) === 0){
				$item->useOn($this);
			}
			$data = array(
				"x" => $this->x + 0.5,
				"y" => $this->y + 0.5,
				"z" => $this->z + 0.5,
				"power" => 4,
				"fuse" => 20 * 4, //4 seconds
			);
			$this->level->setBlock($this, new Air(), false, false, true);
			//TODO
			//$e = ServerAPI::request()->api->entity->add($this->level, ENTITY_OBJECT, OBJECT_PRIMEDTNT, $data);
			//$e->spawnToAll();

			return true;
		}

		return false;
	}
}