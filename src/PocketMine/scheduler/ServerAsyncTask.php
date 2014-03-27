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

class ServerAsyncTask extends ServerTask{

	/**
	 * @var \Threaded<ServerWorker>
	 */
	private $workers;

	/**
	 * @var \Threaded<ServerTask>
	 */
	private $runners;


	public function __construct(\Threaded $runners, Plugin $plugin, \Threaded $task, $id, $delay){
		parent::__construct($plugin, $task, $id, $delay);
		$this->runners = $runners;
		$this->workers = new \Threaded();
	}

	public function isSync(){
		return false;
	}

	public function run(){
		$thread = \Thread::getCurrentThread();
		$this->workers->synchronized(function($workers, \Thread $thread, ServerAsyncTask $asyncTask){
			if($asyncTask->getPeriod() === -2){
				return;
			}
			$workers[] = new ServerWorker($asyncTask, $thread);
		}, $thread, $this);

		parent::run();

		$this->workers->synchronized(function(\Threaded $workers, \Threaded $runners, \Thread $thread){
			$removed = false;
			foreach($workers as $index => $worker){
				if($worker->getThread() === $thread){
					unset($workers[$index]);
					$removed = true;
					break;
				}
			}

			if(!$removed){
				trigger_error("Unable to remove worker ".$thread->getThreadId()." on task ".$this->getTaskId()." for ".$this->getOwner()->getDescription()->getName(), E_USER_WARNING);
			}

			if($this->getPeriod() < 0 and $this->workers->count() === 0){
				unset($runners[$this->getTaskId()]);
			}
		}, $this->workers, $this->runners, $thread);
	}

	/**
	 * @return \Threaded
	 */
	public function getWorkers(){
		return $this->workers;
	}

	/**
	 * @return bool
	 */
	public function cancel0(){
		$this->workers->synchronized(function(ServerAsyncTask $asyncTask, \Threaded $runners, \Threaded $workers){
			$asyncTask->setPeriod(-2);
			if($workers->count() === 0){
				unset($runners[$asyncTask->getTaskId()]);
			}
		}, $this, $this->runners, $this->workers);
		return true;
	}


}