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

use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;

class TaskHandler{

	/** @var Task */
	protected $task;

	/** @var int */
	protected $delay;

	/** @var int */
	protected $period;

	/** @var int */
	protected $nextRun;

	/** @var bool */
	protected $cancelled = false;

	/** @var TimingsHandler */
	private $timings;

	/** @var string */
	private $taskName;
	/** @var string */
	private $ownerName;

	public function __construct(Task $task, int $delay = -1, int $period = -1, ?string $ownerName = null){
		if($task->getHandler() !== null){
			throw new \InvalidArgumentException("Cannot assign multiple handlers to the same task");
		}
		$this->task = $task;
		$this->delay = $delay;
		$this->period = $period;
		$this->taskName = $task->getName();
		$this->ownerName = $ownerName ?? "Unknown";
		$this->timings = Timings::getScheduledTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	public function isCancelled() : bool{
		return $this->cancelled;
	}

	public function getNextRun() : int{
		return $this->nextRun;
	}

	public function setNextRun(int $ticks) : void{
		$this->nextRun = $ticks;
	}

	public function getTask() : Task{
		return $this->task;
	}

	public function getDelay() : int{
		return $this->delay;
	}

	public function isDelayed() : bool{
		return $this->delay > 0;
	}

	public function isRepeating() : bool{
		return $this->period > 0;
	}

	public function getPeriod() : int{
		return $this->period;
	}

	public function cancel() : void{
		try{
			if(!$this->isCancelled()){
				$this->task->onCancel();
			}
		}finally{
			$this->remove();
		}
	}

	public function remove() : void{
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	public function run() : void{
		$this->timings->startTiming();
		try{
			$this->task->onRun();
		}finally{
			$this->timings->stopTiming();
		}
	}

	public function getTaskName() : string{
		return $this->taskName;
	}

	public function getOwnerName() : string{
		return $this->ownerName;
	}
}
