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

namespace pocketmine\world\generator\normal;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\biome\Biome;
use pocketmine\world\biome\BiomeRegistry;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\biome\BiomeSelector;
use pocketmine\world\generator\Gaussian;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\InvalidGeneratorOptionsException;
use pocketmine\world\generator\noise\Simplex;
use pocketmine\world\generator\object\OreType;
use pocketmine\world\generator\populator\GroundCover;
use pocketmine\world\generator\populator\Ore;
use pocketmine\world\generator\populator\Populator;
use pocketmine\world\World;
use function ceil;
use function floor;
use function fmod;
use function is_int;
use function max;
use function min;

class Normal extends Generator{

	private int $waterHeight = 62;
	/** @var Populator[] */
	private array $populators = [];
	/** @var Populator[] */
	private array $generationPopulators = [];
	private Simplex $noiseBase;
	private BiomeSelector $selector;
	private Gaussian $gaussian;

	private const NOISE_SAMPLING_RATE_Y = 8;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(int $seed, string $preset){
		parent::__construct($seed, $preset);

		$this->gaussian = new Gaussian(2);

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);
		$this->random->setSeed($this->seed);

		$this->selector = new class($this->random) extends BiomeSelector{
			protected function lookup(float $temperature, float $rainfall) : int{
				if($rainfall < 0.25){
					if($temperature < 0.7){
						return BiomeIds::OCEAN;
					}elseif($temperature < 0.85){
						return BiomeIds::RIVER;
					}else{
						return BiomeIds::SWAMPLAND;
					}
				}elseif($rainfall < 0.60){
					if($temperature < 0.25){
						return BiomeIds::ICE_PLAINS;
					}elseif($temperature < 0.75){
						return BiomeIds::PLAINS;
					}else{
						return BiomeIds::DESERT;
					}
				}elseif($rainfall < 0.80){
					if($temperature < 0.25){
						return BiomeIds::TAIGA;
					}elseif($temperature < 0.75){
						return BiomeIds::FOREST;
					}else{
						return BiomeIds::BIRCH_FOREST;
					}
				}else{
					if($temperature < 0.20){
						return BiomeIds::EXTREME_HILLS;
					}elseif($temperature < 0.40){
						return BiomeIds::EXTREME_HILLS_EDGE;
					}else{
						return BiomeIds::RIVER;
					}
				}
			}
		};

		$this->selector->recalculate();

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;

		$ores = new Ore();
		$stone = VanillaBlocks::STONE();
		$ores->setOreTypes([
			new OreType(VanillaBlocks::COAL_ORE(), $stone, 20, 16, 0, 128),
			new OreType(VanillaBlocks::IRON_ORE(), $stone, 20, 8, 0, 64),
			new OreType(VanillaBlocks::REDSTONE_ORE(), $stone, 8, 7, 0, 16),
			new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), $stone, 1, 6, 0, 32),
			new OreType(VanillaBlocks::GOLD_ORE(), $stone, 2, 8, 0, 32),
			new OreType(VanillaBlocks::DIAMOND_ORE(), $stone, 1, 7, 0, 16),
			new OreType(VanillaBlocks::DIRT(), $stone, 20, 32, 0, 128),
			new OreType(VanillaBlocks::GRAVEL(), $stone, 10, 16, 0, 128)
		]);
		$this->populators[] = $ores;
	}

	private function pickBiome(int $x, int $z) : Biome{
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->seed;
		$hash *= $hash + 223;
		//the above operations may result in a float. This probably wasn't intended, but we need to stick with it to
		//avoid cliff edges in existing user worlds.
		//We need to mod this so it doesn't generate an error in PHP 8.5 when we cast it back to an int.
		$hash = (int) fmod($hash, 2.0 ** 63);
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if($xNoise === 3){
			$xNoise = 1;
		}
		if($zNoise === 3){
			$zNoise = 1;
		}

		return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);

		//TODO: why don't we just create and set the chunk here directly?
		$chunk = $world->getChunk($chunkX, $chunkZ) ?? throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not yet exist");

		$bedrock = VanillaBlocks::BEDROCK()->getStateId();
		$stillWater = VanillaBlocks::WATER()->getStateId();
		$stone = VanillaBlocks::STONE()->getStateId();

		$baseX = $chunkX * Chunk::EDGE_LENGTH;
		$baseZ = $chunkZ * Chunk::EDGE_LENGTH;

		[$biomeArray, $minNoiseHeights, $maxNoiseHeights] = $this->generateBiomes($baseX, $baseZ);

		$lowestNoiseBlock = (int) floor(min($minNoiseHeights));
		$highestNoiseBlock = (int) ceil(max($maxNoiseHeights));

		//getFastNoise3D expects the inputs to be aligned with the sampling rate, otherwise the samples will be taken
		//from different coordinates than we originally used when we first implemented this generator
		$noiseMin = (int) floor($lowestNoiseBlock / self::NOISE_SAMPLING_RATE_Y) * self::NOISE_SAMPLING_RATE_Y;
		$noiseMax = (int) ceil($highestNoiseBlock / self::NOISE_SAMPLING_RATE_Y) * self::NOISE_SAMPLING_RATE_Y;

		//we only need to generate noise for the blocks which could be affected
		//outside these bounds we'll just flood-fill blocks to save time
		$noise = $this->noiseBase->getFastNoise3D(
			xSize: Chunk::EDGE_LENGTH,
			ySize: $noiseMax - $noiseMin,
			zSize: Chunk::EDGE_LENGTH,
			xSamplingRate: 4,
			ySamplingRate: self::NOISE_SAMPLING_RATE_Y,
			zSamplingRate: 4,
			x: $chunkX * Chunk::EDGE_LENGTH,
			y: $noiseMin,
			z: $chunkZ * Chunk::EDGE_LENGTH
		);

		$minNoiseSubChunk = (int) floor($noiseMin / SubChunk::EDGE_LENGTH);
		foreach($chunk->getSubChunks() as $y => $subChunk){
			if($y >= 0 && $y < $minNoiseSubChunk){
				//Everything above 0 and below noiseMin is always solid stone, which can be flood-filled instead of
				//setting the blocks one at a time - this is vastly faster
				$blocks = [new PalettedBlockArray($stone)];
			}else{
				$blocks = [];
			}
			$chunk->setSubChunk($y, new SubChunk(Block::EMPTY_STATE_ID, $blocks, clone $biomeArray));
		}

		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$chunk->setBlockStateId($x, 0, $z, $bedrock);

				$columnIndex = World::chunkHash($x, $z);
				$minSum = $minNoiseHeights[$columnIndex];
				$maxSum = $maxNoiseHeights[$columnIndex];
				$maxBlockY = max($maxSum, $this->waterHeight);
				$smoothHeight = ($maxSum - $minSum) / 2;

				//Everything below minSum is always solid stone - we already flood-filled the subchunks below though, so
				//we only need to fill the gap in the column here
				for($y = $minNoiseSubChunk * SubChunk::EDGE_LENGTH; $y < $minSum; $y++){
					$chunk->setBlockStateId($x, $y, $z, $stone);
				}
				for($y = (int) floor($minSum); $y <= $maxBlockY; ++$y){
					//noiseValue would anyway be <= 0 above maxSum because the smoothing term is >= 1
					$noiseValue = $y > $noiseMax ?
						-1 :
						$noise[$x][$z][$y - $noiseMin] - 1 / $smoothHeight * ($y - $smoothHeight - $minSum);

					if($noiseValue > 0){
						$chunk->setBlockStateId($x, $y, $z, $stone);
					}elseif($y <= $this->waterHeight){
						$chunk->setBlockStateId($x, $y, $z, $stillWater);
					}
				}
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $world->getChunk($chunkX, $chunkZ);
		$biome = BiomeRegistry::getInstance()->getBiome($chunk->getBiomeId(7, 7, 7));
		$biome->populateChunk($world, $chunkX, $chunkZ, $this->random);
	}

	/**
	 * @return int[][]|PalettedBlockArray[]
	 * @phpstan-return array{PalettedBlockArray, non-empty-array<int, float>, non-empty-array<int, float>}
	 */
	private function generateBiomes(int $baseX, int $baseZ) : array{
		$biomeCache = [];

		$biomeArray = new PalettedBlockArray(BiomeIds::OCEAN);

		$uniform = null;
		$padding = $this->gaussian->smoothSize;
		$start = -$padding;
		$end = Chunk::EDGE_LENGTH + $padding;
		for($x = $start; $x < $end; ++$x){
			$absoluteX = $baseX + $x;
			for($z = $start; $z < $end; ++$z){
				$absoluteZ = $baseZ + $z;

				$columnIndex = World::chunkHash($x, $z);
				$biome = $biomeCache[$columnIndex] = $this->pickBiome($absoluteX, $absoluteZ);
				$biomeId = $biome->getId();
				$uniform = match ($uniform) {
					null, $biomeId => $biomeId,
					default => false
				};

				if($x >= 0 && $x < Chunk::EDGE_LENGTH && $z >= 0 && $z < Chunk::EDGE_LENGTH){
					for($y = 0; $y < 16; $y++){
						$biomeArray->set($x, $y, $z, $biomeId);
					}
				}
			}
		}

		if($uniform === false){
			[$minHeights, $maxHeights] = $this->gaussianSmoothElevation($start, $end, $biomeCache);
		}else{
			//If all the biomes in the blurred area are the same, we can save some performance by skipping blurring
			//With the current generator as of 2025-10-23, blurring can be skipped in two-thirds of chunks
			if(!is_int($uniform)){
				throw new AssumptionFailedError();
			}
			$biome = BiomeRegistry::getInstance()->getBiome($uniform);
			/** @phpstan-var non-empty-array<int, float> $minHeights */
			$minHeights = [];
			/** @phpstan-var non-empty-array<int, float> $maxHeights */
			$maxHeights = [];

			$minElevation = $biome->getMinElevation() - 1;
			$maxElevation = $biome->getMaxElevation();
			for($x = 0; $x < Chunk::EDGE_LENGTH; $x++){
				for($z = 0; $z < Chunk::EDGE_LENGTH; $z++){
					$columnIndex = World::chunkHash($x, $z);
					$minHeights[$columnIndex] = $minElevation;
					$maxHeights[$columnIndex] = $maxElevation;
				}
			}
		}

		return [$biomeArray, $minHeights, $maxHeights];
	}

	/**
	 * @param Biome[] $biomeCache
	 * @phpstan-param array<int, Biome> $biomeCache
	 *
	 * @return float[][]
	 * @phpstan-return array{non-empty-array<int, float>, non-empty-array<int, float>}
	 */
	private function gaussianSmoothElevation(int $start, int $end, array $biomeCache) : array{
		$minHeightsX = [];
		$maxHeightsX = [];
		//blur along the X axis first
		for($x = 0; $x < Chunk::EDGE_LENGTH; $x++){
			//while we don't need to smooth the padding corners, we do need to make sure that the contributions of
			//those corners are included in Z padding, otherwise we can get artifacts at chunk corners
			for($z = $start; $z < $end; $z++){
				$columnIndex = World::chunkHash($x, $z);

				$minSum = 0;
				$maxSum = 0;

				for($sx = -$this->gaussian->smoothSize; $sx <= $this->gaussian->smoothSize; ++$sx){
					$weight = $this->gaussian->kernel1D[$sx + $this->gaussian->smoothSize];
					$adjacent = $biomeCache[World::chunkHash($x + $sx, $z)];

					$minSum += ($adjacent->getMinElevation() - 1) * $weight;
					$maxSum += $adjacent->getMaxElevation() * $weight;
				}

				$minHeightsX[$columnIndex] = $minSum / $this->gaussian->weightSum1D;
				$maxHeightsX[$columnIndex] = $maxSum / $this->gaussian->weightSum1D;
			}
		}

		/** @phpstan-var non-empty-array<int, float> $minHeights */
		$minHeights = [];
		/** @phpstan-var non-empty-array<int, float> $maxHeights */
		$maxHeights = [];

		//then the Z axis, using the blurred values from the previous loop
		for($x = 0; $x < Chunk::EDGE_LENGTH; $x++){
			for($z = 0; $z < Chunk::EDGE_LENGTH; $z++){
				$columnIndex = World::chunkHash($x, $z);

				$minSum = 0;
				$maxSum = 0;

				for($sx = -$this->gaussian->smoothSize; $sx <= $this->gaussian->smoothSize; ++$sx){
					$weight = $this->gaussian->kernel1D[$sx + $this->gaussian->smoothSize];
					$adjacentIndex = World::chunkHash($x, $z + $sx);
					$minElevation = $minHeightsX[$adjacentIndex];
					$maxElevation = $maxHeightsX[$adjacentIndex];

					$minSum += $minElevation * $weight;
					$maxSum += $maxElevation * $weight;
				}

				$minHeights[$columnIndex] = $minSum / $this->gaussian->weightSum1D;
				$maxHeights[$columnIndex] = $maxSum / $this->gaussian->weightSum1D;
			}
		}

		return [$minHeights, $maxHeights];
	}
}
