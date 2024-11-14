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

namespace pmmp\TesterPlugin;

use function time;

abstract class Test{
	const RESULT_WAITING = -1;
	const RESULT_OK = 0;
	const RESULT_FAILED = 1;
	const RESULT_ERROR = 2;

	/** @var Main */
	private $plugin;
	/** @var int */
	private $result = Test::RESULT_WAITING;
	/** @var int */
	private $startTime;
	/** @var int */
	private $timeout = 60; //seconds

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin() : Main{
		return $this->plugin;
	}

	final public function start() : void{
		$this->startTime = time();
		try{
			$this->run();
		}catch(TestFailedException $e){
			$this->getPlugin()->getLogger()->error($e->getMessage());
			$this->setResult(Test::RESULT_FAILED);
		}catch(\Throwable $e){
			$this->getPlugin()->getLogger()->logException($e);
			$this->setResult(Test::RESULT_ERROR);
		}
	}

	public function tick() : void{

	}

	abstract public function run() : void;

	public function isFinished() : bool{
		return $this->result !== Test::RESULT_WAITING;
	}

	public function isTimedOut() : bool{
		return !$this->isFinished() && time() - $this->timeout > $this->startTime;
	}

	protected function setTimeout(int $timeout) : void{
		$this->timeout = $timeout;
	}

	public function getResult() : int{
		return $this->result;
	}

	public function setResult(int $result) : void{
		$this->result = $result;
	}

	abstract public function getName() : string;

	abstract public function getDescription() : string;
}
