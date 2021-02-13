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
use pocketmine\scheduler\DumpWorkerMemoryTask;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Process;
use pocketmine\utils\Utils;
use function arsort;
use function count;
use function fclose;
use function file_exists;
use function file_put_contents;
use function fopen;
use function fwrite;
use function gc_collect_cycles;
use function gc_disable;
use function gc_enable;
use function gc_mem_caches;
use function get_class;
use function get_declared_classes;
use function implode;
use function ini_get;
use function ini_set;
use function is_array;
use function is_object;
use function is_resource;
use function is_string;
use function json_encode;
use function mb_strtoupper;
use function min;
use function mkdir;
use function preg_match;
use function print_r;
use function round;
use function spl_object_hash;
use function sprintf;
use function strlen;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const SORT_NUMERIC;

class MemoryManager{

	/** @var Server */
	private $server;

	/** @var int */
	private $memoryLimit;
	/** @var int */
	private $globalMemoryLimit;
	/** @var int */
	private $checkRate;
	/** @var int */
	private $checkTicker = 0;
	/** @var bool */
	private $lowMemory = false;

	/** @var bool */
	private $continuousTrigger = true;
	/** @var int */
	private $continuousTriggerRate;
	/** @var int */
	private $continuousTriggerCount = 0;
	/** @var int */
	private $continuousTriggerTicker = 0;

	/** @var int */
	private $garbageCollectionPeriod;
	/** @var int */
	private $garbageCollectionTicker = 0;
	/** @var bool */
	private $garbageCollectionTrigger;
	/** @var bool */
	private $garbageCollectionAsync;

	/** @var int */
	private $lowMemChunkRadiusOverride;
	/** @var bool */
	private $lowMemChunkGC;

	/** @var bool */
	private $lowMemDisableChunkCache;
	/** @var bool */
	private $lowMemClearWorldCache;

	/** @var bool */
	private $dumpWorkers = true;

	public function __construct(Server $server){
		$this->server = $server;

		$this->init();
	}

	private function init() : void{
		$this->memoryLimit = ((int) $this->server->getProperty("memory.main-limit", 0)) * 1024 * 1024;

		$defaultMemory = 1024;

		if(preg_match("/([0-9]+)([KMGkmg])/", $this->server->getConfigString("memory-limit", ""), $matches) > 0){
			$m = (int) $matches[1];
			if($m <= 0){
				$defaultMemory = 0;
			}else{
				switch(mb_strtoupper($matches[2])){
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
			ini_set("memory_limit", '-1');
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

		$this->lowMemChunkRadiusOverride = (int) $this->server->getProperty("memory.max-chunks.chunk-radius", 4);
		$this->lowMemChunkGC = (bool) $this->server->getProperty("memory.max-chunks.trigger-chunk-collect", true);

		$this->lowMemDisableChunkCache = (bool) $this->server->getProperty("memory.world-caches.disable-chunk-cache", true);
		$this->lowMemClearWorldCache = (bool) $this->server->getProperty("memory.world-caches.low-memory-trigger", true);

		$this->dumpWorkers = (bool) $this->server->getProperty("memory.memory-dump.dump-async-worker", true);
		gc_enable();
	}

	public function isLowMemory() : bool{
		return $this->lowMemory;
	}

	public function canUseChunkCache() : bool{
		return !$this->lowMemory or !$this->lowMemDisableChunkCache;
	}

	/**
	 * Returns the allowed chunk radius based on the current memory usage.
	 */
	public function getViewDistance(int $distance) : int{
		return ($this->lowMemory and $this->lowMemChunkRadiusOverride > 0) ? min($this->lowMemChunkRadiusOverride, $distance) : $distance;
	}

	/**
	 * Triggers garbage collection and cache cleanup to try and free memory.
	 *
	 * @return void
	 */
	public function trigger(int $memory, int $limit, bool $global = false, int $triggerCount = 0){
		$this->server->getLogger()->debug(sprintf("[Memory Manager] %sLow memory triggered, limit %gMB, using %gMB",
			$global ? "Global " : "", round(($limit / 1024) / 1024, 2), round(($memory / 1024) / 1024, 2)));
		if($this->lowMemClearWorldCache){
			foreach($this->server->getLevels() as $level){
				$level->clearCache(true);
			}
		}

		if($this->lowMemChunkGC){
			foreach($this->server->getLevels() as $level){
				$level->doChunkGarbageCollection();
			}
		}

		$ev = new LowMemoryEvent($memory, $limit, $global, $triggerCount);
		$ev->call();

		$cycles = 0;
		if($this->garbageCollectionTrigger){
			$cycles = $this->triggerGarbageCollector();
		}

		$this->server->getLogger()->debug(sprintf("[Memory Manager] Freed %gMB, $cycles cycles", round(($ev->getMemoryFreed() / 1024) / 1024, 2)));
	}

	/**
	 * Called every tick to update the memory manager state.
	 *
	 * @return void
	 */
	public function check(){
		Timings::$memoryManagerTimer->startTiming();

		if(($this->memoryLimit > 0 or $this->globalMemoryLimit > 0) and ++$this->checkTicker >= $this->checkRate){
			$this->checkTicker = 0;
			$memory = Process::getAdvancedMemoryUsage();
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

	public function triggerGarbageCollector() : int{
		Timings::$garbageCollectorTimer->startTiming();

		if($this->garbageCollectionAsync){
			$pool = $this->server->getAsyncPool();
			if(($w = $pool->shutdownUnusedWorkers()) > 0){
				$this->server->getLogger()->debug("Shut down $w idle async pool workers");
			}
			foreach($pool->getRunningWorkers() as $i){
				$pool->submitTaskToWorker(new GarbageCollectionTask(), $i);
			}
		}

		$cycles = gc_collect_cycles();
		gc_mem_caches();

		Timings::$garbageCollectorTimer->stopTiming();

		return $cycles;
	}

	/**
	 * Dumps the server memory into the specified output folder.
	 *
	 * @return void
	 */
	public function dumpServerMemory(string $outputFolder, int $maxNesting, int $maxStringSize){
		$this->server->getLogger()->notice("[Dump] After the memory dump is done, the server might crash");
		self::dumpMemory($this->server, $outputFolder, $maxNesting, $maxStringSize, $this->server->getLogger());

		if($this->dumpWorkers){
			$pool = $this->server->getAsyncPool();
			foreach($pool->getRunningWorkers() as $i){
				$pool->submitTaskToWorker(new DumpWorkerMemoryTask($outputFolder, $maxNesting, $maxStringSize), $i);
			}
		}
	}

	/**
	 * Static memory dumper accessible from any thread.
	 *
	 * @param mixed   $startingObject
	 *
	 * @return void
	 * @throws \ReflectionException
	 */
	public static function dumpMemory($startingObject, string $outputFolder, int $maxNesting, int $maxStringSize, \Logger $logger){
		$hardLimit = ini_get('memory_limit');
		if($hardLimit === false) throw new AssumptionFailedError("memory_limit INI directive should always exist");
		ini_set('memory_limit', '-1');
		gc_disable();

		if(!file_exists($outputFolder)){
			mkdir($outputFolder, 0777, true);
		}

		$obData = fopen($outputFolder . "/objects.js", "wb+");

		$data = [];

		$objects = [];

		$refCounts = [];

		$instanceCounts = [];

		$staticProperties = [];
		$staticCount = 0;

		foreach(get_declared_classes() as $className){
			$reflection = new \ReflectionClass($className);
			$staticProperties[$className] = [];
			foreach($reflection->getProperties() as $property){
				if(!$property->isStatic() or $property->getDeclaringClass()->getName() !== $className){
					continue;
				}

				if(!$property->isPublic()){
					$property->setAccessible(true);
				}

				$staticCount++;
				$staticProperties[$className][$property->getName()] = self::continueDump($property->getValue(), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}

			if(count($staticProperties[$className]) === 0){
				unset($staticProperties[$className]);
			}
		}

		file_put_contents($outputFolder . "/staticProperties.js", json_encode($staticProperties, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		$logger->info("[Dump] Wrote $staticCount static properties");

		if(isset($GLOBALS)){ //This might be null if we're on a different thread
			$globalVariables = [];
			$globalCount = 0;

			$ignoredGlobals = [
				'GLOBALS' => true,
				'_SERVER' => true,
				'_REQUEST' => true,
				'_POST' => true,
				'_GET' => true,
				'_FILES' => true,
				'_ENV' => true,
				'_COOKIE' => true,
				'_SESSION' => true
			];

			foreach($GLOBALS as $varName => $value){
				if(isset($ignoredGlobals[$varName])){
					continue;
				}

				$globalCount++;
				$globalVariables[$varName] = self::continueDump($value, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}

			file_put_contents($outputFolder . "/globalVariables.js", json_encode($globalVariables, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			$logger->info("[Dump] Wrote $globalCount global variables");
		}

		$data = self::continueDump($startingObject, $objects, $refCounts, 0, $maxNesting, $maxStringSize);

		do{
			$continue = false;
			foreach($objects as $hash => $object){
				if(!is_object($object)){
					continue;
				}
				$continue = true;

				$className = get_class($object);
				if(!isset($instanceCounts[$className])){
					$instanceCounts[$className] = 1;
				}else{
					$instanceCounts[$className]++;
				}

				$objects[$hash] = true;

				$reflection = new \ReflectionObject($object);

				$info = [
					"information" => "$hash@$className",
					"properties" => []
				];

				if(($parent = $reflection->getParentClass()) !== false){
					$info["parent"] = $parent->getName();
				}

				if(count($reflection->getInterfaceNames()) > 0){
					$info["implements"] = implode(", ", $reflection->getInterfaceNames());
				}

				for($original = $reflection; $reflection !== false; $reflection = $reflection->getParentClass()){
					foreach($reflection->getProperties() as $property){
						if($property->isStatic()){
							continue;
						}

						$name = $property->getName();
						if($reflection !== $original){
							if($property->isPrivate()){
								$name = $reflection->getName() . ":" . $name;
							}else{
								continue;
							}
						}
						if(!$property->isPublic()){
							$property->setAccessible(true);
						}

						$info["properties"][$name] = self::continueDump($property->getValue($object), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}
				}

				fwrite($obData, "$hash@$className: " . json_encode($info, JSON_UNESCAPED_SLASHES) . "\n");
			}

		}while($continue);

		$logger->info("[Dump] Wrote " . count($objects) . " objects");

		fclose($obData);

		file_put_contents($outputFolder . "/serverEntry.js", json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		file_put_contents($outputFolder . "/referenceCounts.js", json_encode($refCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		arsort($instanceCounts, SORT_NUMERIC);
		file_put_contents($outputFolder . "/instanceCounts.js", json_encode($instanceCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		$logger->info("[Dump] Finished!");

		ini_set('memory_limit', $hardLimit);
		gc_enable();
	}

	/**
	 * @param mixed    $from
	 * @param object[] $objects reference parameter
	 * @param int[]    $refCounts reference parameter
	 *
	 * @return mixed
	 */
	private static function continueDump($from, array &$objects, array &$refCounts, int $recursion, int $maxNesting, int $maxStringSize){
		if($maxNesting <= 0){
			return "(error) NESTING LIMIT REACHED";
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
				return "(error) ARRAY RECURSION LIMIT REACHED";
			}
			$data = [];
			foreach($from as $key => $value){
				$data[$key] = self::continueDump($value, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize);
			}
		}elseif(is_string($from)){
			$data = "(string) len(" . strlen($from) . ") " . substr(Utils::printable($from), 0, $maxStringSize);
		}elseif(is_resource($from)){
			$data = "(resource) " . print_r($from, true);
		}else{
			$data = $from;
		}

		return $data;
	}
}
