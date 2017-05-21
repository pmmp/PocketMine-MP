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

class SetEntityLinkPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::SET_ENTITY_LINK_PACKET;

	public $from;
	public $to;
	public $type;

	public function decode(){
		$this->from = $this->getEntityUniqueId();
		$this->to = $this->getEntityUniqueId();
		$this->type = $this->getByte();
	}

	public function encode(){
		$this->reset();
		$this->putEntityUniqueId($this->from);
		$this->putEntityUniqueId($this->to);
		$this->putByte($this->type);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleSetEntityLink($this);
	}

}
