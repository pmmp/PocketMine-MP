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

namespace pocketmine\world\generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class VoidGenerator extends Generator{
    public function __construct(int $seed, string $preset) {
		parent::__construct($seed, $preset);
	}

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);

        if($chunkX >= 15 && $chunkX <= 16 && $chunkZ >= 15 && $chunkZ <= 16) {
            for($x = 0; $x < 16; $x++) {
                for($z = 0; $z < 16; $z++) {
                    $worldX = ($chunkX * 16) + $x;
                    $worldZ = ($chunkZ * 16) + $z;
                    if($worldX >= 248 && $worldX <= 263 && $worldZ >= 248 && $worldZ <= 263) {
                        $chunk->setBlockStateId($x, 69, $z, VanillaBlocks::STONE()->getStateId()); // 16x16 platform of stone surrounding 256 69 256
                    }
                }
            }
        }
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
	}
}