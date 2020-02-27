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
	public const FALLING_DUST = 29, DUST = 29;
	public const MOB_SPELL = 30;
	public const MOB_SPELL_AMBIENT = 31;
	public const MOB_SPELL_INSTANTANEOUS = 32;
	public const INK = 33;
	public const SLIME = 34;
	public const RAIN_SPLASH = 35;
	public const VILLAGER_ANGRY = 36;
	public const VILLAGER_HAPPY = 37;
	public const ENCHANTMENT_TABLE = 38;
	public const TRACKING_EMITTER = 39;
	public const NOTE = 40;
	public const WITCH_SPELL = 41;
	public const CARROT = 42;
	public const MOB_APPEARANCE = 43;
	public const END_ROD = 44;
	public const DRAGONS_BREATH = 45;
	public const SPIT = 46;
	public const TOTEM = 47;
	public const FOOD = 48;
	public const FIREWORKS_STARTER = 49;
	public const FIREWORKS_SPARK = 50;
	public const FIREWORKS_OVERLAY = 51;
	public const BALLOON_GAS = 52;
	public const COLORED_FLAME = 53;
	public const SPARKLER = 54;
	public const CONDUIT = 55;
	public const BUBBLE_COLUMN_UP = 56;
	public const BUBBLE_COLUMN_DOWN = 57;
	public const SNEEZE = 58;
	public const SHULKER_BULLET = 59;
	public const BLEACH = 60;
	public const DRAGON_DESTROY_BLOCK = 61;
	public const MYCELIUM_DUST = 62;
	public const FALLING_RED_DUST = 63;
	public const CAMPFIRE_SMOKE = 64;
	public const TALL_CAMPFIRE_SMOKE = 65;
	public const DRAGON_BREATH_FIRE = 66;
	public const DRAGON_BREATH_TRAIL = 67;

}
