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

namespace pocketmine\event\server;

use pocketmine\utils\Process;

/**
 * Called when the server is in a low-memory state as defined by the properties
 * Plugins should free caches or other non-essential data.
 */
class LowMemoryEvent extends ServerEvent{
	public function __construct(
		private int $memory,
		private int $memoryLimit,
		private bool $isGlobal = false,
		private int $triggerCount = 0
	){}

	/**
	 * Returns the memory usage at the time of the event call (in bytes)
	 */
	public function getMemory() : int{
		return $this->memory;
	}

	/**
	 * Returns the memory limit defined (in bytes)
	 */
	public function getMemoryLimit() : int{
		return $this->memoryLimit;
	}

	/**
	 * Returns the times this event has been called in the current low-memory state
	 */
	public function getTriggerCount() : int{
		return $this->triggerCount;
	}

	public function isGlobal() : bool{
		return $this->isGlobal;
	}

	/**
	 * Amount of memory already freed
	 */
	public function getMemoryFreed() : int{
		$usage = Process::getAdvancedMemoryUsage();
		return $this->getMemory() - ($this->isGlobal() ? $usage[1] : $usage[0]);
	}
}
