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
	 * @var GeneratorManagerEntry[] name => classname mapping
	 * @phpstan-var array<string, GeneratorManagerEntry>
	 */
	private array $list = [];

	public function __construct(){
		$this->addGenerator(Flat::class, "flat", \Closure::fromCallable(function(string $preset) : ?InvalidGeneratorOptionsException{
			if($preset === ""){
				return null;
			}
			try{
				FlatGeneratorOptions::parsePreset($preset);
				return null;
			}catch(InvalidGeneratorOptionsException $e){
				return $e;
			}
		}));
		$this->addGenerator(Normal::class, "normal", fn() => null);
		$this->addGenerator(Normal::class, "default", fn() => null);
		$this->addGenerator(Nether::class, "hell", fn() => null);
		$this->addGenerator(Nether::class, "nether", fn() => null);
	}

	/**
	 * @param string   $class           Fully qualified name of class that extends \pocketmine\world\generator\Generator
	 * @param string   $name            Alias for this generator type that can be written in configs
	 * @param \Closure $presetValidator Callback to validate generator options for new worlds
	 * @param bool     $overwrite       Whether to force overwriting any existing registered generator with the same name
	 *
	 * @phpstan-param \Closure(string) : ?InvalidGeneratorOptionsException $presetValidator
	 *
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addGenerator(string $class, string $name, \Closure $presetValidator, bool $overwrite = false) : void{
		Utils::testValidInstance($class, Generator::class);

		$name = strtolower($name);
		if(!$overwrite && isset($this->list[$name])){
			throw new \InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		$this->list[$name] = new GeneratorManagerEntry($class, $presetValidator);
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
	 * Returns the generator entry of a registered Generator matching the given name, or null if not found.
	 */
	public function getGenerator(string $name) : ?GeneratorManagerEntry{
		return $this->list[strtolower($name)] ?? null;
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
		foreach(Utils::stringifyKeys($this->list) as $name => $c){
			if($c->getGeneratorClass() === $class){
				return $name;
			}
		}

		throw new \InvalidArgumentException("Generator class $class is not registered");
	}
}
