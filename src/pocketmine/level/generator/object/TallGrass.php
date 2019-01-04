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

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use function count;

class TallGrass{

	public static function growGrass(ChunkManager $level, Vector3 $pos, Random $random, int $count = 15, int $radius = 10) : void{
		/** @var Block[] $arr */
		$arr = [
			BlockFactory::get(Block::DANDELION),
			BlockFactory::get(Block::POPPY),
			$tallGrass = BlockFactory::get(Block::TALL_GRASS, 1),
			$tallGrass,
			$tallGrass,
			$tallGrass
		];
		$arrC = count($arr) - 1;
		for($c = 0; $c < $count; ++$c){
			$x = $random->nextRange($pos->x - $radius, $pos->x + $radius);
			$z = $random->nextRange($pos->z - $radius, $pos->z + $radius);
			if($level->getBlockAt($x, $pos->y + 1, $z)->getId() === Block::AIR and $level->getBlockAt($x, $pos->y, $z)->getId() === Block::GRASS){
				$level->setBlockAt($x, $pos->y + 1, $z, $arr[$random->nextRange(0, $arrC)]);
			}
		}
	}
}
