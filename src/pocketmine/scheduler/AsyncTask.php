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

/**
 * Class used to run async tasks in other threads.
 *
 * WARNING: Do not call PocketMine-MP API methods from other Threads!!
 */
abstract class AsyncTask extends \Threaded{

	private $complete;
	private $finished;
	private $result;

	public function run(){
		$this->lock();
		$this->result = null;
		$this->onRun();
		$this->finished = true;
		$this->complete = $this->result === null ? true : false;
		$this->unlock();
	}

	/**
	 * @return bool
	 */
	public function isCompleted(){
		return $this->complete === true;
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		return $this->synchronized(function(){
			$this->finished = true;
			return @unserialize($this->result);
		});
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

	/**
	 * @return bool
	 */
	public function isFinished(){
		return $this->finished === true;
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public abstract function onRun();

}
