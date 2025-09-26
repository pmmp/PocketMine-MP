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

namespace pocketmine\network\mcpe\cache;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\ChunkRequestTask;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkListenerNoOpTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function is_string;
use function spl_object_id;
use function strlen;

/**
 * This class is used by the current MCPE protocol system to store cached chunk packets for fast resending.
 */
class ChunkCache implements ChunkListener{
	/** @var self[][] */
	private static array $instances = [];

	/**
	 * Fetches the ChunkCache instance for the given world. This lazily creates cache systems as needed.
	 */
	public static function getInstance(World $world, Compressor $compressor) : self{
		$worldId = spl_object_id($world);
		$compressorId = spl_object_id($compressor);
		if(!isset(self::$instances[$worldId])){
			self::$instances[$worldId] = [];
			$world->addOnUnloadCallback(static function() use ($worldId) : void{
				foreach(self::$instances[$worldId] as $cache){
					$cache->caches = [];
				}
				unset(self::$instances[$worldId]);
				\GlobalLogger::get()->debug("Destroyed chunk packet caches for world#$worldId");
			});
		}
		if(!isset(self::$instances[$worldId][$compressorId])){
			\GlobalLogger::get()->debug("Created new chunk packet cache (world#$worldId, compressor#$compressorId)");
			self::$instances[$worldId][$compressorId] = new self($world, $compressor);
		}
		return self::$instances[$worldId][$compressorId];
	}

	public static function pruneCaches() : void{
		foreach(self::$instances as $compressorMap){
			foreach($compressorMap as $chunkCache){
				foreach($chunkCache->caches as $chunkHash => $promise){
					if(is_string($promise)){
						//Do not clear promises that are not yet fulfilled; they will have requesters waiting on them
						unset($chunkCache->caches[$chunkHash]);
					}
				}
			}
		}
	}

	/**
	 * @var CompressBatchPromise[]|string[]
	 * @phpstan-var array<int, CompressBatchPromise|string>
	 */
	private array $caches = [];

	private int $hits = 0;
	private int $misses = 0;

	/**
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	private function __construct(
		private World $world,
		private Compressor $compressor,
		private int $dimensionId = DimensionIds::OVERWORLD
	){}

	private function prepareChunkAsync(int $chunkX, int $chunkZ, int $chunkHash) : CompressBatchPromise{
		$this->world->registerChunkListener($this, $chunkX, $chunkZ);
		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		if($chunk === null){
			throw new \InvalidArgumentException("Cannot request an unloaded chunk");
		}
		++$this->misses;

		$this->world->timings->syncChunkSendPrepare->startTiming();
		try{
			$promise = new CompressBatchPromise();

			$this->world->getServer()->getAsyncPool()->submitTask(
				new ChunkRequestTask(
					$chunkX,
					$chunkZ,
					$this->dimensionId,
					$chunk,
					$promise,
					$this->compressor
				)
			);
			$this->caches[$chunkHash] = $promise;
			$promise->onResolve(function(CompressBatchPromise $promise) use ($chunkHash) : void{
				//the promise may have been discarded or replaced if the chunk was unloaded or modified in the meantime
				if(($this->caches[$chunkHash] ?? null) === $promise){
					$this->caches[$chunkHash] = $promise->getResult();
				}
			});

			return $promise;
		}finally{
			$this->world->timings->syncChunkSendPrepare->stopTiming();
		}
	}

	/**
	 * Requests asynchronous preparation of the chunk at the given coordinates.
	 *
	 * @return CompressBatchPromise|string Compressed chunk packet, or a promise for one to be resolved asynchronously.
	 */
	public function request(int $chunkX, int $chunkZ) : CompressBatchPromise|string{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->caches[$chunkHash])){
			++$this->hits;
			return $this->caches[$chunkHash];
		}

		return $this->prepareChunkAsync($chunkX, $chunkZ, $chunkHash);
	}

	private function destroy(int $chunkX, int $chunkZ) : bool{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$existing = $this->caches[$chunkHash] ?? null;
		unset($this->caches[$chunkHash]);

		return $existing !== null;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function destroyOrRestart(int $chunkX, int $chunkZ) : void{
		$chunkPosHash = World::chunkHash($chunkX, $chunkZ);
		$cache = $this->caches[$chunkPosHash] ?? null;
		if($cache !== null){
			if(!is_string($cache)){
				//some requesters are waiting for this chunk, so their request needs to be fulfilled
				$cache->cancel();
				unset($this->caches[$chunkPosHash]);

				$this->prepareChunkAsync($chunkX, $chunkZ, $chunkPosHash)->onResolve(...$cache->getResolveCallbacks());
			}else{
				//dump the cache, it'll be regenerated the next time it's requested
				$this->destroy($chunkX, $chunkZ);
			}
		}
	}

	use ChunkListenerNoOpTrait {
		//force overriding of these
		onChunkChanged as private;
		onBlockChanged as private;
		onChunkUnloaded as private;
	}

	/**
	 * @see ChunkListener::onChunkChanged()
	 */
	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$this->destroyOrRestart($chunkX, $chunkZ);
	}

	/**
	 * @see ChunkListener::onBlockChanged()
	 */
	public function onBlockChanged(Vector3 $block) : void{
		//FIXME: requesters will still receive this chunk after it's been dropped, but we can't mark this for a simple
		//sync here because it can spam the worker pool
		$this->destroy($block->getFloorX() >> Chunk::COORD_BIT_SIZE, $block->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	/**
	 * @see ChunkListener::onChunkUnloaded()
	 */
	public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$this->destroy($chunkX, $chunkZ);
		$this->world->unregisterChunkListener($this, $chunkX, $chunkZ);
	}

	/**
	 * Returns the number of bytes occupied by the cache data in this cache. This does not include the size of any
	 * promises referenced by the cache.
	 */
	public function calculateCacheSize() : int{
		$result = 0;
		foreach($this->caches as $cache){
			if(is_string($cache)){
				$result += strlen($cache);
			}
		}
		return $result;
	}

	/**
	 * Returns the percentage of requests to the cache which resulted in a cache hit.
	 */
	public function getHitPercentage() : float{
		$total = $this->hits + $this->misses;
		return $total > 0 ? $this->hits / $total : 0.0;
	}
}
