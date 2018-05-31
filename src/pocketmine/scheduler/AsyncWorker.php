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

use pocketmine\Worker;

class AsyncWorker extends Worker{

	private $logger;
	private $id;

	/** @var int */
	private $memoryLimit;

	public function __construct(\ThreadedLogger $logger, int $id, int $memoryLimit){
		$this->logger = $logger;
		$this->id = $id;
		$this->memoryLimit = $memoryLimit;
	}

	public function run(){
		error_reporting(-1);
		set_error_handler('\pocketmine\error_handler');

		$this->registerClassLoader();
		gc_enable();

		if($this->memoryLimit > 0){
			ini_set('memory_limit', $this->memoryLimit . 'M');
			$this->logger->debug("Set memory limit to " . $this->memoryLimit . " MB");
		}else{
			ini_set('memory_limit', '-1');
			$this->logger->debug("No memory limit set");
		}

		global $store;
		$store = [];
	}

	public function handleException(\Throwable $e){
		$this->logger->logException($e);
	}

	public function getThreadName() : string{
		return "Asynchronous Worker #" . $this->id;
	}

	public function getAsyncWorkerId() : int{
		return $this->id;
	}
}
