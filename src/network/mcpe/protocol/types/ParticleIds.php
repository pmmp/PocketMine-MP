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
	//2 same as 1
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
	//62 same as 12
	public const ITEM_BREAK = 13;
	public const SNOWBALL_POOF = 14;
	public const HUGE_EXPLODE = 15;
	//60 same as 15
	public const HUGE_EXPLODE_SEED = 16;
	public const MOB_FLAME = 17;
	public const HEART = 18;
	public const TERRAIN = 19;
	public const SUSPENDED_TOWN = 20, TOWN_AURA = 20;
	//61 same as 20
	public const PORTAL = 21;
	//22 same as 21
	public const SPLASH = 23, WATER_SPLASH = 23;
	//24 same as 23
	public const WATER_WAKE = 25;
	public const DRIP_WATER = 26;
	public const DRIP_LAVA = 27;
	public const FALLING_DUST = 28, DUST = 28;
	public const MOB_SPELL = 29;
	public const MOB_SPELL_AMBIENT = 30;
	public const MOB_SPELL_INSTANTANEOUS = 31;
	public const INK = 32;
	public const SLIME = 33;
	public const RAIN_SPLASH = 34;
	public const VILLAGER_ANGRY = 35;
	//59 same as 35
	public const VILLAGER_HAPPY = 36;
	public const ENCHANTMENT_TABLE = 37;
	public const TRACKING_EMITTER = 38;
	public const NOTE = 39;
	public const WITCH_SPELL = 40;
	public const CARROT = 41;
	//42 unknown
	public const END_ROD = 43;
	//58 same as 43
	public const DRAGONS_BREATH = 44;
	public const SPIT = 45;
	public const TOTEM = 46;
	public const FOOD = 47;
	public const FIREWORKS_STARTER = 48;
	public const FIREWORKS_SPARK = 49;
	public const FIREWORKS_OVERLAY = 50;
	public const BALLOON_GAS = 51;
	public const COLORED_FLAME = 52;
	public const SPARKLER = 53;
	public const CONDUIT = 54;
	public const BUBBLE_COLUMN_UP = 55;
	public const BUBBLE_COLUMN_DOWN = 56;
	public const SNEEZE = 57;

}
