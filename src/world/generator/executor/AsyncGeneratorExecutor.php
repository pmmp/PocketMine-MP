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

use pmmp\thread\ThreadSafeArray;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncPool;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\ChunkLoader;
use pocketmine\world\ChunkLockId;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\PopulationTask;
use pocketmine\world\World;
use function array_key_exists;
use function assert;
use function count;

/**
 * @phpstan-import-type ChunkPosHash from World
 */
final class AsyncGeneratorExecutor implements GeneratorExecutor{
	/**
	 * @var bool[] chunkHash => isValid
	 * @phpstan-var array<ChunkPosHash, bool>
	 */
	private array $activeTasks = [];

	/**
	 * @var PromiseResolver[] chunkHash => promise
	 * @phpstan-var array<ChunkPosHash, PromiseResolver<Chunk>>
	 */
	private array $requestMap = [];

	/**
	 * @var \SplQueue (queue of chunkHashes)
	 * @phpstan-var \SplQueue<ChunkPosHash>
	 */
	private \SplQueue $requestQueue;
	/**
	 * @var true[] chunkHash => dummy
	 * @phpstan-var array<ChunkPosHash, true>
	 */
	private array $requestQueueIndex = [];

	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	private array $registeredWorkers = [];

	/** @phpstan-var \Closure(int) : void */
	private \Closure $workerStartHook;

	/**
	 * @phpstan-param \Closure() : Generator $generatorFactory Must be a thread-safe closure
	 */
	public function __construct(
		private readonly AsyncPool $workerPool,
		private readonly \Logger $logger,
		private readonly \Closure $generatorFactory,
		private readonly int $maxConcurrentTasks = 2
	){
		//TODO: we really need a better way to check if a closure is thread-safe :(
		$temp = new ThreadSafeArray();
		$temp["dummy"] = $this->generatorFactory;

		$this->requestQueue = new \SplQueue();
		//TODO: don't love the circular reference here, but we need to make sure this gets cleaned up on shutdown
		$this->workerStartHook = function(int $workerId) : void{
			if(array_key_exists($workerId, $this->registeredWorkers)){
				$this->logger->debug("Worker $workerId with previously registered generator restarted, flagging as unregistered");
				unset($this->registeredWorkers[$workerId]);
			}
		};
		$this->workerPool->addWorkerStartHook($this->workerStartHook);
	}

	private function registerWorker(World $world, int $worker) : void{
		$world->getLogger()->debug("Registering generator on worker $worker");
		$this->workerPool->submitTaskToWorker(new AsyncGeneratorRegisterTask(
			$world,
			$this->generatorFactory,
		), $worker);
		$this->registeredWorkers[$worker] = true;
	}

	private function addChunkHashToRequestQueue(int $chunkHash) : void{
		if(!isset($this->requestQueueIndex[$chunkHash])){
			$this->requestQueue->enqueue($chunkHash);
			$this->requestQueueIndex[$chunkHash] = true;
		}
	}

	/**
	 * @phpstan-return Promise<Chunk>
	 */
	private function enqueueRequest(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$this->addChunkHashToRequestQueue($chunkHash);
		/** @phpstan-var PromiseResolver<Chunk> $resolver */
		$resolver = $this->requestMap[$chunkHash] = new PromiseResolver();
		if($associatedChunkLoader === null){
			$temporaryLoader = new class implements ChunkLoader{};
			$world->registerChunkLoader($temporaryLoader, $chunkX, $chunkZ);
			$resolver->getPromise()->onCompletion(
				fn() => $world->unregisterChunkLoader($temporaryLoader, $chunkX, $chunkZ),
				static function() : void{}
			);
		}
		return $resolver->getPromise();
	}

	/**
	 * Checks if a chunk needs to be populated, and whether it's ready to do so.
	 * @return bool[]|PromiseResolver[]|null[]
	 * @phpstan-return array{?PromiseResolver<Chunk>, bool}
	 */
	private function checkPreconditions(World $world, int $chunkX, int $chunkZ) : array{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$resolver = $this->requestMap[$chunkHash] ?? null;
		if($resolver !== null && isset($this->activeTasks[$chunkHash])){
			//generation is already running
			return [$resolver, false];
		}

		$temporaryChunkLoader = new class implements ChunkLoader{};
		$world->registerChunkLoader($temporaryChunkLoader, $chunkX, $chunkZ);
		$chunk = $world->loadChunk($chunkX, $chunkZ);
		$world->unregisterChunkLoader($temporaryChunkLoader, $chunkX, $chunkZ);
		if($chunk !== null && $chunk->isPopulated()){
			//chunk is already populated; return a pre-resolved promise that will directly fire callbacks assigned
			$resolver ??= new PromiseResolver();
			unset($this->requestMap[$chunkHash]);
			$resolver->resolve($chunk);
			return [$resolver, false];
		}
		return [$resolver, true];
	}

	private function drainRequestQueue(World $world) : void{
		$failed = [];
		while(count($this->activeTasks) < $this->maxConcurrentTasks && !$this->requestQueue->isEmpty()){
			$nextChunkHash = $this->requestQueue->dequeue();
			unset($this->requestQueueIndex[$nextChunkHash]);
			World::getXZ($nextChunkHash, $nextChunkX, $nextChunkZ);
			if(isset($this->requestMap[$nextChunkHash])){
				assert(!($this->activeTasks[$nextChunkHash] ?? false), "Population for chunk $nextChunkX $nextChunkZ already running");
				if(
					!$this->orderChunkPopulation($world, $nextChunkX, $nextChunkZ, null)->isResolved() &&
					!isset($this->activeTasks[$nextChunkHash])
				){
					$failed[] = $nextChunkHash;
				}
			}
		}

		//these requests failed even though they weren't rate limited; we can't directly re-add them to the back of the
		//queue because it would result in an infinite loop
		foreach($failed as $hash){
			$this->addChunkHashToRequestQueue($hash);
		}
	}

	/**
	 * @param Chunk[] $adjacentChunks chunkHash => chunk
	 * @phpstan-param array<int, Chunk> $adjacentChunks
	 */
	private function completeTask(World $world, ChunkLockId $chunkLockId, int $x, int $z, Chunk $chunk, array $adjacentChunks, ChunkLoader $temporaryChunkLoader) : void{
		$timings = $world->timings->chunkPopulationCompletion;
		$timings->startTiming();

		$dirtyChunks = 0;
		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				$world->unregisterChunkLoader($temporaryChunkLoader, $x + $xx, $z + $zz);
				if(!$world->unlockChunk($x + $xx, $z + $zz, $chunkLockId)){
					$dirtyChunks++;
				}
			}
		}

		$index = World::chunkHash($x, $z);
		if(!isset($this->activeTasks[$index])){
			throw new AssumptionFailedError("This should always be set, regardless of whether the task was orphaned or not");
		}
		if(!$this->activeTasks[$index]){
			$world->getLogger()->debug("Discarding orphaned population result for chunk x=$x,z=$z");
			unset($this->activeTasks[$index]);
		}else{
			if($dirtyChunks === 0){
				foreach($adjacentChunks as $relativeChunkHash => $adjacentChunk){
					World::getXZ($relativeChunkHash, $relativeX, $relativeZ);
					if($relativeX < -1 || $relativeX > 1 || $relativeZ < -1 || $relativeZ > 1){
						throw new AssumptionFailedError("Adjacent chunks should be in range -1 ... +1 coordinates");
					}
					$world->setChunk($x + $relativeX, $z + $relativeZ, $adjacentChunk);
				}

				if(ChunkPopulateEvent::hasHandlers()){
					(new ChunkPopulateEvent($world, $x, $z, $chunk))->call();
				}

				foreach($world->getChunkListeners($x, $z) as $listener){
					$listener->onChunkPopulated($x, $z, $chunk);
				}
			}else{
				$world->getLogger()->debug("Discarding population result for chunk x=$x,z=$z - terrain was modified on the main thread before async population completed");
			}

			//This needs to be in this specific spot because user code might call back to orderChunkPopulation().
			//If it does, and finds the promise, and doesn't find an active task associated with it, it will schedule
			//another PopulationTask. We don't want that because we're here processing the results.
			//We can't remove the promise from the array before setting the chunks in the world because that would lead
			//to the same problem. Therefore, it's necessary that this code be split into two if/else, with this in the
			//middle.
			unset($this->activeTasks[$index]);

			if($dirtyChunks === 0){
				$promise = $this->requestMap[$index] ?? null;
				if($promise !== null){
					unset($this->requestMap[$index]);
					$promise->resolve($chunk);
				}else{
					//Handlers of ChunkPopulateEvent, ChunkLoadEvent, or just ChunkListeners can cause this
					$world->getLogger()->debug("Unable to resolve population promise for chunk x=$x,z=$z - populated chunk was forcibly unloaded while setting modified chunks");
				}
			}else{
				//request failed, stick it back on the queue
				//we didn't resolve the promise or touch it in any way, so any fake chunk loaders are still valid and
				//don't need to be added a second time.
				$this->addChunkHashToRequestQueue($index);
			}

			$this->drainRequestQueue($world);
		}
		$timings->stopTiming();
	}

	/**
	 * @phpstan-param PromiseResolver<Chunk>|null $resolver
	 * @phpstan-return Promise<Chunk>
	 */
	private function beginTask(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader, ?PromiseResolver $resolver) : Promise{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);

		$timings = $world->timings->chunkPopulationOrder;
		$timings->startTiming();

		try{
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					if($world->isChunkLocked($chunkX + $xx, $chunkZ + $zz)){
						//chunk is already in use by another generation request; queue the request for later
						return $resolver?->getPromise() ?? $this->enqueueRequest($world, $chunkX, $chunkZ, $associatedChunkLoader);
					}
				}
			}

			$this->activeTasks[$chunkHash] = true;
			if($resolver === null){
				$resolver = new PromiseResolver();
				$this->requestMap[$chunkHash] = $resolver;
			}

			$chunkPopulationLockId = new ChunkLockId();

			$temporaryChunkLoader = new class implements ChunkLoader{
			};
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					$world->lockChunk($chunkX + $xx, $chunkZ + $zz, $chunkPopulationLockId);
					$world->registerChunkLoader($temporaryChunkLoader, $chunkX + $xx, $chunkZ + $zz);
				}
			}

			$centerChunk = $world->loadChunk($chunkX, $chunkZ);
			$adjacentChunks = $world->getAdjacentChunks($chunkX, $chunkZ);
			$task = new PopulationTask(
				$world->getId(),
				$chunkX,
				$chunkZ,
				$centerChunk,
				$adjacentChunks,
				function(Chunk $centerChunk, array $adjacentChunks) use ($world, $chunkPopulationLockId, $chunkX, $chunkZ, $temporaryChunkLoader) : void{
					if(!$world->isLoaded()){
						return;
					}

					$this->completeTask($world, $chunkPopulationLockId, $chunkX, $chunkZ, $centerChunk, $adjacentChunks, $temporaryChunkLoader);
				}
			);
			$workerId = $this->workerPool->selectWorker();
			if(!isset($this->workerPool->getRunningWorkers()[$workerId]) && isset($this->registeredWorkers[$workerId])){
				$world->getLogger()->debug("Selected worker $workerId previously had generator registered, but is now offline");
				unset($this->registeredWorkers[$workerId]);
			}
			if(!isset($this->registeredWorkers[$workerId])){
				$this->registerWorker($world, $workerId);
			}
			$this->workerPool->submitTaskToWorker($task, $workerId);

			return $resolver->getPromise();
		}finally{
			$timings->stopTiming();
		}
	}

	/**
	 * Attempts to initiate asynchronous generation/population of the target chunk, if it's currently reasonable to do
	 * so (and if it isn't already generated/populated).
	 * If the generator is busy, the request will be put into a queue and delayed until a better time.
	 *
	 * A ChunkLoader can be associated with the generation request to ensure that the generation request is cancelled if
	 * no loaders are attached to the target chunk. If no loader is provided, one will be assigned (and automatically
	 * removed when the generation request completes).
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function requestChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		[$resolver, $proceedWithPopulation] = $this->checkPreconditions($world, $chunkX, $chunkZ);
		if(!$proceedWithPopulation){
			return $resolver?->getPromise() ?? $this->enqueueRequest($world, $chunkX, $chunkZ, $associatedChunkLoader);
		}

		if(count($this->activeTasks) >= $this->maxConcurrentTasks){
			//too many chunks are already generating; delay resolution of the request until later
			return $resolver?->getPromise() ?? $this->enqueueRequest($world, $chunkX, $chunkZ, $associatedChunkLoader);
		}
		return $this->beginTask($world, $chunkX, $chunkZ, $associatedChunkLoader, $resolver);
	}

	/**
	 * Initiates asynchronous generation/population of the target chunk, if it's not already generated/populated.
	 * If generation has already been requested for the target chunk, the promise for the already active request will be
	 * returned directly.
	 *
	 * If the chunk is currently locked (for example due to another chunk using it for async generation), the request
	 * will be queued and executed at the earliest opportunity.
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function orderChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		[$resolver, $proceedWithPopulation] = $this->checkPreconditions($world, $chunkX, $chunkZ);
		if(!$proceedWithPopulation){
			return $resolver?->getPromise() ?? $this->enqueueRequest($world, $chunkX, $chunkZ, $associatedChunkLoader);
		}

		return $this->beginTask($world, $chunkX, $chunkZ, $associatedChunkLoader, $resolver);
	}

	public function cancelChunkPopulation(World $world, int $chunkX, int $chunkZ) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		if(array_key_exists($chunkHash, $this->requestMap)){
			$this->logger->debug("Rejecting population promise for chunk $chunkX $chunkZ");
			$this->requestMap[$chunkHash]->reject();
			unset($this->requestMap[$chunkHash]);
			if(isset($this->activeTasks[$chunkHash])){
				$this->logger->debug("Marking population task for chunk $chunkX $chunkZ as orphaned");
				$this->activeTasks[$chunkHash] = false;
			}
		}
	}

	public function shutdown(World $world) : void{
		$world->getLogger()->debug("Cancelling unfulfilled generation requests");

		foreach($this->requestMap as $chunkHash => $promise){
			$promise->reject();
			unset($this->requestMap[$chunkHash]);
		}
		if(count($this->requestMap) !== 0){
			//TODO: this might actually get hit because generation rejection callbacks might try to schedule new
			//requests, and we can't prevent that right now because there's no way to detect "unloading" state
			throw new AssumptionFailedError("New generation requests scheduled during unload");
		}

		foreach($this->registeredWorkers as $worker => $true){
			$this->workerPool->submitTaskToWorker(new AsyncGeneratorUnregisterTask($world), $worker);
		}

		$this->workerPool->removeWorkerStartHook($this->workerStartHook);
	}
}
