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
	public const CANDLE_FLAME = 9;
	public const LAVA = 10;
	public const LARGE_SMOKE = 11;
	public const REDSTONE = 12;
	public const RISING_RED_DUST = 13;
	public const ITEM_BREAK = 14;
	public const SNOWBALL_POOF = 15;
	public const HUGE_EXPLODE = 16;
	public const HUGE_EXPLODE_SEED = 17;
	public const MOB_FLAME = 18;
	public const HEART = 19;
	public const TERRAIN = 20;
	public const SUSPENDED_TOWN = 21, TOWN_AURA = 21;
	public const PORTAL = 22;
	//23 same as 22
	public const SPLASH = 24, WATER_SPLASH = 24;
	public const WATER_SPLASH_MANUAL = 25;
	public const WATER_WAKE = 26;
	public const DRIP_WATER = 27;
	public const DRIP_LAVA = 28;
	public const DRIP_HONEY = 29;
	public const STALACTITE_DRIP_WATER = 30;
	public const STALACTITE_DRIP_LAVA = 31;
	public const FALLING_DUST = 32, DUST = 32;
	public const MOB_SPELL = 33;
	public const MOB_SPELL_AMBIENT = 34;
	public const MOB_SPELL_INSTANTANEOUS = 35;
	public const INK = 36;
	public const SLIME = 37;
	public const RAIN_SPLASH = 38;
	public const VILLAGER_ANGRY = 39;
	public const VILLAGER_HAPPY = 40;
	public const ENCHANTMENT_TABLE = 41;
	public const TRACKING_EMITTER = 42;
	public const NOTE = 43;
	public const WITCH_SPELL = 44;
	public const CARROT = 45;
	public const MOB_APPEARANCE = 46;
	public const END_ROD = 47;
	public const DRAGONS_BREATH = 48;
	public const SPIT = 49;
	public const TOTEM = 50;
	public const FOOD = 51;
	public const FIREWORKS_STARTER = 52;
	public const FIREWORKS_SPARK = 53;
	public const FIREWORKS_OVERLAY = 54;
	public const BALLOON_GAS = 55;
	public const COLORED_FLAME = 56;
	public const SPARKLER = 57;
	public const CONDUIT = 58;
	public const BUBBLE_COLUMN_UP = 59;
	public const BUBBLE_COLUMN_DOWN = 60;
	public const SNEEZE = 61;
	public const SHULKER_BULLET = 62;
	public const BLEACH = 63;
	public const DRAGON_DESTROY_BLOCK = 64;
	public const MYCELIUM_DUST = 65;
	public const FALLING_RED_DUST = 66;
	public const CAMPFIRE_SMOKE = 67;
	public const TALL_CAMPFIRE_SMOKE = 68;
	public const DRAGON_BREATH_FIRE = 69;
	public const DRAGON_BREATH_TRAIL = 70;
	public const BLUE_FLAME = 71;
	public const SOUL = 72;
	public const OBSIDIAN_TEAR = 73;
	public const PORTAL_REVERSE = 74;
	public const SNOWFLAKE = 75;
	public const VIBRATION_SIGNAL = 76;
	public const SCULK_SENSOR_REDSTONE = 77;
	public const SPORE_BLOSSOM_SHOWER = 78;
	public const SPORE_BLOSSOM_AMBIENT = 79;
	public const WAX = 80;
	public const ELECTRIC_SPARK = 81;

}
