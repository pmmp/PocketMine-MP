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

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\network\mcpe\NetworkSession;

/**
 * Called before a packet is decoded and handled by the network session.
 * Cancelling this event will drop the packet without decoding it, minimizing wasted CPU time.
 */
class DataPacketDecodeEvent extends ServerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		private NetworkSession $origin,
		private int $packetId,
		private string $packetBuffer
	){}

	public function getOrigin() : NetworkSession{
		return $this->origin;
	}

	public function getPacketId() : int{
		return $this->packetId;
	}

	public function getPacketBuffer() : string{
		return $this->packetBuffer;
	}
}
