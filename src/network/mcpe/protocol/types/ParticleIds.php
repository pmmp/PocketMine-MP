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

namespace pocketmine\network\mcpe\protocol\types;

final class ParticleIds{

	private function __construct(){
		//NOOP
	}
	public const BUBBLE = 1;
	public const BUBBLE_MANUAL = 2;
	public const CRITICAL = 3;
	public const BLOCK_FORCE_FIELD = 4;
	public const SMOKE = 5;
	public const EXPLODE = 6;
	public const EVAPORATION = 7;
	public const FLAME = 8;
	public const LAVA = 9;
	public const LARGE_SMOKE = 10;
	public const REDSTONE = 11;
	public const RISING_RED_DUST = 12;
	public const ITEM_BREAK = 13;
	public const SNOWBALL_POOF = 14;
	public const HUGE_EXPLODE = 15;
	public const HUGE_EXPLODE_SEED = 16;
	public const MOB_FLAME = 17;
	public const HEART = 18;
	public const TERRAIN = 19;
	public const SUSPENDED_TOWN = 20, TOWN_AURA = 20;
	public const PORTAL = 21;
	//22 same as 21
	public const SPLASH = 23, WATER_SPLASH = 23;
	public const WATER_SPLASH_MANUAL = 24;
	public const WATER_WAKE = 25;
	public const DRIP_WATER = 26;
	public const DRIP_LAVA = 27;
	public const DRIP_HONEY = 28;
	public const STALACTITE_DRIP_WATER = 29;
	public const STALACTITE_DRIP_LAVA = 30;
	public const FALLING_DUST = 31, DUST = 31;
	public const MOB_SPELL = 32;
	public const MOB_SPELL_AMBIENT = 33;
	public const MOB_SPELL_INSTANTANEOUS = 34;
	public const INK = 35;
	public const SLIME = 36;
	public const RAIN_SPLASH = 37;
	public const VILLAGER_ANGRY = 38;
	public const VILLAGER_HAPPY = 39;
	public const ENCHANTMENT_TABLE = 40;
	public const TRACKING_EMITTER = 41;
	public const NOTE = 42;
	public const WITCH_SPELL = 43;
	public const CARROT = 44;
	public const MOB_APPEARANCE = 45;
	public const END_ROD = 46;
	public const DRAGONS_BREATH = 47;
	public const SPIT = 48;
	public const TOTEM = 49;
	public const FOOD = 50;
	public const FIREWORKS_STARTER = 51;
	public const FIREWORKS_SPARK = 52;
	public const FIREWORKS_OVERLAY = 53;
	public const BALLOON_GAS = 54;
	public const COLORED_FLAME = 55;
	public const SPARKLER = 56;
	public const CONDUIT = 57;
	public const BUBBLE_COLUMN_UP = 58;
	public const BUBBLE_COLUMN_DOWN = 59;
	public const SNEEZE = 60;
	public const SHULKER_BULLET = 61;
	public const BLEACH = 62;
	public const DRAGON_DESTROY_BLOCK = 63;
	public const MYCELIUM_DUST = 64;
	public const FALLING_RED_DUST = 65;
	public const CAMPFIRE_SMOKE = 66;
	public const TALL_CAMPFIRE_SMOKE = 67;
	public const DRAGON_BREATH_FIRE = 68;
	public const DRAGON_BREATH_TRAIL = 69;
	public const BLUE_FLAME = 70;
	public const SOUL = 71;
	public const OBSIDIAN_TEAR = 72;
	public const PORTAL_REVERSE = 73;
	public const SNOWFLAKE = 74;
	public const VIBRATION_SIGNAL = 75;
	public const SCULK_SENSOR_REDSTONE = 76;
	public const SPORE_BLOSSOM_SHOWER = 77;
	public const SPORE_BLOSSOM_AMBIENT = 78;
	public const WAX = 79;
	public const ELECTRIC_SPARK = 80;

}
