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

use PocketMine\Plugin\Plugin;

/**
 * Class to easily handle scheduling tasks
 *
 * WARNING: Asynchronous task must never access any method in the API.
 */
class ServerRunnable extends \Threaded{
	private $taskId = -1;

	/**
	 * @return void
	 */
	public function cancel(){
		ServerScheduler::getInstance()->cancelTask($this->getTaskId());
	}

	/**
	 * Runs the task on the next tick
	 *
	 * @param Plugin $plugin
	 *
	 * @return ServerTask|null
	 */
	public function runTask(Plugin $plugin){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTask($plugin, $this));
		}
		return null;
	}

	/**
	 * Schedules the task to run asynchronously
	 *
	 * @param Plugin $plugin
	 *
	 * @return ServerTask|null
	 */
	public function runTaskAsynchronously(Plugin $plugin){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTaskAsynchronously($plugin, $this));
		}
		return null;
	}

	/**
	 * Runs the task after a number of server ticks
	 *
	 * @param Plugin $plugin
	 * @param int    $delay
	 *
	 * @return ServerTask|null
	 */
	public function runTaskLater(Plugin $plugin, $delay){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTaskLater($plugin, $this, $delay));
		}
		return null;
	}

	/**
	 * @param Plugin $plugin
	 * @param int    $delay
	 *
	 * @return ServerTask|null
	 */
	public function runTaskLaterAsynchronously(Plugin $plugin, $delay){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTaskLaterAsynchronously($plugin, $this, $delay));
		}
		return null;
	}

	/**
	 * @param Plugin $plugin
	 * @param int    $delay
	 * @param int    $period
	 *
	 * @return ServerTask|null
	 */
	public function runTaskTimer(Plugin $plugin, $delay, $period){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTaskTimer($plugin, $this, $delay, $period));
		}
		return null;
	}

	/**
	 * @param Plugin $plugin
	 * @param int    $delay
	 * @param int    $period
	 *
	 * @return ServerTask|null
	 */
	public function runTaskTimerAsynchronously(Plugin $plugin, $delay, $period){
		if($this->checkState()){
			return $this->setupId(ServerScheduler::getInstance()->runTaskTimerAsynchronously($plugin, $this, $delay, $period));
		}
		return null;
	}

	/**
	 * @return int
	 */
	public function getTaskId(){
		if($this->taskId === -1){
			return -1;
		}
		return $this->taskId;
	}


	public function checkState(){
		if($this->taskId !== -1){
			return false;
		}
		return true;
	}

	public function setupId(ServerTask $task){
		if($task !== null){
			$this->taskId = $task->getTaskId();
		}
		return $task;
	}


}