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
	private $chunkCollect;
	private $chunkTrigger;

	private $chunkCache;
	private $cacheTrigger;

	/** @var \WeakRef[] */
	private $leakWatch = [];

	private $leakInfo = [];

	private $leakSeed = 0;

	public function __construct(Server $server){
		$this->server = $server;

		$this->init();
	}

	private function init(){
		$this->memoryLimit = ((int) $this->server->getProperty("memory.main-limit", 0)) * 1024 * 1024;

		$defaultMemory = 1024;

		if(preg_match("/([0-9]+)([KMGkmg])/", $this->server->getConfigString("memory-limit", ""), $matches) > 0){
			$m = (int) $matches[1];
			if($m <= 0){
				$defaultMemory = 0;
			}else{
				switch(strtoupper($matches[2])){
					case "K":
						$defaultMemory = $m / 1024;
						break;
					case "M":
						$defaultMemory = $m;
						break;
					case "G":
						$defaultMemory = $m * 1024;
						break;
					default:
						$defaultMemory = $m;
						break;
				}
			}
		}

		$hardLimit = ((int) $this->server->getProperty("memory.main-hard-limit", $defaultMemory));

		if($hardLimit <= 0){
			ini_set("memory_limit", -1);
		}else{
			ini_set("memory_limit", $hardLimit . "M");
		}

		$this->globalMemoryLimit = ((int) $this->server->getProperty("memory.global-limit", 0)) * 1024 * 1024;
		$this->checkRate = (int) $this->server->getProperty("memory.check-rate", 20);
		$this->continuousTrigger = (bool) $this->server->getProperty("memory.continuous-trigger", true);
		$this->continuousTriggerRate = (int) $this->server->getProperty("memory.continuous-trigger-rate", 30);

		$this->garbageCollectionPeriod = (int) $this->server->getProperty("memory.garbage-collection.period", 36000);
		$this->garbageCollectionTrigger = (bool) $this->server->getProperty("memory.garbage-collection.low-memory-trigger", true);
		$this->garbageCollectionAsync = (bool) $this->server->getProperty("memory.garbage-collection.collect-async-worker", true);

		$this->chunkLimit = (int) $this->server->getProperty("memory.max-chunks.trigger-limit", 96);
		$this->chunkCollect = (bool) $this->server->getProperty("memory.max-chunks.trigger-chunk-collect", true);
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

		if($this->chunkTrigger and $this->chunkCollect){
			foreach($this->server->getLevels() as $level){
				$level->doChunkGarbageCollection();
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

	/**
	 * @param object $object
	 *
	 * @return string Object identifier for future checks
	 */
	public function addObjectWatcher($object){
		if(!is_object($object)){
			throw new \InvalidArgumentException("Not an object!");
		}


		$identifier = spl_object_hash($object) . ":" . get_class($object);

		if(isset($this->leakInfo[$identifier])){
			return $this->leakInfo["id"];
		}

		$this->leakInfo[$identifier] = [
			"id" => $id = md5($identifier . ":" . $this->leakSeed++),
			"class" => get_class($object),
			"hash" => $identifier
		];
		$this->leakInfo[$id] = $this->leakInfo[$identifier];

		$this->leakWatch[$id] = new \WeakRef($object);

		return $id;
	}

	public function isObjectAlive($id){
		if(isset($this->leakWatch[$id])){
			return $this->leakWatch[$id]->valid();
		}

		return false;
	}

	public function removeObjectWatch($id){
		if(!isset($this->leakWatch[$id])){
			return;
		}
		unset($this->leakInfo[$this->leakInfo[$id]["hash"]]);
		unset($this->leakInfo[$id]);
		unset($this->leakWatch[$id]);
	}

	public function doObjectCleanup(){
		foreach($this->leakWatch as $id => $w){
			if(!$w->valid()){
				$this->removeObjectWatch($id);
			}
		}
	}

	public function getObjectInformation($id, $includeObject = false){
		if(!isset($this->leakWatch[$id])){
			return null;
		}

		$valid = false;
		$references = 0;
		$object = null;

		if($this->leakWatch[$id]->acquire()){
			$object = $this->leakWatch[$id]->get();
			$this->leakWatch[$id]->release();

			$valid = true;
			$references = getReferenceCount($object, false);
		}

		return [
			"id" => $id,
			"class" => $this->leakInfo[$id]["class"],
			"hash" => $this->leakInfo[$id]["hash"],
			"valid" => $valid,
			"references" => $references,
			"object" => $includeObject ? $object : null
		];
	}

	public function dumpServerMemory($outputFolder, $maxNesting, $maxStringSize){
		gc_disable();

		if(!file_exists($outputFolder)){
			mkdir($outputFolder, 0777, true);
		}

		$this->server->getLogger()->notice("[Dump] After the memory dump is done, the server might crash");

		$obData = fopen($outputFolder . "/objects.js", "wb+");

		$staticProperties = [];

		$data = [];

		$objects = [];

		$refCounts = [];

		$this->continueDump($this->server, $data, $objects, $refCounts, 0, $maxNesting, $maxStringSize);

		do{
			$continue = false;
			foreach($objects as $hash => $object){
				if(!is_object($object)){
					continue;
				}
				$continue = true;

				$className = get_class($object);

				$objects[$hash] = true;

				$reflection = new \ReflectionObject($object);

				$info = [
					"information" => "$hash@$className",
					"properties" => []
				];

				if($reflection->getParentClass()){
					$info["parent"] = $reflection->getParentClass()->getName();
				}

				if(count($reflection->getInterfaceNames()) > 0){
					$info["implements"] = implode(", ", $reflection->getInterfaceNames());
				}

				foreach($reflection->getProperties() as $property){
					if($property->isStatic()){
						continue;
					}

					if(!$property->isPublic()){
						$property->setAccessible(true);
					}
					$this->continueDump($property->getValue($object), $info["properties"][$property->getName()], $objects, $refCounts, 0, $maxNesting, $maxStringSize);
				}

				fwrite($obData, "$hash@$className: ". json_encode($info, JSON_UNESCAPED_SLASHES) . "\n");

				if(!isset($objects["staticProperties"][$className])){
					$staticProperties[$className] = [];
					foreach($reflection->getProperties() as $property){
						if(!$property->isStatic() or $property->getDeclaringClass()->getName() !== $className){
							continue;
						}

						if(!$property->isPublic()){
							$property->setAccessible(true);
						}
						$this->continueDump($property->getValue($object), $staticProperties[$className][$property->getName()], $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}
				}
			}

			echo "[Dump] Wrote " . count($objects) . " objects\n";
		}while($continue);

		file_put_contents($outputFolder . "/staticProperties.js", json_encode($staticProperties, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . "/serverEntry.js", json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . "/referenceCounts.js", json_encode($refCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		echo "[Dump] Finished!\n";

		gc_enable();

		$this->server->forceShutdown();
	}

	private function continueDump($from, &$data, &$objects, &$refCounts, $recursion, $maxNesting, $maxStringSize){
		if($maxNesting <= 0){
			$data = "(error) NESTING LIMIT REACHED";
			return;
		}

		--$maxNesting;

		if(is_object($from)){
			if(!isset($objects[$hash = spl_object_hash($from)])){
				$objects[$hash] = $from;
				$refCounts[$hash] = 0;
			}

			++$refCounts[$hash];

			$data = "(object) $hash@" . get_class($from);
		}elseif(is_array($from)){
			if($recursion >= 5){
				$data = "(error) ARRAY RECURSION LIMIT REACHED";
				return;
			}
			$data = [];
			foreach($from as $key => $value){
				$this->continueDump($value, $data[$key], $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize);
			}
		}elseif(is_string($from)){
			$data = "(string) len(".strlen($from).") " . substr(Utils::printable($from), 0, $maxStringSize);
		}elseif(is_resource($from)){
			$data = "(resource) " . print_r($from, true);
		}else{
			$data = $from;
		}
	}
}