<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */
 
declare(strict_types=1);

/**
 * Noise classes used in Levels
 */
namespace pocketmine\level\generator;

use pocketmine\entity\CreatureType;
use pocketmine\level\biome\Biome;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;

abstract class Generator{

	/**
	 * Converts a string level seed into an integer for use by the generator.
	 *
	 * @param string $seed
	 *
	 * @return int|null
	 */
	public static function convertSeed(string $seed) : ?int{
		if($seed === ""){ //empty seed should cause a random seed to be selected - can't use 0 here because 0 is a valid seed
			$convertedSeed = null;
		}elseif(ctype_digit($seed)){ //this avoids treating seeds like "404.4" as integer seeds
			$convertedSeed = (int) $seed;
		}else{
			$convertedSeed = Utils::javaStringHash($seed);
		}

		return $convertedSeed;
	}

	/** @var ChunkManager */
	protected $level;
	/** @var int */
	protected $seed;
	/** @var array */
	protected $options;

	/** @var Random */
	protected $random;

	public function __construct(ChunkManager $level, int $seed, array $options = []){
		$this->level = $level;
		$this->seed = $seed;
		$this->options = $options;
		$this->random = new Random($seed);
	}

	abstract public function generateChunk(int $chunkX, int $chunkZ) : void;

	abstract public function populateChunk(int $chunkX, int $chunkZ) : void;

	public function getSettings() : array{
		return $this->options;
	}

	public function getPossibleCreatures(Vector3 $pos, CreatureType $creatureType) : array{
		return Biome::getBiome($this->level->getChunk($pos->x >> 4, $pos->z >> 4)->getBiomeId($pos->x & 15, $pos->z & 15))->getSpawnableList($creatureType);
	}

	abstract public function getName() : string;

	abstract public function getSpawn() : Vector3;
}