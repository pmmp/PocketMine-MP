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

use pocketmine\data\bedrock\BiomeIds;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\SimpleChunkManager;
use pocketmine\world\World;
use function array_map;
use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * @phpstan-type OnCompletion \Closure(Chunk $centerChunk, array<int, Chunk> $adjacentChunks) : void
 */
class PopulationTask extends AsyncTask{
	private const TLS_KEY_ON_COMPLETION = "onCompletion";

	private ?string $chunk;

	private string $adjacentChunks;

	/**
	 * @param Chunk[]|null[] $adjacentChunks
	 * @phpstan-param array<int, Chunk|null> $adjacentChunks
	 * @phpstan-param OnCompletion $onCompletion
	 */
	public function __construct(
		private int $worldId,
		private int $chunkX,
		private int $chunkZ,
		?Chunk $chunk,
		array $adjacentChunks,
		\Closure $onCompletion
	){
		$this->chunk = $chunk !== null ? FastChunkSerializer::serializeTerrain($chunk) : null;

		$this->adjacentChunks = igbinary_serialize(array_map(
			fn(?Chunk $c) => $c !== null ? FastChunkSerializer::serializeTerrain($c) : null,
			$adjacentChunks
		)) ?? throw new AssumptionFailedError("igbinary_serialize() returned null");

		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void{
		$context = ThreadLocalGeneratorContext::fetch($this->worldId);
		if($context === null){
			throw new AssumptionFailedError("Generator context should have been initialized before any PopulationTask execution");
		}
		$generator = $context->getGenerator();
		$manager = new SimpleChunkManager($context->getWorldMinY(), $context->getWorldMaxY());

		$chunk = $this->chunk !== null ? FastChunkSerializer::deserializeTerrain($this->chunk) : null;

		/** @var string[] $serialChunks */
		$serialChunks = igbinary_unserialize($this->adjacentChunks);
		$chunks = array_map(
			fn(?string $serialized) => $serialized !== null ? FastChunkSerializer::deserializeTerrain($serialized) : null,
			$serialChunks
		);

		self::setOrGenerateChunk($manager, $generator, $this->chunkX, $this->chunkZ, $chunk);

		/** @var Chunk[] $resultChunks */
		$resultChunks = []; //this is just to keep phpstan's type inference happy
		foreach($chunks as $relativeChunkHash => $c){
			World::getXZ($relativeChunkHash, $relativeX, $relativeZ);
			$resultChunks[$relativeChunkHash] = self::setOrGenerateChunk($manager, $generator, $this->chunkX + $relativeX, $this->chunkZ + $relativeZ, $c);
		}
		$chunks = $resultChunks;

		$generator->populateChunk($manager, $this->chunkX, $this->chunkZ);
		$chunk = $manager->getChunk($this->chunkX, $this->chunkZ);
		if($chunk === null){
			throw new AssumptionFailedError("We just generated this chunk, so it must exist");
		}
		$chunk->setPopulated();

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		$serialChunks = [];
		foreach($chunks as $relativeChunkHash => $c){
			$serialChunks[$relativeChunkHash] = $c->isTerrainDirty() ? FastChunkSerializer::serializeTerrain($c) : null;
		}
		$this->adjacentChunks = igbinary_serialize($serialChunks) ?? throw new AssumptionFailedError("igbinary_serialize() returned null");
	}

	private static function setOrGenerateChunk(SimpleChunkManager $manager, Generator $generator, int $chunkX, int $chunkZ, ?Chunk $chunk) : Chunk{
		$manager->setChunk($chunkX, $chunkZ, $chunk ?? new Chunk([], BiomeArray::fill(BiomeIds::OCEAN), false));
		if($chunk === null){
			$generator->generateChunk($manager, $chunkX, $chunkZ);
			$chunk = $manager->getChunk($chunkX, $chunkZ);
			if($chunk === null){
				throw new AssumptionFailedError("We just set this chunk, so it must exist");
			}
			$chunk->setTerrainDirtyFlag(Chunk::DIRTY_FLAG_BLOCKS, true);
			$chunk->setTerrainDirtyFlag(Chunk::DIRTY_FLAG_BIOMES, true);
		}
		return $chunk;
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $onCompletion
		 * @phpstan-var OnCompletion $onCompletion
		 */
		$onCompletion = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);

		$chunk = $this->chunk !== null ?
			FastChunkSerializer::deserializeTerrain($this->chunk) :
			throw new AssumptionFailedError("Center chunk should never be null");

		/**
		 * @var string[]|null[] $serialAdjacentChunks
		 * @phpstan-var array<int, string|null> $serialAdjacentChunks
		 */
		$serialAdjacentChunks = igbinary_unserialize($this->adjacentChunks);
		$adjacentChunks = [];
		foreach($serialAdjacentChunks as $relativeChunkHash => $c){
			if($c !== null){
				$adjacentChunks[$relativeChunkHash] = FastChunkSerializer::deserializeTerrain($c);
			}
		}

		$onCompletion($chunk, $adjacentChunks);
	}
}
