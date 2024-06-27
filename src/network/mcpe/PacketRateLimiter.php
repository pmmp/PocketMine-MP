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

namespace pocketmine\network\mcpe;

use pocketmine\network\PacketHandlingException;
use function hrtime;
use function intdiv;
use function min;

final class PacketRateLimiter{
	/**
	 * At most this many more packets can be received. If this reaches zero, any additional packets received will cause
	 * the player to be kicked from the server.
	 * This number is increased every tick up to a maximum limit, and decreased by one every time a packet is received.
	 */
	private int $budget;
	private int $lastUpdateTimeNs;
	private int $maxBudget;

	public function __construct(
		private string $name,
		private int $averagePerTick,
		int $maxBufferTicks,
		private int $updateFrequencyNs = 50_000_000,
	){
		$this->maxBudget = $this->averagePerTick * $maxBufferTicks;
		$this->budget = $this->maxBudget;
		$this->lastUpdateTimeNs = hrtime(true);
	}

	/**
	 * @throws PacketHandlingException if the rate limit has been exceeded
	 */
	public function decrement(int $amount = 1) : void{
		if($this->budget <= 0){
			$this->update();
			if($this->budget <= 0){
				throw new PacketHandlingException("Exceeded rate limit for \"$this->name\"");
			}
		}
		$this->budget -= $amount;
	}

	public function update() : void{
		$nowNs = hrtime(true);
		$timeSinceLastUpdateNs = $nowNs - $this->lastUpdateTimeNs;
		if($timeSinceLastUpdateNs > $this->updateFrequencyNs){
			$ticksSinceLastUpdate = intdiv($timeSinceLastUpdateNs, $this->updateFrequencyNs);
			/*
			 * If the server takes an abnormally long time to process a tick, add the budget for time difference to
			 * compensate. This extra budget may be very large, but it will disappear the next time a normal update
			 * occurs. This ensures that backlogs during a large lag spike don't cause everyone to get kicked.
			 * As long as all the backlogged packets are processed before the next tick, everything should be OK for
			 * clients behaving normally.
			 */
			$this->budget = min($this->budget, $this->maxBudget) + ($this->averagePerTick * 2 * $ticksSinceLastUpdate);
			$this->lastUpdateTimeNs = $nowNs;
		}
	}
}
