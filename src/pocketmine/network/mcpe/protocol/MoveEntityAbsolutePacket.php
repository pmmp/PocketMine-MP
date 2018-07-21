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
use pocketmine\network\mcpe\handler\SessionHandler;

class MoveEntityAbsolutePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_ENTITY_ABSOLUTE_PACKET;

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
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->flags = $this->getByte();
		$this->position = $this->getVector3();
		$this->xRot = $this->getByteRotation();
		$this->yRot = $this->getByteRotation();
		$this->zRot = $this->getByteRotation();
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->flags);
		$this->putVector3($this->position);
		$this->putByteRotation($this->xRot);
		$this->putByteRotation($this->yRot);
		$this->putByteRotation($this->zRot);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleMoveEntityAbsolute($this);
	}
}
