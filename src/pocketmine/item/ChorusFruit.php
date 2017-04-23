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
use pocketmine\block\Liquid;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class ChorusFruit extends Food{

	public function onConsume(Entity $human){
		$minX = ((int) $human->x) - 8;
		$minY = ((int) $human->y) - 8;
		$minZ = ((int) $human->z) - 8;
		$maxX = $minX + 16;
		$maxY = $minY + 16;
		$maxZ = $minZ + 16;

		$level = $human->getLevel();

		for($attempts = 0; $attempts < 16; ++$attempts){
			$x = mt_rand($minX, $maxX);
			$y = mt_rand($minY, $maxY);
			$z = mt_rand($minZ, $maxZ);

			$space = 0;

			while($y > 0 and !Block::$solid[$level->getBlockIdAt($x, $y, $z)]){
				$y--;
				$space++;
			}

			if($space < 2 or $level->getBlock(new Vector3($x, $y + 1, $z)) instanceof Liquid or $level->getBlock(new Vector3($x, $y + 2, $z)) instanceof Liquid){
				continue;
			}

			$human->teleport(new Vector3($x + 0.5, $y + 1, $z + 0.5));
			break;
		}
	}
}