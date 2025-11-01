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
use pocketmine\world\BlockTransaction;
use pocketmine\world\Position;
use function mt_rand;

class MossBlock extends Opaque{

	/**
	 * @phpstan-param \Closure(BlockTransaction, Position): void $vegetationSelector
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, private readonly \Closure $vegetationSelector){
		parent::__construct($idInfo, $name, $typeInfo);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!($item instanceof Fertilizer) || $this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		$world = $this->position->getWorld();

		$item->pop();

		$maxX = mt_rand(0, 1) === 0 ? 2 : 3;
		$maxZ = mt_rand(0, 1) === 0 ? 2 : 3;

		$originX = $this->position->x;
		$originY = $this->position->y;
		$originZ = $this->position->z;

		for($dx = -$maxX; $dx <= $maxX; ++$dx){
			for($dz = -$maxZ; $dz <= $maxZ; ++$dz){
				$absdx = $dx < 0 ? -$dx : $dx;
				$absdz = $dz < 0 ? -$dz : $dz;

				if($absdx === $maxX && $absdz === $maxZ){
					continue;
				}

				if($absdx === $maxX || $absdz === $maxZ){
					if(mt_rand(1, 100) > 75){
						continue;
					}
				}

				$x = $originX + $dx;
				$z = $originZ + $dz;
				$startY = $originY + 1;

				$foundBlock = null;

				$startBlock = $world->getBlockAt($x, $startY, $z);
				if($startBlock->getTypeId() === BlockTypeIds::AIR){
					for($y = $startY; $y >= $startY - 6; --$y){
						$b = $world->getBlockAt($x, $y, $z);
						if($b->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($x, $y + 1, $z)->getTypeId() === BlockTypeIds::AIR){
							$foundBlock = $b;
							break;
						}
					}
				}else{
					for($y = $startY; $y <= $startY + 4; ++$y){
						$b = $world->getBlockAt($x, $y, $z);
						if($b->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($x, $y + 1, $z)->getTypeId() === BlockTypeIds::AIR){
							$foundBlock = $b;
							break;
						}
					}
				}

				if($foundBlock !== null && $foundBlock->hasTypeTag(BlockTypeTags::MOSS_REPLACEABLE) &&
					($foundBlock === $this || BlockEventHelper::spread($foundBlock, (clone $this), $this))){
					if(mt_rand(1, 100) <= 60){
						$above = $foundBlock->getSide(Facing::UP);
						$tx = new BlockTransaction($world);
						($this->vegetationSelector)($tx, $above->getPosition());
						$tx->apply();
					}
				}
			}
		}

		return true;
	}
}
