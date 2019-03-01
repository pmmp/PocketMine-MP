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

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$level = $player->getLevel();
		for($i = 0; $i < 1000; $i++){
			$x = $this->x + mt_rand(-15, 15);
			$y = $this->y + mt_rand(-7, 7);
			$z = $this->z + mt_rand(-15, 15);
			if($level->getBlockAt($x, $y, $z)->getId() === Block::AIR and $y < Level::Y_MAX and $y > 0){
				$source = $this->asVector3();
				$target = new Vector3($x, $y, $z);

				$level->setBlock($source, BlockFactory::get(Block::AIR));
				$level->setBlock($target, BlockFactory::get(Block::DRAGON_EGG));

				$dir = $target->subtract($source)->normalize();
				$max = min(128, $source->distance($target));
				for($j = 0; $j <= $max; $j++){
					$this->getLevel()->broadcastLevelEvent($source->add($dir->multiply($j)->add(0, 1.5, 0)), LevelEventPacket::EVENT_PARTICLE_PORTAL);
				}
				break;
			}
		}

		return true;
	}
}