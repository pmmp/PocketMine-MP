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

namespace pocketmine\player;

use pocketmine\world\World;
use const M_SQRT2;

//TODO: turn this into an interface?
final class ChunkSelector{

	/**
	 * @preturn \Generator|int[]
	 * @phpstan-return \Generator<int, int, void, void>
	 */
	public function selectChunks(int $radius, int $centerX, int $centerZ) : \Generator{
		for($subRadius = 0; $subRadius < $radius; $subRadius++){
			$subRadiusSquared = $subRadius ** 2;
			$nextSubRadiusSquared = ($subRadius + 1) ** 2;
			$minX = (int) ($subRadius / M_SQRT2);

			$lastZ = 0;

			for($x = $subRadius; $x >= $minX; --$x){
				for($z = $lastZ; $z <= $x; ++$z){
					$distanceSquared = ($x ** 2 + $z ** 2);
					if($distanceSquared < $subRadiusSquared){
						continue;
					}elseif($distanceSquared >= $nextSubRadiusSquared){
						break; //skip to next X
					}

					$lastZ = $z;
					//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

					/* Top right quadrant */
					yield World::chunkHash($centerX + $x, $centerZ + $z);
					/* Top left quadrant */
					yield World::chunkHash($centerX - $x - 1, $centerZ + $z);
					/* Bottom right quadrant */
					yield World::chunkHash($centerX + $x, $centerZ - $z - 1);
					/* Bottom left quadrant */
					yield World::chunkHash($centerX - $x - 1, $centerZ - $z - 1);

					if($x !== $z){
						/* Top right quadrant mirror */
						yield World::chunkHash($centerX + $z, $centerZ + $x);
						/* Top left quadrant mirror */
						yield World::chunkHash($centerX - $z - 1, $centerZ + $x);
						/* Bottom right quadrant mirror */
						yield World::chunkHash($centerX + $z, $centerZ - $x - 1);
						/* Bottom left quadrant mirror */
						yield World::chunkHash($centerX - $z - 1, $centerZ - $x - 1);
					}
				}
			}
		}
	}
}
