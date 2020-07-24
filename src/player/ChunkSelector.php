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

use pocketmine\world\ChunkPos;

//TODO: turn this into an interface?
final class ChunkSelector{

	/**
	 * @preturn \Generator|ChunkPos[]
	 * @phpstan-return \Generator<int, ChunkPos, void, void>
	 */
	public function selectChunks(int $radius, ChunkPos $chunkPos) : \Generator{
		$radiusSquared = $radius ** 2;

		for($x = 0; $x < $radius; ++$x){
			for($z = 0; $z <= $x; ++$z){
				if(($x ** 2 + $z ** 2) > $radiusSquared){
					break; //skip to next band
				}

				//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

				/* Top right quadrant */
				yield $chunkPos->add($x, $z);
				/* Top left quadrant */
				yield $chunkPos->add(-$x - 1, $z);
				/* Bottom right quadrant */
				yield $chunkPos->add($x, -$z - 1);
				/* Bottom left quadrant */
				yield $chunkPos->add(-$x - 1, -$z - 1);

				if($x !== $z){
					/* Top right quadrant mirror */
					yield $chunkPos->add($z, $x);
					/* Top left quadrant mirror */
					yield $chunkPos->add(-$z - 1, $x);
					/* Bottom right quadrant mirror */
					yield $chunkPos->add($z, -$x - 1);
					/* Bottom left quadrant mirror */
					yield $chunkPos->add(-$z - 1, -$x - 1);
				}
			}
		}
	}
}
