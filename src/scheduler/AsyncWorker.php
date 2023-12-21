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

use pmmp\thread\Thread as NativeThread;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Worker;
use pocketmine\utils\AssumptionFailedError;
use function gc_enable;
use function ini_set;

class AsyncWorker extends Worker{
	/** @var mixed[] */
	private static array $store = [];

	private static ?SleeperNotifier $notifier = null;

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

	protected function onRun() : void{
		\GlobalLogger::set($this->logger);

		gc_enable();

		if($this->memoryLimit > 0){
			ini_set('memory_limit', $this->memoryLimit . 'M');
			$this->logger->debug("Set memory limit to " . $this->memoryLimit . " MB");
		}else{
			ini_set('memory_limit', '-1');
			$this->logger->debug("No memory limit set");
		}

		self::$notifier = $this->sleeperEntry->createNotifier();
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

	/**
	 * Saves mixed data into the worker's thread-local object store. This can be used to store objects which you
	 * want to use on this worker thread from multiple AsyncTasks.
	 *
	 * @deprecated Use static class properties instead.
	 */
	public function saveToThreadStore(string $identifier, mixed $value) : void{
		if(NativeThread::getCurrentThread() !== $this){
			throw new \LogicException("Thread-local data can only be stored in the thread context");
		}
		self::$store[$identifier] = $value;
	}

	/**
	 * Retrieves mixed data from the worker's thread-local object store.
	 *
	 * Note that the thread-local object store could be cleared and your data might not exist, so your code should
	 * account for the possibility that what you're trying to retrieve might not exist.
	 *
	 * Objects stored in this storage may ONLY be retrieved while the task is running.
	 *
	 * @deprecated Use static class properties instead.
	 */
	public function getFromThreadStore(string $identifier) : mixed{
		if(NativeThread::getCurrentThread() !== $this){
			throw new \LogicException("Thread-local data can only be fetched in the thread context");
		}
		return self::$store[$identifier] ?? null;
	}

	/**
	 * Removes previously-stored mixed data from the worker's thread-local object store.
	 *
	 * @deprecated Use static class properties instead.
	 */
	public function removeFromThreadStore(string $identifier) : void{
		if(NativeThread::getCurrentThread() !== $this){
			throw new \LogicException("Thread-local data can only be removed in the thread context");
		}
		unset(self::$store[$identifier]);
	}
}
