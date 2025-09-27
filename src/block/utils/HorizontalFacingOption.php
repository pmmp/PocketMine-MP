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

enum HorizontalFacingOption : int{
	case NORTH = Facing::NORTH->value;
	case SOUTH = Facing::SOUTH->value;
	case WEST = Facing::WEST->value;
	case EAST = Facing::EAST->value;

	public static function tryFromFacing(Facing $facing) : ?self{
		return match($facing){
			Facing::NORTH => self::NORTH,
			Facing::SOUTH => self::SOUTH,
			Facing::WEST => self::WEST,
			Facing::EAST => self::EAST,
			default => null,
		};
	}

	public static function fromFacing(Facing $facing) : self{
		return self::tryFromFacing($facing) ?? throw new \InvalidArgumentException("Facing $facing->name cannot be converted to a horizontal facing");
	}

	public function toFacing() : Facing{
		return match($this){
			self::NORTH => Facing::NORTH,
			self::SOUTH => Facing::SOUTH,
			self::WEST => Facing::WEST,
			self::EAST => Facing::EAST,
		};
	}
}
