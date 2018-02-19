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

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Painting extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::PAINTING, $meta, "Painting");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if($blockClicked->isTransparent() === false and $face > 1 and $blockReplace->isSolid() === false){
			$faces = [
				2 => 1,
				3 => 3,
				4 => 0,
				5 => 2,

			];
			$motives = [
				// Motive Width Height
				["Kebab", 1, 1],
				["Aztec", 1, 1],
				["Alban", 1, 1],
				["Aztec2", 1, 1],
				["Bomb", 1, 1],
				["Plant", 1, 1],
				["Wasteland", 1, 1],
				["Wanderer", 1, 2],
				["Graham", 1, 2],
				["Pool", 2, 1],
				["Courbet", 2, 1],
				["Sunset", 2, 1],
				["Sea", 2, 1],
				["Creebet", 2, 1],
				["Match", 2, 2],
				["Bust", 2, 2],
				["Stage", 2, 2],
				["Void", 2, 2],
				["SkullAndRoses", 2, 2],
				//array("Wither", 2, 2),
				["Fighters", 4, 2],
				["Skeleton", 4, 3],
				["DonkeyKong", 4, 3],
				["Pointer", 4, 4],
				["Pigscene", 4, 4],
				["Flaming Skull", 4, 4],
			];
			$motive = $motives[mt_rand(0, count($motives) - 1)];
			$data = [
				"x" => $blockClicked->x,
				"y" => $blockClicked->y,
				"z" => $blockClicked->z,
				"yaw" => $faces[$face] * 90,
				"Motive" => $motive[0],
			];
			//TODO
			//$e = $server->api->entity->add($level, ENTITY_OBJECT, OBJECT_PAINTING, $data);
			//$e->spawnToAll();
			/*if(($player->gamemode & 0x01) === 0x00){
				$player->removeItem(Item::get($this->getId(), $this->getDamage(), 1));
			}*/

			return true;
		}

		return false;
	}

}
