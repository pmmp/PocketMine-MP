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

namespace pocketmine\level\generator;

use pocketmine\level\generator\hell\Nether;
use pocketmine\level\generator\normal\Normal;
use function array_keys;
use function is_subclass_of;
use function strtolower;

final class GeneratorManager{
	/** @var string[] name => classname mapping */
	private static $list = [];

	/**
	 * Registers the default known generators.
	 */
	public static function registerDefaultGenerators() : void{
		self::addGenerator(Flat::class, "flat");
		self::addGenerator(Normal::class, "normal");
		self::addGenerator(Normal::class, "default");
		self::addGenerator(Nether::class, "hell");
		self::addGenerator(Nether::class, "nether");
	}

	/**
	 * @param string $class Fully qualified name of class that extends \pocketmine\level\generator\Generator
	 * @param string $name Alias for this generator type that can be written in configs
	 * @param bool   $overwrite Whether to force overwriting any existing registered generator with the same name
	 */
	public static function addGenerator(string $class, string $name, bool $overwrite = false) : void{
		if(!is_subclass_of($class, Generator::class)){
			throw new \InvalidArgumentException("Class $class does not extend " . Generator::class);
		}

		if(!$overwrite and isset(self::$list[$name = strtolower($name)])){
			throw new \InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		self::$list[$name] = $class;
	}

	/**
	 * Returns a list of names for registered generators.
	 *
	 * @return string[]
	 */
	public static function getGeneratorList() : array{
		return array_keys(self::$list);
	}

	/**
	 * Returns a class name of a registered Generator matching the given name.
	 *
	 * @param string $name
	 *
	 * @return string|Generator Name of class that extends Generator (not an actual Generator object)
	 */
	public static function getGenerator(string $name){
		if(isset(self::$list[$name = strtolower($name)])){
			return self::$list[$name];
		}

		return Normal::class;
	}

	/**
	 * Returns the registered name of the given Generator class.
	 *
	 * @param string $class Fully qualified name of class that extends \pocketmine\level\generator\Generator
	 *
	 * @return string
	 */
	public static function getGeneratorName(string $class) : string{
		foreach(self::$list as $name => $c){
			if($c === $class){
				return $name;
			}
		}

		return "unknown";
	}

	private function __construct(){
		//NOOP
	}
}
