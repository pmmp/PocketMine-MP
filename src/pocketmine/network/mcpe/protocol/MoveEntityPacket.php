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

class MoveEntityPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::MOVE_ENTITY_PACKET;

	public $entityRuntimeId;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $headYaw;
	public $pitch;
	public $onGround = false;
	public $teleported = false;

	public function decode(){
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->pitch = $this->getByteRotation();
		$this->headYaw = $this->getByteRotation();
		$this->yaw = $this->getByteRotation();
		$this->onGround = $this->getBool();
		$this->teleported = $this->getBool();
	}

	public function encode(){
		$this->reset();
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putByteRotation($this->pitch);
		$this->putByteRotation($this->headYaw);
		$this->putByteRotation($this->yaw);
		$this->putBool($this->onGround);
		$this->putBool($this->teleported);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleMoveEntity($this);
	}

}
