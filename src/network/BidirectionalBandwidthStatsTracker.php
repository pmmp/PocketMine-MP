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

namespace pocketmine\network;

final class BidirectionalBandwidthStatsTracker{

	/** @var BandwidthStatsTracker */
	private $send;

	/** @var BandwidthStatsTracker */
	private $receive;

	public function __construct(int $historySize){
		$this->send = new BandwidthStatsTracker($historySize);
		$this->receive = new BandwidthStatsTracker($historySize);
	}

	public function getSend() : BandwidthStatsTracker{ return $this->send; }

	public function getReceive() : BandwidthStatsTracker{ return $this->receive; }

	public function add(int $sendBytes, int $recvBytes) : void{
		$this->send->add($sendBytes);
		$this->receive->add($recvBytes);
	}

	/** @see BandwidthStatsTracker::rotateHistory() */
	public function rotateAverageHistory() : void{
		$this->send->rotateHistory();
		$this->receive->rotateHistory();
	}

	/** @see BandwidthStatsTracker::resetHistory() */
	public function resetHistory() : void{
		$this->send->resetHistory();
		$this->receive->resetHistory();
	}
}
