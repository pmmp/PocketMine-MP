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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;

class SimulationTypePacket extends DataPacket/* implements ClientboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::SIMULATION_TYPE_PACKET;

	public const GAME = 0;
	public const EDITOR = 1;
	public const TEST = 2;

	private int $type;

	public static function create(int $type) : self{
		$result = new self;
		$result->type = $type;
		return $result;
	}

	public function getType() : int{ return $this->type; }

	protected function decodePayload() : void{
		$this->type = $this->getByte();
	}

	protected function encodePayload() : void{
		$this->putByte($this->type);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleSimulationType($this);
	}
}
