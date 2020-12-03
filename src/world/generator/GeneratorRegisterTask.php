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

use pocketmine\scheduler\AsyncTask;
use pocketmine\world\World;
use function igbinary_serialize;
use function igbinary_unserialize;

class GeneratorRegisterTask extends AsyncTask{

	/**
	 * @var string
	 * @phpstan-var class-string<Generator>
	 */
	public $generatorClass;
	/** @var string */
	public $settings;
	/** @var int */
	public $seed;
	/** @var int */
	public $worldId;
	/** @var int */
	public $worldHeight = World::Y_MAX;

	/**
	 * @param mixed[] $generatorSettings
	 * @phpstan-param class-string<Generator> $generatorClass
	 * @phpstan-param array<string, mixed> $generatorSettings
	 */
	public function __construct(World $world, string $generatorClass, array $generatorSettings = []){
		$this->generatorClass = $generatorClass;
		$this->settings = igbinary_serialize($generatorSettings);
		$this->seed = $world->getSeed();
		$this->worldId = $world->getId();
		$this->worldHeight = $world->getWorldHeight();
	}

	public function onRun() : void{
		/**
		 * @var Generator $generator
		 * @see Generator::__construct()
		 */
		$generator = new $this->generatorClass($this->seed, igbinary_unserialize($this->settings));
		ThreadLocalGeneratorContext::register(new ThreadLocalGeneratorContext($generator, $this->worldHeight), $this->worldId);
	}
}
