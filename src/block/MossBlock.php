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

namespace pocketmine\block;

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function abs;
use function mt_rand;

class MossBlock extends Opaque{
	protected const VERTICAL_RANGE = 5;

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!($item instanceof Fertilizer) || $this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		$item->pop();

		$world = $this->position->getWorld();
		$maxX = $this->getHorizontalRadius();
		$maxZ = $this->getHorizontalRadius();
		$originX = $this->position->x;
		$originY = $this->position->y;
		$originZ = $this->position->z;

		for($dx = -$maxX; $dx <= $maxX; ++$dx){
			for($dz = -$maxZ; $dz <= $maxZ; ++$dz){
				if(
					(abs($dx) === $maxX && abs($dz) === $maxZ) ||
					((abs($dx) === $maxX || abs($dz) === $maxZ) && mt_rand(1, 100) > 75)
				){
					continue;
				}

				$foundBlock = null;
				if($dx !== 0 || $dz !== 0){
					$x = $originX + $dx;
					$z = $originZ + $dz;
					$startY = $originY + 1;
					$direction = $world->getBlockAt($x, $startY, $z)->getTypeId() === BlockTypeIds::AIR ? -1 : 1;
					$limit = $direction === -1 ? $originY - self::VERTICAL_RANGE : $originY + self::VERTICAL_RANGE;

					for($y = $startY; $direction === -1 ? $y >= $limit : $y <= $limit; $y += $direction){
						$b = $world->getBlockAt($x, $y, $z);
						if($b->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($x, $y + 1, $z)->getTypeId() === BlockTypeIds::AIR){
							$foundBlock = $b;
							break;
						}
					}
				}else{
					$foundBlock = $this;
				}

				if(
					$foundBlock !== null && $foundBlock->hasTypeTag(BlockTypeTags::MOSS_REPLACEABLE) &&
					($foundBlock->hasSameTypeId($this) || BlockEventHelper::spread($foundBlock, (clone $this), $this)) &&
					mt_rand(1, 100) <= 60
				){
					$this->generateVegetation($foundBlock->getSide(Facing::UP)->getPosition());
				}
			}
		}

		return true;
	}

	protected function getHorizontalRadius() : int{
		return mt_rand(0, 1) === 0 ? 2 : 3;
	}

	protected function generateVegetation(Position $pos) : void{
		$world = $pos->getWorld();
		if(!$world->isInWorld($pos->x, $pos->y, $pos->z)){
			return;
		}
		$rand = mt_rand(1, 10000);
		if($rand <= 5208){
			$world->setBlock($pos, VanillaBlocks::TALL_GRASS());
		}elseif($rand <= 5208 + 2604){
			$world->setBlock($pos, VanillaBlocks::MOSS_CARPET());
		}elseif($rand <= 5208 + 2604 + 1042){
			if($world->isInWorld($pos->x, $pos->y + 1, $pos->z)){
				$world->setBlock($pos, VanillaBlocks::DOUBLE_TALLGRASS());
				$world->setBlock($pos->up(), VanillaBlocks::DOUBLE_TALLGRASS()->setTop(true));
			}
		}elseif($rand <= 5208 + 2604 + 1042 + 729){
			//TODO: Azalea 7.29%
		}else{
			//TODO: Flowering Azalea 4.17%
		}
	}
}
