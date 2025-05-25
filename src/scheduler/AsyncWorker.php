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

namespace pocketmine\scheduler;

use pocketmine\GarbageCollectorManager;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Worker;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use function ini_set;

class AsyncWorker extends Worker{
	private static ?SleeperNotifier $notifier = null;
	private static ?GarbageCollectorManager $cycleGcManager = null;

	public function __construct(
		private ThreadSafeLogger $logger,
		private int $id,
		private int $memoryLimit,
		private SleeperHandlerEntry $sleeperEntry
	){}

	public static function getNotifier() : SleeperNotifier{
		if(self::$notifier !== null){
			return self::$notifier;
		}
		throw new AssumptionFailedError("SleeperNotifier not found in thread-local storage");
	}

	public static function maybeCollectCycles() : void{
		if(self::$cycleGcManager === null){
			throw new AssumptionFailedError("GarbageCollectorManager not found in thread-local storage");
		}
		self::$cycleGcManager->maybeCollectCycles();
	}

	protected function onRun() : void{
		\GlobalLogger::set($this->logger);

		if($this->memoryLimit > 0){
			ini_set('memory_limit', $this->memoryLimit . 'M');
			$this->logger->debug("Set memory limit to " . $this->memoryLimit . " MB");
		}else{
			ini_set('memory_limit', '-1');
			$this->logger->debug("No memory limit set");
		}

		self::$notifier = $this->sleeperEntry->createNotifier();
		Timings::init();
		self::$cycleGcManager = new GarbageCollectorManager($this->logger, Timings::$asyncTaskWorkers);
	}

	public function getLogger() : ThreadSafeLogger{
		return $this->logger;
	}

	public function getThreadName() : string{
		return "AsyncWorker#" . $this->id;
	}

	public function getAsyncWorkerId() : int{
		return $this->id;
	}
}
