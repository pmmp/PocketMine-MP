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

	public const EVENT_UNDEFINED = 0;

	public const EVENT_SOUND_CLICK = 1000;
	public const EVENT_SOUND_CLICK_FAIL = 1001;
	public const EVENT_SOUND_LAUNCH = 1002;
	public const EVENT_SOUND_OPEN_DOOR = 1003;
	public const EVENT_SOUND_FIZZ = 1004;
	public const EVENT_SOUND_FUSE = 1005;
	public const EVENT_SOUND_PLAY_RECORDING = 1006;
	public const EVENT_SOUND_GHAST_WARNING = 1007;
	public const EVENT_SOUND_GHAST_FIREBALL = 1008;
	public const EVENT_SOUND_BLAZE_FIREBALL = 1009;
	public const EVENT_SOUND_ZOMBIE_WOODEN_DOOR = 1010;
	public const EVENT_SOUND_ZOMBIE_DOOR_CRASH = 1012;
	public const EVENT_SOUND_ZOMBIE_INFECTED = 1016;
	public const EVENT_SOUND_ZOMBIE_CONVERTED = 1017;
	public const EVENT_SOUND_ENDERMAN_TELEPORT = 1018;
	public const EVENT_SOUND_ANVIL_BROKEN = 1020;
	public const EVENT_SOUND_ANVIL_USED = 1021;
	public const EVENT_SOUND_ANVIL_LAND = 1022;

	public const EVENT_SOUND_INFINITY_ARROW_PICKUP = 1030;

	public const EVENT_SOUND_TELEPORT_ENDER_PEARL = 1032;

	public const EVENT_SOUND_ADD_ITEM = 1040;
	public const EVENT_SOUND_ITEM_FRAME_BREAK = 1041;
	public const EVENT_SOUND_ITEM_FRAME_PLACE = 1042;
	public const EVENT_SOUND_ITEM_FRAME_REMOVE_ITEM = 1043;
	public const EVENT_SOUND_ITEM_FRAME_ROTATE_ITEM = 1044;

	public const EVENT_SOUND_EXPERIENCE_ORB_PICKUP = 1051;
	public const EVENT_SOUND_TOTEM_USED = 1052;
	public const EVENT_SOUND_ARMOR_STAND_BREAK = 1060;
	public const EVENT_SOUND_ARMOR_STAND_HIT = 1061;
	public const EVENT_SOUND_ARMOR_STAND_LAND = 1062;
	public const EVENT_SOUND_ARMOR_STAND_PLACE = 1063;

	public const EVENT_PARTICLES_SHOOT = 2000;
	public const EVENT_PARTICLES_DESTROY_BLOCK = 2001;
	public const EVENT_PARTICLES_POTION_SPLASH = 2002;
	public const EVENT_PARTICLES_EYE_OF_ENDER_DEATH = 2003;
	public const EVENT_PARTICLES_MOB_BLOCK_SPAWN = 2004;
	public const EVENT_PARTICLE_CROP_GROWTH = 2005;
	public const EVENT_PARTICLE_SOUND_GUARDIAN_GHOST = 2006;
	public const EVENT_PARTICLE_DEATH_SMOKE = 2007;
	public const EVENT_PARTICLE_DENY_BLOCK = 2008;
	public const EVENT_PARTICLE_GENERIC_SPAWN = 2009;
	public const EVENT_PARTICLES_DRAGON_EGG = 2010;
	public const EVENT_PARTICLES_CROP_EATEN = 2011;
	public const EVENT_PARTICLES_CRIT = 2012;
	public const EVENT_PARTICLES_TELEPORT = 2013;
	public const EVENT_PARTICLES_CRACK_BLOCK = 2014;
	public const EVENT_PARTICLES_BUBBLE = 2015;
	public const EVENT_PARTICLES_EVAPORATE = 2016;
	public const EVENT_PARTICLES_DESTROY_ARMOR_STAND = 2017;
	public const EVENT_PARTICLES_BREAKING_EGG = 2018;
	public const EVENT_PARTICLE_DESTROY_EGG = 2019;
	public const EVENT_PARTICLES_EVAPORATE_WATER = 2020;
	public const EVENT_PARTICLES_DESTROY_BLOCK_NO_SOUND = 2021;
	public const EVENT_PARTICLES_KNOCKBACK_ROAR = 2022;
	public const EVENT_PARTICLES_TELEPORT_TRAIL = 2023;
	public const EVENT_PARTICLES_POINT_CLOUD = 2024;
	public const EVENT_PARTICLES_EXPLOSION = 2025;
	public const EVENT_PARTICLES_BLOCK_EXPLOSION = 2026;

	public const EVENT_START_RAINING = 3001;
	public const EVENT_START_THUNDERSTORM = 3002;
	public const EVENT_STOP_RAINING = 3003;
	public const EVENT_STOP_THUNDERSTORM = 3004;
	public const EVENT_GLOBAL_PAUSE = 3005;
	public const EVENT_SIM_TIME_STEP = 3006;
	public const EVENT_SIM_TIME_SCALE = 3007;

	public const EVENT_ACTIVATE_BLOCK = 3500;
	public const EVENT_CAULDRON_EXPLODE = 3501;
	public const EVENT_CAULDRON_DYE_ARMOR = 3502;
	public const EVENT_CAULDRON_CLEAN_ARMOR = 3503;
	public const EVENT_CAULDRON_FILL_POTION = 3504;
	public const EVENT_CAULDRON_TAKE_POTION = 3505;
	public const EVENT_CAULDRON_FILL_WATER = 3506;
	public const EVENT_CAULDRON_TAKE_WATER = 3507;
	public const EVENT_CAULDRON_ADD_DYE = 3508;
	public const EVENT_CAULDRON_CLEAN_BANNER = 3509;
	public const EVENT_CAULDRON_FLUSH = 3510;
	public const EVENT_AGENT_SPAWN_EFFECT = 3511;
	public const EVENT_CAULDRON_FILL_LAVA = 3512;
	public const EVENT_CAULDRON_TAKE_LAVA = 3513;

	public const EVENT_START_BLOCK_CRACKING = 3600;
	public const EVENT_STOP_BLOCK_CRACKING = 3601;
	public const EVENT_UPDATE_BLOCK_CRACKING = 3602;

	public const EVENT_ALL_PLAYERS_SLEEPING = 9800;

	public const EVENT_JUMP_PREVENTED = 9810;

	public const EVENT_PARTICLE_LEGACY_EVENT = 0x4000;

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
