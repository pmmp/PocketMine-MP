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

namespace pocketmine\utils;

use pocketmine\thread\Thread;
use function time;

class ServerKiller extends Thread{

	/** @var int */
	public $time;

	private bool $stopped = false;

	/**
	 * @param int $time
	 */
	public function __construct($time = 15){
		$this->time = $time;
	}

	protected function onRun() : void{
		$start = time();
		$this->synchronized(function() : void{
			if(!$this->stopped){
				$this->wait($this->time * 1000000);
			}
		});
		if(time() - $start >= $this->time){
			echo "\nTook too long to stop, server was killed forcefully!\n";
			@Process::kill(Process::pid(), true);
		}
	}

	public function quit() : void{
		$this->synchronized(function() : void{
			$this->stopped = true;
			$this->notify();
		});
		parent::quit();
	}

	public function getThreadName() : string{
		return "Server Killer";
	}
}
