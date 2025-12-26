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

namespace pocketmine\world\format;

use PHPUnit\Framework\TestCase;
use pocketmine\data\bedrock\BiomeIds;

class ChunkTest extends TestCase{

	public function testClone() : void{
		$chunk = new Chunk([], false);
		$chunk->setBlockStateId(0, 0, 0, 1);
		$chunk->setBiomeId(0, 0, 0, 1);
		$chunk->setHeightMap(0, 0, 1);

		$chunk2 = clone $chunk;
		$chunk2->setBlockStateId(0, 0, 0, 2);
		$chunk2->setBiomeId(0, 0, 0, 2);
		$chunk2->setHeightMap(0, 0, 2);

		self::assertNotSame($chunk->getBlockStateId(0, 0, 0), $chunk2->getBlockStateId(0, 0, 0));
		self::assertNotSame($chunk->getBiomeId(0, 0, 0), $chunk2->getBiomeId(0, 0, 0));
		self::assertNotSame($chunk->getHeightMap(0, 0), $chunk2->getHeightMap(0, 0));
	}

	public function testBiomeIdConstructor() : void{
		$chunk = new Chunk([], false, BiomeIds::DESERT);

		// Check that all biomes are set to DESERT
		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				for($y = Chunk::MIN_SUBCHUNK_INDEX << SubChunk::COORD_BIT_SIZE; $y < (Chunk::MAX_SUBCHUNK_INDEX + 1) << SubChunk::COORD_BIT_SIZE; ++$y){
					self::assertSame(BiomeIds::DESERT, $chunk->getBiomeId($x, $y, $z));
				}
			}
		}
	}

	public function testFillBiomes() : void{
		$chunk = new Chunk([], false);
		$chunk->fillBiomes(BiomeIds::JUNGLE);

		// Check that all biomes are set to JUNGLE
		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				for($y = Chunk::MIN_SUBCHUNK_INDEX << SubChunk::COORD_BIT_SIZE; $y < (Chunk::MAX_SUBCHUNK_INDEX + 1) << SubChunk::COORD_BIT_SIZE; ++$y){
					self::assertSame(BiomeIds::JUNGLE, $chunk->getBiomeId($x, $y, $z));
				}
			}
		}
	}

	public function testExtrapolateBiomes() : void{
		$chunk = new Chunk([], false);

		// Create a 2D biome array (16x16 = 256 elements)
		$biomes2d = [];
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				// Create a pattern where biome ID = x + z * 16
				$biomes2d[$z * 16 + $x] = ($x % 4) + 1; // Use values 1-4 for testing
			}
		}

		$chunk->extrapolateBiomes($biomes2d);

		// Verify that the 2D biomes have been extrapolated to all Y levels
		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$expectedBiome = ($x % 4) + 1;
				for($y = Chunk::MIN_SUBCHUNK_INDEX << SubChunk::COORD_BIT_SIZE; $y < (Chunk::MAX_SUBCHUNK_INDEX + 1) << SubChunk::COORD_BIT_SIZE; ++$y){
					self::assertSame($expectedBiome, $chunk->getBiomeId($x, $y, $z), "Biome mismatch at ($x, $y, $z)");
				}
			}
		}
	}

	public function testExtrapolateBiomesInvalidSize() : void{
		$chunk = new Chunk([], false);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Biome array must contain exactly 256 elements");

		// Try with wrong size array
		$chunk->extrapolateBiomes([1, 2, 3]);
	}
}
