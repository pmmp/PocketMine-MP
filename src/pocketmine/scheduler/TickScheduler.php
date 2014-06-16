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

namespace pocketmine\scheduler;

class TickScheduler extends \Thread{
	protected $sleepTime;
	protected $ticksPerSecond;
	protected $tickMeasure;
	public $hasTick;

	public function __construct($ticksPerSecond = 20){
		$this->ticksPerSecond = (int) $ticksPerSecond;
		$this->sleepTime = (int) (1000000 / $this->ticksPerSecond);
		$this->tickMeasure = $this->sleepTime;
		$this->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
	}

	/**
	 * Returns true if clear to run tick
	 *
	 * @return bool
	 */
	public function hasTick(){
		return $this->synchronized(function (){
			$hasTick = $this->hasTick;
			$this->hasTick = false;

			return $hasTick === true;
		});
	}

	public function doTick(){
		$this->notify();
	}

	/**
	 * @return float
	 */
	public function getTPS(){
		return $this->synchronized(function (){
			return round(($this->sleepTime / $this->tickMeasure) * $this->ticksPerSecond, 2);
		});
	}

	public function run(){
		$tickTime = microtime(true);
		$this->tickMeasure = $this->sleepTime;
		while(true){
			$this->synchronized(function (){
				$this->hasTick = true;
				$this->wait();
				$this->hasTick = false;
			});

			$this->tickMeasure = (int) ((($time = microtime(true)) - $tickTime) * 1000000);
			$tickTime = $time;
			$sleepTime = $this->sleepTime * ($this->sleepTime / max($this->sleepTime, $this->tickMeasure));
			usleep((int) $sleepTime);
		}
	}
}