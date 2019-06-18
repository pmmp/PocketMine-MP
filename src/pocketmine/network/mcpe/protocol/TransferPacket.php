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

class TransferPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::TRANSFER_PACKET;

	/** @var string */
	public $address;
	/** @var int */
	public $port = 19132;

	public static function create(string $address, int $port) : self{
		$result = new self;
		$result->address = $address;
		$result->port = $port;
		return $result;
	}

	protected function decodePayload() : void{
		$this->address = $this->getString();
		$this->port = $this->getLShort();
	}

	protected function encodePayload() : void{
		$this->putString($this->address);
		$this->putLShort($this->port);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleTransfer($this);
	}
}
