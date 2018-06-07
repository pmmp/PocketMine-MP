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

final class GeneratorManager{

	private static $list = [];

	public static function registerDefaultGenerators() : void{
		self::addGenerator(Flat::class, "flat");
		self::addGenerator(Normal::class, "normal");
		self::addGenerator(Normal::class, "default");
		self::addGenerator(Nether::class, "hell");
		self::addGenerator(Nether::class, "nether");
	}

	public static function addGenerator(string $object, string $name) : bool{
		if(is_subclass_of($object, Generator::class) and !isset(self::$list[$name = strtolower($name)])){
			self::$list[$name] = $object;

			return true;
		}

		return false;
	}

	/**
	 * @return string[]
	 */
	public static function getGeneratorList() : array{
		return array_keys(self::$list);
	}

	/**
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
