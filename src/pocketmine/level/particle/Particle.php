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
	public const TYPE_LAVA = 9;
	public const TYPE_LARGE_SMOKE = 10;
	public const TYPE_REDSTONE = 11;
	public const TYPE_RISING_RED_DUST = 12;
	public const TYPE_ITEM_BREAK = 13;
	public const TYPE_SNOWBALL_POOF = 14;
	public const TYPE_HUGE_EXPLODE = 15;
	public const TYPE_HUGE_EXPLODE_SEED = 16;
	public const TYPE_MOB_FLAME = 17;
	public const TYPE_HEART = 18;
	public const TYPE_TERRAIN = 19;
	public const TYPE_SUSPENDED_TOWN = 20, TYPE_TOWN_AURA = 20;
	public const TYPE_PORTAL = 21;
	//22 same as 21
	public const TYPE_SPLASH = 23, TYPE_WATER_SPLASH = 23;
	public const TYPE_WATER_SPLASH_MANUAL = 24;
	public const TYPE_WATER_WAKE = 25;
	public const TYPE_DRIP_WATER = 26;
	public const TYPE_DRIP_LAVA = 27;
	public const TYPE_DRIP_HONEY = 28;
	public const TYPE_FALLING_DUST = 29, TYPE_DUST = 29;
	public const TYPE_MOB_SPELL = 30;
	public const TYPE_MOB_SPELL_AMBIENT = 31;
	public const TYPE_MOB_SPELL_INSTANTANEOUS = 32;
	public const TYPE_INK = 33;
	public const TYPE_SLIME = 34;
	public const TYPE_RAIN_SPLASH = 35;
	public const TYPE_VILLAGER_ANGRY = 36;
	public const TYPE_VILLAGER_HAPPY = 37;
	public const TYPE_ENCHANTMENT_TABLE = 38;
	public const TYPE_TRACKING_EMITTER = 39;
	public const TYPE_NOTE = 40;
	public const TYPE_WITCH_SPELL = 41;
	public const TYPE_CARROT = 42;
	public const TYPE_MOB_APPEARANCE = 43;
	public const TYPE_END_ROD = 44;
	public const TYPE_DRAGONS_BREATH = 45;
	public const TYPE_SPIT = 46;
	public const TYPE_TOTEM = 47;
	public const TYPE_FOOD = 48;
	public const TYPE_FIREWORKS_STARTER = 49;
	public const TYPE_FIREWORKS_SPARK = 50;
	public const TYPE_FIREWORKS_OVERLAY = 51;
	public const TYPE_BALLOON_GAS = 52;
	public const TYPE_COLORED_FLAME = 53;
	public const TYPE_SPARKLER = 54;
	public const TYPE_CONDUIT = 55;
	public const TYPE_BUBBLE_COLUMN_UP = 56;
	public const TYPE_BUBBLE_COLUMN_DOWN = 57;
	public const TYPE_SNEEZE = 58;
	public const TYPE_SHULKER_BULLET = 59;
	public const TYPE_BLEACH = 60;
	public const TYPE_DRAGON_DESTROY_BLOCK = 61;
	public const TYPE_MYCELIUM_DUST = 62;
	public const TYPE_FALLING_RED_DUST = 63;
	public const TYPE_CAMPFIRE_SMOKE = 64;
	public const TYPE_TALL_CAMPFIRE_SMOKE = 65;
	public const TYPE_DRAGON_BREATH_FIRE = 66;
	public const TYPE_DRAGON_BREATH_TRAIL = 67;

	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

}
