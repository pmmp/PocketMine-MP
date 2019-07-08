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

use pocketmine\block\BlockFactory;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\biome\Biome;
use pocketmine\world\World;
use function serialize;
use function unserialize;

class GeneratorRegisterTask extends AsyncTask{

	public $generatorClass;
	public $settings;
	public $seed;
	public $worldId;
	public $worldHeight = World::Y_MAX;

	public function __construct(World $world, string $generatorClass, array $generatorSettings = []){
		$this->generatorClass = $generatorClass;
		$this->settings = serialize($generatorSettings);
		$this->seed = $world->getSeed();
		$this->worldId = $world->getId();
		$this->worldHeight = $world->getWorldHeight();
	}

	public function onRun() : void{
		BlockFactory::init();
		Biome::init();
		$manager = new GeneratorChunkManager($this->worldHeight);
		$this->worker->saveToThreadStore("generation.world{$this->worldId}.manager", $manager);

		/**
		 * @var Generator $generator
		 * @see Generator::__construct()
		 */
		$generator = new $this->generatorClass($manager, $this->seed, unserialize($this->settings));
		$this->worker->saveToThreadStore("generation.world{$this->worldId}.generator", $generator);
	}
}
