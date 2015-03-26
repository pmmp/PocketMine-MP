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

namespace pocketmine\level\generator;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Random;

class GenerationTask extends AsyncTask{

	public $generator;
	public $settings;
	public $seed;
	public $levelId;
	public $chunk;
	public $chunkClass;

	public function __construct(Level $level, Generator $generator, FullChunk $chunk){
		$this->generator = get_class($generator);
		$this->settings = $generator->getSettings();
		$this->seed = $level->getSeed();
		$this->levelId = $level->getId();
		$this->chunk = $chunk->toBinary();
		$this->chunkClass = get_class($chunk);
	}

	public function onRun(){
		/** @var SimpleChunkManager $manager */
		$manager = $this->getFromThreadStore($key = "generation.level{$this->levelId}.manager");
		/** @var Generator $generator */
		$generator = $this->getFromThreadStore($gKey = "generation.level{$this->levelId}.generator");
		if($manager === null or $generator === null){
			Block::init();
			Biome::init();
			$manager = new SimpleChunkManager($this->seed);
			$this->saveToThreadStore($key, $manager);
			/** @var Generator $generator */
			$generator = $this->generator;
			$generator = new $generator($this->settings);
			$generator->init($manager, new Random($manager->getSeed()));
			$this->saveToThreadStore($gKey, $generator);
		}

		/** @var FullChunk $chunk */
		$chunk = $this->chunkClass;
		$chunk = $chunk::fromBinary($this->chunk);
		if($chunk === null){
			//TODO error
			return;
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

		$generator->generateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setGenerated(true);
		$this->chunk = $chunk->toBinary();

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level !== null){
			/** @var FullChunk $chunk */
			$chunk = $this->chunkClass;
			$chunk = $chunk::fromBinary($this->chunk);
			if($chunk === null){
				//TODO error
				return;
			}
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
