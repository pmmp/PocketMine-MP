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

class ServerPluginTaskCanceller extends \Threaded{

	/**
	 * @var Plugin
	 */
	public $plugin;

	/**
	 * @var ServerTask[]
	 */
	public $temp;

	/**
	 * @var ServerTask[]
	 */
	public $pending;

	/**
	 * @var ServerTask[]
	 */
	public $runners;

	public function __construct(Plugin $plugin, $temp, $pending, $runners){
		$this->plugin = $plugin;
		$this->temp = $temp;
		$this->pending = $pending;
		$this->runners = $runners;
	}

	public function run(){
		$this->check($this->temp);
		$this->check($this->pending);
	}

	/**
	 * @param ServerTask[] $collection
	 *
	 * @return bool
	 */
	private function check($collection){
		foreach($collection as $index => $task){
			if($task->getOwner() === $this->plugin){
				$task->cancel0();
				unset($collection[$index]);
				if($task->isSync()){
					unset($this->runners[$task->getTaskId()]);
				}
				return true;
			}
		}
		return false;
	}

}