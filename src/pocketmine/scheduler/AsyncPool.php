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

use pocketmine\Server;

class AsyncPool{

	/** @var Server */
	private $server;

	protected $size;
	/** @var int */
	private $workerMemoryLimit;

	/** @var AsyncTask[] */
	private $tasks = [];
	/** @var int[] */
	private $taskWorkers = [];
	/** @var int */
	private $nextTaskId = 1;

	/** @var AsyncWorker[] */
	private $workers = [];
	/** @var int[] */
	private $workerUsage = [];

	public function __construct(Server $server, int $size, int $workerMemoryLimit){
		$this->server = $server;
		$this->size = $size;
		$this->workerMemoryLimit = $workerMemoryLimit;

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
			$this->workers[$i] = new AsyncWorker($this->server->getLogger(), $i + 1, $this->workerMemoryLimit);
			$this->workers[$i]->setClassLoader($this->server->getLoader());
			$this->workers[$i]->start();
		}
	}

	public function getSize() : int{
		return $this->size;
	}

	public function increaseSize(int $newSize){
		if($newSize > $this->size){
			for($i = $this->size; $i < $newSize; ++$i){
				$this->workerUsage[$i] = 0;
				$this->workers[$i] = new AsyncWorker($this->server->getLogger(), $i + 1, $this->workerMemoryLimit);
				$this->workers[$i]->setClassLoader($this->server->getLoader());
				$this->workers[$i]->start();
			}
			$this->size = $newSize;
		}
	}

	public function submitTaskToWorker(AsyncTask $task, int $worker){
		if($worker < 0 or $worker >= $this->size){
			throw new \InvalidArgumentException("Invalid worker $worker");
		}
		if($task->getTaskId() !== null){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$task->progressUpdates = new \Threaded;
		$task->setTaskId($this->nextTaskId++);

		$this->tasks[$task->getTaskId()] = $task;

		$this->workers[$worker]->stack($task);
		$this->workerUsage[$worker]++;
		$this->taskWorkers[$task->getTaskId()] = $worker;
	}

	public function submitTask(AsyncTask $task) : int{
		if($task->getTaskId() !== null){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$selectedWorker = mt_rand(0, $this->size - 1);
		$selectedTasks = $this->workerUsage[$selectedWorker];
		for($i = 0; $i < $this->size; ++$i){
			if($this->workerUsage[$i] < $selectedTasks){
				$selectedWorker = $i;
				$selectedTasks = $this->workerUsage[$i];
			}
		}

		$this->submitTaskToWorker($task, $selectedWorker);
		return $selectedWorker;
	}

	private function removeTask(AsyncTask $task, bool $force = false){
		if(isset($this->taskWorkers[$task->getTaskId()])){
			if(!$force and ($task->isRunning() or !$task->isGarbage())){
				return;
			}
			$this->workerUsage[$this->taskWorkers[$task->getTaskId()]]--;
		}

		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);
	}

	public function removeTasks(){
		foreach($this->workers as $worker){
			/** @var AsyncTask $task */
			while(($task = $worker->unstack()) !== null){
				//cancelRun() is not strictly necessary here, but it might be used to inform plugins of the task state
				//(i.e. it never executed).
				$task->cancelRun();
				$this->removeTask($task, true);
			}
		}
		do{
			foreach($this->tasks as $task){
				$task->cancelRun();
				$this->removeTask($task);
			}

			if(count($this->tasks) > 0){
				Server::microSleep(25000);
			}
		}while(count($this->tasks) > 0);

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];

		$this->collectWorkers();
	}

	private function collectWorkers(){
		foreach($this->workers as $worker){
			$worker->collect();
		}
	}

	public function collectTasks(){
		foreach($this->tasks as $task){
			if(!$task->isGarbage()){
				$task->checkProgressUpdates($this->server);
			}
			if($task->isGarbage() and !$task->isRunning() and !$task->isCrashed()){
				if(!$task->hasCancelledRun()){
					try{
						$task->onCompletion($this->server);
						if($task->removeDanglingStoredObjects()){
							$this->server->getLogger()->notice("AsyncTask " . get_class($task) . " stored local complex data but did not remove them after completion");
						}
					}catch(\Throwable $e){
						$this->server->getLogger()->critical("Could not execute completion of asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": " . $e->getMessage());
						$this->server->getLogger()->logException($e);

						$task->removeDanglingStoredObjects(); //silent
					}
				}

				$this->removeTask($task);
			}elseif($task->isTerminated() or $task->isCrashed()){
				$this->server->getLogger()->critical("Could not execute asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": Task crashed");
				$this->removeTask($task, true);
			}
		}

		$this->collectWorkers();
	}

	public function shutdown() : void{
		$this->collectTasks();
		$this->removeTasks();
		foreach($this->workers as $worker){
			$worker->quit();
		}
		$this->workers = [];
	}
}
