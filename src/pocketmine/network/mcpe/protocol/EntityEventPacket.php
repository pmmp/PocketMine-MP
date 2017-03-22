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

class EntityEventPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::ENTITY_EVENT_PACKET;

	const HURT_ANIMATION = 2;
	const DEATH_ANIMATION = 3;

	const TAME_FAIL = 6;
	const TAME_SUCCESS = 7;
	const SHAKE_WET = 8;
	const USE_ITEM = 9;
	const EAT_GRASS_ANIMATION = 10;
	const FISH_HOOK_BUBBLE = 11;
	const FISH_HOOK_POSITION = 12;
	const FISH_HOOK_HOOK = 13;
	const FISH_HOOK_TEASE = 14;
	const SQUID_INK_CLOUD = 15;
	const AMBIENT_SOUND = 16;

	const RESPAWN = 18;

	//TODO: add more events

	public $eid;
	public $event;
	public $unknown;

	public function decode(){
		$this->eid = $this->getEntityRuntimeId();
		$this->event = $this->getByte();
		$this->unknown = $this->getVarInt();
	}

	public function encode(){
		$this->reset();
		$this->putEntityRuntimeId($this->eid);
		$this->putByte($this->event);
		$this->putVarInt($this->unknown);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleEntityEvent($this);
	}

}
