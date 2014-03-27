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

class ServerFuture extends ServerTask{

	/**
	 * @var ServerCallable
	 */
	private $callable;

	/**
	 * @var mixed
	 */
	private $value = null;

	/**
	 * @param ServerCallable $callable
	 * @param Plugin         $plugin
	 * @param int            $id
	 */
	public function __construct(ServerCallable $callable, Plugin $plugin, $id){
		parent::__construct($plugin, null, $id);
		$this->callable = $callable;
	}

	public function cancel(){
		if($this->getPeriod() !== -1){
			return false;
		}
		$this->setPeriod(-2);
		return true;
	}

	public function isCancelled(){
		return $this->getPeriod() === -2;
	}

	public function isDone(){
		return $this->getPeriod() !== -1 and $this->getPeriod() !== -3;
	}

	/**
	 * @param int $timeout Microseconds to wait
	 *
	 * @return mixed|null
	 */
	public function get($timeout){
		$period = $this->getPeriod();
		$timestamp = $timeout > 0 ? (int) (microtime(true) / 1000000) : 0;

		while(true){
			if($period === -1 or $period === -3){
				$this->wait($timeout);
				$period = $this->getPeriod();
				if($period === -1 or $period === -3){
					if($timeout === 0){
						continue;
					}
					$timeout += $timestamp - ($timestamp = (int) (microtime(true) / 1000000));

					if($timeout > 0){
						continue;
					}
					return null;
				}
			}
			if($period === -2){
				return null; //Cancelled
			}
			if($period === -4){
				return $this->value;
			}
			return null; //Invalid state
		}
	}

	public function run(){
		if($this->synchronized(function(ServerFuture $future){
			if($future->getPeriod() === -2){
				return false;
			}
			$future->setPeriod(-3);
			return true;
		}, $this) === false){
			return;
		}

		$this->callable->run();
		$this->value = $this->callable->getResult();

		$this->synchronized(function(ServerFuture $future){
			$future->setPeriod(-4);
			$future->notify();
		}, $this);
	}

	public function cancel0(){
		if($this->getPeriod() !== -1){
			return false;
		}
		$this->setPeriod(-2);
		$this->notify();
		return true;
	}
}