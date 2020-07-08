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

namespace pocketmine\network\mcpe;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkListenerNoOpTrait;
use pocketmine\world\ChunkPos;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function spl_object_id;
use function strlen;

/**
 * This class is used by the current MCPE protocol system to store cached chunk packets for fast resending.
 *
 * TODO: make MemoryManager aware of this so the cache can be destroyed when memory is low
 * TODO: this needs a hook for world unloading
 */
class ChunkCache implements ChunkListener{
	/** @var self[][] */
	private static $instances = [];

	/**
	 * Fetches the ChunkCache instance for the given world. This lazily creates cache systems as needed.
	 *
	 * @return ChunkCache
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

	/** @var World */
	private $world;
	/** @var Compressor */
	private $compressor;

	/** @var CompressBatchPromise[] */
	private $caches = [];

	/** @var int */
	private $hits = 0;
	/** @var int */
	private $misses = 0;

	private function __construct(World $world, Compressor $compressor){
		$this->world = $world;
		$this->compressor = $compressor;
	}

	/**
	 * Requests asynchronous preparation of the chunk at the given coordinates.
	 *
	 * @return CompressBatchPromise a promise of resolution which will contain a compressed chunk packet.
	 */
	public function request(ChunkPos $chunkPos) : CompressBatchPromise{
		$this->world->registerChunkListener($this, $chunkPos->getX(), $chunkPos->getZ());

		if(isset($this->caches[$chunkPos->hash])){
			++$this->hits;
			return $this->caches[$chunkPos->hash];
		}

		++$this->misses;

		$this->world->timings->syncChunkSendPrepareTimer->startTiming();
		try{
			$this->caches[$chunkPos->hash] = new CompressBatchPromise();

			$this->world->getServer()->getAsyncPool()->submitTask(
				new ChunkRequestTask(
					$chunkPos,
					$this->world->getChunk($chunkPos->getX(), $chunkPos->getZ()),
					$this->caches[$chunkPos->hash],
					$this->compressor,
					function() use ($chunkPos) : void{
						$this->world->getLogger()->error("Failed preparing chunk $chunkPos, retrying");

						$this->restartPendingRequest($chunkPos);
					}
				)
			);

			return $this->caches[$chunkPos->hash];
		}finally{
			$this->world->timings->syncChunkSendPrepareTimer->stopTiming();
		}
	}

	private function destroy(ChunkPos $chunkPos) : bool{
		$existing = $this->caches[$chunkPos->hash] ?? null;
		unset($this->caches[$chunkPos->hash]);

		return $existing !== null;
	}

	/**
	 * Restarts an async request for an unresolved chunk.
	 *
	 * @throws \InvalidArgumentException
	 */
	private function restartPendingRequest(ChunkPos $chunkPos) : void{
		$existing = $this->caches[$chunkPos->hash] ?? null;
		if($existing === null or $existing->hasResult()){
			throw new \InvalidArgumentException("Restart can only be applied to unresolved promises");
		}
		$existing->cancel();
		unset($this->caches[$chunkPos->hash]);

		$this->request($chunkPos)->onResolve(...$existing->getResolveCallbacks());
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function destroyOrRestart(ChunkPos $chunkPos) : void{
		$cache = $this->caches[$chunkPos->hash] ?? null;
		if($cache !== null){
			if(!$cache->hasResult()){
				//some requesters are waiting for this chunk, so their request needs to be fulfilled
				$this->restartPendingRequest($chunkPos);
			}else{
				//dump the cache, it'll be regenerated the next time it's requested
				$this->destroy($chunkPos);
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
	public function onChunkChanged(Chunk $chunk) : void{
		//FIXME: this gets fired for stuff that doesn't change terrain related things (like lighting updates)
		$this->destroyOrRestart($chunk->getPos());
	}

	/**
	 * @see ChunkListener::onBlockChanged()
	 */
	public function onBlockChanged(Vector3 $block) : void{
		//FIXME: requesters will still receive this chunk after it's been dropped, but we can't mark this for a simple
		//sync here because it can spam the worker pool
		$this->destroy(ChunkPos::fromVec3($block));
	}

	/**
	 * @see ChunkListener::onChunkUnloaded()
	 */
	public function onChunkUnloaded(Chunk $chunk) : void{
		$pos = $chunk->getPos();
		$this->destroy($pos);
		$this->world->unregisterChunkListener($this, $pos->getX(), $pos->getZ());
	}

	/**
	 * Returns the number of bytes occupied by the cache data in this cache. This does not include the size of any
	 * promises referenced by the cache.
	 */
	public function calculateCacheSize() : int{
		$result = 0;
		foreach($this->caches as $cache){
			if($cache->hasResult()){
				$result += strlen($cache->getResult());
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
