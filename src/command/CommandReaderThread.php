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

namespace pocketmine\command;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use pocketmine\thread\ThreadException;
use function microtime;
use function preg_replace;
use function usleep;

final class CommandReaderThread extends Thread{
	private \Threaded $buffer;
	private ?SleeperNotifier $notifier;

	public bool $shutdown = false;

	public function __construct(\Threaded $buffer, ?SleeperNotifier $notifier = null){
		$this->buffer = $buffer;
		$this->notifier = $notifier;
	}

	public function shutdown() : void{
		$this->shutdown = true;
	}

	public function quit() : void{
		$wait = microtime(true) + 0.5;
		while(microtime(true) < $wait){
			if($this->isRunning()){
				usleep(100000);
			}else{
				parent::quit();
				return;
			}
		}

		throw new ThreadException("CommandReader is stuck in a blocking STDIN read");
	}

	protected function onRun() : void{
		$buffer = $this->buffer;
		$notifier = $this->notifier;

		$reader = new CommandReader();
		while(!$this->shutdown){
			$line = $reader->readLine();

			if($line !== null){
				$buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
				if($notifier !== null){
					$notifier->wakeupSleeper();
				}
			}
		}
	}

	public function getThreadName() : string{
		return "Console";
	}
}
