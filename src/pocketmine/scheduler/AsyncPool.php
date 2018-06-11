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
	private const WORKER_START_OPTIONS = PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS;

	/** @var Server */
	private $server;

	/** @var \ClassLoader */
	private $classLoader;
	/** @var \ThreadedLogger */
	private $logger;

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

	/** @var \Closure[] */
	private $workerStartHooks = [];

	public function __construct(Server $server, int $size, int $workerMemoryLimit, \ClassLoader $classLoader, \ThreadedLogger $logger){
		$this->server = $server;
		$this->size = $size;
		$this->workerMemoryLimit = $workerMemoryLimit;
		$this->classLoader = $classLoader;
		$this->logger = $logger;
	}

	public function getSize() : int{
		return $this->size;
	}

	public function increaseSize(int $newSize){
		if($newSize > $this->size){
			$this->size = $newSize;
		}
	}

	/**
	 * Registers a Closure callback to be fired whenever a new worker is started by the pool.
	 * The signature should be `function(int $worker) : void`
	 *
	 * This function will call the hook for every already-running worker.
	 *
	 * @param \Closure $hook
	 */
	public function addWorkerStartHook(\Closure $hook) : void{
		$this->workerStartHooks[spl_object_hash($hook)] = $hook;
		foreach($this->workers as $i => $worker){
			$hook($i);
		}
	}

	/**
	 * Removes a previously-registered callback listening for workers being started.
	 *
	 * @param \Closure $hook
	 */
	public function removeWorkerStartHook(\Closure $hook) : void{
		unset($this->workerStartHooks[spl_object_hash($hook)]);
	}

	/**
	 * Returns an array of IDs of currently running workers.
	 *
	 * @return int[]
	 */
	public function getRunningWorkers() : array{
		return array_keys($this->workers);
	}

	/**
	 * Fetches the worker with the specified ID, starting it if it does not exist, and firing any registered worker
	 * start hooks.
	 *
	 * @param int $worker
	 *
	 * @return AsyncWorker
	 */
	private function getWorker(int $worker) : AsyncWorker{
		if(!isset($this->workers[$worker])){
			$this->workerUsage[$worker] = 0;
			$this->workers[$worker] = new AsyncWorker($this->logger, $worker + 1, $this->workerMemoryLimit);
			$this->workers[$worker]->setClassLoader($this->classLoader);
			$this->workers[$worker]->start(self::WORKER_START_OPTIONS);

			foreach($this->workerStartHooks as $hook){
				$hook($worker);
			}
		}

		return $this->workers[$worker];
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

		$this->getWorker($worker)->stack($task);
		$this->workerUsage[$worker]++;
		$this->taskWorkers[$task->getTaskId()] = $worker;
	}

	public function submitTask(AsyncTask $task) : int{
		if($task->getTaskId() !== null){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$worker = null;
		$minUsage = PHP_INT_MAX;
		foreach($this->workerUsage as $i => $usage){
			if($usage < $minUsage){
				$worker = $i;
				$minUsage = $usage;
				if($usage === 0){
					break;
				}
			}
		}
		if($worker === null or ($minUsage > 0 and count($this->workers) < $this->size)){
			//select a worker to start on the fly
			for($i = 0; $i < $this->size; ++$i){
				if(!isset($this->workers[$i])){
					$worker = $i;
					break;
				}
			}
		}

		assert($worker !== null);

		$this->submitTaskToWorker($task, $worker);
		return $worker;
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
							$this->logger->notice("AsyncTask " . get_class($task) . " stored local complex data but did not remove them after completion");
						}
					}catch(\Throwable $e){
						$this->logger->critical("Could not execute completion of asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": " . $e->getMessage());
						$this->logger->logException($e);

						$task->removeDanglingStoredObjects(); //silent
					}
				}

				$this->removeTask($task);
			}elseif($task->isTerminated() or $task->isCrashed()){
				$this->logger->critical("Could not execute asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": Task crashed");
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
