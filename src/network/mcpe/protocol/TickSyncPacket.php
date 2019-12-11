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

use pocketmine\network\mcpe\handler\PacketHandler;

class TickSyncPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::TICK_SYNC_PACKET;

	/** @var int */
	private $clientSendTime;
	/** @var int */
	private $serverReceiveTime;

	public static function request(int $clientTime) : self{
		$result = new self;
		$result->clientSendTime = $clientTime;
		$result->serverReceiveTime = 0; //useless
		return $result;
	}

	public static function response(int $clientSendTime, int $serverReceiveTime) : self{
		$result = new self;
		$result->clientSendTime = $clientSendTime;
		$result->serverReceiveTime = $serverReceiveTime;
		return $result;
	}

	public function getClientSendTime() : int{
		return $this->clientSendTime;
	}

	public function getServerReceiveTime() : int{
		return $this->serverReceiveTime;
	}

	protected function decodePayload() : void{
		$this->clientSendTime = $this->getLLong();
		$this->serverReceiveTime = $this->getLLong();
	}

	protected function encodePayload() : void{
		$this->putLLong($this->clientSendTime);
		$this->putLLong($this->serverReceiveTime);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleTickSync($this);
	}
}
