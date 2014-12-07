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

/**
 * Task scheduling related classes
 */
namespace pocketmine\scheduler;

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\PluginException;
use pocketmine\utils\ReversePriorityQueue;

class ServerScheduler{
	public static $WORKERS = 4;
	/**
	 * @var ReversePriorityQueue<Task>
	 */
	protected $queue;

	/**
	 * @var TaskHandler[]
	 */
	protected $tasks = [];

	/** @var \Pool */
	protected $asyncPool;

	/** @var AsyncTask[] */
	protected $asyncTaskStorage = [];

	protected $asyncTasks = 0;

	/** @var int */
	private $ids = 1;

	/** @var int */
	protected $currentTick = 0;

	public function __construct(){
		$this->queue = new ReversePriorityQueue();
		$this->asyncPool = new \Pool(self::$WORKERS, AsyncWorker::class);
	}

	/**
	 * @param Task $task
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleTask(Task $task){
		return $this->addTask($task, -1, -1);
	}

	/**
	 * Submits an asynchronous task to the Worker Pool
	 *
	 * @param AsyncTask $task
	 *
	 * @return void
	 */
	public function scheduleAsyncTask(AsyncTask $task){
		$id = $this->nextId();
		$task->setTaskId($id);
		$this->asyncPool->submit($task);
		$this->asyncTaskStorage[$task->getTaskId()] = $task;
		++$this->asyncTasks;
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleDelayedTask(Task $task, $delay){
		return $this->addTask($task, (int) $delay, -1);
	}

	/**
	 * @param Task $task
	 * @param int  $period
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleRepeatingTask(Task $task, $period){
		return $this->addTask($task, -1, (int) $period);
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 * @param int  $period
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleDelayedRepeatingTask(Task $task, $delay, $period){
		return $this->addTask($task, (int) $delay, (int) $period);
	}

	/**
	 * @param int $taskId
	 */
	public function cancelTask($taskId){
		if($taskId !== null and isset($this->tasks[$taskId])){
			$this->tasks[$taskId]->cancel();
			unset($this->tasks[$taskId]);
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function cancelTasks(Plugin $plugin){
		foreach($this->tasks as $taskId => $task){
			$ptask = $task->getTask();
			if($ptask instanceof PluginTask and $ptask->getOwner() === $plugin){
				$task->cancel();
				unset($this->tasks[$taskId]);
			}
		}
	}

	public function cancelAllTasks(){
		foreach($this->tasks as $task){
			$task->cancel();
		}
		$this->tasks = [];
		$this->asyncTaskStorage = [];
		//$this->asyncPool->shutdown();
		$this->asyncTasks = 0;
		$this->queue = new ReversePriorityQueue();
		$this->asyncPool = new \Pool(self::$WORKERS, AsyncWorker::class);
	}

	/**
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function isQueued($taskId){
		return isset($this->tasks[$taskId]);
	}

	/**
	 * @param Task $task
	 * @param      $delay
	 * @param      $period
	 *
	 * @return null|TaskHandler
	 *
	 * @throws PluginException
	 */
	private function addTask(Task $task, $delay, $period){
		if($task instanceof PluginTask){
			if(!($task->getOwner() instanceof Plugin)){
				throw new PluginException("Invalid owner of PluginTask " . get_class($task));
			}elseif(!$task->getOwner()->isEnabled()){
				throw new PluginException("Plugin '" . $task->getOwner()->getName() . "' attempted to register a task while disabled");
			}
		}

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		if($task instanceof CallbackTask){
			$callable = $task->getCallable();
			if(is_array($callable)){
				if(is_object($callable[0])){
					$taskName = "Callback#" . get_class($callable[0]) . "::" . $callable[1];
				}else{
					$taskName = "Callback#" . $callable[0] . "::" . $callable[1];
				}
			}else{
				$taskName = "Callback#" . $callable;
			}
		}else{
			$taskName = get_class($task);
		}

		return $this->handle(new TaskHandler($taskName, $task, $this->nextId(), $delay, $period));
	}

	private function handle(TaskHandler $handler){
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

	/**
	 * @param int $currentTick
	 */
	public function mainThreadHeartbeat($currentTick){
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			/** @var TaskHandler $task */
			$task = $this->queue->extract();
			if($task->isCancelled()){
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}else{
				$task->timings->startTiming();
				try{
					$task->run($this->currentTick);
				}catch(\Exception $e){
					Server::getInstance()->getLogger()->critical("Could not execute task " . $task->getTaskName() . ": " . $e->getMessage());
					if(($logger = Server::getInstance()->getLogger()) instanceof MainLogger){
						$logger->logException($e);
					}
				}
				$task->timings->stopTiming();
			}
			if($task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}

		if($this->asyncTasks > 0){ //Garbage collector
			$this->asyncPool->collect([$this, "collectAsyncTask"]);

			if($this->asyncTasks > 0){
				foreach($this->asyncTaskStorage as $asyncTask){
					$this->collectAsyncTask($asyncTask);
				}
			}
		}
	}

	public function collectAsyncTask(AsyncTask $task){
		if($task->isFinished() and !$task->isGarbage()){
			--$this->asyncTasks;
			$task->onCompletion(Server::getInstance());
			$task->setGarbage();
			unset($this->asyncTaskStorage[$task->getTaskId()]);
		}

		return $task->isGarbage();
	}

	private function isReady($currentTicks){
		return count($this->tasks) > 0 and $this->queue->current()->getNextRun() <= $currentTicks;
	}

	/**
	 * @return int
	 */
	private function nextId(){
		return $this->ids++;
	}

}
