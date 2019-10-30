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
use pocketmine\network\mcpe\NetworkSession;

class RespawnPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::RESPAWN_PACKET;

    public const STATE_SPAWN = 1;
    public const STATE_SELECT_SPAWN = 2;
    public const STATE_SELECTED_SPAWN = 3;

	/** @var Vector3 */
	public $position;
	/** @var  int */
    public $respawnState = self::STATE_SPAWN;
    /** @var  int */
    public $unknownEntityId; //???

	protected function decodePayload(){
		$this->position = $this->getVector3();
		$this->respawnState = $this->getByte();
		$this->unknownEntityId = $this->getEntityUniqueId();
	}

	protected function encodePayload(){
		$this->putVector3($this->position);
		$this->putByte($this->respawnState);
		$this->putEntityUniqueId($this->unknownEntityId);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleRespawn($this);
	}
}
