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
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\SimpleChunkManager;
use pocketmine\world\World;

class PopulationTask extends AsyncTask{
	private const TLS_KEY_WORLD = "world";

	/** @var bool */
	public $state;
	/** @var int */
	public $worldId;
	/** @var string */
	public $chunk;

	/** @var string */
	public $chunk0;
	/** @var string */
	public $chunk1;
	/** @var string */
	public $chunk2;
	/** @var string */
	public $chunk3;

	//center chunk

	/** @var string */
	public $chunk5;
	/** @var string */
	public $chunk6;
	/** @var string */
	public $chunk7;
	/** @var string */
	public $chunk8;

	public function __construct(World $world, Chunk $chunk){
		$this->state = true;
		$this->worldId = $world->getId();
		$this->chunk = FastChunkSerializer::serializeWithoutLight($chunk);

		foreach($world->getAdjacentChunks($chunk->getX(), $chunk->getZ()) as $i => $c){
			$this->{"chunk$i"} = $c !== null ? FastChunkSerializer::serializeWithoutLight($c) : null;
		}

		$this->storeLocal(self::TLS_KEY_WORLD, $world);
	}

	public function onRun() : void{
		$manager = $this->worker->getFromThreadStore("generation.world{$this->worldId}.manager");
		$generator = $this->worker->getFromThreadStore("generation.world{$this->worldId}.generator");
		if(!($manager instanceof SimpleChunkManager) or !($generator instanceof Generator)){
			$this->state = false;
			return;
		}

		/** @var Chunk[] $chunks */
		$chunks = [];

		$chunk = FastChunkSerializer::deserialize($this->chunk);

		for($i = 0; $i < 9; ++$i){
			if($i === 4){
				continue;
			}
			$xx = -1 + $i % 3;
			$zz = -1 + (int) ($i / 3);
			$ck = $this->{"chunk$i"};
			if($ck === null){
				$chunks[$i] = new Chunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
			}else{
				$chunks[$i] = FastChunkSerializer::deserialize($ck);
			}
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
		if(!$chunk->isGenerated()){
			$generator->generateChunk($manager, $chunk->getX(), $chunk->getZ());
			$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
			$chunk->setGenerated();
		}

		foreach($chunks as $i => $c){
			$manager->setChunk($c->getX(), $c->getZ(), $c);
			if(!$c->isGenerated()){
				$generator->generateChunk($manager, $c->getX(), $c->getZ());
				$chunks[$i] = $manager->getChunk($c->getX(), $c->getZ());
				$chunks[$i]->setGenerated();
			}
		}

		$generator->populateChunk($manager, $chunk->getX(), $chunk->getZ());
		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setPopulated();

		$this->chunk = FastChunkSerializer::serializeWithoutLight($chunk);

		foreach($chunks as $i => $c){
			$this->{"chunk$i"} = $c->isDirty() ? FastChunkSerializer::serializeWithoutLight($c) : null;
		}

		$manager->cleanChunks();
	}

	public function onCompletion() : void{
		/** @var World $world */
		$world = $this->fetchLocal(self::TLS_KEY_WORLD);
		if(!$world->isClosed()){
			if(!$this->state){
				$world->registerGeneratorToWorker($this->worker->getAsyncWorkerId());
			}

			$chunk = FastChunkSerializer::deserialize($this->chunk);

			for($i = 0; $i < 9; ++$i){
				if($i === 4){
					continue;
				}
				$c = $this->{"chunk$i"};
				if($c !== null){
					$c = FastChunkSerializer::deserialize($c);
					$world->generateChunkCallback($c->getX(), $c->getZ(), $this->state ? $c : null);
				}
			}

			$world->generateChunkCallback($chunk->getX(), $chunk->getZ(), $this->state ? $chunk : null);
		}
	}
}
