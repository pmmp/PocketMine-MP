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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\NetworkSession;

class MoveEntityPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::MOVE_ENTITY_PACKET;

	public $eid;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $headYaw;
	public $pitch;
	public $onGround;
	public $teleported;

	public function decode(){
		$this->eid = $this->getEntityRuntimeId();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->pitch = $this->getByte() * (360.0 / 256);
		$this->headYaw = $this->getByte() * (360.0 / 256);
		$this->yaw = $this->getByte() * (360.0 / 256);
		$this->onGround = $this->getByte();
		$this->teleported = $this->getByte();
	}

	public function encode(){
		$this->reset();
		$this->putEntityRuntimeId($this->eid);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putByte($this->pitch / (360.0 / 256));
		$this->putByte($this->headYaw / (360.0 / 256));
		$this->putByte($this->yaw / (360.0 / 256));
		$this->putByte($this->onGround);
		$this->putByte($this->teleported);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleMoveEntity($this);
	}

}
