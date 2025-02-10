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

use pocketmine\timings\TimingsHandler;
use function gc_collect_cycles;
use function gc_disable;
use function gc_status;
use function hrtime;
use function max;
use function min;
use function number_format;

/**
 * Allows threads to manually trigger the cyclic garbage collector using a threshold like PHP's own garbage collector,
 * but triggered at a time that suits the thread instead of in random code pathways.
 *
 * The GC trigger behaviour in this class was adapted from Zend/zend_gc.c as of PHP 8.3.14.
 */
final class GarbageCollectorManager{
	//TODO: These values could be adjusted to better suit PM, but for now we just want to mirror PHP GC to minimize
	//behavioural changes.
	private const GC_THRESHOLD_TRIGGER = 100;
	private const GC_THRESHOLD_MAX = 1_000_000_000;
	private const GC_THRESHOLD_DEFAULT = 10_001;
	private const GC_THRESHOLD_STEP = 10_000;

	private int $threshold = self::GC_THRESHOLD_DEFAULT;
	private int $collectionTimeTotalNs = 0;

	private \Logger $logger;
	private TimingsHandler $timings;

	public function __construct(
		\Logger $logger,
		?TimingsHandler $parentTimings,
	){
		gc_disable();
		$this->logger = new \PrefixedLogger($logger, "Cyclic Garbage Collector");
		$this->timings = new TimingsHandler("Cyclic Garbage Collector", $parentTimings);
	}

	private function adjustGcThreshold(int $cyclesCollected, int $rootsAfterGC) : void{
		//TODO Very simple heuristic for dynamic GC buffer resizing:
		//If there are "too few" collections, increase the collection threshold
		//by a fixed step
		//Adapted from zend_gc.c/gc_adjust_threshold() as of PHP 8.3.14
		if($cyclesCollected < self::GC_THRESHOLD_TRIGGER || $rootsAfterGC >= $this->threshold){
			$this->threshold = min(self::GC_THRESHOLD_MAX, $this->threshold + self::GC_THRESHOLD_STEP);
		}elseif($this->threshold > self::GC_THRESHOLD_DEFAULT){
			$this->threshold = max(self::GC_THRESHOLD_DEFAULT, $this->threshold - self::GC_THRESHOLD_STEP);
		}
	}

	public function getThreshold() : int{ return $this->threshold; }

	public function getCollectionTimeTotalNs() : int{ return $this->collectionTimeTotalNs; }

	public function maybeCollectCycles() : int{
		$rootsBefore = gc_status()["roots"];
		if($rootsBefore < $this->threshold){
			return 0;
		}

		$this->timings->startTiming();

		$start = hrtime(true);
		$cycles = gc_collect_cycles();
		$end = hrtime(true);

		$rootsAfter = gc_status()["roots"];
		$this->adjustGcThreshold($cycles, $rootsAfter);

		$this->timings->stopTiming();

		$time = $end - $start;
		$this->collectionTimeTotalNs += $time;
		$this->logger->debug("gc_collect_cycles: " . number_format($time) . " ns ($rootsBefore -> $rootsAfter roots, $cycles cycles collected) - total GC time: " . number_format($this->collectionTimeTotalNs) . " ns");

		return $cycles;
	}
}
