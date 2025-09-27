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

use pocketmine\scheduler\AsyncPool;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\PopulationTask;
use function array_key_exists;

final class AsyncGeneratorExecutor implements GeneratorExecutor{
	private static int $nextAsyncContextId = 1;

	private readonly \Logger $logger;

	/** @phpstan-var \Closure(int) : void */
	private readonly \Closure $workerStartHook;

	private readonly int $asyncContextId;

	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	private array $generatorRegisteredWorkers = [];

	public function __construct(
		\Logger $logger,
		private readonly AsyncPool $workerPool,
		private readonly GeneratorExecutorSetupParameters $setupParameters,
		?int $asyncContextId = null
	){
		$this->logger = new \PrefixedLogger($logger, "AsyncGeneratorExecutor");

		//TODO: we only allow setting this for PM5 because of PopulationTask uses in plugins
		$this->asyncContextId = $asyncContextId ?? self::$nextAsyncContextId++;

		$this->workerStartHook = function(int $workerId) : void{
			if(array_key_exists($workerId, $this->generatorRegisteredWorkers)){
				$this->logger->debug("Worker $workerId with previously registered generator restarted, flagging as unregistered");
				unset($this->generatorRegisteredWorkers[$workerId]);
			}
		};
		$this->workerPool->addWorkerStartHook($this->workerStartHook);
	}

	private function registerGeneratorToWorker(int $worker) : void{
		$this->logger->debug("Registering generator on worker $worker");
		$this->workerPool->submitTaskToWorker(new AsyncGeneratorRegisterTask($this->setupParameters, $this->asyncContextId), $worker);
		$this->generatorRegisteredWorkers[$worker] = true;
	}

	private function unregisterGenerator() : void{
		foreach($this->workerPool->getRunningWorkers() as $i){
			if(isset($this->generatorRegisteredWorkers[$i])){
				$this->workerPool->submitTaskToWorker(new AsyncGeneratorUnregisterTask($this->asyncContextId), $i);
			}
		}
		$this->generatorRegisteredWorkers = [];
	}

	public function populate(int $chunkX, int $chunkZ, ?Chunk $centerChunk, array $adjacentChunks, \Closure $onCompletion) : void{
		$task = new PopulationTask(
			$this->asyncContextId,
			$chunkX,
			$chunkZ,
			$centerChunk,
			$adjacentChunks,
			$onCompletion
		);
		$workerId = $this->workerPool->selectWorker();
		if(!isset($this->workerPool->getRunningWorkers()[$workerId]) && isset($this->generatorRegisteredWorkers[$workerId])){
			$this->logger->debug("Selected worker $workerId previously had generator registered, but is now offline");
			unset($this->generatorRegisteredWorkers[$workerId]);
		}
		if(!isset($this->generatorRegisteredWorkers[$workerId])){
			$this->registerGeneratorToWorker($workerId);
		}
		$this->workerPool->submitTaskToWorker($task, $workerId);
	}

	public function shutdown() : void{
		$this->unregisterGenerator();
		$this->workerPool->removeWorkerStartHook($this->workerStartHook);
	}
}
