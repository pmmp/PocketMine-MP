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

namespace pocketmine;

use pocketmine\snooze\SleeperHandler;
use pocketmine\timings\TimingsHandler;
use function hrtime;

/**
 * Custom Snooze sleeper handler which captures notification processing time.
 * @internal
 */
final class TimeTrackingSleeperHandler extends SleeperHandler{

	private int $notificationProcessingTimeNs = 0;

	public function __construct(
		private TimingsHandler $timings
	){
		parent::__construct();
	}

	/**
	 * Returns the time in nanoseconds spent processing notifications since the last reset.
	 */
	public function getNotificationProcessingTime() : int{ return $this->notificationProcessingTimeNs; }

	/**
	 * Resets the notification processing time tracker to zero.
	 */
	public function resetNotificationProcessingTime() : void{ $this->notificationProcessingTimeNs = 0; }

	public function processNotifications() : void{
		$startTime = hrtime(true);
		$this->timings->startTiming();
		try{
			parent::processNotifications();
		}finally{
			$this->notificationProcessingTimeNs += hrtime(true) - $startTime;
			$this->timings->stopTiming();
		}
	}
}
