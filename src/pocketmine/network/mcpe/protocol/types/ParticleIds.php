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

interface ParticleIds{
	public const BUBBLE = 1;
	public const CRITICAL = 2;
	public const BLOCK_FORCE_FIELD = 3;
	public const SMOKE = 4;
	public const EXPLODE = 5;
	public const EVAPORATION = 6;
	public const FLAME = 7;
	public const LAVA = 8;
	public const LARGE_SMOKE = 9;
	public const REDSTONE = 10;
	public const RISING_RED_DUST = 11;
	public const ITEM_BREAK = 12;
	public const SNOWBALL_POOF = 13;
	public const HUGE_EXPLODE = 14;
	public const HUGE_EXPLODE_SEED = 15;
	public const MOB_FLAME = 16;
	public const HEART = 17;
	public const TERRAIN = 18;
	public const SUSPENDED_TOWN = 19, TOWN_AURA = 19;
	public const PORTAL = 20;
	public const SPLASH = 21, WATER_SPLASH = 21;
	public const WATER_WAKE = 22;
	public const DRIP_WATER = 23;
	public const DRIP_LAVA = 24;
	public const FALLING_DUST = 25, DUST = 25;
	public const MOB_SPELL = 26;
	public const MOB_SPELL_AMBIENT = 27;
	public const MOB_SPELL_INSTANTANEOUS = 28;
	public const INK = 29;
	public const SLIME = 30;
	public const RAIN_SPLASH = 31;
	public const VILLAGER_ANGRY = 32;
	public const VILLAGER_HAPPY = 33;
	public const ENCHANTMENT_TABLE = 34;
	public const TRACKING_EMITTER = 35;
	public const NOTE = 36;
	public const WITCH_SPELL = 37;
	public const CARROT = 38;
	//39 unknown
	public const END_ROD = 40;
	public const DRAGONS_BREATH = 41;
	public const SPIT = 42;
	public const TOTEM = 43;
	public const FOOD = 44;
	public const FIREWORKS_STARTER = 45;
	public const FIREWORKS_SPARK = 46;
	public const FIREWORKS_OVERLAY = 47;
	public const BALLOON_GAS = 48;
	public const COLORED_FLAME = 49;
	public const SPARKLER = 50;
	public const CONDUIT = 51;
	public const BUBBLE_COLUMN_UP = 52;
	public const BUBBLE_COLUMN_DOWN = 53;
	public const SNEEZE = 54;
}
