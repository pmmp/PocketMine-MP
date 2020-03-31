<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\raklib;

use pocketmine\snooze\SleeperNotifier;
use raklib\server\ipc\InterThreadChannelWriter;

final class PthreadsChannelWriter implements InterThreadChannelWriter{
	/** @var \Threaded */
	private $buffer;
	/** @var SleeperNotifier|null */
	private $notifier;

	public function __construct(\Threaded $buffer, ?SleeperNotifier $notifier = null){
		$this->buffer = $buffer;
		$this->notifier = $notifier;
	}

	public function write(string $str) : void{
		$this->buffer[] = $str;
		if($this->notifier !== null){
			$this->notifier->wakeupSleeper();
		}
	}
}
