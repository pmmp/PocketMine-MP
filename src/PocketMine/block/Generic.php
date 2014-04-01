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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class Generic extends Block{

	/**
	 * @param int    $id
	 * @param int    $meta
	 * @param string $name
	 */
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		return $this->level->setBlock($this, $this, true, false, true);
	}

	public function isBreakable(Item $item){
		return $this->breakable;
	}

	public function onBreak(Item $item){
		return $this->level->setBlock($this, new Air(), true, false, true);
	}

	public function onUpdate($type){
		if($this->hasPhysics === true and $type === Level::BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down->getID() === self::AIR or ($down instanceof Liquid)){
				$data = array(
					"x" => $this->x + 0.5,
					"y" => $this->y + 0.5,
					"z" => $this->z + 0.5,
					"Tile" => $this->id,
				);
				$server = Server::getInstance();
				$this->level->setBlock($this, new Air(), false, false, true);
				//TODO
				//$e = $server->api->entity->add($this->level, ENTITY_FALLING, FALLING_SAND, $data);
				//$e->spawnToAll();
				$server->api->block->blockUpdateAround(clone $this, Level::BLOCK_UPDATE_NORMAL, 1);
			}

			return false;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		return $this->isActivable;
	}
}