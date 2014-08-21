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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;
use pocketmine\Player;

class SpawnEgg extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::SPAWN_EGG, 0, $count, "Spawn Egg");
		$this->meta = $meta;
		$this->isActivable = true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$entity = null;
		$chunk = $level->getChunkAt($block->getX() >> 4, $block->getZ() >> 4);

		if(!($chunk instanceof FullChunk)){
			return false;
		}

		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
					new Double("", $block->getX()),
					new Double("", $block->getY()),
					new Double("", $block->getZ())
				]),
			"Motion" => new Enum("Motion", [
					new Double("", 0),
					new Double("", 0),
					new Double("", 0)
				]),
			"Rotation" => new Enum("Rotation", [
					new Float("", lcg_value() * 360),
					new Float("", 0)
				]),
		]);

		switch($this->meta){
			case Villager::NETWORK_ID:
				$nbt->Health = new Short("Health", 20);
				$entity = new Villager($chunk, $nbt);
				break;
			case Zombie::NETWORK_ID:
				$nbt->Health = new Short("Health", 20);
				$entity = new Zombie($chunk, $nbt);
				break;
			/*
			//TODO: use entity constants
			case 10:
			case 11:
			case 12:
			case 13:
				$data = array(
					"x" => $block->x + 0.5,
					"y" => $block->y,
					"z" => $block->z + 0.5,
				);
				//$e = Server::getInstance()->api->entity->add($block->level, ENTITY_MOB, $this->meta, $data);
				//Server::getInstance()->api->entity->spawnToAll($e);
				if(($player->gamemode & 0x01) === 0){
					--$this->count;
				}

				return true;*/
		}

		if($entity instanceof Entity){
			if(($player->gamemode & 0x01) === 0){
				--$this->count;
			}
			$entity->spawnToAll();

			return true;
		}

		return false;
	}
}