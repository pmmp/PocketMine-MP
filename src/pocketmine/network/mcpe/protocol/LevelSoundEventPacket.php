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
	public const SOUND_ATTACK_STRONG = 42;
	public const SOUND_WARN = 43;
	public const SOUND_SHEAR = 44;
	public const SOUND_MILK = 45;
	public const SOUND_THUNDER = 46;
	public const SOUND_EXPLODE = 47;
	public const SOUND_FIRE = 48;
	public const SOUND_IGNITE = 49;
	public const SOUND_FUSE = 50;
	public const SOUND_STARE = 51;
	public const SOUND_SPAWN = 52;
	public const SOUND_SHOOT = 53;
	public const SOUND_BREAK_BLOCK = 54;
	public const SOUND_LAUNCH = 55;
	public const SOUND_BLAST = 56;
	public const SOUND_LARGE_BLAST = 57;
	public const SOUND_TWINKLE = 58;
	public const SOUND_REMEDY = 59;
	public const SOUND_UNFECT = 60;
	public const SOUND_LEVELUP = 61;
	public const SOUND_BOW_HIT = 62;
	public const SOUND_BULLET_HIT = 63;
	public const SOUND_EXTINGUISH_FIRE = 64;
	public const SOUND_ITEM_FIZZ = 65;
	public const SOUND_CHEST_OPEN = 66;
	public const SOUND_CHEST_CLOSED = 67;
	public const SOUND_SHULKERBOX_OPEN = 68;
	public const SOUND_SHULKERBOX_CLOSED = 69;
	public const SOUND_POWER_ON = 70;
	public const SOUND_POWER_OFF = 71;
	public const SOUND_ATTACH = 72;
	public const SOUND_DETACH = 73;
	public const SOUND_DENY = 74;
	public const SOUND_TRIPOD = 75;
	public const SOUND_POP = 76;
	public const SOUND_DROP_SLOT = 77;
	public const SOUND_NOTE = 78;
	public const SOUND_THORNS = 79;
	public const SOUND_PISTON_IN = 80;
	public const SOUND_PISTON_OUT = 81;
	public const SOUND_PORTAL = 82;
	public const SOUND_WATER = 83;
	public const SOUND_LAVA_POP = 84;
	public const SOUND_LAVA = 85;
	public const SOUND_BURP = 86;
	public const SOUND_BUCKET_FILL_WATER = 87;
	public const SOUND_BUCKET_FILL_LAVA = 88;
	public const SOUND_BUCKET_EMPTY_WATER = 89;
	public const SOUND_BUCKET_EMPTY_LAVA = 90;
	public const SOUND_RECORD_13 = 91;
	public const SOUND_RECORD_CAT = 92;
	public const SOUND_RECORD_BLOCKS = 93;
	public const SOUND_RECORD_CHIRP = 94;
	public const SOUND_RECORD_FAR = 95;
	public const SOUND_RECORD_MALL = 96;
	public const SOUND_RECORD_MELLOHI = 97;
	public const SOUND_RECORD_STAL = 98;
	public const SOUND_RECORD_STRAD = 99;
	public const SOUND_RECORD_WARD = 100;
	public const SOUND_RECORD_11 = 101;
	public const SOUND_RECORD_WAIT = 102;
	public const SOUND_GUARDIAN_FLOP = 104;
	public const SOUND_ELDERGUARDIAN_CURSE = 105;
	public const SOUND_MOB_WARNING = 106;
	public const SOUND_MOB_WARNING_BABY = 107;
	public const SOUND_TELEPORT = 108;
	public const SOUND_SHULKER_OPEN = 109;
	public const SOUND_SHULKER_CLOSE = 110;
	public const SOUND_HAGGLE = 111;
	public const SOUND_HAGGLE_YES = 112;
	public const SOUND_HAGGLE_NO = 113;
	public const SOUND_HAGGLE_IDLE = 114;
	public const SOUND_CHORUSGROW = 115;
	public const SOUND_CHORUSDEATH = 116;
	public const SOUND_GLASS = 117;
	public const SOUND_CAST_SPELL = 118;
	public const SOUND_PREPARE_ATTACK = 119;
	public const SOUND_PREPARE_SUMMON = 120;
	public const SOUND_PREPARE_WOLOLO = 121;
	public const SOUND_FANG = 122;
	public const SOUND_CHARGE = 123;
	public const SOUND_CAMERA_TAKE_PICTURE = 124;
	public const SOUND_LEASHKNOT_PLACE = 125;
	public const SOUND_LEASHKNOT_BREAK = 126;
	public const SOUND_GROWL = 127;
	public const SOUND_WHINE = 128;
	public const SOUND_PANT = 129;
	public const SOUND_PURR = 130;
	public const SOUND_PURREOW = 131;
	public const SOUND_DEATH_MIN_VOLUME = 132;
	public const SOUND_DEATH_MID_VOLUME = 133;
	public const SOUND_IMITATE_BLAZE = 134;
	public const SOUND_IMITATE_CAVE_SPIDER = 135;
	public const SOUND_IMITATE_CREEPER = 136;
	public const SOUND_IMITATE_ELDER_GUARDIAN = 137;
	public const SOUND_IMITATE_ENDER_DRAGON = 138;
	public const SOUND_IMITATE_ENDERMAN = 139;
	public const SOUND_IMITATE_EVOCATION_ILLAGER = 141;
	public const SOUND_IMITATE_GHAST = 142;
	public const SOUND_IMITATE_HUSK = 143;
	public const SOUND_IMITATE_ILLUSION_ILLAGER = 144;
	public const SOUND_IMITATE_MAGMA_CUBE = 145;
	public const SOUND_IMITATE_POLAR_BEAR = 146;
	public const SOUND_IMITATE_SHULKER = 147;
	public const SOUND_IMITATE_SILVERFISH = 148;
	public const SOUND_IMITATE_SKELETON = 149;
	public const SOUND_IMITATE_SLIME = 150;
	public const SOUND_IMITATE_SPIDER = 151;
	public const SOUND_IMITATE_STRAY = 152;
	public const SOUND_IMITATE_VEX = 153;
	public const SOUND_IMITATE_VINDICATION_ILLAGER = 154;
	public const SOUND_IMITATE_WITCH = 155;
	public const SOUND_IMITATE_WITHER = 156;
	public const SOUND_IMITATE_WITHER_SKELETON = 157;
	public const SOUND_IMITATE_WOLF = 158;
	public const SOUND_IMITATE_ZOMBIE = 159;
	public const SOUND_IMITATE_ZOMBIE_PIGMAN = 160;
	public const SOUND_IMITATE_ZOMBIE_VILLAGER = 161;
	public const SOUND_BLOCK_END_PORTAL_FRAME_FILL = 162;
	public const SOUND_BLOCK_END_PORTAL_SPAWN = 163;
	public const SOUND_RANDOM_ANVIL_USE = 164;
	public const SOUND_BOTTLE_DRAGONBREATH = 165;
	public const SOUND_DEFAULT = 166;
	public const SOUND_UNDEFINED = 167;

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
		$this->position = $this->getVector3();
		$this->extraData = $this->getVarInt();
		$this->pitch = $this->getVarInt();
		$this->unknownBool = $this->getBool();
		$this->disableRelativeVolume = $this->getBool();
	}

	protected function encodePayload(){
		$this->putByte($this->sound);
		$this->putVector3($this->position);
		$this->putVarInt($this->extraData);
		$this->putVarInt($this->pitch);
		$this->putBool($this->unknownBool);
		$this->putBool($this->disableRelativeVolume);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLevelSoundEvent($this);
	}
}
