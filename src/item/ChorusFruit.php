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

use pocketmine\block\Liquid;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\world\sound\EndermanTeleportSound;
use function min;
use function mt_rand;

class ChorusFruit extends Food{

	public function getFoodRestore() : int{
		return 4;
	}

	public function getSaturationRestore() : float{
		return 2.4;
	}

	public function requiresHunger() : bool{
		return false;
	}

	public function onConsume(Living $consumer) : void{
		$world = $consumer->getWorld();

		$origin = $consumer->getPosition();
		$minX = $origin->getFloorX() - 8;
		$minY = min($origin->getFloorY(), $consumer->getWorld()->getMaxY()) - 8;
		$minZ = $origin->getFloorZ() - 8;

		$maxX = $minX + 16;
		$maxY = $minY + 16;
		$maxZ = $minZ + 16;

		for($attempts = 0; $attempts < 16; ++$attempts){
			$x = mt_rand($minX, $maxX);
			$y = mt_rand($minY, $maxY);
			$z = mt_rand($minZ, $maxZ);

			while($y >= 0 and !$world->getBlockAt($x, $y, $z)->isSolid()){
				$y--;
			}
			if($y < 0){
				continue;
			}

			$blockUp = $world->getBlockAt($x, $y + 1, $z);
			$blockUp2 = $world->getBlockAt($x, $y + 2, $z);
			if($blockUp->isSolid() or $blockUp instanceof Liquid or $blockUp2->isSolid() or $blockUp2 instanceof Liquid){
				continue;
			}

			//Sounds are broadcasted at both source and destination
			$world->addSound($origin, new EndermanTeleportSound());
			$consumer->teleport($target = new Vector3($x + 0.5, $y + 1, $z + 0.5));
			$world->addSound($target, new EndermanTeleportSound());

			break;
		}
	}

	public function getCooldownTicks() : int{
		return 20;
	}
}
