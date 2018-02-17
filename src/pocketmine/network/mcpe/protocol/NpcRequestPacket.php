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

class NpcRequestPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::NPC_REQUEST_PACKET;

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $requestType;
	/** @var string */
	public $commandString;
	/** @var int */
	public $actionType;

	protected function decodePayload(){
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->requestType = $this->getByte();
		$this->commandString = $this->getString();
		$this->actionType = $this->getByte();
	}

	protected function encodePayload(){
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->requestType);
		$this->putString($this->commandString);
		$this->putByte($this->actionType);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleNpcRequest($this);
	}
}
