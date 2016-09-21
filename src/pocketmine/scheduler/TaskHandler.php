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

namespace pocketmine\scheduler;

use pocketmine\event\Timings;

class TaskHandler{

	/** @var Task */
	protected $task;

	/** @var int */
	protected $taskId;

	/** @var int */
	protected $delay;

	/** @var int */
	protected $period;

	/** @var int */
	protected $nextRun;

	/** @var bool */
	protected $cancelled = false;

	/** @var \pocketmine\event\TimingsHandler */
	public $timings;

	public $timingName = null;

	/**
	 * @param string $timingName
	 * @param Task   $task
	 * @param int    $taskId
	 * @param int    $delay
	 * @param int    $period
	 */
	public function __construct($timingName, Task $task, $taskId, $delay = -1, $period = -1){
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
		$this->timingName = $timingName === null ? "Unknown" : $timingName;
		$this->timings = Timings::getPluginTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	/**
	 * @return bool
	 */
	public function isCancelled(){
		return $this->cancelled === true;
	}

	/**
	 * @return int
	 */
	public function getNextRun(){
		return $this->nextRun;
	}

	/**
	 * @param int $ticks
	 */
	public function setNextRun($ticks){
		$this->nextRun = $ticks;
	}

	/**
	 * @return int
	 */
	public function getTaskId(){
		return $this->taskId;
	}

	/**
	 * @return Task
	 */
	public function getTask(){
		return $this->task;
	}

	/**
	 * @return int
	 */
	public function getDelay(){
		return $this->delay;
	}

	/**
	 * @return bool
	 */
	public function isDelayed(){
		return $this->delay > 0;
	}

	/**
	 * @return bool
	 */
	public function isRepeating(){
		return $this->period > 0;
	}

	/**
	 * @return int
	 */
	public function getPeriod(){
		return $this->period;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function cancel(){
		if(!$this->isCancelled()){
			$this->task->onCancel();
		}
		$this->remove();
	}

	public function remove(){
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	/**
	 * @param int $currentTick
	 */
	public function run($currentTick){
		$this->task->onRun($currentTick);
	}

	/**
	 * @return string
	 */
	public function getTaskName(){
		if($this->timingName !== null){
			return $this->timingName;
		}

		return get_class($this->task);
	}
}
