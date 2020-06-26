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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

class PositionTrackingDBServerBroadcastPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::POSITION_TRACKING_D_B_SERVER_BROADCAST_PACKET;

	public const ACTION_UPDATE = 0;
	public const ACTION_DESTROY = 1;
	public const ACTION_NOT_FOUND = 2;

	/** @var int */
	private $action;
	/** @var int */
	private $trackingId;
	/**
	 * @var CacheableNbt
	 * @phpstan-var CacheableNbt<\pocketmine\nbt\tag\CompoundTag>
	 */
	private $nbt;

	/**
	 * @phpstan-param CacheableNbt<\pocketmine\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(int $action, int $trackingId, CacheableNbt $nbt) : self{
		$result = new self;
		$result->action = $action;
		$result->trackingId = $trackingId;
		$result->nbt = $nbt;
		return $result;
	}

	public function getAction() : int{ return $this->action; }

	public function getTrackingId() : int{ return $this->trackingId; }

	/** @phpstan-return CacheableNbt<\pocketmine\nbt\tag\CompoundTag> */
	public function getNbt() : CacheableNbt{ return $this->nbt; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->action = $in->getByte();
		$this->trackingId = $in->getVarInt();
		$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->action);
		$out->putVarInt($this->trackingId);
		$out->put($this->nbt->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePositionTrackingDBServerBroadcast($this);
	}
}
