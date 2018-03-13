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

class LevelEventPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::LEVEL_EVENT_PACKET;

	public const EVENT_SOUND_CLICK = 1000;
	public const EVENT_SOUND_CLICK_FAIL = 1001;
	public const EVENT_SOUND_SHOOT = 1002;
	public const EVENT_SOUND_DOOR = 1003;
	public const EVENT_SOUND_FIZZ = 1004;
	public const EVENT_SOUND_IGNITE = 1005;

	public const EVENT_SOUND_GHAST = 1007;
	public const EVENT_SOUND_GHAST_SHOOT = 1008;
	public const EVENT_SOUND_BLAZE_SHOOT = 1009;
	public const EVENT_SOUND_DOOR_BUMP = 1010;

	public const EVENT_SOUND_DOOR_CRASH = 1012;

	public const EVENT_SOUND_ENDERMAN_TELEPORT = 1018;

	public const EVENT_SOUND_ANVIL_BREAK = 1020;
	public const EVENT_SOUND_ANVIL_USE = 1021;
	public const EVENT_SOUND_ANVIL_FALL = 1022;

	public const EVENT_SOUND_POP = 1030;

	public const EVENT_SOUND_PORTAL = 1032;

	public const EVENT_SOUND_ITEMFRAME_ADD_ITEM = 1040;
	public const EVENT_SOUND_ITEMFRAME_REMOVE = 1041;
	public const EVENT_SOUND_ITEMFRAME_PLACE = 1042;
	public const EVENT_SOUND_ITEMFRAME_REMOVE_ITEM = 1043;
	public const EVENT_SOUND_ITEMFRAME_ROTATE_ITEM = 1044;

	public const EVENT_SOUND_CAMERA = 1050;
	public const EVENT_SOUND_ORB = 1051;
	public const EVENT_SOUND_TOTEM = 1052;

	public const EVENT_SOUND_ARMOR_STAND_BREAK = 1060;
	public const EVENT_SOUND_ARMOR_STAND_HIT = 1061;
	public const EVENT_SOUND_ARMOR_STAND_FALL = 1062;
	public const EVENT_SOUND_ARMOR_STAND_PLACE = 1063;

	//TODO: check 2000-2017
	public const EVENT_PARTICLE_SHOOT = 2000;
	public const EVENT_PARTICLE_DESTROY = 2001;
	public const EVENT_PARTICLE_SPLASH = 2002;
	public const EVENT_PARTICLE_EYE_DESPAWN = 2003;
	public const EVENT_PARTICLE_SPAWN = 2004;

	public const EVENT_GUARDIAN_CURSE = 2006;

	public const EVENT_PARTICLE_BLOCK_FORCE_FIELD = 2008;
	public const EVENT_PARTICLE_PROJECTILE_HIT = 2009;

	public const EVENT_PARTICLE_ENDERMAN_TELEPORT = 2013;
	public const EVENT_PARTICLE_PUNCH_BLOCK = 2014;

	public const EVENT_START_RAIN = 3001;
	public const EVENT_START_THUNDER = 3002;
	public const EVENT_STOP_RAIN = 3003;
	public const EVENT_STOP_THUNDER = 3004;
	public const EVENT_PAUSE_GAME = 3005; //data: 1 to pause, 0 to resume

	public const EVENT_REDSTONE_TRIGGER = 3500;
	public const EVENT_CAULDRON_EXPLODE = 3501;
	public const EVENT_CAULDRON_DYE_ARMOR = 3502;
	public const EVENT_CAULDRON_CLEAN_ARMOR = 3503;
	public const EVENT_CAULDRON_FILL_POTION = 3504;
	public const EVENT_CAULDRON_TAKE_POTION = 3505;
	public const EVENT_CAULDRON_FILL_WATER = 3506;
	public const EVENT_CAULDRON_TAKE_WATER = 3507;
	public const EVENT_CAULDRON_ADD_DYE = 3508;
	public const EVENT_CAULDRON_CLEAN_BANNER = 3509;

	public const EVENT_BLOCK_START_BREAK = 3600;
	public const EVENT_BLOCK_STOP_BREAK = 3601;

	public const EVENT_SET_DATA = 4000;

	public const EVENT_PLAYERS_SLEEPING = 9800;

	public const EVENT_ADD_PARTICLE_MASK = 0x4000;

	/** @var int */
	public $evid;
	/** @var Vector3|null */
	public $position;
	/** @var int */
	public $data;

	protected function decodePayload(){
		$this->evid = $this->getVarInt();
		$this->position = $this->getVector3();
		$this->data = $this->getVarInt();
	}

	protected function encodePayload(){
		$this->putVarInt($this->evid);
		$this->putVector3Nullable($this->position);
		$this->putVarInt($this->data);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLevelEvent($this);
	}

}
