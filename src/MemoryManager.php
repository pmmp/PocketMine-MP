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

namespace pocketmine;

use pocketmine\event\server\LowMemoryEvent;
use pocketmine\network\mcpe\cache\ChunkCache;
use pocketmine\scheduler\DumpWorkerMemoryTask;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\timings\Timings;
use pocketmine\utils\Process;
use pocketmine\YmlServerProperties as Yml;
use function gc_collect_cycles;
use function gc_mem_caches;
use function ini_set;
use function intdiv;
use function mb_strtoupper;
use function min;
use function preg_match;
use function round;
use function sprintf;

class MemoryManager{
	private const DEFAULT_CHECK_RATE = Server::TARGET_TICKS_PER_SECOND;
	private const DEFAULT_CONTINUOUS_TRIGGER_RATE = Server::TARGET_TICKS_PER_SECOND * 2;
	private const DEFAULT_TICKS_PER_GC = 30 * 60 * Server::TARGET_TICKS_PER_SECOND;

	private GarbageCollectorManager $cycleGcManager;

	private int $memoryLimit;
	private int $globalMemoryLimit;
	private int $checkRate;
	private int $checkTicker = 0;
	private bool $lowMemory = false;

	private bool $continuousTrigger = true;
	private int $continuousTriggerRate;
	private int $continuousTriggerCount = 0;
	private int $continuousTriggerTicker = 0;

	private int $garbageCollectionPeriod;
	private int $garbageCollectionTicker = 0;

	private int $lowMemChunkRadiusOverride;

	private bool $dumpWorkers = true;

	private \Logger $logger;

	public function __construct(
		private Server $server
	){
		$this->logger = new \PrefixedLogger($server->getLogger(), "Memory Manager");
		$this->cycleGcManager = new GarbageCollectorManager($this->logger, Timings::$memoryManager);

		$this->init($server->getConfigGroup());
	}

	private function init(ServerConfigGroup $config) : void{
		$this->memoryLimit = $config->getPropertyInt(Yml::MEMORY_MAIN_LIMIT, 0) * 1024 * 1024;

		$defaultMemory = 1024;

		if(preg_match("/([0-9]+)([KMGkmg])/", $config->getConfigString("memory-limit", ""), $matches) > 0){
			$m = (int) $matches[1];
			if($m <= 0){
				$defaultMemory = 0;
			}else{
				$defaultMemory = match(mb_strtoupper($matches[2])){
					"K" => intdiv($m, 1024),
					"M" => $m,
					"G" => $m * 1024,
					default => $m,
				};
			}
		}

		$hardLimit = $config->getPropertyInt(Yml::MEMORY_MAIN_HARD_LIMIT, $defaultMemory);

		if($hardLimit <= 0){
			ini_set("memory_limit", '-1');
		}else{
			ini_set("memory_limit", $hardLimit . "M");
		}

		$this->globalMemoryLimit = $config->getPropertyInt(Yml::MEMORY_GLOBAL_LIMIT, 0) * 1024 * 1024;
		$this->checkRate = $config->getPropertyInt(Yml::MEMORY_CHECK_RATE, self::DEFAULT_CHECK_RATE);
		$this->continuousTrigger = $config->getPropertyBool(Yml::MEMORY_CONTINUOUS_TRIGGER, true);
		$this->continuousTriggerRate = $config->getPropertyInt(Yml::MEMORY_CONTINUOUS_TRIGGER_RATE, self::DEFAULT_CONTINUOUS_TRIGGER_RATE);

		$this->garbageCollectionPeriod = $config->getPropertyInt(Yml::MEMORY_GARBAGE_COLLECTION_PERIOD, self::DEFAULT_TICKS_PER_GC);

		$this->lowMemChunkRadiusOverride = $config->getPropertyInt(Yml::MEMORY_MAX_CHUNKS_CHUNK_RADIUS, 4);

		$this->dumpWorkers = $config->getPropertyBool(Yml::MEMORY_MEMORY_DUMP_DUMP_ASYNC_WORKER, true);
	}

	public function isLowMemory() : bool{
		return $this->lowMemory;
	}

	public function getGlobalMemoryLimit() : int{
		return $this->globalMemoryLimit;
	}

	/**
	 * Returns the allowed chunk radius based on the current memory usage.
	 */
	public function getViewDistance(int $distance) : int{
		return ($this->lowMemory && $this->lowMemChunkRadiusOverride > 0) ? min($this->lowMemChunkRadiusOverride, $distance) : $distance;
	}

	/**
	 * Triggers garbage collection and cache cleanup to try and free memory.
	 */
	public function trigger(int $memory, int $limit, bool $global = false, int $triggerCount = 0) : void{
		$this->logger->debug(sprintf("%sLow memory triggered, limit %gMB, using %gMB",
			$global ? "Global " : "", round(($limit / 1024) / 1024, 2), round(($memory / 1024) / 1024, 2)));
		foreach($this->server->getWorldManager()->getWorlds() as $world){
			$world->clearCache(true);
		}
		ChunkCache::pruneCaches();

		foreach($this->server->getWorldManager()->getWorlds() as $world){
			$world->doChunkGarbageCollection();
		}

		$ev = new LowMemoryEvent($memory, $limit, $global, $triggerCount);
		$ev->call();

		$cycles = $this->triggerGarbageCollector();

		$this->logger->debug(sprintf("Freed %gMB, $cycles cycles", round(($ev->getMemoryFreed() / 1024) / 1024, 2)));
	}

	/**
	 * Called every tick to update the memory manager state.
	 */
	public function check() : void{
		Timings::$memoryManager->startTiming();

		if(($this->memoryLimit > 0 || $this->globalMemoryLimit > 0) && ++$this->checkTicker >= $this->checkRate){
			$this->checkTicker = 0;
			$memory = Process::getAdvancedMemoryUsage();
			$trigger = false;
			if($this->memoryLimit > 0 && $memory[0] > $this->memoryLimit){
				$trigger = 0;
			}elseif($this->globalMemoryLimit > 0 && $memory[1] > $this->globalMemoryLimit){
				$trigger = 1;
			}

			if($trigger !== false){
				if($this->lowMemory && $this->continuousTrigger){
					if(++$this->continuousTriggerTicker >= $this->continuousTriggerRate){
						$this->continuousTriggerTicker = 0;
						$this->trigger($memory[$trigger], $this->memoryLimit, $trigger > 0, ++$this->continuousTriggerCount);
					}
				}else{
					$this->lowMemory = true;
					$this->continuousTriggerCount = 0;
					$this->trigger($memory[$trigger], $this->memoryLimit, $trigger > 0);
				}
			}else{
				$this->lowMemory = false;
			}
		}

		if($this->garbageCollectionPeriod > 0 && ++$this->garbageCollectionTicker >= $this->garbageCollectionPeriod){
			$this->garbageCollectionTicker = 0;
			$this->triggerGarbageCollector();
		}else{
			$this->cycleGcManager->maybeCollectCycles();
		}

		Timings::$memoryManager->stopTiming();
	}

	public function triggerGarbageCollector() : int{
		Timings::$garbageCollector->startTiming();

		$pool = $this->server->getAsyncPool();
		if(($w = $pool->shutdownUnusedWorkers()) > 0){
			$this->logger->debug("Shut down $w idle async pool workers");
		}
		foreach($pool->getRunningWorkers() as $i){
			$pool->submitTaskToWorker(new GarbageCollectionTask(), $i);
		}

		$cycles = gc_collect_cycles();
		gc_mem_caches();

		Timings::$garbageCollector->stopTiming();

		return $cycles;
	}

	/**
	 * Dumps the server memory into the specified output folder.
	 */
	public function dumpServerMemory(string $outputFolder, int $maxNesting, int $maxStringSize) : void{
		$logger = new \PrefixedLogger($this->server->getLogger(), "Memory Dump");
		$logger->notice("After the memory dump is done, the server might crash");
		MemoryDump::dumpMemory($this->server, $outputFolder, $maxNesting, $maxStringSize, $logger);

		if($this->dumpWorkers){
			$pool = $this->server->getAsyncPool();
			foreach($pool->getRunningWorkers() as $i){
				$pool->submitTaskToWorker(new DumpWorkerMemoryTask($outputFolder, $maxNesting, $maxStringSize), $i);
			}
		}
	}
}
