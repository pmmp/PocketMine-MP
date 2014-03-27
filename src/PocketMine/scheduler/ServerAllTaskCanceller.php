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

class ServerAllTaskCanceller extends \Threaded{

	/**
	 * @var ServerTask[]
	 */
	protected $temp;

	/**
	 * @var ServerTask[]
	 */
	protected $pending;

	/**
	 * @var ServerTask[]
	 */
	protected $runners;

	public function __construct($temp, $pending, $runners){
		$this->temp = $temp;
		$this->pending = $pending;
		$this->runners = $runners;
	}

	public function run(){
		foreach($this->runners as $index => $task){
			$task->cancel0();
			if($task->isSync()){
				unset($this->runners[$index]);
			}
		}
		while($this->temp->count() > 0){
			$this->temp->pop();
		}
		while($this->pending->count() > 0){
			$this->pending->pop();
		}
	}

	/**
	 * @param ServerTask[] $collection
	 *
	 * @return bool
	 */
	private function check($collection){
		foreach($collection as $index => $task){
			if($task->getTaskId() === $this->taskId){
				$task->cancel0();
				unset($collection[$index]);
				if($task->isSync()){
					unset($this->runners[$this->taskId]);
				}
				return true;
			}
		}
		return false;
	}

}