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

use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
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
		$world->registerChunkLoader($temporaryChunkLoader, $chunkX, $chunkZ);

		//TODO: the following code is basically identical to PopulationTask
		//we should probably generalize this
		for($xx = $chunkX - 1; $xx <= $chunkX + 1; ++$xx){
			for($zz = $chunkZ - 1; $zz <= $chunkZ + 1; ++$zz){
				$world->registerChunkLoader($temporaryChunkLoader, $xx, $zz);
				$chunk = $world->loadChunk($xx, $zz);
				if($chunk === null){
					$this->generator->generateChunk($world, $xx, $zz);
				}
			}
		}

		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunk === null){
			throw new AssumptionFailedError("We just loaded and/or generated this chunk, it should not be null");
		}

		if(!$chunk->isPopulated()){
			$this->generator->populateChunk($world, $chunkX, $chunkZ);
			$chunk->setPopulated();

			//TODO: this is basically identical to the AsyncChunkGenerator generateChunkCallback side
			//we probably ought to generalize this
			if(ChunkPopulateEvent::hasHandlers()){
				(new ChunkPopulateEvent($world, $chunkX, $chunkZ, $chunk))->call();
			}

			foreach($world->getChunkListeners($chunkX, $chunkZ) as $listener){
				$listener->onChunkPopulated($chunkX, $chunkZ, $chunk);
			}
		}

		for($xx = $chunkX - 1; $xx <= $chunkX + 1; ++$xx){
			for($zz = $chunkZ - 1; $zz <= $chunkZ + 1; ++$zz){
				$world->unregisterChunkLoader($temporaryChunkLoader, $xx, $zz);
			}
		}

		/** @phpstan-var PromiseResolver<Chunk> $resolver */
		$resolver = new PromiseResolver();
		$resolver->resolve($chunk);
		return $resolver->getPromise();
	}

	public function cancelChunkPopulation(World $world, int $chunkX, int $chunkZ) : void{
		//NOOP
	}

	public function shutdown(World $world) : void{
		//NOOP
	}
}
