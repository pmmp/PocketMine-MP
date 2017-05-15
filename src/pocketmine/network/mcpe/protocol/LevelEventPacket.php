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

class LevelEventPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::LEVEL_EVENT_PACKET;

	const EVENT_SOUND_CLICK = 1000;
	const EVENT_SOUND_CLICK_FAIL = 1001;
	const EVENT_SOUND_SHOOT = 1002;
	const EVENT_SOUND_DOOR = 1003;
	const EVENT_SOUND_FIZZ = 1004;
	const EVENT_SOUND_IGNITE = 1005;

	const EVENT_SOUND_GHAST = 1007;
	const EVENT_SOUND_GHAST_SHOOT = 1008;
	const EVENT_SOUND_BLAZE_SHOOT = 1009;
	const EVENT_SOUND_DOOR_BUMP = 1010;

	const EVENT_SOUND_DOOR_CRASH = 1012;

	const EVENT_SOUND_ENDERMAN_TELEPORT = 1018;

	const EVENT_SOUND_ANVIL_BREAK = 1020;
	const EVENT_SOUND_ANVIL_USE = 1021;
	const EVENT_SOUND_ANVIL_FALL = 1022;

	const EVENT_SOUND_POP = 1030;

	const EVENT_SOUND_PORTAL = 1032;

	const EVENT_SOUND_ITEMFRAME_ADD_ITEM = 1040;
	const EVENT_SOUND_ITEMFRAME_REMOVE = 1041;
	const EVENT_SOUND_ITEMFRAME_PLACE = 1042;
	const EVENT_SOUND_ITEMFRAME_REMOVE_ITEM = 1043;
	const EVENT_SOUND_ITEMFRAME_ROTATE_ITEM = 1044;

	const EVENT_SOUND_CAMERA = 1050;
	const EVENT_SOUND_ORB = 1051;

	const EVENT_PARTICLE_SHOOT = 2000;
	const EVENT_PARTICLE_DESTROY = 2001;
	const EVENT_PARTICLE_SPLASH = 2002;
	const EVENT_PARTICLE_EYE_DESPAWN = 2003;
	const EVENT_PARTICLE_SPAWN = 2004;

	const EVENT_GUARDIAN_CURSE = 2006;

	const EVENT_PARTICLE_BLOCK_FORCE_FIELD = 2008;

	const EVENT_PARTICLE_PUNCH_BLOCK = 2014;

	const EVENT_START_RAIN = 3001;
	const EVENT_START_THUNDER = 3002;
	const EVENT_STOP_RAIN = 3003;
	const EVENT_STOP_THUNDER = 3004;

	const EVENT_REDSTONE_TRIGGER = 3500;
	const EVENT_CAULDRON_EXPLODE = 3501;
	const EVENT_CAULDRON_DYE_ARMOR = 3502;
	const EVENT_CAULDRON_CLEAN_ARMOR = 3503;
	const EVENT_CAULDRON_FILL_POTION = 3504;
	const EVENT_CAULDRON_TAKE_POTION = 3505;
	const EVENT_CAULDRON_FILL_WATER = 3506;
	const EVENT_CAULDRON_TAKE_WATER = 3507;
	const EVENT_CAULDRON_ADD_DYE = 3508;

	const EVENT_BLOCK_START_BREAK = 3600;
	const EVENT_BLOCK_STOP_BREAK = 3601;

	const EVENT_SET_DATA = 4000;

	const EVENT_PLAYERS_SLEEPING = 9800;

	const EVENT_ADD_PARTICLE_MASK = 0x4000;

	public $evid;
	public $x = 0; //Weather effects don't have coordinates
	public $y = 0;
	public $z = 0;
	public $data;

	public function decode(){
		$this->evid = $this->getVarInt();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->data = $this->getVarInt();
	}

	public function encode(){
		$this->reset();
		$this->putVarInt($this->evid);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putVarInt($this->data);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLevelEvent($this);
	}

}
