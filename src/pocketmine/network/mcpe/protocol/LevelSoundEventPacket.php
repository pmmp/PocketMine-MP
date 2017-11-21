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

class LevelSoundEventPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::LEVEL_SOUND_EVENT_PACKET;

	public const SOUND_ITEM_USE_ON = 0;
	public const SOUND_HIT = 1;
	public const SOUND_STEP = 2;
	public const SOUND_FLY = 3;
	public const SOUND_JUMP = 4;
	public const SOUND_BREAK = 5;
	public const SOUND_PLACE = 6;
	public const SOUND_HEAVY_STEP = 7;
	public const SOUND_GALLOP = 8;
	public const SOUND_FALL = 9;
	public const SOUND_AMBIENT = 10;
	public const SOUND_AMBIENT_BABY = 11;
	public const SOUND_AMBIENT_IN_WATER = 12;
	public const SOUND_BREATHE = 13;
	public const SOUND_DEATH = 14;
	public const SOUND_DEATH_IN_WATER = 15;
	public const SOUND_DEATH_TO_ZOMBIE = 16;
	public const SOUND_HURT = 17;
	public const SOUND_HURT_IN_WATER = 18;
	public const SOUND_MAD = 19;
	public const SOUND_BOOST = 20;
	public const SOUND_BOW = 21;
	public const SOUND_SQUISH_BIG = 22;
	public const SOUND_SQUISH_SMALL = 23;
	public const SOUND_FALL_BIG = 24;
	public const SOUND_FALL_SMALL = 25;
	public const SOUND_SPLASH = 26;
	public const SOUND_FIZZ = 27;
	public const SOUND_FLAP = 28;
	public const SOUND_SWIM = 29;
	public const SOUND_DRINK = 30;
	public const SOUND_EAT = 31;
	public const SOUND_TAKEOFF = 32;
	public const SOUND_SHAKE = 33;
	public const SOUND_PLOP = 34;
	public const SOUND_LAND = 35;
	public const SOUND_SADDLE = 36;
	public const SOUND_ARMOR = 37;
	public const SOUND_ADD_CHEST = 38;
	public const SOUND_THROW = 39;
	public const SOUND_ATTACK = 40;
	public const SOUND_ATTACK_NODAMAGE = 41;
	public const SOUND_WARN = 42;
	public const SOUND_SHEAR = 43;
	public const SOUND_MILK = 44;
	public const SOUND_THUNDER = 45;
	public const SOUND_EXPLODE = 46;
	public const SOUND_FIRE = 47;
	public const SOUND_IGNITE = 48;
	public const SOUND_FUSE = 49;
	public const SOUND_STARE = 50;
	public const SOUND_SPAWN = 51;
	public const SOUND_SHOOT = 52;
	public const SOUND_BREAK_BLOCK = 53;
	public const SOUND_LAUNCH = 54;
	public const SOUND_BLAST = 55;
	public const SOUND_LARGE_BLAST = 56;
	public const SOUND_TWINKLE = 57;
	public const SOUND_REMEDY = 58;
	public const SOUND_UNFECT = 59;
	public const SOUND_LEVELUP = 60;
	public const SOUND_BOW_HIT = 61;
	public const SOUND_BULLET_HIT = 62;
	public const SOUND_EXTINGUISH_FIRE = 63;
	public const SOUND_ITEM_FIZZ = 64;
	public const SOUND_CHEST_OPEN = 65;
	public const SOUND_CHEST_CLOSED = 66;
	public const SOUND_SHULKERBOX_OPEN = 67;
	public const SOUND_SHULKERBOX_CLOSED = 68;
	public const SOUND_POWER_ON = 69;
	public const SOUND_POWER_OFF = 70;
	public const SOUND_ATTACH = 71;
	public const SOUND_DETACH = 72;
	public const SOUND_DENY = 73;
	public const SOUND_TRIPOD = 74;
	public const SOUND_POP = 75;
	public const SOUND_DROP_SLOT = 76;
	public const SOUND_NOTE = 77;
	public const SOUND_THORNS = 78;
	public const SOUND_PISTON_IN = 79;
	public const SOUND_PISTON_OUT = 80;
	public const SOUND_PORTAL = 81;
	public const SOUND_WATER = 82;
	public const SOUND_LAVA_POP = 83;
	public const SOUND_LAVA = 84;
	public const SOUND_BURP = 85;
	public const SOUND_BUCKET_FILL_WATER = 86;
	public const SOUND_BUCKET_FILL_LAVA = 87;
	public const SOUND_BUCKET_EMPTY_WATER = 88;
	public const SOUND_BUCKET_EMPTY_LAVA = 89;
	public const SOUND_RECORD_13 = 90;
	public const SOUND_RECORD_CAT = 91;
	public const SOUND_RECORD_BLOCKS = 92;
	public const SOUND_RECORD_CHIRP = 93;
	public const SOUND_RECORD_FAR = 94;
	public const SOUND_RECORD_MALL = 95;
	public const SOUND_RECORD_MELLOHI = 96;
	public const SOUND_RECORD_STAL = 97;
	public const SOUND_RECORD_STRAD = 98;
	public const SOUND_RECORD_WARD = 99;
	public const SOUND_RECORD_11 = 100;
	public const SOUND_RECORD_WAIT = 101;
	public const SOUND_GUARDIAN_FLOP = 103;
	public const SOUND_ELDERGUARDIAN_CURSE = 104;
	public const SOUND_MOB_WARNING = 105;
	public const SOUND_MOB_WARNING_BABY = 106;
	public const SOUND_TELEPORT = 107;
	public const SOUND_SHULKER_OPEN = 108;
	public const SOUND_SHULKER_CLOSE = 109;
	public const SOUND_HAGGLE = 110;
	public const SOUND_HAGGLE_YES = 111;
	public const SOUND_HAGGLE_NO = 112;
	public const SOUND_HAGGLE_IDLE = 113;
	public const SOUND_CHORUSGROW = 114;
	public const SOUND_CHORUSDEATH = 115;
	public const SOUND_GLASS = 116;
	public const SOUND_CAST_SPELL = 117;
	public const SOUND_PREPARE_ATTACK = 118;
	public const SOUND_PREPARE_SUMMON = 119;
	public const SOUND_PREPARE_WOLOLO = 120;
	public const SOUND_FANG = 121;
	public const SOUND_CHARGE = 122;
	public const SOUND_CAMERA_TAKE_PICTURE = 123;
	public const SOUND_LEASHKNOT_PLACE = 124;
	public const SOUND_LEASHKNOT_BREAK = 125;
	public const SOUND_GROWL = 126;
	public const SOUND_WHINE = 127;
	public const SOUND_PANT = 128;
	public const SOUND_PURR = 129;
	public const SOUND_PURREOW = 130;
	public const SOUND_DEATH_MIN_VOLUME = 131;
	public const SOUND_DEATH_MID_VOLUME = 132;
	public const SOUND_IMITATE_BLAZE = 133;
	public const SOUND_IMITATE_CAVE_SPIDER = 134;
	public const SOUND_IMITATE_CREEPER = 135;
	public const SOUND_IMITATE_ELDER_GUARDIAN = 136;
	public const SOUND_IMITATE_ENDER_DRAGON = 137;
	public const SOUND_IMITATE_ENDERMAN = 138;
	public const SOUND_IMITATE_EVOCATION_ILLAGER = 140;
	public const SOUND_IMITATE_GHAST = 141;
	public const SOUND_IMITATE_HUSK = 142;
	public const SOUND_IMITATE_ILLUSION_ILLAGER = 143;
	public const SOUND_IMITATE_MAGMA_CUBE = 144;
	public const SOUND_IMITATE_POLAR_BEAR = 145;
	public const SOUND_IMITATE_SHULKER = 146;
	public const SOUND_IMITATE_SILVERFISH = 147;
	public const SOUND_IMITATE_SKELETON = 148;
	public const SOUND_IMITATE_SLIME = 149;
	public const SOUND_IMITATE_SPIDER = 150;
	public const SOUND_IMITATE_STRAY = 151;
	public const SOUND_IMITATE_VEX = 152;
	public const SOUND_IMITATE_VINDICATION_ILLAGER = 153;
	public const SOUND_IMITATE_WITCH = 154;
	public const SOUND_IMITATE_WITHER = 155;
	public const SOUND_IMITATE_WITHER_SKELETON = 156;
	public const SOUND_IMITATE_WOLF = 157;
	public const SOUND_IMITATE_ZOMBIE = 158;
	public const SOUND_IMITATE_ZOMBIE_PIGMAN = 159;
	public const SOUND_IMITATE_ZOMBIE_VILLAGER = 160;
	public const SOUND_DEFAULT = 161;
	public const SOUND_UNDEFINED = 162;

	/** @var int */
	public $sound;
	/** @var Vector3 */
	public $position;
	/** @var int */
	public $extraData = -1;
	/** @var int */
	public $pitch = 1;
	/** @var bool */
	public $unknownBool = false;
	/** @var bool */
	public $disableRelativeVolume = false;

	protected function decodePayload(){
		$this->sound = $this->getByte();
		$this->position = $this->getVector3Obj();
		$this->extraData = $this->getVarInt();
		$this->pitch = $this->getVarInt();
		$this->unknownBool = $this->getBool();
		$this->disableRelativeVolume = $this->getBool();
	}

	protected function encodePayload(){
		$this->putByte($this->sound);
		$this->putVector3Obj($this->position);
		$this->putVarInt($this->extraData);
		$this->putVarInt($this->pitch);
		$this->putBool($this->unknownBool);
		$this->putBool($this->disableRelativeVolume);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLevelSoundEvent($this);
	}
}