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

namespace pocketmine\data\bedrock;

final class EffectIds{

	private function __construct(){
		//NOOP
	}

	public const SPEED = 1;
	public const SLOWNESS = 2;
	public const HASTE = 3;
	public const MINING_FATIGUE = 4;
	public const STRENGTH = 5;
	public const INSTANT_HEALTH = 6;
	public const INSTANT_DAMAGE = 7;
	public const JUMP_BOOST = 8;
	public const NAUSEA = 9;
	public const REGENERATION = 10;
	public const RESISTANCE = 11;
	public const FIRE_RESISTANCE = 12;
	public const WATER_BREATHING = 13;
	public const INVISIBILITY = 14;
	public const BLINDNESS = 15;
	public const NIGHT_VISION = 16;
	public const HUNGER = 17;
	public const WEAKNESS = 18;
	public const POISON = 19;
	public const WITHER = 20;
	public const HEALTH_BOOST = 21;
	public const ABSORPTION = 22;
	public const SATURATION = 23;
	public const LEVITATION = 24;
	public const FATAL_POISON = 25;
	public const CONDUIT_POWER = 26;
	public const SLOW_FALLING = 27;
	public const BAD_OMEN = 28;
	public const VILLAGE_HERO = 29;
}
