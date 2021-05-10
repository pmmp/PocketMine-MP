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

namespace pocketmine\network\mcpe\raklib;

use pocketmine\snooze\SleeperNotifier;
use raklib\server\ipc\InterThreadChannelFactory;
use function serialize;

final class PthreadsChannelFactory implements InterThreadChannelFactory{

	private ?SleeperNotifier $notifier;

	public function __construct(?SleeperNotifier $notifier){
		$this->notifier = $notifier;
	}

	public function createChannel() : array{
		$buffer = new \Threaded();
		return [serialize($buffer), new PthreadsChannelWriter($buffer, $this->notifier)];
	}
}
