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

 *
 *
*/

namespace pocketmine\scheduler;

use pocketmine\Server;

class AsyncPool{

	/** @var Server */
	private $server;

	protected $size;

	/** @var AsyncTask[] */
	private $tasks = [];
	/** @var int[] */
	private $taskWorkers = [];

	/** @var AsyncWorker[] */
	private $workers = [];
	/** @var int[] */
	private $workerUsage = [];

	public function __construct(Server $server, $size){
		$this->server = $server;
		$this->size = (int) $size;

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
			$this->workers[$i] = new AsyncWorker();
			$this->workers[$i]->start();
		}
	}

	public function submitTask(AsyncTask $task){
		if(isset($this->tasks[$task->getTaskId()]) or $task->isGarbage()){
			return;
		}

		$this->tasks[$task->getTaskId()] = $task;

		$selectedWorker = mt_rand(0, $this->size - 1);
		$selectedTasks = $this->workerUsage[$selectedWorker];
		for($i = 0; $i < $this->size; ++$i){
			if($this->workerUsage[$i] < $selectedTasks){
				$selectedWorker = $i;
				$selectedTasks = $this->workerUsage[$i];
			}
		}

		$this->workers[$selectedWorker]->stack($task);
		$this->workerUsage[$selectedWorker]++;
		$this->taskWorkers[$task->getTaskId()] = $selectedWorker;
	}

	private function removeTask(AsyncTask $task){
		if(isset($this->taskWorkers[$task->getTaskId()])){
			$this->workers[$w = $this->taskWorkers[$task->getTaskId()]]->unstack($task);
			$this->workerUsage[$w]--;
		}

		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);
	}

	public function removeTasks(){
		foreach($this->tasks as $task){
			$this->removeTask($task);
		}

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];
	}

	public function collectTasks(){
		foreach($this->tasks as $task){
			if($task->isGarbage()){

				$task->onCompletion($this->server);

				$this->removeTask($task);
			}elseif($task->isTerminated()){
				$info = $task->getTerminationInfo();
				$this->removeTask($task);
				$this->server->getLogger()->critical("Could not execute asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": " . $info["message"]);
				$this->server->getLogger()->critical("On ".$info["scope"].", line ".$info["line"] .", ".$info["function"]."()");
			}
		}
	}
}