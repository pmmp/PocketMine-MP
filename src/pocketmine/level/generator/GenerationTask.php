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

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class GenerationTask extends AsyncTask{

	public $state;
	public $levelId;
	public $chunk;

	public function __construct(Level $level, Chunk $chunk){
		$this->state = true;
		$this->levelId = $level->getId();
		$this->chunk = $chunk->fastSerialize();
	}

	public function onRun(){
		/** @var SimpleChunkManager $manager */
		$manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
		/** @var Generator $generator */
		$generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
		if($manager === null or $generator === null){
			$this->state = false;
			return;
		}

		/** @var Chunk $chunk */
		$chunk = Chunk::fastDeserialize($this->chunk);
		if($chunk === null){
			//TODO error
			return;
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

		$generator->generateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setGenerated();
		$this->chunk = $chunk->fastSerialize();

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level !== null){
			if($this->state === false){
				$level->registerGenerator();
				return;
			}
			/** @var Chunk $chunk */
			$chunk = Chunk::fastDeserialize($this->chunk);
			if($chunk === null){
				//TODO error
				return;
			}
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
