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

enum ChiseledBookshelfSlot : int{
	case TOP_LEFT = 0;
	case TOP_MIDDLE = 1;
	case TOP_RIGHT = 2;
	case BOTTOM_LEFT = 3;
	case BOTTOM_MIDDLE = 4;
	case BOTTOM_RIGHT = 5;

	private const SLOTS_PER_SHELF = 3;

	public static function fromBlockFaceCoordinates(float $x, float $y) : self{
		if($x < 0 || $x > 1){
			throw new \InvalidArgumentException("X must be between 0 and 1, got $x");
		}
		if($y < 0 || $y > 1){
			throw new \InvalidArgumentException("Y must be between 0 and 1, got $y");
		}

		$slot = ($y < 0.5 ? self::SLOTS_PER_SHELF : 0) + match(true){
			//we can't use simple maths here as the action is aligned to the 16x16 pixel grid :(
			$x < 6 / 16 => 0,
			$x < 11 / 16 => 1,
			default => 2
		};

		return self::from($slot);
	}
}
