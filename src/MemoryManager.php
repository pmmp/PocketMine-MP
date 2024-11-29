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
use pocketmine\utils\Utils;
use pocketmine\YmlServerProperties as Yml;
use Symfony\Component\Filesystem\Path;
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
use function get_defined_functions;
use function ini_get;
use function ini_set;
use function intdiv;
use function is_array;
use function is_float;
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
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const SORT_NUMERIC;

class MemoryManager{
	private const DEFAULT_CHECK_RATE = Server::TARGET_TICKS_PER_SECOND;
	private const DEFAULT_CONTINUOUS_TRIGGER_RATE = Server::TARGET_TICKS_PER_SECOND * 2;
	private const DEFAULT_TICKS_PER_GC = 30 * 60 * Server::TARGET_TICKS_PER_SECOND;

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
	private bool $garbageCollectionTrigger;
	private bool $garbageCollectionAsync;

	private int $lowMemChunkRadiusOverride;
	private bool $lowMemChunkGC;

	private bool $lowMemDisableChunkCache;
	private bool $lowMemClearWorldCache;

	private bool $dumpWorkers = true;

	private \Logger $logger;

	public function __construct(
		private Server $server
	){
		$this->logger = new \PrefixedLogger($server->getLogger(), "Memory Manager");

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
		$this->garbageCollectionTrigger = $config->getPropertyBool(Yml::MEMORY_GARBAGE_COLLECTION_LOW_MEMORY_TRIGGER, true);
		$this->garbageCollectionAsync = $config->getPropertyBool(Yml::MEMORY_GARBAGE_COLLECTION_COLLECT_ASYNC_WORKER, true);

		$this->lowMemChunkRadiusOverride = $config->getPropertyInt(Yml::MEMORY_MAX_CHUNKS_CHUNK_RADIUS, 4);
		$this->lowMemChunkGC = $config->getPropertyBool(Yml::MEMORY_MAX_CHUNKS_TRIGGER_CHUNK_COLLECT, true);

		$this->lowMemDisableChunkCache = $config->getPropertyBool(Yml::MEMORY_WORLD_CACHES_DISABLE_CHUNK_CACHE, true);
		$this->lowMemClearWorldCache = $config->getPropertyBool(Yml::MEMORY_WORLD_CACHES_LOW_MEMORY_TRIGGER, true);

		$this->dumpWorkers = $config->getPropertyBool(Yml::MEMORY_MEMORY_DUMP_DUMP_ASYNC_WORKER, true);
		gc_enable();
	}

	public function isLowMemory() : bool{
		return $this->lowMemory;
	}

	public function getGlobalMemoryLimit() : int{
		return $this->globalMemoryLimit;
	}

	public function canUseChunkCache() : bool{
		return !$this->lowMemory || !$this->lowMemDisableChunkCache;
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
		if($this->lowMemClearWorldCache){
			foreach($this->server->getWorldManager()->getWorlds() as $world){
				$world->clearCache(true);
			}
			ChunkCache::pruneCaches();
		}

		if($this->lowMemChunkGC){
			foreach($this->server->getWorldManager()->getWorlds() as $world){
				$world->doChunkGarbageCollection();
			}
		}

		$ev = new LowMemoryEvent($memory, $limit, $global, $triggerCount);
		$ev->call();

		$cycles = 0;
		if($this->garbageCollectionTrigger){
			$cycles = $this->triggerGarbageCollector();
		}

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
		}

		Timings::$memoryManager->stopTiming();
	}

	public function triggerGarbageCollector() : int{
		Timings::$garbageCollector->startTiming();

		if($this->garbageCollectionAsync){
			$pool = $this->server->getAsyncPool();
			if(($w = $pool->shutdownUnusedWorkers()) > 0){
				$this->logger->debug("Shut down $w idle async pool workers");
			}
			foreach($pool->getRunningWorkers() as $i){
				$pool->submitTaskToWorker(new GarbageCollectionTask(), $i);
			}
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
		self::dumpMemory($this->server, $outputFolder, $maxNesting, $maxStringSize, $logger);

		if($this->dumpWorkers){
			$pool = $this->server->getAsyncPool();
			foreach($pool->getRunningWorkers() as $i){
				$pool->submitTaskToWorker(new DumpWorkerMemoryTask($outputFolder, $maxNesting, $maxStringSize), $i);
			}
		}
	}

	/**
	 * Static memory dumper accessible from any thread.
	 */
	public static function dumpMemory(mixed $startingObject, string $outputFolder, int $maxNesting, int $maxStringSize, \Logger $logger) : void{
		$hardLimit = Utils::assumeNotFalse(ini_get('memory_limit'), "memory_limit INI directive should always exist");
		ini_set('memory_limit', '-1');
		gc_disable();

		if(!file_exists($outputFolder)){
			mkdir($outputFolder, 0777, true);
		}

		$obData = Utils::assumeNotFalse(fopen(Path::join($outputFolder, "objects.js"), "wb+"));

		$objects = [];

		$refCounts = [];

		$instanceCounts = [];

		$staticProperties = [];
		$staticCount = 0;

		$functionStaticVars = [];
		$functionStaticVarsCount = 0;

		foreach(get_declared_classes() as $className){
			$reflection = new \ReflectionClass($className);
			$staticProperties[$className] = [];
			foreach($reflection->getProperties() as $property){
				if(!$property->isStatic() || $property->getDeclaringClass()->getName() !== $className){
					continue;
				}

				if(!$property->isInitialized()){
					continue;
				}

				$staticCount++;
				$staticProperties[$className][$property->getName()] = self::continueDump($property->getValue(), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}

			if(count($staticProperties[$className]) === 0){
				unset($staticProperties[$className]);
			}

			foreach($reflection->getMethods() as $method){
				if($method->getDeclaringClass()->getName() !== $reflection->getName()){
					continue;
				}
				$methodStatics = [];
				foreach(Utils::promoteKeys($method->getStaticVariables()) as $name => $variable){
					$methodStatics[$name] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
				}
				if(count($methodStatics) > 0){
					$functionStaticVars[$className . "::" . $method->getName()] = $methodStatics;
					$functionStaticVarsCount += count($functionStaticVars);
				}
			}
		}

		file_put_contents(Path::join($outputFolder, "staticProperties.js"), json_encode($staticProperties, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $staticCount static properties");

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

		foreach(Utils::promoteKeys($GLOBALS) as $varName => $value){
			if(isset($ignoredGlobals[$varName])){
				continue;
			}

			$globalCount++;
			$globalVariables[$varName] = self::continueDump($value, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
		}

		file_put_contents(Path::join($outputFolder, "globalVariables.js"), json_encode($globalVariables, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $globalCount global variables");

		foreach(get_defined_functions()["user"] as $function){
			$reflect = new \ReflectionFunction($function);

			$vars = [];
			foreach(Utils::promoteKeys($reflect->getStaticVariables()) as $varName => $variable){
				$vars[$varName] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}
			if(count($vars) > 0){
				$functionStaticVars[$function] = $vars;
				$functionStaticVarsCount += count($vars);
			}
		}
		file_put_contents(Path::join($outputFolder, 'functionStaticVars.js'), json_encode($functionStaticVars, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $functionStaticVarsCount function static variables");

		$data = self::continueDump($startingObject, $objects, $refCounts, 0, $maxNesting, $maxStringSize);

		do{
			$continue = false;
			foreach(Utils::stringifyKeys($objects) as $hash => $object){
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
				$info = [
					"information" => "$hash@$className",
				];
				if($object instanceof \Closure){
					$info["definition"] = Utils::getNiceClosureName($object);
					$info["referencedVars"] = [];
					$reflect = new \ReflectionFunction($object);
					if(($closureThis = $reflect->getClosureThis()) !== null){
						$info["this"] = self::continueDump($closureThis, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}

					foreach(Utils::promoteKeys($reflect->getStaticVariables()) as $name => $variable){
						$info["referencedVars"][$name] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}
				}else{
					$reflection = new \ReflectionObject($object);

					$info["properties"] = [];

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
							if(!$property->isInitialized($object)){
								continue;
							}

							$info["properties"][$name] = self::continueDump($property->getValue($object), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
						}
					}
				}

				fwrite($obData, json_encode($info, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
			}

		}while($continue);

		$logger->info("Wrote " . count($objects) . " objects");

		fclose($obData);

		file_put_contents(Path::join($outputFolder, "serverEntry.js"), json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		file_put_contents(Path::join($outputFolder, "referenceCounts.js"), json_encode($refCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

		arsort($instanceCounts, SORT_NUMERIC);
		file_put_contents(Path::join($outputFolder, "instanceCounts.js"), json_encode($instanceCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

		$logger->info("Finished!");

		ini_set('memory_limit', $hardLimit);
		gc_enable();
	}

	/**
	 * @param object[] $objects   reference parameter
	 * @param int[]    $refCounts reference parameter
	 *
	 * @phpstan-param array<string, object> $objects
	 * @phpstan-param array<string, int> $refCounts
	 * @phpstan-param-out array<string, object> $objects
	 * @phpstan-param-out array<string, int> $refCounts
	 */
	private static function continueDump(mixed $from, array &$objects, array &$refCounts, int $recursion, int $maxNesting, int $maxStringSize) : mixed{
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

			$data = "(object) $hash";
		}elseif(is_array($from)){
			if($recursion >= 5){
				return "(error) ARRAY RECURSION LIMIT REACHED";
			}
			$data = [];
			$numeric = 0;
			foreach(Utils::promoteKeys($from) as $key => $value){
				$data[$numeric] = [
					"k" => self::continueDump($key, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
					"v" => self::continueDump($value, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
				];
				$numeric++;
			}
		}elseif(is_string($from)){
			$data = "(string) len(" . strlen($from) . ") " . substr(Utils::printable($from), 0, $maxStringSize);
		}elseif(is_resource($from)){
			$data = "(resource) " . print_r($from, true);
		}elseif(is_float($from)){
			$data = "(float) $from";
		}else{
			$data = $from;
		}

		return $data;
	}
}
