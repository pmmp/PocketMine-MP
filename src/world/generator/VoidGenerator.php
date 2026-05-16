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

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;

class VoidGenerator extends Generator{
	public function __construct(int $seed, string $preset){
		parent::__construct($seed, $preset);
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = new Chunk([], false);

		$biomeArray = new PalettedBlockArray(BiomeIds::PLAINS);
		foreach($chunk->getSubChunks() as $y => $subChunk){
			$chunk->setSubChunk($y, new SubChunk(Block::EMPTY_STATE_ID, [], clone $biomeArray));
		}

		$stoneState = VanillaBlocks::STONE()->getStateId();
		$cobbleState = VanillaBlocks::COBBLESTONE()->getStateId();

		for($x = 0; $x < Chunk::EDGE_LENGTH; $x++){
			for($z = 0; $z < Chunk::EDGE_LENGTH; $z++){
				$worldX = ($chunkX * Chunk::EDGE_LENGTH) + $x;
				$worldZ = ($chunkZ * Chunk::EDGE_LENGTH) + $z;

				if($worldX >= -16 && $worldX <= 16 && $worldZ >= -16 && $worldZ <= 16){
					$chunk->setBlockStateId($x, -64, $z, ($worldX === 0 && $worldZ === 0) ? $cobbleState : $stoneState);
				}
			}
		}
		$world->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		//NOOP
	}
}
