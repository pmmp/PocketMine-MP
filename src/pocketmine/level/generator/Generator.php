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

/**
 * Noise classes used in Levels
 */
namespace pocketmine\level\generator;

use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;
use function ctype_digit;

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
	/** @var Random */
	protected $random;

	abstract public function __construct(array $settings = []);


	public function init(ChunkManager $level, Random $random) : void{
		$this->level = $level;
		$this->random = $random;
	}

	abstract public function generateChunk(int $chunkX, int $chunkZ) : void;

	abstract public function populateChunk(int $chunkX, int $chunkZ) : void;

	abstract public function getSettings() : array;

	abstract public function getName() : string;

	abstract public function getSpawn() : Vector3;
}
