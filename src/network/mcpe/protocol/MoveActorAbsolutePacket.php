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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\handler\PacketHandler;

class MoveActorAbsolutePacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_ABSOLUTE_PACKET;

	public const FLAG_GROUND = 0x01;
	public const FLAG_TELEPORT = 0x02;

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $flags = 0;
	/** @var Vector3 */
	public $position;
	/** @var float */
	public $xRot;
	/** @var float */
	public $yRot;
	/** @var float */
	public $zRot;

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->buf->getEntityRuntimeId();
		$this->flags = $this->buf->getByte();
		$this->position = $this->buf->getVector3();
		$this->xRot = $this->buf->getByteRotation();
		$this->yRot = $this->buf->getByteRotation();
		$this->zRot = $this->buf->getByteRotation();
	}

	protected function encodePayload() : void{
		$this->buf->putEntityRuntimeId($this->entityRuntimeId);
		$this->buf->putByte($this->flags);
		$this->buf->putVector3($this->position);
		$this->buf->putByteRotation($this->xRot);
		$this->buf->putByteRotation($this->yRot);
		$this->buf->putByteRotation($this->zRot);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleMoveActorAbsolute($this);
	}
}
