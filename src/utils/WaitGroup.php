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

namespace pocketmine\utils;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a group of tasks that can be asynchronously completed.
 *
 * Unlike `sync.WaitGroup` in Go, each WaitGroup can only be used once.
 * New tasks cannot be added after `wait()` has been called.
 *
 * A WaitGroup can be one of three states:
 *
 * - Collecting: `locked` is false, `execute` is null. This is the initial state.
 *   `add()` and `done()` can be called as long as number of tasks added is not less than number of tasks done.
 *   Transitions to Waiting state when `wait()` is called.
 * - Waiting: `locked` is true, `execute` is a closure.
 *   `add()` can no longer be called. The `execute` function is called after all pending tasks are done.
 *   Transitions to Complete state and calls the `execute` function is called after all pending tasks are done.
 * - Complete: `locked` is true, `execute` is null.
 *   The number of pending tasks is exactly zero, so `add()` and `done()` cannot be called.
 *   The WaitGroup should no longer be used at this state.
 */
final class WaitGroup{

	/** @var bool Whether the WaitGroup has been locked */
	private bool $locked = false;
	/** @phpstan-var null|Closure(): void The function to execute after lock */
	private ?Closure $execute = null;
	/** @var int Number of pending tasks */
	private int $counter = 0;

	/**
	 * Executes a function when all pending tasks have completed.
	 * Prevents further tasks to be added.
	 *
	 * @phpstan-param Closure(): void $execute The function to be executed after completion.
	 */
	public function wait(Closure $execute) : void{
		if($this->locked){
			throw new RuntimeException("Cannot wait the same WaitGroup multiple times");
		}

		$this->locked = true;

		if($this->counter === 0){
			$execute();
		} else {
			$this->execute = $execute;
		}
	}

	/**
	 * Adds $count pending tasks to the wait group.
	 *
	 * New tasks cannot be added after `wait()` has been called.
	 */
	public function add(int $count = 1) : void{
		if($count === 0){
			return; // so that we don't perform subsequent sanity checks
		}

		if($count < 0){
			throw new InvalidArgumentException("\$count must be non-negative");
		}

		if($this->locked){
			throw new RuntimeException("Cannot add to WaitGroup after locked");
		}

		$this->counter += $count;
	}

	/**
	 * Marks $count pending tasks as complete.
	 * The tasks must be `add()`ed to the wait group in prior.
	 *
	 * This method triggers the wait-execute function if `wait()` has been called.
	 */
	public function done(int $count = 1) : void{
		if($count === 0){
			return; // so that we don't perform subsequent sanity checks
		}

		if($count < 0){
			throw new InvalidArgumentException("\$count must be non-negative");
		}

		if($this->counter - $count < 0){
			throw new RuntimeException("More done() called than add()");
		}

		$this->counter -= $count;

		if($this->locked){
			if($this->execute === null){
				throw new AssumptionFailedError("WaitGroup has completed but counter is nonzero");
			}

			if($this->counter === 0){
				($this->execute)();
				$this->execute = null; // to avoid calling multiple times
			}
		}
	}
}
