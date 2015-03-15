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

namespace pocketmine\level\generator\normal;

use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\level\generator\GenerationManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Perlin;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\normal\biome\NormalBiome;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Random;

class Normal extends Generator{

	/** @var Populator[] */
	private $populators = [];
	/** @var ChunkManager */
	private $level;
	/** @var Random */
	private $random;
	private $waterHeight = 62;
	private $bedrockDepth = 5;

	/** @var Populator[] */
	private $generationPopulators = [];
	/** @var Simplex */
	private $noiseBase;

	/** @var BiomeSelector */
	private $selector;

	private static $GAUSSIAN_KERNEL = null;
	private static $SMOOTH_SIZE = 2;

	public function __construct(array $options = []){
		if(self::$GAUSSIAN_KERNEL === null){
			self::generateKernel();
		}
	}

	private static function generateKernel(){
		self::$GAUSSIAN_KERNEL = [];
		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;
		for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
			for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}

	public function getName(){
		return "normal";
	}

	public function getSettings(){
		return [];
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 16, 0.012, 0.5, 2);
		$this->random->setSeed($this->level->getSeed());
		$this->selector = new BiomeSelector($this->random, Biome::getBiome(Biome::OCEAN));

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;


		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(new CoalOre(), 20, 16, 0, 128),
			new OreType(New IronOre(), 20, 8, 0, 64),
			new OreType(new RedstoneOre(), 8, 7, 0, 16),
			new OreType(new LapisOre(), 1, 6, 0, 32),
			new OreType(new GoldOre(), 2, 8, 0, 32),
			new OreType(new DiamondOre(), 1, 7, 0, 16),
			new OreType(new Dirt(), 20, 32, 0, 128),
			new OreType(new Gravel(), 10, 16, 0, 128),
		]);
		$this->populators[] = $ores;
	}

	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$noise = Generator::getFastNoise3D($this->noiseBase, 16, 128, 16, 8, 8, 8, $chunkX * 16, 0, $chunkZ * 16);

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$minSum = 0;
				$maxSum = 0;
				$weightSum = 0;

				$biome = $this->selector->pickBiome($chunkX * 16 + $x, $chunkZ * 16 + $z);
				$chunk->setBiomeId($x, $z, $biome->getId());
				$chunk->setBiomeColor($x, $z, 255 - (($biome->getId() * 1377) % 255), 255 - (($biome->getId() * 4096) % 255), 255 - (($biome->getId() * 31337) % 255));

				for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
					for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){
						if($sx === 0 and $sz === 0){
							$adjacent = $biome;
						}else{
							$adjacent = $this->selector->pickBiome($chunkX * 16 + $x + $sx * 16, $chunkZ * 16 + $z + $sz * 16);
						}
						/** @var NormalBiome $adjacent */
						$weight = self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE];
						$minSum += $adjacent->getMinElevation() * $weight;
						$maxSum += $adjacent->getMaxElevation() * $weight;
						$weightSum += $weight;
					}
				}

				$minElevation = $minSum / $weightSum;
				$smoothHeight = ($maxSum / $weightSum - $minElevation) / 2;

				for($y = 0; $y < 128; ++$y){
					if($y === 0){
						$chunk->setBlockId($x, $y, $z, Block::BEDROCK);
						continue;
					}
					$noiseValue = $noise[$x][$z][$y] - 1 / $smoothHeight * ($y - $smoothHeight - $minElevation);

					if($noiseValue >= 0){
						$chunk->setBlockId($x, $y, $z, Block::STONE);
					}else{
						if($y <= $this->waterHeight){
							$chunk->setBlockId($x, $y, $z, Block::STILL_WATER);
							$lightValue = 15 - ($this->waterHeight - $y) * 2;
							if($lightValue > 0){
								$chunk->setBlockSkyLight($x, $y, $z, $lightValue);
							}
						}else{
							$chunk->setBlockSkyLight($x, $y, $z, 15);
						}
					}
				}
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn(){
		return $this->level->getSafeSpawn(new Vector3(127.5, 128, 127.5));
	}

}