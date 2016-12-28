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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>

class LevelSoundEventPacket extends DataPacket{
	const NETWORK_ID = Info::LEVEL_SOUND_EVENT_PACKET;

	const SOUND_ITEM_USE_ON = 0;
	const SOUND_HIT = 1;
	const SOUND_STEP = 2;
	const SOUND_JUMP = 3;
	const SOUND_BREAK = 4;
	const SOUND_PLACE = 5;
	const SOUND_HEAVY_STEP = 6;
	const SOUND_GALLOP = 7;
	const SOUND_FALL = 8;
	const SOUND_AMBIENT = 9;
	const SOUND_AMBIENT_BABY = 10;
	const SOUND_AMBIENT_IN_WATER = 11;
	const SOUND_BREATHE = 12;
	const SOUND_DEATH = 13;
	const SOUND_DEATH_IN_WATER = 14;
	const SOUND_DEATH_TO_ZOMBIE = 15;
	const SOUND_HURT = 16;
	const SOUND_HURT_IN_WATER = 17;
	const SOUND_MAD = 18;
	const SOUND_BOOST = 19;
	const SOUND_BOW = 20;
	const SOUND_SQUISH_BIG  = 21;
	const SOUND_SQUISH_SMALL = 22;
	const SOUND_FALL_BIG = 23;
	const SOUND_FALL_SMALL = 24;
	const SOUND_SPLASH = 25;
	const SOUND_FIZZ = 26;
	const SOUND_FLAP = 27;
	const SOUND_SWIM = 28;
	const SOUND_DRINK = 29;
	const SOUND_EAT = 30;
	const SOUND_TAKEOFF = 31;
	const SOUND_SHAKE = 32;
	const SOUND_PLOP = 33;
	const SOUND_LAND = 34;
	const SOUND_SADDLE = 35;
	const SOUND_ARMOR = 36;
	const SOUND_ADD_CHEST = 37;
	const SOUND_THROW = 38;
	const SOUND_ATTACK = 39;
	const SOUND_ATTACK_NODAMAGE = 40;
	const SOUND_WARN = 41;
	const SOUND_SHEAR = 42;
	const SOUND_MILK = 43;
	const SOUND_THUNDER = 44;
	const SOUND_EXPLODE = 45;
	const SOUND_FIRE = 46;
	const SOUND_IGNITE = 47;
	const SOUND_FUSE = 48;
	const SOUND_STARE = 49;
	const SOUND_SPAWN = 50;
	const SOUND_SHOOT = 51;
	const SOUND_BREAK_BLOCK = 52;
	const SOUND_REMEDY = 53;
	const SOUND_UNFECT = 54;
	const SOUND_LEVELUP = 55;
	const SOUND_BOW_HIT = 56;
	const SOUND_BULLET_HIT = 57;
	const SOUND_EXTINGUISH_FIRE = 58;
	const SOUND_ITEM_FIZZ = 59;
	const SOUND_CHEST_OPEN = 60;
	const SOUND_CHEST_CLOSED = 61;
	const SOUND_POWER_ON = 62;
	const SOUND_POWER_OFF = 63;
	const SOUND_ATTACH = 64;
	const SOUND_DETACH = 65;
	const SOUND_DENY = 66;
	const SOUND_TRIPOD = 67;
	const SOUND_POP = 68;
	const SOUND_DROP_SLOT = 69;
	const SOUND_NOTE = 70;
	const SOUND_THORNS = 71;
	const SOUND_PISTON_IN = 72;
	const SOUND_PISTON_OUT = 73;
	const SOUND_PORTAL = 74;
	const SOUND_WATER = 75;
	const SOUND_LAVA_POP = 76;
	const SOUND_LAVA = 77;
	const SOUND_BURP = 78;
	const SOUND_BUCKET_FILL_WATER = 79;
	const SOUND_BUCKET_FILL_LAVA = 80;
	const SOUND_BUCKET_EMPTY_WATER = 81;
	const SOUND_BUCKET_EMPTY_LAVA = 82;
	const SOUND_GUARDIAN_FLOP = 83;
	const SOUND_ELDERGUARDIAN_CURSE = 84;
	const SOUND_MOB_WARNING = 85;
	const SOUND_MOB_WARNING_BABY = 86;
	const SOUND_TELEPORT = 87;
	const SOUND_SHULKER_OPEN = 88;
	const SOUND_SHULKER_CLOSE = 89;
	const SOUND_DEFAULT = 90;
	const SOUND_UNDEFINED = 91;

	public $sound;
	public $x;
	public $y;
	public $z;
	public $extraData = -1;
	public $pitch = 1;
	public $unknownBool = false;
	public $unknownBool2 = false;

	public function decode(){
		$this->sound = $this->getByte();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->extraData = $this->getVarInt();
		$this->pitch = $this->getVarInt();
		$this->unknownBool = $this->getBool();
		$this->unknownBool2 = $this->getBool();
	}

	public function encode(){
		$this->reset();
		$this->putByte($this->sound);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putVarInt($this->extraData);
		$this->putVarInt($this->pitch);
		$this->putBool($this->unknownBool);
		$this->putBool($this->unknownBool2);
	}
}