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

namespace pocketmine\block\utils;

use pocketmine\math\Facing;

final class NearbyBlockChangeFlags{
	public function __construct(){
		// NOOP
	}

	public const FLAG_SELF = 1;
	public const FLAG_DOWN = self::FLAG_SELF << 1;
	public const FLAG_UP = self::FLAG_DOWN << 1;
	public const FLAG_NORTH = self::FLAG_UP << 1;
	public const FLAG_SOUTH = self::FLAG_NORTH << 1;
	public const FLAG_WEST = self::FLAG_SOUTH << 1;
	public const FLAG_EAST = self::FLAG_WEST << 1;

	public static function fromFacing(int $facing) : int{
		return match($facing){
			Facing::DOWN => self::FLAG_DOWN,
			Facing::UP => self::FLAG_UP,
			Facing::NORTH => self::FLAG_NORTH,
			Facing::SOUTH => self::FLAG_SOUTH,
			Facing::WEST => self::FLAG_WEST,
			Facing::EAST => self::FLAG_EAST,
			default => throw new \InvalidArgumentException("Unknown facing $facing"),
		};
	}

	public static function toFacing(int $flag) : ?int{
		return match($flag){
			self::FLAG_DOWN => Facing::DOWN,
			self::FLAG_UP => Facing::UP,
			self::FLAG_NORTH => Facing::NORTH,
			self::FLAG_SOUTH => Facing::SOUTH,
			self::FLAG_WEST => Facing::WEST,
			self::FLAG_EAST => Facing::EAST,
			default => null,
		};
	}
}
