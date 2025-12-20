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

namespace pocketmine\world\generator\object;

use pocketmine\math\VectorMath;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\SubChunk;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use pocketmine\world\World;
use function sin;
use const M_PI;

class Ore{
	public function __construct(
		private Random $random,
		public OreType $type
	){}

	public function getType() : OreType{
		return $this->type;
	}

	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z) : bool{
		return $world->getBlockAt($x, $y, $z)->hasSameTypeId($this->type->replaces);
	}

	public function placeObject(ChunkManager $world, int $x, int $y, int $z) : void{
		$clusterSize = $this->type->clusterSize;
		$angle = $this->random->nextFloat() * M_PI;
		$offset = VectorMath::getDirection2D($angle)->multiply($clusterSize / 8);
		$x1 = $x + 8 + $offset->x;
		$x2 = $x + 8 - $offset->x;
		$z1 = $z + 8 + $offset->y;
		$z2 = $z + 8 - $offset->y;
		$y1 = $y + $this->random->nextBoundedInt(3) + 2;
		$y2 = $y + $this->random->nextBoundedInt(3) + 2;

		$explorer = new SubChunkExplorer($world);

		$tried = [];
		$replaceableStateIds = [];
		$materialStateId = $this->type->material->getStateId();
		for($count = 0; $count <= $clusterSize; ++$count){
			$centerX = $x1 + ($x2 - $x1) * $count / $clusterSize;
			$centerY = $y1 + ($y2 - $y1) * $count / $clusterSize;
			$centerZ = $z1 + ($z2 - $z1) * $count / $clusterSize;
			$radius = ((sin($count * (M_PI / $clusterSize)) + 1) * $this->random->nextFloat() * $clusterSize / 16 + 1) / 2;

			$this->placeSphere($world, $explorer, $centerX, $centerY, $centerZ, $radius, $tried, $replaceableStateIds, $materialStateId);
		}
	}

	/**
	 * Places a sphere of ore blocks centered at the given coordinates with the given radius.
	 * Only the blocks that are replaceable according to the ore type will be replaced.
	 *
	 * @param true[] $visited
	 * @param bool[] $replaceableStateIds
	 *
	 * @phpstan-param array<int, true> $visited
	 * @phpstan-param array<int, bool> $replaceableStateIds
	 */
	private function placeSphere(
		ChunkManager $world,
		SubChunkExplorer $explorer,
		float $centerX,
		float $centerY,
		float $centerZ,
		float $radius,
		array &$visited,
		array &$replaceableStateIds,
		int $materialStateId
	) : void{
		$startX = (int) ($centerX - $radius);
		$startY = (int) ($centerY - $radius);
		$startZ = (int) ($centerZ - $radius);
		$endX = (int) ($centerX + $radius);
		$endY = (int) ($centerY + $radius);
		$endZ = (int) ($centerZ + $radius);

		for($xx = $startX; $xx <= $endX; ++$xx){
			$sizeX = ($xx + 0.5 - $centerX) / $radius;
			$sizeX *= $sizeX;

			if($sizeX < 1){
				for($yy = $startY; $yy <= $endY; ++$yy){
					$sizeY = ($yy + 0.5 - $centerY) / $radius;
					$sizeY *= $sizeY;

					if($yy > 0 && ($sizeX + $sizeY) < 1){
						for($zz = $startZ; $zz <= $endZ; ++$zz){
							$sizeZ = ($zz + 0.5 - $centerZ) / $radius;
							$sizeZ *= $sizeZ;

							if(($sizeX + $sizeY + $sizeZ) < 1){
								$hash = World::blockHash($xx, $yy, $zz);
								if(isset($visited[$hash])){
									continue;
								}
								$visited[$hash] = true;
								if($explorer->moveTo($xx, $yy, $zz) === SubChunkExplorerStatus::INVALID || $explorer->currentSubChunk === null){
									throw new \LogicException("Unavailable chunk at block x=$xx, y=$yy, z=$zz");
								}
								$stateId = $explorer->currentSubChunk->getBlockStateId($xx & SubChunk::COORD_MASK, $yy & SubChunk::COORD_MASK, $zz & SubChunk::COORD_MASK);
								$replaceable = $replaceableStateIds[$stateId] ??= $world->getBlockAt($xx, $yy, $zz)->hasSameTypeId($this->type->replaces);
								if($replaceable){
									$explorer->currentSubChunk->setBlockStateId($xx & SubChunk::COORD_MASK, $yy & SubChunk::COORD_MASK, $zz & SubChunk::COORD_MASK, $materialStateId);
								}
							}
						}
					}
				}
			}
		}
	}
}
