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

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\world\biome\Biome;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\biome\BiomeSelector;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\InvalidGeneratorOptionsException;
use pocketmine\world\generator\noise\Simplex;
use pocketmine\world\generator\object\OreType;
use pocketmine\world\generator\populator\GroundCover;
use pocketmine\world\generator\populator\Ore;
use pocketmine\world\generator\populator\Populator;
use pocketmine\world\World;
use function exp;

class Normal extends Generator{

	/** @var Populator[] */
	private $populators = [];
	/** @var int */
	private $waterHeight = 62;

	/** @var Populator[] */
	private $generationPopulators = [];
	/** @var Simplex */
	private $noiseBase;

	/** @var BiomeSelector */
	private $selector;

	private static $GAUSSIAN_KERNEL = null;
	private static $SMOOTH_SIZE = 2;

	/**
	 * @param ChunkManager $world
	 * @param int          $seed
	 * @param array        $options
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(ChunkManager $world, int $seed, array $options = []){
		parent::__construct($world, $seed, $options);
		if(self::$GAUSSIAN_KERNEL === null){
			self::generateKernel();
		}

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);
		$this->random->setSeed($this->seed);

		$this->selector = new class($this->random) extends BiomeSelector{
			protected function lookup(float $temperature, float $rainfall) : int{
				if($rainfall < 0.25){
					if($temperature < 0.7){
						return Biome::OCEAN;
					}elseif($temperature < 0.85){
						return Biome::RIVER;
					}else{
						return Biome::SWAMP;
					}
				}elseif($rainfall < 0.60){
					if($temperature < 0.25){
						return Biome::ICE_PLAINS;
					}elseif($temperature < 0.75){
						return Biome::PLAINS;
					}else{
						return Biome::DESERT;
					}
				}elseif($rainfall < 0.80){
					if($temperature < 0.25){
						return Biome::TAIGA;
					}elseif($temperature < 0.75){
						return Biome::FOREST;
					}else{
						return Biome::BIRCH_FOREST;
					}
				}else{
					//FIXME: This will always cause River to be used since the rainfall is always greater than 0.8 if we
					//reached this branch. However I don't think that substituting temperature for rainfall is correct given
					//that mountain biomes are supposed to be pretty cold.
					if($rainfall < 0.25){
						return Biome::MOUNTAINS;
					}elseif($rainfall < 0.70){
						return Biome::SMALL_MOUNTAINS;
					}else{
						return Biome::RIVER;
					}
				}
			}
		};

		$this->selector->recalculate();

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(BlockFactory::get(BlockLegacyIds::COAL_ORE), 20, 16, 0, 128),
			new OreType(BlockFactory::get(BlockLegacyIds::IRON_ORE), 20, 8, 0, 64),
			new OreType(BlockFactory::get(BlockLegacyIds::REDSTONE_ORE), 8, 7, 0, 16),
			new OreType(BlockFactory::get(BlockLegacyIds::LAPIS_ORE), 1, 6, 0, 32),
			new OreType(BlockFactory::get(BlockLegacyIds::GOLD_ORE), 2, 8, 0, 32),
			new OreType(BlockFactory::get(BlockLegacyIds::DIAMOND_ORE), 1, 7, 0, 16),
			new OreType(BlockFactory::get(BlockLegacyIds::DIRT), 20, 32, 0, 128),
			new OreType(BlockFactory::get(BlockLegacyIds::GRAVEL), 10, 16, 0, 128)
		]);
		$this->populators[] = $ores;
	}

	private static function generateKernel() : void{
		self::$GAUSSIAN_KERNEL = [];

		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;

		for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
			self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE] = [];

			for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}

	private function pickBiome(int $x, int $z) : Biome{
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->seed;
		$hash *= $hash + 223;
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if($xNoise == 3){
			$xNoise = 1;
		}
		if($zNoise == 3){
			$zNoise = 1;
		}

		return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);

		$noise = $this->noiseBase->getFastNoise3D(16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);

		$chunk = $this->world->getChunk($chunkX, $chunkZ);

		$biomeCache = [];

		$bedrock = BlockFactory::get(BlockLegacyIds::BEDROCK)->getFullId();
		$stillWater = BlockFactory::get(BlockLegacyIds::STILL_WATER)->getFullId();
		$stone = BlockFactory::get(BlockLegacyIds::STONE)->getFullId();

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$minSum = 0;
				$maxSum = 0;
				$weightSum = 0;

				$biome = $this->pickBiome($chunkX * 16 + $x, $chunkZ * 16 + $z);
				$chunk->setBiomeId($x, $z, $biome->getId());

				for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
					for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){

						$weight = self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE];

						if($sx === 0 and $sz === 0){
							$adjacent = $biome;
						}else{
							$index = World::chunkHash($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
							if(isset($biomeCache[$index])){
								$adjacent = $biomeCache[$index];
							}else{
								$biomeCache[$index] = $adjacent = $this->pickBiome($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
							}
						}

						$minSum += ($adjacent->getMinElevation() - 1) * $weight;
						$maxSum += $adjacent->getMaxElevation() * $weight;

						$weightSum += $weight;
					}
				}

				$minSum /= $weightSum;
				$maxSum /= $weightSum;

				$smoothHeight = ($maxSum - $minSum) / 2;

				for($y = 0; $y < 128; ++$y){
					if($y === 0){
						$chunk->setFullBlock($x, $y, $z, $bedrock);
						continue;
					}
					$noiseValue = $noise[$x][$z][$y] - 1 / $smoothHeight * ($y - $smoothHeight - $minSum);

					if($noiseValue > 0){
						$chunk->setFullBlock($x, $y, $z, $stone);
					}elseif($y <= $this->waterHeight){
						$chunk->setFullBlock($x, $y, $z, $stillWater);
					}
				}
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($this->world, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($this->world, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->world, $chunkX, $chunkZ, $this->random);
	}
}
