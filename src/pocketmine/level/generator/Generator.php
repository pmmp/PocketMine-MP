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
use pocketmine\level\generator\hell\Nether;
use pocketmine\level\generator\normal\Normal;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class Generator{
	private static $list = [];

	public static function registerDefaultGenerators() : void{
		self::addGenerator(Flat::class, "flat");
		self::addGenerator(Normal::class, "normal");
		self::addGenerator(Normal::class, "default");
		self::addGenerator(Nether::class, "hell");
		self::addGenerator(Nether::class, "nether");
	}

	public static function addGenerator($object, $name) : bool{
		if(is_subclass_of($object, Generator::class) and !isset(Generator::$list[$name = strtolower($name)])){
			Generator::$list[$name] = $object;

			return true;
		}

		return false;
	}

	/**
	 * @return string[]
	 */
	public static function getGeneratorList() : array{
		return array_keys(Generator::$list);
	}

	/**
	 * @param $name
	 *
	 * @return string|Generator Name of class that extends Generator (not an actual Generator object)
	 */
	public static function getGenerator($name){
		if(isset(Generator::$list[$name = strtolower($name)])){
			return Generator::$list[$name];
		}

		return Normal::class;
	}

	public static function getGeneratorName($class){
		foreach(Generator::$list as $name => $c){
			if($c === $class){
				return $name;
			}
		}

		return "unknown";
	}

	abstract public function __construct(array $settings = []);

	abstract public function init(ChunkManager $level, Random $random);

	abstract public function generateChunk(int $chunkX, int $chunkZ);

	abstract public function populateChunk(int $chunkX, int $chunkZ);

	abstract public function getSettings() : array;

	abstract public function getName() : string;

	abstract public function getSpawn() : Vector3;
}
