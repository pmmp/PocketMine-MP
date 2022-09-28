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

use pocketmine\utils\ObjectSet;
use pocketmine\utils\ReversePriorityQueue;

class TaskScheduler{
	private bool $enabled = true;

	/**
	 * @var ReversePriorityQueue
	 * @phpstan-var ReversePriorityQueue<int, TaskHandler>
	 */
	protected $queue;

	/**
	 * @var ObjectSet|TaskHandler[]
	 * @phpstan-var ObjectSet<TaskHandler>
	 */
	protected $tasks;

	/** @var int */
	protected $currentTick = 0;

	public function __construct(
		private ?string $owner = null
	){
		$this->queue = new ReversePriorityQueue();
		$this->tasks = new ObjectSet();
	}

	public function scheduleTask(Task $task) : TaskHandler{
		return $this->addTask($task, -1, -1);
	}

	public function scheduleDelayedTask(Task $task, int $delay) : TaskHandler{
		return $this->addTask($task, $delay, -1);
	}

	public function scheduleRepeatingTask(Task $task, int $period) : TaskHandler{
		return $this->addTask($task, -1, $period);
	}

	public function scheduleDelayedRepeatingTask(Task $task, int $delay, int $period) : TaskHandler{
		return $this->addTask($task, $delay, $period);
	}

	public function cancelAllTasks() : void{
		foreach($this->tasks as $id => $task){
			$task->cancel();
		}
		$this->tasks->clear();
		while(!$this->queue->isEmpty()){
			$this->queue->extract();
		}
	}

	public function isQueued(TaskHandler $task) : bool{
		return $this->tasks->contains($task);
	}

	private function addTask(Task $task, int $delay, int $period) : TaskHandler{
		if(!$this->enabled){
			throw new \LogicException("Tried to schedule task to disabled scheduler");
		}

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		return $this->handle(new TaskHandler($task, $delay, $period, $this->owner));
	}

	private function handle(TaskHandler $handler) : TaskHandler{
		if($handler->isDelayed()){
			$nextRun = $this->currentTick + $handler->getDelay();
		}else{
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks->add($handler);
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

	public function mainThreadHeartbeat(int $currentTick) : void{
		if(!$this->enabled){
			throw new \LogicException("Cannot run heartbeat on a disabled scheduler");
		}
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			/** @var TaskHandler $task */
			$task = $this->queue->extract();
			if($task->isCancelled()){
				$this->tasks->remove($task);
				continue;
			}
			$task->run();
			if(!$task->isCancelled() && $task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				$this->tasks->remove($task);
			}
		}
	}

	private function isReady(int $currentTick) : bool{
		return !$this->queue->isEmpty() && $this->queue->current()->getNextRun() <= $currentTick;
	}
}
