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
use pocketmine\snooze\SleeperHandler;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\ThreadCrashException;
use pocketmine\thread\ThreadSafeClassLoader;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function array_keys;
use function array_map;
use function assert;
use function count;
use function get_class;
use function spl_object_id;
use function time;
use const PHP_INT_MAX;

/**
 * Manages general-purpose worker threads used for processing asynchronous tasks, and the tasks submitted to those
 * workers.
 */
class AsyncPool{
	private const WORKER_START_OPTIONS = NativeThread::INHERIT_INI | NativeThread::INHERIT_COMMENTS;

	/**
	 * @var AsyncPoolWorkerEntry[]
	 * @phpstan-var array<int, AsyncPoolWorkerEntry>
	 */
	private array $workers = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var (\Closure(int $workerId) : void)[]
	 */
	private array $workerStartHooks = [];

	public function __construct(
		protected int $size,
		private int $workerMemoryLimit,
		private ThreadSafeClassLoader $classLoader,
		private ThreadSafeLogger $logger,
		private SleeperHandler $eventLoop
	){}

	/**
	 * Returns the maximum size of the pool. Note that there may be less active workers than this number.
	 */
	public function getSize() : int{
		return $this->size;
	}

	/**
	 * Increases the maximum size of the pool to the specified amount. This does not immediately start new workers.
	 */
	public function increaseSize(int $newSize) : void{
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
	 * @phpstan-param \Closure(int $workerId) : void $hook
	 */
	public function addWorkerStartHook(\Closure $hook) : void{
		Utils::validateCallableSignature(function(int $worker) : void{}, $hook);
		$this->workerStartHooks[spl_object_id($hook)] = $hook;
		foreach($this->workers as $i => $worker){
			$hook($i);
		}
	}

	/**
	 * Removes a previously-registered callback listening for workers being started.
	 *
	 * @phpstan-param \Closure(int $workerId) : void $hook
	 */
	public function removeWorkerStartHook(\Closure $hook) : void{
		unset($this->workerStartHooks[spl_object_id($hook)]);
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
	 */
	private function getWorker(int $workerId) : AsyncPoolWorkerEntry{
		if(!isset($this->workers[$workerId])){
			$sleeperEntry = $this->eventLoop->addNotifier(function() use ($workerId) : void{
				$this->collectTasksFromWorker($workerId);
			});
			$this->workers[$workerId] = new AsyncPoolWorkerEntry(new AsyncWorker($this->logger, $workerId, $this->workerMemoryLimit, $sleeperEntry), $sleeperEntry->getNotifierId());
			$this->workers[$workerId]->worker->setClassLoaders([$this->classLoader]);
			$this->workers[$workerId]->worker->start(self::WORKER_START_OPTIONS);

			foreach($this->workerStartHooks as $hook){
				$hook($workerId);
			}
		}else{
			$this->checkCrashedWorker($workerId, null);
		}

		return $this->workers[$workerId];
	}

	/**
	 * Submits an AsyncTask to an arbitrary worker.
	 */
	public function submitTaskToWorker(AsyncTask $task, int $worker) : void{
		if($worker < 0 || $worker >= $this->size){
			throw new \InvalidArgumentException("Invalid worker $worker");
		}
		if($task->isSubmitted()){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$task->setSubmitted();

		$this->getWorker($worker)->submit($task);
	}

	/**
	 * Selects a worker ID to run a task.
	 *
	 * - if an idle worker is found, it will be selected
	 * - else, if the worker pool is not full, a new worker will be selected
	 * - else, the worker with the smallest backlog is chosen.
	 */
	public function selectWorker() : int{
		$worker = null;
		$minUsage = PHP_INT_MAX;
		foreach($this->workers as $i => $entry){
			if(($usage = $entry->tasks->count()) < $minUsage){
				$worker = $i;
				$minUsage = $usage;
				if($usage === 0){
					break;
				}
			}
		}
		if($worker === null || ($minUsage > 0 && count($this->workers) < $this->size)){
			//select a worker to start on the fly
			for($i = 0; $i < $this->size; ++$i){
				if(!isset($this->workers[$i])){
					$worker = $i;
					break;
				}
			}
		}

		assert($worker !== null);
		return $worker;
	}

	/**
	 * Submits an AsyncTask to the worker with the least load. If all workers are busy and the pool is not full, a new
	 * worker may be started.
	 */
	public function submitTask(AsyncTask $task) : int{
		if($task->isSubmitted()){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$worker = $this->selectWorker();
		$this->submitTaskToWorker($task, $worker);
		return $worker;
	}

	private function checkCrashedWorker(int $workerId, ?AsyncTask $crashedTask) : void{
		$entry = $this->workers[$workerId];
		if($entry->worker->isTerminated()){
			if($crashedTask === null){
				foreach($entry->tasks as $task){
					if($task->isTerminated()){
						$crashedTask = $task;
						break;
					}elseif(!$task->isFinished()){
						break;
					}
				}
			}
			$info = $entry->worker->getCrashInfo();
			if($info !== null){
				if($crashedTask !== null){
					$message = "Worker $workerId crashed while running task " . get_class($crashedTask) . "#" . spl_object_id($crashedTask);
				}else{
					$message = "Worker $workerId crashed while doing unknown work";
				}
				throw new ThreadCrashException($message, $info);
			}else{
				throw new \RuntimeException("Worker $workerId crashed for unknown reason");
			}
		}
	}

	/**
	 * Collects finished and/or crashed tasks from the workers, firing their on-completion hooks where appropriate.
	 *
	 * @throws \ReflectionException
	 * @return bool whether there are tasks left to be collected
	 */
	public function collectTasks() : bool{
		foreach($this->workers as $workerId => $entry){
			$this->collectTasksFromWorker($workerId);
		}

		//we check this in a second loop, because task collection could have caused new tasks to be added to the queues
		foreach($this->workers as $entry){
			if(!$entry->tasks->isEmpty()){
				return true;
			}
		}
		return false;
	}

	public function collectTasksFromWorker(int $worker) : bool{
		if(!isset($this->workers[$worker])){
			throw new \InvalidArgumentException("No such worker $worker");
		}
		$queue = $this->workers[$worker]->tasks;
		$more = false;
		while(!$queue->isEmpty()){
			/** @var AsyncTask $task */
			$task = $queue->bottom();
			if($task->isFinished()){ //make sure the task actually executed before trying to collect
				$queue->dequeue();

				if($task->isTerminated()){
					$this->checkCrashedWorker($worker, $task);
					throw new AssumptionFailedError("checkCrashedWorker() should have thrown an exception, making this unreachable");
				}else{
					/*
					 * It's possible for a task to submit a progress update and then finish before the progress
					 * update is detected by the parent thread, so here we consume any missed updates.
					 *
					 * When this happens, it's possible for a progress update to arrive between the previous
					 * checkProgressUpdates() call and the next isGarbage() call, causing progress updates to be
					 * lost. Thus, it's necessary to do one last check here to make sure all progress updates have
					 * been consumed before completing.
					 */
					$this->checkTaskProgressUpdates($task);
					Timings::getAsyncTaskCompletionTimings($task)->time(function() use ($task) : void{
						$task->onCompletion();
					});
				}
			}else{
				$this->checkTaskProgressUpdates($task);
				$more = true;
				break; //current task is still running, skip to next worker
			}
		}
		$this->workers[$worker]->worker->collect();
		return $more;
	}

	/**
	 * Returns an array of worker ID => task queue size
	 *
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getTaskQueueSizes() : array{
		return array_map(function(AsyncPoolWorkerEntry $entry) : int{ return $entry->tasks->count(); }, $this->workers);
	}

	public function shutdownUnusedWorkers() : int{
		$ret = 0;
		$time = time();
		foreach($this->workers as $i => $entry){
			if($entry->lastUsed + 300 < $time && $entry->tasks->isEmpty()){
				$entry->worker->quit();
				$this->eventLoop->removeNotifier($entry->sleeperNotifierId);
				unset($this->workers[$i]);
				$ret++;
			}
		}

		return $ret;
	}

	/**
	 * Cancels all pending tasks and shuts down all the workers in the pool.
	 */
	public function shutdown() : void{
		while($this->collectTasks()){
			//NOOP
		}

		foreach($this->workers as $worker){
			$worker->worker->quit();
			$this->eventLoop->removeNotifier($worker->sleeperNotifierId);
		}
		$this->workers = [];
	}

	private function checkTaskProgressUpdates(AsyncTask $task) : void{
		Timings::getAsyncTaskProgressUpdateTimings($task)->time(function() use ($task) : void{
			$task->checkProgressUpdates();
		});
	}
}
