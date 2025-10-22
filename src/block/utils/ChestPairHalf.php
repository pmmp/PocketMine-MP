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

enum ChestPairHalf{
	/** This is the left half of the chest */
	case LEFT;
	/** This is the right half of the chest */
	case RIGHT;

	public function getOtherHalfSide(HorizontalFacingOption $hzFacing) : Facing{
		return match($this){
			self::RIGHT => Facing::rotateY($hzFacing->toFacing(), clockwise: true),
			self::LEFT => Facing::rotateY($hzFacing->toFacing(), clockwise: false)
		};
	}

	public function opposite() : self{
		return match($this){
			self::LEFT => self::RIGHT,
			self::RIGHT => self::LEFT
		};
	}
}
