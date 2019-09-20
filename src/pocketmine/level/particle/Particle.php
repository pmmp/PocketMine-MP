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
	//2 same as 1
	public const TYPE_CRITICAL = 3;
	public const TYPE_BLOCK_FORCE_FIELD = 4;
	public const TYPE_SMOKE = 5;
	public const TYPE_EXPLODE = 6;
	public const TYPE_EVAPORATION = 7;
	public const TYPE_FLAME = 8;
	public const TYPE_LAVA = 9;
	public const TYPE_LARGE_SMOKE = 10;
	public const TYPE_REDSTONE = 11;
	public const TYPE_RISING_RED_DUST = 12;
	//62 same as 12
	public const TYPE_ITEM_BREAK = 13;
	public const TYPE_SNOWBALL_POOF = 14;
	public const TYPE_HUGE_EXPLODE = 15;
	//60 same as 15
	public const TYPE_HUGE_EXPLODE_SEED = 16;
	public const TYPE_MOB_FLAME = 17;
	public const TYPE_HEART = 18;
	public const TYPE_TERRAIN = 19;
	public const TYPE_SUSPENDED_TOWN = 20, TYPE_TOWN_AURA = 20;
	//61 same as 20
	public const TYPE_PORTAL = 21;
	//22 same as 21
	public const TYPE_SPLASH = 23, TYPE_WATER_SPLASH = 23;
	//24 same as 23
	public const TYPE_WATER_WAKE = 25;
	public const TYPE_DRIP_WATER = 26;
	public const TYPE_DRIP_LAVA = 27;
	public const TYPE_FALLING_DUST = 28, TYPE_DUST = 28;
	public const TYPE_MOB_SPELL = 29;
	public const TYPE_MOB_SPELL_AMBIENT = 30;
	public const TYPE_MOB_SPELL_INSTANTANEOUS = 31;
	public const TYPE_INK = 32;
	public const TYPE_SLIME = 33;
	public const TYPE_RAIN_SPLASH = 34;
	public const TYPE_VILLAGER_ANGRY = 35;
	//59 same as 35
	public const TYPE_VILLAGER_HAPPY = 36;
	public const TYPE_ENCHANTMENT_TABLE = 37;
	public const TYPE_TRACKING_EMITTER = 38;
	public const TYPE_NOTE = 39;
	public const TYPE_WITCH_SPELL = 40;
	public const TYPE_CARROT = 41;
	//42 unknown
	public const TYPE_END_ROD = 43;
	//58 same as 43
	public const TYPE_DRAGONS_BREATH = 44;
	public const TYPE_SPIT = 45;
	public const TYPE_TOTEM = 46;
	public const TYPE_FOOD = 47;
	public const TYPE_FIREWORKS_STARTER = 48;
	public const TYPE_FIREWORKS_SPARK = 49;
	public const TYPE_FIREWORKS_OVERLAY = 50;
	public const TYPE_BALLOON_GAS = 51;
	public const TYPE_COLORED_FLAME = 52;
	public const TYPE_SPARKLER = 53;
	public const TYPE_CONDUIT = 54;
	public const TYPE_BUBBLE_COLUMN_UP = 55;
	public const TYPE_BUBBLE_COLUMN_DOWN = 56;
	public const TYPE_SNEEZE = 57;

	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

}
