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
use pocketmine\world\ChunkPos;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
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
		$this->chunk = FastChunkSerializer::serialize($chunk);

		$pos = $chunk->getPos();
		foreach($world->getAdjacentChunks($pos->getX(), $pos->getZ()) as $i => $c){
			$this->{"chunk$i"} = $c !== null ? FastChunkSerializer::serialize($c) : null;
		}

		$this->storeLocal(self::TLS_KEY_WORLD, $world);
	}

	public function onRun() : void{
		$manager = $this->worker->getFromThreadStore("generation.world{$this->worldId}.manager");
		$generator = $this->worker->getFromThreadStore("generation.world{$this->worldId}.generator");
		if(!($manager instanceof GeneratorChunkManager) or !($generator instanceof Generator)){
			$this->state = false;
			return;
		}

		/** @var Chunk[] $chunks */
		$chunks = [];

		$chunk = FastChunkSerializer::deserialize($this->chunk);
		$centerCPos = $chunk->getPos();

		for($i = 0; $i < 9; ++$i){
			if($i === 4){
				continue;
			}
			$xx = -1 + $i % 3;
			$zz = -1 + (int) ($i / 3);
			$ck = $this->{"chunk$i"};
			if($ck === null){
				$chunks[$i] = new Chunk($centerCPos->add($xx, $zz));
			}else{
				$chunks[$i] = FastChunkSerializer::deserialize($ck);
			}
		}

		$manager->setChunk($centerCPos->getX(), $centerCPos->getZ(), $chunk);
		if(!$chunk->isGenerated()){
			$generator->generateChunk($centerCPos->getX(), $centerCPos->getZ());
			$chunk = $manager->getChunk($centerCPos->getX(), $centerCPos->getZ());
			$chunk->setGenerated();
		}

		foreach($chunks as $i => $c){
			$cPos = $c->getPos();
			$manager->setChunk($cPos->getX(), $cPos->getZ(), $c);
			if(!$c->isGenerated()){
				$generator->generateChunk($cPos->getX(), $cPos->getZ());
				$chunks[$i] = $manager->getChunk($cPos->getX(), $cPos->getZ());
				$chunks[$i]->setGenerated();
			}
		}

		$generator->populateChunk($centerCPos->getX(), $centerCPos->getZ());
		$chunk = $manager->getChunk($centerCPos->getX(), $centerCPos->getZ());
		$chunk->setPopulated();

		$blockFactory = BlockFactory::getInstance();
		$chunk->recalculateHeightMap($blockFactory->lightFilter, $blockFactory->diffusesSkyLight);
		$chunk->populateSkyLight($blockFactory->lightFilter);
		$chunk->setLightPopulated();

		$this->chunk = FastChunkSerializer::serialize($chunk);

		foreach($chunks as $i => $c){
			$this->{"chunk$i"} = $c->isDirty() ? FastChunkSerializer::serialize($c) : null;
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
					$cpos = $c->getPos();
					$world->generateChunkCallback($cpos->getX(), $cpos->getZ(), $this->state ? $c : null);
				}
			}

			$pos = $chunk->getPos();
			$world->generateChunkCallback($pos->getX(), $pos->getZ(), $this->state ? $chunk : null);
		}
	}
}
