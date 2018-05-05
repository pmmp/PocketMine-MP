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

/**
 * Interruptible tick sleeper. Threaded children can be attached which may notify the sleeper in order to wake the
 * server up during a tick sleep.
 */
class TickSleeper{
	/** @var \Threaded */
	private $threadedSleeper;

	/** @var SleeperNotifier[] */
	private $notifiers = [];

	/** @var int */
	private $nextSleeperId = 0;

	public function __construct(){
		$this->threadedSleeper = new \Threaded();
	}

	public function getThreadedSleeper() : \Threaded{
		return $this->threadedSleeper;
	}

	public function wait(int $microseconds) : bool{
		return $this->threadedSleeper->wait($microseconds);
	}

	public function addNotifier(SleeperNotifier $notifier) : void{
		$id = $this->nextSleeperId++;
		$notifier->attachSleeper($this->threadedSleeper, $id);
		$this->notifiers[$id] = $notifier;
	}

	/**
	 * Removes a notifier from the sleeper. Note that this does not prevent the notifier waking the sleeper up - it just
	 * stops the sleeper getting actions processed from the main thread.
	 *
	 * @param SleeperNotifier $notifier
	 */
	public function removeNotifier(SleeperNotifier $notifier) : void{
		unset($this->notifiers[$notifier->getSleeperId()]);
	}

	/**
	 * @return \Generator|SleeperNotifier[]
	 */
	public function getNotifiers() : \Generator{
		foreach($this->notifiers as $v){
			yield $v;
		}
	}
}
