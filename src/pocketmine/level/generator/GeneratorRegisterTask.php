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

use pocketmine\block\BlockFactory;
use pocketmine\item\ItemFactory;
use pocketmine\level\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Random;
use function serialize;
use function unserialize;

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
	public $levelId;
	/** @var int */
	public $worldHeight = Level::Y_MAX;

	/**
	 * @param mixed[] $generatorSettings
	 * @phpstan-param class-string<Generator> $generatorClass
	 * @phpstan-param array<string, mixed> $generatorSettings
	 */
	public function __construct(Level $level, string $generatorClass, array $generatorSettings = []){
		$this->generatorClass = $generatorClass;
		$this->settings = serialize($generatorSettings);
		$this->seed = $level->getSeed();
		$this->levelId = $level->getId();
		$this->worldHeight = $level->getWorldHeight();
	}

	public function onRun(){
		BlockFactory::init();
		ItemFactory::init();
		Biome::init();
		$manager = new SimpleChunkManager($this->seed, $this->worldHeight);
		$this->saveToThreadStore("generation.level{$this->levelId}.manager", $manager);

		/**
		 * @var Generator $generator
		 * @see Generator::__construct()
		 */
		$generator = new $this->generatorClass(unserialize($this->settings));
		$generator->init($manager, new Random($manager->getSeed()));
		$this->saveToThreadStore("generation.level{$this->levelId}.generator", $generator);
	}
}
