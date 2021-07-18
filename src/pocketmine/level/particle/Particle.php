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

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;

abstract class Particle extends Vector3{

	public const TYPE_BUBBLE = 1;
	public const TYPE_BUBBLE_MANUAL = 2;
	public const TYPE_CRITICAL = 3;
	public const TYPE_BLOCK_FORCE_FIELD = 4;
	public const TYPE_SMOKE = 5;
	public const TYPE_EXPLODE = 6;
	public const TYPE_EVAPORATION = 7;
	public const TYPE_FLAME = 8;
	public const TYPE_CANDLE_FLAME = 9;
	public const TYPE_LAVA = 10;
	public const TYPE_LARGE_SMOKE = 11;
	public const TYPE_REDSTONE = 12;
	public const TYPE_RISING_RED_DUST = 13;
	public const TYPE_ITEM_BREAK = 14;
	public const TYPE_SNOWBALL_POOF = 15;
	public const TYPE_HUGE_EXPLODE = 16;
	public const TYPE_HUGE_EXPLODE_SEED = 17;
	public const TYPE_MOB_FLAME = 18;
	public const TYPE_HEART = 19;
	public const TYPE_TERRAIN = 20;
	public const TYPE_SUSPENDED_TOWN = 21, TYPE_TOWN_AURA = 21;
	public const TYPE_PORTAL = 22;
	//23 same as 22
	public const TYPE_SPLASH = 24, TYPE_WATER_SPLASH = 24;
	public const TYPE_WATER_SPLASH_MANUAL = 25;
	public const TYPE_WATER_WAKE = 26;
	public const TYPE_DRIP_WATER = 27;
	public const TYPE_DRIP_LAVA = 28;
	public const TYPE_DRIP_HONEY = 29;
	public const TYPE_STALACTITE_DRIP_WATER = 30;
	public const TYPE_STALACTITE_DRIP_LAVA = 31;
	public const TYPE_FALLING_DUST = 32, TYPE_DUST = 32;
	public const TYPE_MOB_SPELL = 33;
	public const TYPE_MOB_SPELL_AMBIENT = 34;
	public const TYPE_MOB_SPELL_INSTANTANEOUS = 35;
	public const TYPE_INK = 36;
	public const TYPE_SLIME = 37;
	public const TYPE_RAIN_SPLASH = 38;
	public const TYPE_VILLAGER_ANGRY = 39;
	public const TYPE_VILLAGER_HAPPY = 40;
	public const TYPE_ENCHANTMENT_TABLE = 41;
	public const TYPE_TRACKING_EMITTER = 42;
	public const TYPE_NOTE = 43;
	public const TYPE_WITCH_SPELL = 44;
	public const TYPE_CARROT = 45;
	public const TYPE_MOB_APPEARANCE = 46;
	public const TYPE_END_ROD = 47;
	public const TYPE_DRAGONS_BREATH = 48;
	public const TYPE_SPIT = 49;
	public const TYPE_TOTEM = 50;
	public const TYPE_FOOD = 51;
	public const TYPE_FIREWORKS_STARTER = 52;
	public const TYPE_FIREWORKS_SPARK = 53;
	public const TYPE_FIREWORKS_OVERLAY = 54;
	public const TYPE_BALLOON_GAS = 55;
	public const TYPE_COLORED_FLAME = 56;
	public const TYPE_SPARKLER = 57;
	public const TYPE_CONDUIT = 58;
	public const TYPE_BUBBLE_COLUMN_UP = 59;
	public const TYPE_BUBBLE_COLUMN_DOWN = 60;
	public const TYPE_SNEEZE = 61;
	public const TYPE_SHULKER_BULLET = 62;
	public const TYPE_BLEACH = 63;
	public const TYPE_DRAGON_DESTROY_BLOCK = 64;
	public const TYPE_MYCELIUM_DUST = 65;
	public const TYPE_FALLING_RED_DUST = 66;
	public const TYPE_CAMPFIRE_SMOKE = 67;
	public const TYPE_TALL_CAMPFIRE_SMOKE = 68;
	public const TYPE_DRAGON_BREATH_FIRE = 69;
	public const TYPE_DRAGON_BREATH_TRAIL = 70;
	public const TYPE_BLUE_FLAME = 71;
	public const TYPE_SOUL = 72;
	public const TYPE_OBSIDIAN_TEAR = 73;
	public const TYPE_PORTAL_REVERSE = 74;
	public const TYPE_SNOWFLAKE = 75;
	public const TYPE_VIBRATION_SIGNAL = 76;
	public const TYPE_SCULK_SENSOR_REDSTONE = 77;
	public const TYPE_SPORE_BLOSSOM_SHOWER = 78;
	public const TYPE_SPORE_BLOSSOM_AMBIENT = 79;
	public const TYPE_WAX = 80;
	public const TYPE_ELECTRIC_SPARK = 81;

	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

}
