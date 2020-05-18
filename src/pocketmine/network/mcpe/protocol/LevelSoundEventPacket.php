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
	public const SOUND_ARMOR_PLACE = 38;
	public const SOUND_ADD_CHEST = 39;
	public const SOUND_THROW = 40;
	public const SOUND_ATTACK = 41;
	public const SOUND_ATTACK_NO_DAMAGE = 42;
	public const SOUND_ATTACK_STRONG = 43;
	public const SOUND_WARN = 44;
	public const SOUND_SHEAR = 45;
	public const SOUND_MILK = 46;
	public const SOUND_THUNDER = 47;
	public const SOUND_EXPLODE = 48;
	public const SOUND_FIRE = 49;
	public const SOUND_IGNITE = 50;
	public const SOUND_FUSE = 51;
	public const SOUND_STARE = 52;
	public const SOUND_SPAWN = 53;
	public const SOUND_SHOOT = 54;
	public const SOUND_BREAK_BLOCK = 55;
	public const SOUND_LAUNCH = 56;
	public const SOUND_BLAST = 57;
	public const SOUND_LARGE_BLAST = 58;
	public const SOUND_TWINKLE = 59;
	public const SOUND_REMEDY = 60;
	public const SOUND_UNFECT = 61;
	public const SOUND_LEVEL_UP = 62;
	public const SOUND_BOW_HIT = 63;
	public const SOUND_BULLET_HIT = 64;
	public const SOUND_EXTINGUISH_FIRE = 65;
	public const SOUND_ITEM_FIZZ = 66;
	public const SOUND_CHEST_OPEN = 67;
	public const SOUND_CHEST_CLOSED = 68;
	public const SOUND_SHULKER_BOX_OPEN = 69;
	public const SOUND_SHULKER_BOX_CLOSED = 70;
	public const SOUND_ENDER_CHEST_OPEN = 71;
	public const SOUND_ENDER_CHEST_CLOSED = 72;
	public const SOUND_POWER_ON = 73;
	public const SOUND_POWER_OFF = 74;
	public const SOUND_ATTACH = 75;
	public const SOUND_DETACH = 76;
	public const SOUND_DENY = 77;
	public const SOUND_TRIPOD = 78;
	public const SOUND_POP = 79;
	public const SOUND_DROP_SLOT = 80;
	public const SOUND_NOTE = 81;
	public const SOUND_THORNS = 82;
	public const SOUND_PISTON_IN = 83;
	public const SOUND_PISTON_OUT = 84;
	public const SOUND_PORTAL = 85;
	public const SOUND_WATER = 86;
	public const SOUND_LAVA_POP = 87;
	public const SOUND_LAVA = 88;
	public const SOUND_BURP = 89;
	public const SOUND_BUCKET_FILL_WATER = 90;
	public const SOUND_BUCKET_FILL_LAVA = 91;
	public const SOUND_BUCKET_EMPTY_WATER = 92;
	public const SOUND_BUCKET_EMPTY_LAVA = 93;
	public const SOUND_EQUIP_CHAIN = 94;
	public const SOUND_EQUIP_DIAMOND = 95;
	public const SOUND_EQUIP_GENERIC = 96;
	public const SOUND_EQUIP_GOLD = 97;
	public const SOUND_EQUIP_IRON = 98;
	public const SOUND_EQUIP_LEATHER = 99;
	public const SOUND_EQUIP_ELYTRA = 100;
	public const SOUND_RECORD13 = 101;
	public const SOUND_RECORD_CAT = 102;
	public const SOUND_RECORD_BLOCKS = 103;
	public const SOUND_RECORD_CHIRP = 104;
	public const SOUND_RECORD_FAR = 105;
	public const SOUND_RECORD_MALL = 106;
	public const SOUND_RECORD_MELLOHI = 107;
	public const SOUND_RECORD_STAL = 108;
	public const SOUND_RECORD_STRAD = 109;
	public const SOUND_RECORD_WARD = 110;
	public const SOUND_RECORD11 = 111;
	public const SOUND_RECORD_WAIT = 112;
	public const SOUND_RECORD_NULL = 113;
	public const SOUND_FLOP = 114;
	public const SOUND_GUARDIAN_CURSE = 115;
	public const SOUND_MOB_WARNING = 116;
	public const SOUND_MOB_WARNING_BABY = 117;
	public const SOUND_TELEPORT = 118;
	public const SOUND_SHULKER_OPEN = 119;
	public const SOUND_SHULKER_CLOSE = 120;
	public const SOUND_HAGGLE = 121;
	public const SOUND_HAGGLE_YES = 122;
	public const SOUND_HAGGLE_NO = 123;
	public const SOUND_HAGGLE_IDLE = 124;
	public const SOUND_CHORUS_GROW = 125;
	public const SOUND_CHORUS_DEATH = 126;
	public const SOUND_GLASS = 127;
	public const SOUND_POTION_BREWED = 128;
	public const SOUND_CAST_SPELL = 129;
	public const SOUND_PREPARE_ATTACK_SPELL = 130;
	public const SOUND_PREPARE_SUMMON = 131;
	public const SOUND_PREPARE_WOLOLO = 132;
	public const SOUND_FANG = 133;
	public const SOUND_CHARGE = 134;
	public const SOUND_TAKE_PICTURE = 135;
	public const SOUND_PLACE_LEASH_KNOT = 136;
	public const SOUND_BREAK_LEASH_KNOT = 137;
	public const SOUND_AMBIENT_GROWL = 138;
	public const SOUND_AMBIENT_WHINE = 139;
	public const SOUND_AMBIENT_PANT = 140;
	public const SOUND_AMBIENT_PURR = 141;
	public const SOUND_AMBIENT_PURREOW = 142;
	public const SOUND_DEATH_MIN_VOLUME = 143;
	public const SOUND_DEATH_MID_VOLUME = 144;
	public const SOUND_IMITATE_BLAZE = 145;
	public const SOUND_IMITATE_CAVE_SPIDER = 146;
	public const SOUND_IMITATE_CREEPER = 147;
	public const SOUND_IMITATE_ELDER_GUARDIAN = 148;
	public const SOUND_IMITATE_ENDER_DRAGON = 149;
	public const SOUND_IMITATE_ENDERMAN = 150;
	public const SOUND_IMITATE_ENDERMITE = 151;
	public const SOUND_IMITATE_EVOCATION_ILLAGER = 152;
	public const SOUND_IMITATE_GHAST = 153;
	public const SOUND_IMITATE_HUSK = 154;
	public const SOUND_IMITATE_ILLUSION_ILLAGER = 155;
	public const SOUND_IMITATE_MAGMA_CUBE = 156;
	public const SOUND_IMITATE_POLAR_BEAR = 157;
	public const SOUND_IMITATE_SHULKER = 158;
	public const SOUND_IMITATE_SILVERFISH = 159;
	public const SOUND_IMITATE_SKELETON = 160;
	public const SOUND_IMITATE_SLIME = 161;
	public const SOUND_IMITATE_SPIDER = 162;
	public const SOUND_IMITATE_STRAY = 163;
	public const SOUND_IMITATE_VEX = 164;
	public const SOUND_IMITATE_VINDICATION_ILLAGER = 165;
	public const SOUND_IMITATE_WITCH = 166;
	public const SOUND_IMITATE_WITHER = 167;
	public const SOUND_IMITATE_WITHER_SKELETON = 168;
	public const SOUND_IMITATE_WOLF = 169;
	public const SOUND_IMITATE_ZOMBIE = 170;
	public const SOUND_IMITATE_ZOMBIE_PIGMAN = 171;
	public const SOUND_IMITATE_ZOMBIE_VILLAGER = 172;
	public const SOUND_ENDER_EYE_PLACED = 173;
	public const SOUND_END_PORTAL_CREATED = 174;
	public const SOUND_ANVIL_USE = 175;
	public const SOUND_BOTTLE_DRAGON_BREATH = 176;
	public const SOUND_PORTAL_TRAVEL = 177;
	public const SOUND_TRIDENT_HIT = 178;
	public const SOUND_TRIDENT_RETURN = 179;
	public const SOUND_TRIDENT_RIPTIDE_1 = 180;
	public const SOUND_TRIDENT_RIPTIDE_2 = 181;
	public const SOUND_TRIDENT_RIPTIDE_3 = 182;
	public const SOUND_TRIDENT_THROW = 183;
	public const SOUND_TRIDENT_THUNDER = 184;
	public const SOUND_TRIDENT_HIT_GROUND = 185;
	public const SOUND_DEFAULT = 186;
	public const SOUND_FLETCHING_TABLE_USE = 187;
	public const SOUND_ELEM_CONSTRUCT_OPEN = 188;
	public const SOUND_ICE_BOMB_HIT = 189;
	public const SOUND_BALLOON_POP = 190;
	public const SOUND_L_T_REACTION_ICE_BOMB = 191;
	public const SOUND_L_T_REACTION_BLEACH = 192;
	public const SOUND_L_T_REACTION_ELEPHANT_TOOTHPASTE = 193;
	public const SOUND_L_T_REACTION_ELEPHANT_TOOTHPASTE2 = 194;
	public const SOUND_L_T_REACTION_GLOW_STICK = 195;
	public const SOUND_L_T_REACTION_GLOW_STICK2 = 196;
	public const SOUND_L_T_REACTION_LUMINOL = 197;
	public const SOUND_L_T_REACTION_SALT = 198;
	public const SOUND_L_T_REACTION_FERTILIZER = 199;
	public const SOUND_L_T_REACTION_FIREBALL = 200;
	public const SOUND_L_T_REACTION_MAGNESIUM_SALT = 201;
	public const SOUND_L_T_REACTION_MISC_FIRE = 202;
	public const SOUND_L_T_REACTION_FIRE = 203;
	public const SOUND_L_T_REACTION_MISC_EXPLOSION = 204;
	public const SOUND_L_T_REACTION_MISC_MYSTICAL = 205;
	public const SOUND_L_T_REACTION_MISC_MYSTICAL2 = 206;
	public const SOUND_L_T_REACTION_PRODUCT = 207;
	public const SOUND_SPARKLER_USE = 208;
	public const SOUND_GLOW_STICK_USE = 209;
	public const SOUND_SPARKLER_ACTIVE = 210;
	public const SOUND_CONVERT_TO_DROWNED = 211;
	public const SOUND_BUCKET_FILL_FISH = 212;
	public const SOUND_BUCKET_EMPTY_FISH = 213;
	public const SOUND_BUBBLE_COLUMN_UPWARDS = 214;
	public const SOUND_BUBBLE_COLUMN_DOWNWARDS = 215;
	public const SOUND_BUBBLE_POP = 216;
	public const SOUND_BUBBLE_UP_INSIDE = 217;
	public const SOUND_BUBBLE_DOWN_INSIDE = 218;
	public const SOUND_HURT_BABY = 219;
	public const SOUND_DEATH_BABY = 220;
	public const SOUND_STEP_BABY = 221;
	public const SOUND_SPAWN_BABY = 222;
	public const SOUND_BORN = 223;
	public const SOUND_TURTLE_EGG_BREAK = 224;
	public const SOUND_TURTLE_EGG_CRACK = 225;
	public const SOUND_TURTLE_EGG_HATCHED = 226;
	public const SOUND_LAY_EGG = 227;
	public const SOUND_TURTLE_EGG_ATTACKED = 228;
	public const SOUND_BEACON_ACTIVATE = 229;
	public const SOUND_BEACON_AMBIENT = 230;
	public const SOUND_BEACON_DEACTIVATE = 231;
	public const SOUND_BEACON_POWER = 232;
	public const SOUND_CONDUIT_ACTIVATE = 233;
	public const SOUND_CONDUIT_AMBIENT = 234;
	public const SOUND_CONDUIT_ATTACK = 235;
	public const SOUND_CONDUIT_DEACTIVATE = 236;
	public const SOUND_CONDUIT_SHORT = 237;
	public const SOUND_SWOOP = 238;
	public const SOUND_BAMBOO_SAPLING_PLACE = 239;
	public const SOUND_PRE_SNEEZE = 240;
	public const SOUND_SNEEZE = 241;
	public const SOUND_AMBIENT_TAME = 242;
	public const SOUND_SCARED = 243;
	public const SOUND_SCAFFOLDING_CLIMB = 244;
	public const SOUND_CROSSBOW_LOADING_START = 245;
	public const SOUND_CROSSBOW_LOADING_MIDDLE = 246;
	public const SOUND_CROSSBOW_LOADING_END = 247;
	public const SOUND_CROSSBOW_SHOOT = 248;
	public const SOUND_CROSSBOW_QUICK_CHARGE_START = 249;
	public const SOUND_CROSSBOW_QUICK_CHARGE_MIDDLE = 250;
	public const SOUND_CROSSBOW_QUICK_CHARGE_END = 251;
	public const SOUND_AMBIENT_AGGRESSIVE = 252;
	public const SOUND_AMBIENT_WORRIED = 253;
	public const SOUND_CANT_BREED = 254;
	public const SOUND_SHIELD_BLOCK = 255;
	public const SOUND_LECTERN_BOOK_PLACE = 256;
	public const SOUND_GRINDSTONE_USE = 257;
	public const SOUND_BELL = 258;
	public const SOUND_CAMPFIRE_CRACKLE = 259;
	public const SOUND_SWEET_BERRY_BUSH_HURT = 262;
	public const SOUND_SWEET_BERRY_BUSH_PICK = 263;
	public const SOUND_ROAR = 260;
	public const SOUND_STUN = 261;
	public const SOUND_CARTOGRAPHY_TABLE_USE = 264;
	public const SOUND_STONECUTTER_USE = 265;
	public const SOUND_COMPOSTER_EMPTY = 266;
	public const SOUND_COMPOSTER_FILL = 267;
	public const SOUND_COMPOSTER_FILL_LAYER = 268;
	public const SOUND_COMPOSTER_READY = 269;
	public const SOUND_BARREL_OPEN = 270;
	public const SOUND_BARREL_CLOSE = 271;
	public const SOUND_RAID_HORN = 272;
	public const SOUND_LOOM_USE = 273;
	public const SOUND_AMBIENT_IN_RAID = 274;
	public const SOUND_U_I_CARTOGRAPHY_TABLE_USE = 275;
	public const SOUND_U_I_STONECUTTER_USE = 276;
	public const SOUND_U_I_LOOM_USE = 277;
	public const SOUND_SMOKER_USE = 278;
	public const SOUND_BLAST_FURNACE_USE = 279;
	public const SOUND_SMITHING_TABLE_USE = 280;
	public const SOUND_SCREECH = 281;
	public const SOUND_SLEEP = 282;
	public const SOUND_FURNACE_USE = 283;
	public const SOUND_MOOSHROOM_CONVERT = 284;
	public const SOUND_MILK_SUSPICIOUSLY = 285;
	public const SOUND_CELEBRATE = 286;
	public const SOUND_JUMP_PREVENT = 287;
	public const SOUND_AMBIENT_POLLINATE = 288;
	public const SOUND_BEEHIVE_DRIP = 289;
	public const SOUND_BEEHIVE_ENTER = 290;
	public const SOUND_BEEHIVE_EXIT = 291;
	public const SOUND_BEEHIVE_WORK = 292;
	public const SOUND_BEEHIVE_SHEAR = 293;
	public const SOUND_HONEYBOTTLE_DRINK = 294;
	public const SOUND_UNDEFINED = 295;

	/** @var int */
	public $sound;
	/** @var Vector3 */
	public $position;
	/** @var int */
	public $extraData = -1;
	/** @var string */
	public $entityType = ":"; //???
	/** @var bool */
	public $isBabyMob = false; //...
	/** @var bool */
	public $disableRelativeVolume = false;

	protected function decodePayload(){
		$this->sound = $this->getUnsignedVarInt();
		$this->position = $this->getVector3();
		$this->extraData = $this->getVarInt();
		$this->entityType = $this->getString();
		$this->isBabyMob = $this->getBool();
		$this->disableRelativeVolume = $this->getBool();
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->sound);
		$this->putVector3($this->position);
		$this->putVarInt($this->extraData);
		$this->putString($this->entityType);
		$this->putBool($this->isBabyMob);
		$this->putBool($this->disableRelativeVolume);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLevelSoundEvent($this);
	}
}
