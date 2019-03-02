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

/**
 * Task scheduling related classes
 */

namespace pocketmine\scheduler;

use pocketmine\utils\ReversePriorityQueue;

class TaskScheduler{
	/** @var string|null */
	private $owner;

	/** @var bool */
	private $enabled = true;

	/**
	 * @var ReversePriorityQueue<Task>
	 */
	protected $queue;

	/**
	 * @var TaskHandler[]
	 */
	protected $tasks = [];

	/** @var int */
	private $ids = 1;

	/** @var int */
	protected $currentTick = 0;

	/**
	 * @param null|string $owner
	 */
	public function __construct(?string $owner = null){
		$this->owner = $owner;
		$this->queue = new ReversePriorityQueue();
	}

	/**
	 * @param Task $task
	 *
	 * @return TaskHandler
	 */
	public function scheduleTask(Task $task) : TaskHandler{
		return $this->addTask($task, -1, -1);
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 *
	 * @return TaskHandler
	 */
	public function scheduleDelayedTask(Task $task, int $delay) : TaskHandler{
		return $this->addTask($task, $delay, -1);
	}

	/**
	 * @param Task $task
	 * @param int  $period
	 *
	 * @return TaskHandler
	 */
	public function scheduleRepeatingTask(Task $task, int $period) : TaskHandler{
		return $this->addTask($task, -1, $period);
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 * @param int  $period
	 *
	 * @return TaskHandler
	 */
	public function scheduleDelayedRepeatingTask(Task $task, int $delay, int $period) : TaskHandler{
		return $this->addTask($task, $delay, $period);
	}

	/**
	 * @param int $taskId
	 */
	public function cancelTask(int $taskId) : void{
		if(isset($this->tasks[$taskId])){
			try{
				$this->tasks[$taskId]->cancel();
			}finally{
				unset($this->tasks[$taskId]);
			}
		}
	}

	public function cancelAllTasks() : void{
		foreach($this->tasks as $id => $task){
			$this->cancelTask($id);
		}
		$this->tasks = [];
		while(!$this->queue->isEmpty()){
			$this->queue->extract();
		}
		$this->ids = 1;
	}

	/**
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function isQueued(int $taskId) : bool{
		return isset($this->tasks[$taskId]);
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 * @param int  $period
	 *
	 * @return TaskHandler
	 *
	 * @throws \InvalidStateException
	 */
	private function addTask(Task $task, int $delay, int $period) : TaskHandler{
		if(!$this->enabled){
			throw new \InvalidStateException("Tried to schedule task to disabled scheduler");
		}

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		return $this->handle(new TaskHandler($task, $this->nextId(), $delay, $period, $this->owner));
	}

	private function handle(TaskHandler $handler) : TaskHandler{
		if($handler->isDelayed()){
			$nextRun = $this->currentTick + $handler->getDelay();
		}else{
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks[$handler->getTaskId()] = $handler;
		$this->queue->insert($handler, $nextRun);

		return $handler;
	}

	public function shutdown() : void{
		$this->enabled = false;
		$this->cancelAllTasks();
	}

	public function setEnabled(bool $enabled) : void{
		$this->enabled = $enabled;
	}

	/**
	 * @param int $currentTick
	 */
	public function mainThreadHeartbeat(int $currentTick) : void{
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			/** @var TaskHandler $task */
			$task = $this->queue->extract();
			if($task->isCancelled()){
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}
			$task->run($this->currentTick);
			if($task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}
	}

	private function isReady(int $currentTick) : bool{
		return !$this->queue->isEmpty() and $this->queue->current()->getNextRun() <= $currentTick;
	}

	/**
	 * @return int
	 */
	private function nextId() : int{
		return $this->ids++;
	}
}
