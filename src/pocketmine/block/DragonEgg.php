<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class DragonEgg extends Fallable{

	protected $id = self::DRAGON_EGG;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Dragon Egg";
	}

	public function getHardness() : float{
		return 3;
	}

	public function getBlastResistance() : float{
		return 45;
	}

	public function getLightLevel() : int{
		return 1;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$attempts = 0;
		$level = $player->getLevel();
		while(true){
			$x = $this->getX() + rand(-15,15);
			$y = $this->getY() + rand(-7,7);
			$z = $this->getZ() + rand(-15,15);
			if($y < Level::Y_MAX && $level->getBlockIdAt($x, $y, $z) == 0){
				$oldPos = $this->asVector3();
				$level->setBlock($this, BlockFactory::get(Block::AIR), true, true);
				$pos = new Position($x, $y, $z, $level);
				$level->setBlock($pos, $this, true, true);
				$sub = $pos->subtract($oldPos);
				$distance = $oldPos->distance($pos);
				for($c = 0; $c <= $distance; $c++){
					$progress = $c / $distance;
					$this->getLevel()->broadcastLevelEvent(new Vector3($oldPos->x + $sub->x * $progress, 1.62 + $oldPos->y + $sub->y * $progress, $oldPos->z + $sub->z * $progress), LevelEventPacket::EVENT_PARTICLE_PORTAL);
				}
				break;
			}

			if(++$attempts > 15){
				return false;
			}
		}

		return true;
	}
}