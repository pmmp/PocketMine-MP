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

namespace PocketMine\Scheduler;

use PocketMine;
use PocketMine\Plugin\Plugin;

class ServerScheduler{

	/**
	 * @var ServerScheduler
	 */
	private static $instance = null;

	private $ids = 1;

	/**
	 * @var ServerTask
	 */
	private $head;

	/**
	 * @var ServerTask
	 */
	private $tail;

	/**
	 * @var \SplPriorityQueue<ServerTask>
	 */
	private $pending;

	/**
	 * @var ServerTask[]
	 */
	private $temp = array();

	/**
	 * @var \Threaded<ServerTask>
	 */
	private $runners;

	/**
	 * @var int
	 */
	private $currentTick = -1;

	/**
	 * @var \Pool[]
	 */
	private $executor;

	/**
	 * @return ServerScheduler
	 */
	public static function getInstance(){
		return self::$instance;
	}

	/**
	 * @param int $workers
	 */
	public function __construct($workers = 2){
		self::$instance = $this;
		$this->head = new ServerTask();
		$this->tail = new ServerTask();
		$this->pending = new \SplPriorityQueue();
		$this->temp = array();
		$this->runners = new \Threaded();
		$this->executor = new \Pool($workers);

	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 *
	 * @return int taskId
	 */
	public function scheduleSyncDelayedTask(Plugin $plugin, \Threaded $task, $delay = 0){
		return $this->scheduleSyncRepeatingTask($plugin, $task, $delay, -1);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 *
	 * @return int taskId
	 */
	public function scheduleAsyncDelayedTask(Plugin $plugin, \Threaded $task, $delay = 0){
		return $this->scheduleAsyncRepeatingTask($plugin, $task, $delay, -1);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 *
	 * @return ServerTask|null
	 */
	public function runTaskAsynchronously(Plugin $plugin, \Threaded $task){
		return $this->runTaskLaterAsynchronously($plugin, $task, 0);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 *
	 * @return ServerTask|null
	 */
	public function runTask(Plugin $plugin, \Threaded $task){
		return $this->runTaskLater($plugin, $task, 0);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 *
	 * @return ServerTask|null
	 */
	public function runTaskLater(Plugin $plugin, \Threaded $task, $delay = 0){
		return $this->runTaskTimer($plugin, $task, $delay, -1);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 *
	 * @return ServerTask|null
	 */
	public function runTaskLaterAsynchronously(Plugin $plugin, \Threaded $task, $delay){
		return $this->runTaskTimerAsynchronously($plugin, $task, $delay, -1);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 * @param int       $period
	 *
	 * @return int taskId
	 */
	public function scheduleSyncRepeatingTask(Plugin $plugin, \Threaded $task, $delay, $period){
		return $this->runTaskTimer($plugin, $task, $delay, $period)->getTaskId();
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 * @param int       $period
	 *
	 * @return int taskId
	 */
	public function scheduleAsyncRepeatingTask(Plugin $plugin, \Threaded $task, $delay, $period){
		return $this->runTaskTimerAsynchronously($plugin, $task, $delay, $period)->getTaskId();
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 * @param int       $period
	 *
	 * @return ServerTask|null
	 */
	public function runTaskTimer(Plugin $plugin, \Threaded $task, $delay, $period){
		if(!$this->validate($plugin, $task)){
			return null;
		}

		if($delay < 0){
			$delay = 0;
		}

		if($period === 0){
			$period = 1;
		}elseif($period < -1){
			$period = -1;
		}

		return $this->handle(new ServerTask($plugin, $task, $this->nextId(), $period), $delay);
	}

	/**
	 * @param Plugin    $plugin
	 * @param \Threaded $task
	 * @param int       $delay
	 * @param int       $period
	 *
	 * @return ServerTask|null
	 */
	public function runTaskTimerAsynchronously(Plugin $plugin, \Threaded $task, $delay, $period){
		if(!$this->validate($plugin, $task)){
			return null;
		}

		if($delay < 0){
			$delay = 0;
		}

		if($period === 0){
			$period = 1;
		}elseif($period < -1){
			$period = -1;
		}

		return $this->handle(new ServerAsyncTask($this->runners, $plugin, $task, $this->nextId(), $period), $delay);
	}

	/**
	 * @param Plugin   $plugin
	 * @param ServerCallable $task
	 *
	 * @return ServerFuture|null
	 */
	public function callSyncMethod(Plugin $plugin, ServerCallable $task){
		if(!$this->validate($plugin, $task)){
			return null;
		}
		$future = new ServerFuture($task, $plugin, $this->nextId());
		$this->handle($future, 0);
		return $future;
	}

	/**
	 * @param int $taskId
	 */
	public function cancelTask($taskId){
		if($taskId < 0){
			return;
		}

		if(isset($this->runners[$taskId])){
			$this->runners[$taskId]->cancel0();
		}

		$task = new ServerTask(null, new ServerTaskCanceller($taskId));
		$this->handle($task, 0);
		for($taskPending = $this->head->getNext(); $taskPending !== null; $taskPending = $taskPending->getNext()){
			if($taskPending === $task){
				return;
			}
			if($taskPending->getTaskId() === $taskId){
				$taskPending->cancel0();
			}
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function cancelTasks(Plugin $plugin){
		if($plugin === null){
			return;
		}

		$task = new ServerTask(null, new ServerPluginTaskCanceller($plugin));
		$this->handle($task, 0);
		for($taskPending = $this->head->getNext(); $taskPending !== null; $taskPending = $taskPending->getNext()){
			if($taskPending === $task){
				return;
			}
			if($taskPending->getTaskId() !== -1 and $taskPending->getOwner() === $plugin){
				$taskPending->cancel0();
			}
		}

		foreach($this->runners as $runner){
			if($runner->getOwner() === $plugin){
				$runner->cancel0();
			}
		}
	}

	/**
	 *
	 */
	public function cancelAllTasks(){
		$task = new ServerTask(null, new ServerAllTaskCanceller());
		$this->handle($task, 0);
		for($taskPending = $this->head->getNext(); $taskPending !== null; $taskPending = $taskPending->getNext()){
			if($taskPending === $task){
				break;
			}
			$taskPending->cancel0();
		}

		foreach($this->runners as $runner){
			$runner->cancel0();
		}
	}

	/**
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function isCurrentlyRunning($taskId){
		if(!isset($this->runners[$taskId]) or $this->runners[$taskId]->isSync()){
			return false;
		}

		$asyncTask = $this->runners[$taskId];
		return $asyncTask->syncronized(function($asyncTask){
			return count($asyncTask->getWorkers()) === 0;
		}, $asyncTask);
	}

	/**
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function isQueued($taskId){
		if($taskId <= 0){
			return false;
		}

		for($task = $this->head->getNext(); $task !== null; $task = $task->getNext()){
			if($task->getTaskId() === $taskId){
				return $task->getPeriod() >= -1;
			}
		}

		if(isset($this->runners[$taskId])){
			return $this->runners[$taskId]->getPeriod() >= -1;
		}
		return false;
	}

	/**
	 * @return ServerWorker[]
	 */
	public function getActiveWorkers(){
		$workers = array();//new \Threaded();

		foreach($this->runners as $taskObj){
			if($taskObj->isSync()){
				continue;
			}

			$taskObj->syncronized(function($workers, $taskObj){
				foreach($taskObj->getWorkers() as $worker){
					$workers[] = $worker;
				}
			}, $workers, $taskObj);
		}

		//$workers->run(); //Protect against memory leaks
		return $workers;
	}

	/**
	 * @return ServerTask[]
	 */
	public function getPendingTasks(){
		$truePending = array();
		for($task = $this->head->getNext(); $task !== null; $task = $task->getNext()){
			if($task->getTaskId() !== -1){
				$truePending[] = $task;
			}
		}

		$pending = array();
		foreach($this->runners as $task){
			if($task->getPeriod() >= -1){
				$pending[] = $task;
			}
		}

		foreach($truePending as $task){
			if($task->getPeriod() >= -1 and !in_array($pending, $task, true)){
				$pending[] = $task;
			}
		}

		return $pending;
	}

	/**
	 * @param int $currentTick
	 */
	public function mainThreadHeartbeat($currentTick){
		$this->currentTick = $currentTick;
		$this->parsePending();
		while($this->isReady($currentTick)){
			$task = $this->pending->extract();
			if($task->getPeriod() < -1){
				if($task->isSync()){
					unset($this->runners[$task->getTaskId()]);
				}
				$this->parsePending();
				continue;
			}

			if($task->isSync()){
				$task->run();
			}else{
				$this->executor->submit($task);
			}

			$period = $task->getPeriod();
			if($period > 0){
				$task->setNextRun($currentTick + $period);
				$this->temp[] = $task;
			}elseif($task->isSync()){
				unset($this->runners[$task->getTaskId()]);
			}
		}

		foreach($this->temp as $task){
			$this->pending->insert($task, $task->getNextRun());
		}
		$this->temp = array();

	}

	/**
	 * @param ServerTask $task
	 */
	private function addTask(ServerTask $task){
		$this->tail->setNext($task);
	}

	/**
	 * @param ServerTask $task
	 * @param int        $delay
	 *
	 * @return ServerTask
	 */
	private function handle(ServerTask $task, $delay){
		$task->setNextRun($this->currentTick + $delay);
		$this->addTask($task);
		return $task;
	}

	/**
	 * @param Plugin $plugin
	 * @param \Threaded $task
	 *
	 * @return bool
	 */
	private function validate(Plugin $plugin, \Threaded $task){
		if($plugin === null or $task === null){
			return false;
		}elseif(!$plugin->isEnabled()){
			return false;
		}
		return true;
	}

	/**
	 * @return int
	 */
	private function nextId(){
		return $this->ids++;
	}

	private function parsePending(){
		$head = $this->head;
		$task = $head->getNext();
		$lastTask = $head;
		for(; $task !== null; $task = $lastTask->getNext()){
			if($task->getTaskId() === -1){
				$task->run();
			}elseif($task->getPeriod() >= -1){
				$this->pending[] = $task;
				$this->runners[$task->getTaskId()] = $task;
			}
			$lastTask = $task;
		}

		for($task = $head; $task !== $lastTask; $task = $head){
			$head = $task->getNext();
			$task->setNext(null);
		}
		$this->head = $lastTask;
	}

	/**
	 * @param int $currentTick
	 *
	 * @return bool
	 */
	private function isReady($currentTick){
		return $this->pending->count() > 0 and $this->pending->top()->getNextRun() <= $currentTick;
	}
}
