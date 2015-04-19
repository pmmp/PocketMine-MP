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

namespace pocketmine;

use pocketmine\event\server\LowMemoryEvent;
use pocketmine\event\Timings;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\utils\Utils;

class MemoryManager{

	/** @var Server */
	private $server;

	private $memoryLimit;
	private $globalMemoryLimit;
	private $checkRate;
	private $checkTicker = 0;
	private $lowMemory = false;

	private $continuousTrigger = true;
	private $continuousTriggerRate;
	private $continuousTriggerCount = 0;
	private $continuousTriggerTicker = 0;

	private $garbageCollectionPeriod;
	private $garbageCollectionTicker = 0;
	private $garbageCollectionTrigger;
	private $garbageCollectionAsync;

	private $chunkLimit;
	private $chunkTrigger;

	private $chunkCache;
	private $cacheTrigger;

	public function __construct(Server $server){
		$this->server = $server;

		$this->init();
	}

	private function init(){
		$this->memoryLimit = ((int) $this->server->getProperty("memory.main-limit", 320)) * 1024 * 1024;
		$this->globalMemoryLimit = ((int) $this->server->getProperty("memory.global-limit", 512)) * 1024 * 1024;
		$this->checkRate = (int) $this->server->getProperty("memory.check-rate", 20);
		$this->continuousTrigger = (bool) $this->server->getProperty("memory.continuous-trigger", true);
		$this->continuousTriggerRate = (int) $this->server->getProperty("memory.continuous-trigger-rate", 30);

		$this->garbageCollectionPeriod = (int) $this->server->getProperty("memory.garbage-collection.period", 12000);
		$this->garbageCollectionTrigger = (bool) $this->server->getProperty("memory.garbage-collection.low-memory-trigger", true);
		$this->garbageCollectionAsync = (bool) $this->server->getProperty("memory.garbage-collection.collect-async-worker", true);

		$this->chunkLimit = (int) $this->server->getProperty("memory.max-chunks.trigger-limit", 12000);
		$this->chunkTrigger = (bool) $this->server->getProperty("memory.max-chunks.low-memory-trigger", true);

		$this->chunkCache = (bool) $this->server->getProperty("memory.world-caches.disable-chunk-cache", true);
		$this->cacheTrigger = (bool) $this->server->getProperty("memory.world-caches.low-memory-trigger", true);

		gc_enable();
	}

	public function isLowMemory(){
		return $this->lowMemory;
	}

	public function canUseChunkCache(){
		return !($this->lowMemory and $this->chunkTrigger);
	}

	public function getViewDistance($distance){
		return $this->lowMemory ? min($this->chunkLimit, $distance) : $distance;
	}

	public function trigger($memory, $limit, $global = false, $triggerCount = 0){
		$this->server->getLogger()->debug("[Memory Manager] ".($global ? "Global " : "") ."Low memory triggered, limit ". round(($limit / 1024) / 1024, 2)."MB, using ". round(($memory / 1024) / 1024, 2)."MB");

		if($this->cacheTrigger){
			foreach($this->server->getLevels() as $level){
				$level->clearCache(true);
			}
		}

		$ev = new LowMemoryEvent($memory, $limit, $global, $triggerCount);
		$this->server->getPluginManager()->callEvent($ev);

		$cycles = 0;
		if($this->garbageCollectionTrigger){
			$cycles = $this->triggerGarbageCollector();
		}

		$this->server->getLogger()->debug("[Memory Manager] Freed " . round(($ev->getMemoryFreed() / 1024) / 1024, 2)."MB, $cycles cycles");
	}

	public function check(){
		Timings::$memoryManagerTimer->startTiming();

		if(($this->memoryLimit > 0 or $this->globalMemoryLimit > 0) and ++$this->checkTicker >= $this->checkRate){
			$this->checkTicker = 0;
			$memory = Utils::getMemoryUsage(true);
			$trigger = false;
			if($this->memoryLimit > 0 and $memory[0] > $this->memoryLimit){
				$trigger = 0;
			}elseif($this->globalMemoryLimit > 0 and $memory[1] > $this->globalMemoryLimit){
				$trigger = 1;
			}

			if($trigger !== false){
				if($this->lowMemory and $this->continuousTrigger){
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

		if($this->garbageCollectionPeriod > 0 and ++$this->garbageCollectionTicker >= $this->garbageCollectionPeriod){
			$this->garbageCollectionTicker = 0;
			$this->triggerGarbageCollector();
		}

		Timings::$memoryManagerTimer->stopTiming();
	}

	public function triggerGarbageCollector(){
		Timings::$garbageCollectorTimer->startTiming();

		if($this->garbageCollectionAsync){
			$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
			for($i = 0; $i < $size; ++$i){
				$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GarbageCollectionTask(), $i);
			}
		}

		$cycles = gc_collect_cycles();

		Timings::$garbageCollectorTimer->stopTiming();

		return $cycles;
	}
}