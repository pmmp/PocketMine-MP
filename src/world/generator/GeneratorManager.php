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

namespace pocketmine\world\generator;

use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\generator\hell\Nether;
use pocketmine\world\generator\normal\Normal;
use function array_keys;
use function strtolower;

final class GeneratorManager{
	use SingletonTrait;

	/**
	 * @var string[] name => classname mapping
	 * @phpstan-var array<string, class-string<Generator>>
	 */
	private $list = [];

	public function __construct(){
		$this->addGenerator(Flat::class, "flat");
		$this->addGenerator(Normal::class, "normal");
		$this->addGenerator(Normal::class, "default");
		$this->addGenerator(Nether::class, "hell");
		$this->addGenerator(Nether::class, "nether");
	}

	/**
	 * @param string $class Fully qualified name of class that extends \pocketmine\world\generator\Generator
	 * @param string $name Alias for this generator type that can be written in configs
	 * @param bool   $overwrite Whether to force overwriting any existing registered generator with the same name
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addGenerator(string $class, string $name, bool $overwrite = false) : void{
		Utils::testValidInstance($class, Generator::class);

		if(!$overwrite and isset($this->list[$name = strtolower($name)])){
			throw new \InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		$this->list[$name] = $class;
	}

	/**
	 * Returns a list of names for registered generators.
	 *
	 * @return string[]
	 */
	public function getGeneratorList() : array{
		return array_keys($this->list);
	}

	/**
	 * Returns a class name of a registered Generator matching the given name.
	 *
	 * @param bool   $throwOnMissing @deprecated this is for backwards compatibility only
	 *
	 * @return string Name of class that extends Generator
	 * @phpstan-return class-string<Generator>
	 *
	 * @throws \InvalidArgumentException if the generator type isn't registered
	 */
	public function getGenerator(string $name, bool $throwOnMissing = false){
		if(isset($this->list[$name = strtolower($name)])){
			return $this->list[$name];
		}

		if($throwOnMissing){
			throw new \InvalidArgumentException("Alias \"$name\" does not map to any known generator");
		}
		return Normal::class;
	}

	/**
	 * Returns the registered name of the given Generator class.
	 *
	 * @param string $class Fully qualified name of class that extends \pocketmine\world\generator\Generator
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws \InvalidArgumentException if the class type cannot be matched to a known alias
	 */
	public function getGeneratorName(string $class) : string{
		Utils::testValidInstance($class, Generator::class);
		foreach($this->list as $name => $c){
			if($c === $class){
				return $name;
			}
		}

		throw new \InvalidArgumentException("Generator class $class is not registered");
	}
}
