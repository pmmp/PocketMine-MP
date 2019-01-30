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

namespace pocketmine\level\generator\hell;

use pocketmine\block\Block;
use pocketmine\level\biome\Biome;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\InvalidGeneratorOptionsException;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use function abs;

class Nether extends Generator{

	/** @var Populator[] */
	private $populators = [];
	/** @var int */
	private $waterHeight = 32;
	/** @var int */
	private $emptyHeight = 64;
	/** @var int */
	private $emptyAmplitude = 1;
	/** @var float */
	private $density = 0.5;

	/** @var Populator[] */
	private $generationPopulators = [];
	/** @var Simplex */
	private $noiseBase;

	/**
	 * @param ChunkManager $level
	 * @param int          $seed
	 * @param array        $options
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(ChunkManager $level, int $seed, array $options = []){
		parent::__construct($level, $seed, $options);

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);
		$this->random->setSeed($this->seed);

		/*$ores = new Ore();
		$ores->setOreTypes([
			new OreType(new CoalOre(), 20, 16, 0, 128),
			new OreType(new IronOre(), 20, 8, 0, 64),
			new OreType(new RedstoneOre(), 8, 7, 0, 16),
			new OreType(new LapisOre(), 1, 6, 0, 32),
			new OreType(new GoldOre(), 2, 8, 0, 32),
			new OreType(new DiamondOre(), 1, 7, 0, 16),
			new OreType(new Dirt(), 20, 32, 0, 128),
			new OreType(new Gravel(), 10, 16, 0, 128)
		]);
		$this->populators[] = $ores;*/
	}

	public function getName() : string{
		return "nether";
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);

		$noise = $this->noiseBase->getFastNoise3D(16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){

				$biome = Biome::getBiome(Biome::HELL);
				$chunk->setBiomeId($x, $z, $biome->getId());

				for($y = 0; $y < 128; ++$y){
					if($y === 0 or $y === 127){
						$chunk->setBlock($x, $y, $z, Block::BEDROCK, 0);
						continue;
					}
					$noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
					$noiseValue -= 1 - $this->density;

					if($noiseValue > 0){
						$chunk->setBlock($x, $y, $z, Block::NETHERRACK, 0);
					}elseif($y <= $this->waterHeight){
						$chunk->setBlock($x, $y, $z, Block::STILL_LAVA, 0);
					}
				}
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn() : Vector3{
		return new Vector3(127.5, 128, 127.5);
	}
}
