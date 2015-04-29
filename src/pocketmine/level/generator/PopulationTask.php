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

class PopulationTask extends AsyncTask{


	public $state;
	public $levelId;
	public $chunk;
	public $chunkClass;

	public $chunk00;
	public $chunk01;
	public $chunk02;
	public $chunk10;
	//center chunk
	public $chunk12;
	public $chunk20;
	public $chunk21;
	public $chunk22;

	public function __construct(Level $level, FullChunk $chunk){
		$this->state = true;
		$this->levelId = $level->getId();
		$this->chunk = $chunk->toFastBinary();
		$this->chunkClass = get_class($chunk);

		$this->chunk00 = $level->getChunk($chunk->getX() - 1, $chunk->getZ() - 1, true)->toFastBinary();
		$this->chunk01 = $level->getChunk($chunk->getX() - 1, $chunk->getZ(), true)->toFastBinary();
		$this->chunk02 = $level->getChunk($chunk->getX() - 1, $chunk->getZ() + 1, true)->toFastBinary();
		$this->chunk10 = $level->getChunk($chunk->getX(), $chunk->getZ() - 1, true)->toFastBinary();

		$this->chunk12 = $level->getChunk($chunk->getX(), $chunk->getZ() + 1, true)->toFastBinary();
		$this->chunk20 = $level->getChunk($chunk->getX() + 1, $chunk->getZ() - 1, true)->toFastBinary();
		$this->chunk21 = $level->getChunk($chunk->getX() + 1, $chunk->getZ(), true)->toFastBinary();
		$this->chunk22 = $level->getChunk($chunk->getX() + 1, $chunk->getZ() + 1, true)->toFastBinary();
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

		/** @var FullChunk[] $chunks */
		$chunks = [];
		/** @var FullChunk $chunkC */
		$chunkC = $this->chunkClass;

		$chunks[0] = $chunkC::fromFastBinary($this->chunk00);
		$chunks[1] = $chunkC::fromFastBinary($this->chunk01);
		$chunks[2] = $chunkC::fromFastBinary($this->chunk02);
		$chunks[3] = $chunkC::fromFastBinary($this->chunk10);
		$chunk = $chunkC::fromFastBinary($this->chunk);
		$chunks[5] = $chunkC::fromFastBinary($this->chunk12);
		$chunks[6] = $chunkC::fromFastBinary($this->chunk20);
		$chunks[7] = $chunkC::fromFastBinary($this->chunk21);
		$chunks[8] = $chunkC::fromFastBinary($this->chunk22);

		if($chunk === null){
			//TODO error
			return;
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

		foreach($chunks as $c){
			if($c !== null){
				$manager->setChunk($c->getX(), $c->getZ(), $c);
			}
		}

		$generator->populateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->recalculateHeightMap();
		$chunk->setPopulated(true);
		$this->chunk = $chunk->toFastBinary();

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);

		foreach($chunks as $i => $c){
			if($c !== null){
				$c = $chunks[$i] = $manager->getChunk($c->getX(), $c->getZ());
				if(!$c->hasChanged()){
					$chunks[$i] = null;
				}
			}else{
				//This way non-changed chunks are not set
				$chunks[$i] = null;
			}
		}

		$manager->cleanChunks();

		$this->chunk00 = $chunks[0] !== null ? $chunks[0]->toFastBinary() : null;
		$this->chunk01 = $chunks[1] !== null ? $chunks[1]->toFastBinary() : null;
		$this->chunk02 = $chunks[2] !== null ? $chunks[2]->toFastBinary() : null;
		$this->chunk10 = $chunks[3] !== null ? $chunks[3]->toFastBinary() : null;

		$this->chunk12 = $chunks[5] !== null ? $chunks[5]->toFastBinary() : null;
		$this->chunk20 = $chunks[6] !== null ? $chunks[6]->toFastBinary() : null;
		$this->chunk21 = $chunks[7] !== null ? $chunks[7]->toFastBinary() : null;
		$this->chunk22 = $chunks[8] !== null ? $chunks[8]->toFastBinary() : null;
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level !== null){
			if($this->state === false){
				$level->registerGenerator();
				return;
			}
			/** @var FullChunk[] $chunks */
			$chunks = [];
			/** @var FullChunk $chunkC */
			$chunkC = $this->chunkClass;

			$chunks[0] = $chunkC::fromFastBinary($this->chunk00, $level->getProvider());
			$chunks[1] = $chunkC::fromFastBinary($this->chunk01, $level->getProvider());
			$chunks[2] = $chunkC::fromFastBinary($this->chunk02, $level->getProvider());
			$chunks[3] = $chunkC::fromFastBinary($this->chunk10, $level->getProvider());
			$chunk = $chunkC::fromFastBinary($this->chunk, $level->getProvider());
			$chunks[5] = $chunkC::fromFastBinary($this->chunk12, $level->getProvider());
			$chunks[6] = $chunkC::fromFastBinary($this->chunk20, $level->getProvider());
			$chunks[7] = $chunkC::fromFastBinary($this->chunk21, $level->getProvider());
			$chunks[8] = $chunkC::fromFastBinary($this->chunk22, $level->getProvider());

			foreach($chunks as $c){
				if($c !== null){
					$level->generateChunkCallback($c->getX(), $c->getZ(), $c);
				}
			}


			if($chunk === null){
				//TODO error
				return;
			}

			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
