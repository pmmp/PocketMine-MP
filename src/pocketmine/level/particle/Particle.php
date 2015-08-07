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

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\DataPacket;

abstract class Particle extends Vector3{

	const TYPE_BUBBLE = 1;
	const TYPE_CRITICAL = 2;
	const TYPE_SMOKE = 3;
	const TYPE_EXPLODE = 4;
	const TYPE_WHITE_SMOKE = 5;
	const TYPE_FLAME = 6;
	const TYPE_LAVA = 7;
	const TYPE_LARGE_SMOKE = 8;
	const TYPE_REDSTONE = 9;
	const TYPE_ITEM_BREAK = 10;
	const TYPE_SNOWBALL_POOF = 11;
	const TYPE_LARGE_EXPLODE = 12;
	const TYPE_HUGE_EXPLODE = 13;
	const TYPE_MOB_FLAME = 14;
	const TYPE_HEART = 15;
	const TYPE_TERRAIN = 16;
	const TYPE_TOWN_AURA = 17;
	const TYPE_PORTAL = 18;
	const TYPE_WATER_SPLASH = 19;
	const TYPE_WATER_WAKE = 20;
	const TYPE_DRIP_WATER = 21;
	const TYPE_DRIP_LAVA = 22;
	const TYPE_DUST = 23;
	const TYPE_MOB_SPELL = 24;
	const TYPE_MOB_SPELL_AMBIENT = 25;
	const TYPE_MOB_SPELL_INSTANTANEOUS = 26;
	const TYPE_INK = 27;
	const TYPE_SLIME = 28;
	const TYPE_RAIN_SPLASH = 29;
	const TYPE_VILLAGER_ANGRY = 30;
	const TYPE_VILLAGER_HAPPY = 31;
	const TYPE_ENCHANTMENT_TABLE = 32;
	
	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

}
