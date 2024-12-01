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

namespace pocketmine\timings;

use pmmp\thread\Thread as NativeThread;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\utils\ObjectSet;
use pocketmine\utils\Utils;
use function array_merge;
use function array_push;
use function hrtime;
use function implode;
use function spl_object_id;

/**
 * @phpstan-type CollectPromise Promise<list<string>>
 */
class TimingsHandler{
	private const FORMAT_VERSION = 3; //thread timings collection

	private static bool $enabled = false;
	private static int $timingStart = 0;

	/** @phpstan-var ObjectSet<\Closure(bool $enable) : void> */
	private static ?ObjectSet $toggleCallbacks = null;
	/** @phpstan-var ObjectSet<\Closure() : void> */
	private static ?ObjectSet $reloadCallbacks = null;
	/** @phpstan-var ObjectSet<\Closure() : list<CollectPromise>> */
	private static ?ObjectSet $collectCallbacks = null;

	/**
	 * @phpstan-template T of object
	 * @phpstan-param ?ObjectSet<T> $where
	 * @phpstan-param-out ObjectSet<T> $where
	 * @phpstan-return ObjectSet<T>
	 */
	private static function lazyGetSet(?ObjectSet &$where) : ObjectSet{
		//workaround for phpstan bug - allows us to ignore 1 error instead of 6 without suppressing other errors
		return $where ??= new ObjectSet();
	}

	/**
	 * @phpstan-return ObjectSet<\Closure(bool $enable) : void>
	 */
	public static function getToggleCallbacks() : ObjectSet{ return self::lazyGetSet(self::$toggleCallbacks); }

	/**
	 * @phpstan-return ObjectSet<\Closure() : void>
	 */
	public static function getReloadCallbacks() : ObjectSet{ return self::lazyGetSet(self::$reloadCallbacks); }

	/**
	 * @phpstan-return ObjectSet<\Closure() : list<CollectPromise>>
	 */
	public static function getCollectCallbacks() : ObjectSet{ return self::lazyGetSet(self::$collectCallbacks); }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public static function printCurrentThreadRecords() : array{
		$threadId = NativeThread::getCurrentThread()?->getThreadId();
		$groups = [];

		foreach(TimingsRecord::getAll() as $timings){
			$time = $timings->getTotalTime();
			$count = $timings->getCount();
			if($count === 0){
				//this should never happen - a timings record shouldn't exist if it hasn't been used
				continue;
			}

			$avg = $time / $count;

			$group = $timings->getGroup() . ($threadId !== null ? " ThreadId: $threadId" : "");
			$groups[$group][] = implode(" ", [
				$timings->getName(),
				"Time: $time",
				"Count: $count",
				"Avg: $avg",
				"Violations: " . $timings->getViolations(),
				"RecordId: " . $timings->getId(),
				"ParentRecordId: " . ($timings->getParentId() ?? "none"),
				"TimerId: " . $timings->getTimerId(),
				"Ticks: " . $timings->getTicksActive(),
				"Peak: " . $timings->getPeakTime(),
			]);
		}
		$result = [];

		foreach(Utils::stringifyKeys($groups) as $groupName => $lines){
			$result[] = $groupName;
			foreach($lines as $line){
				$result[] = "    $line";
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private static function printFooter() : array{
		$result = [];

		$result[] = "# Version " . Server::getInstance()->getVersion();
		$result[] = "# " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion();

		$result[] = "# FormatVersion " . self::FORMAT_VERSION;

		$sampleTime = hrtime(true) - self::$timingStart;
		$result[] = "Sample time $sampleTime (" . ($sampleTime / 1000000000) . "s)";

		return $result;
	}

	/**
	 * @deprecated This only collects timings from the main thread. Collecting timings from all threads is an async
	 * operation, so it can't be done synchronously.
	 *
	 * @return string[]
	 */
	public static function printTimings() : array{
		$records = self::printCurrentThreadRecords();
		$footer = self::printFooter();

		return [...$records, ...$footer];
	}

	/**
	 * Collects timings asynchronously, allowing timings from multiple threads to be aggregated into a single report.
	 *
	 * NOTE: You need to add a callback to collectCallbacks if you want to include timings from other threads. They
	 * won't be automatically collected if you don't, since the main thread has no way to access them.
	 *
	 * This is an asynchronous operation, and the result is returned as a promise.
	 * The caller must add a callback to the returned promise to get the complete timings report.
	 *
	 * @phpstan-return Promise<list<string>>
	 */
	public static function requestPrintTimings() : Promise{
		$thisThreadRecords = self::printCurrentThreadRecords();

		$otherThreadRecordPromises = [];
		if(self::$collectCallbacks !== null){
			foreach(self::$collectCallbacks as $callback){
				$callbackPromises = $callback();
				array_push($otherThreadRecordPromises, ...$callbackPromises);
			}
		}

		$resolver = new PromiseResolver();
		Promise::all($otherThreadRecordPromises)->onCompletion(
			function(array $promisedRecords) use ($resolver, $thisThreadRecords) : void{
				$resolver->resolve([...$thisThreadRecords, ...array_merge(...$promisedRecords), ...self::printFooter()]);
			},
			function() : void{
				throw new \AssertionError("This promise is not expected to be rejected");
			}
		);

		return $resolver->getPromise();
	}

	public static function isEnabled() : bool{
		return self::$enabled;
	}

	public static function setEnabled(bool $enable = true) : void{
		if($enable === self::$enabled){
			return;
		}
		self::$enabled = $enable;
		self::internalReload();
		if(self::$toggleCallbacks !== null){
			foreach(self::$toggleCallbacks as $callback){
				$callback($enable);
			}
		}
	}

	public static function getStartTime() : float{
		return self::$timingStart;
	}

	private static function internalReload() : void{
		TimingsRecord::reset();
		if(self::$enabled){
			self::$timingStart = hrtime(true);
		}
	}

	public static function reload() : void{
		self::internalReload();
		if(self::$reloadCallbacks !== null){
			foreach(self::$reloadCallbacks as $callback){
				$callback();
			}
		}
	}

	public static function tick(bool $measure = true) : void{
		if(self::$enabled){
			TimingsRecord::tick($measure);
		}
	}

	private ?TimingsRecord $rootRecord = null;
	private int $timingDepth = 0;

	/**
	 * @var TimingsRecord[]
	 * @phpstan-var array<int, TimingsRecord>
	 */
	private array $recordsByParent = [];

	public function __construct(
		private string $name,
		private ?TimingsHandler $parent = null,
		private string $group = Timings::GROUP_MINECRAFT
	){}

	public function getName() : string{ return $this->name; }

	public function getGroup() : string{ return $this->group; }

	public function startTiming() : void{
		if(self::$enabled){
			$this->internalStartTiming(hrtime(true));
		}
	}

	private function internalStartTiming(int $now) : void{
		if(++$this->timingDepth === 1){
			if($this->parent !== null){
				$this->parent->internalStartTiming($now);
			}

			$current = TimingsRecord::getCurrentRecord();
			if($current !== null){
				$record = $this->recordsByParent[spl_object_id($current)] ?? null;
				if($record === null){
					$record = new TimingsRecord($this, $current);
					$this->recordsByParent[spl_object_id($current)] = $record;
				}
			}else{
				if($this->rootRecord === null){
					$this->rootRecord = new TimingsRecord($this, null);
				}
				$record = $this->rootRecord;
			}
			$record->startTiming($now);
		}
	}

	public function stopTiming() : void{
		if(self::$enabled){
			$this->internalStopTiming(hrtime(true));
		}
	}

	private function internalStopTiming(int $now) : void{
		if($this->timingDepth === 0){
			//TODO: it would be nice to bail here, but since we'd have to track timing depth across resets
			//and enable/disable, it would have a performance impact. Therefore, considering the limited
			//usefulness of bailing here anyway, we don't currently bother.
			return;
		}
		if(--$this->timingDepth !== 0){
			return;
		}

		$record = TimingsRecord::getCurrentRecord();
		$timerId = spl_object_id($this);
		for(; $record !== null && $record->getTimerId() !== $timerId; $record = TimingsRecord::getCurrentRecord()){
			\GlobalLogger::get()->error("Timer \"" . $record->getName() . "\" should have been stopped before stopping timer \"" . $this->name . "\"");
			$record->stopTiming($now);
		}
		$record?->stopTiming($now);
		if($this->parent !== null){
			$this->parent->internalStopTiming($now);
		}
	}

	/**
	 * @return mixed the result of the given closure
	 *
	 * @phpstan-template TClosureReturn
	 * @phpstan-param \Closure() : TClosureReturn $closure
	 * @phpstan-return TClosureReturn
	 */
	public function time(\Closure $closure){
		$this->startTiming();
		try{
			return $closure();
		}finally{
			$this->stopTiming();
		}
	}

	/**
	 * @internal
	 */
	public function reset() : void{
		$this->rootRecord = null;
		$this->recordsByParent = [];
		$this->timingDepth = 0;
	}
}
