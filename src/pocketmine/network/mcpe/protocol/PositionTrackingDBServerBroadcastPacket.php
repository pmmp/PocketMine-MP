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

use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;

class PositionTrackingDBServerBroadcastPacket extends DataPacket/* implements ClientboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::POSITION_TRACKING_D_B_SERVER_BROADCAST_PACKET;

	public const ACTION_UPDATE = 0;
	public const ACTION_DESTROY = 1;
	public const ACTION_NOT_FOUND = 2;

	/** @var int */
	private $action;
	/** @var int */
	private $trackingId;
	/** @var CompoundTag */
	private $nbt;

	public static function create(int $action, int $trackingId, CompoundTag $nbt) : self{
		$result = new self;
		$result->action = $action;
		$result->trackingId = $trackingId;
		$result->nbt = $nbt;
		return $result;
	}

	public function getAction() : int{ return $this->action; }

	public function getTrackingId() : int{ return $this->trackingId; }

	public function getNbt() : CompoundTag{ return $this->nbt; }

	protected function decodePayload() : void{
		$this->action = $this->getByte();
		$this->trackingId = $this->getVarInt();
		$offset = $this->getOffset();
		$nbt = (new NetworkLittleEndianNBTStream())->read($this->getBuffer(), false, $offset);
		$this->setOffset($offset);
		if(!($nbt instanceof CompoundTag)){
			throw new \UnexpectedValueException("Expected TAG_Compound");
		}
		$this->nbt = $nbt;
	}

	protected function encodePayload() : void{
		$this->putByte($this->action);
		$this->putVarInt($this->trackingId);
		$this->put((new NetworkLittleEndianNBTStream())->write($this->nbt));
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handlePositionTrackingDBServerBroadcast($this);
	}
}
