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

class ServerTask extends \Threaded{
	/**
	 * @var ServerTask
	 */
	private $next = null;
	/**
	 * -1 means no repeating
	 * -2 means cancel
	 * -3 means processing for Future
	 * -4 means done for Future
	 * Never 0
	 * >0 means number of ticks to wait between each execution
	 *
	 * @var int
	 */
	private $period;
	private $nextRun;

	/**
	 * @var \Threaded;
	 */
	private $task;
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var int
	 */
	private $id;

	public function __construct(Plugin $plugin = null, \Threaded $task = null, $id = -1, $period = -1){
		$this->plugin = $plugin;
		$this->task = $task;
		$this->id = $id;
		$this->period = $period;
	}

	/**
	 * @return int
	 */
	public function getTaskId(){
		return $this->id;
	}

	/**
	 * @return Plugin
	 */
	public function getOwner(){
		return $this->plugin;
	}

	public function isSync(){
		return true;
	}

	public function run(){
		$this->task->run();
	}

	public function getPeriod(){
		return $this->period;
	}

	/**
	 * @param int $period
	 */
	public function setPeriod($period){
		$this->period = $period;
	}

	public function getNextRun(){
		return $this->nextRun;
	}

	public function setNextRun($nextRun){
		$this->nextRun = $nextRun;
	}

	/**
	 * @return ServerTask
	 */
	public function getNext(){
		return $this->next;
	}

	public function setNext(ServerTask $next){
		$this->next = $next;
	}

	public function getTaskClass(){
		return get_class($this->next);
	}

	public function cancel(){
		ServerScheduler::getInstance()->cancelTask($this->id);
	}

	public function cancel0(){
		$this->setPeriod(-2);
		return true;
	}


}