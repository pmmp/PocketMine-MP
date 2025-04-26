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

namespace pocketmine\world\generator\executor;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\PopulationTask;
use pocketmine\world\World;

/**
 * Very simple chunk generator which runs everything immediately on the main thread.
 * Useful if your generator is very fast and doesn't benefit from async tasks or threading.
 */
final class SyncGeneratorExecutor implements GeneratorExecutor{
	public function __construct(
		private readonly Generator $generator
	){}

	public function requestChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		return $this->orderChunkPopulation($world, $chunkX, $chunkZ, $associatedChunkLoader);
	}

	public function orderChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		$temporaryChunkLoader = new class implements ChunkLoader{};

		//TODO: annoying boilerplate on multiple executors
		//this is mainly just needed to suppress warnings and make sure the chunks stay loaded while they're being worked on
		for($xx = $chunkX - 1; $xx <= $chunkX + 1; ++$xx){
			for($zz = $chunkZ - 1; $zz <= $chunkZ + 1; ++$zz){
				$world->registerChunkLoader($temporaryChunkLoader, $xx, $zz);
			}
		}

		[$centerChunk, $adjacentChunks] = PopulationTask::populateChunks(
			$world->getMinY(),
			$world->getMaxY(),
			$this->generator,
			$chunkX,
			$chunkZ,
			$world->getChunk($chunkX, $chunkZ),
			$world->getAdjacentChunks($chunkX, $chunkZ)
		);

		$world->onChunkPopulated($chunkX, $chunkZ, $centerChunk, $adjacentChunks);

		for($xx = $chunkX - 1; $xx <= $chunkX + 1; ++$xx){
			for($zz = $chunkZ - 1; $zz <= $chunkZ + 1; ++$zz){
				$world->unregisterChunkLoader($temporaryChunkLoader, $xx, $zz);
			}
		}

		/** @phpstan-var PromiseResolver<Chunk> $resolver */
		$resolver = new PromiseResolver();
		$resolver->resolve($centerChunk);
		return $resolver->getPromise();
	}

	public function cancelChunkPopulation(World $world, int $chunkX, int $chunkZ) : void{
		//NOOP
	}

	public function shutdown(World $world) : void{
		//NOOP
	}
}
