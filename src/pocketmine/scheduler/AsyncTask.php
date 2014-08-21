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

/**
 * Class used to run async tasks in other threads.
 *
 * WARNING: Do not call PocketMine-MP API methods from other Threads!!
 */
abstract class AsyncTask extends \Threaded{

	protected $complete = null;
	protected $finished = null;
	protected $result = null;
	protected $taskId = null;

	public function run(){
		$this->finished = false;
		$this->complete = false;
		$this->result = null;

		$this->onRun();

		$this->finished = true;

	}

	/**
	 * @return bool
	 */
	public function isFinished(){
		return $this->finished === true;
	}

	/**
	 * @return bool
	 */
	public function isCompleted(){
		return $this->complete === true;
	}

	public function setCompleted(){
		$this->complete = true;
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		return @unserialize($this->result);
	}

	/**
	 * @return bool
	 */
	public function hasResult(){
		return $this->result !== null;
	}

	/**
	 * @param mixed $result
	 */
	public function setResult($result){
		$this->result = @serialize($result);
	}

	public function setTaskId($taskId){
		$this->taskId = $taskId;
	}

	public function getTaskId(){
		return $this->taskId;
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public abstract function onRun();

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 *
	 * @param Server $server
	 *
	 * @return void
	 */
	public function onCompletion(Server $server){

	}

}
