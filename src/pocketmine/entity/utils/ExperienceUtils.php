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

namespace pocketmine\entity\utils;

use pocketmine\math\Math;
use pocketmine\utils\AssumptionFailedError;
use function max;

abstract class ExperienceUtils{

	/**
	 * Calculates and returns the amount of XP needed to get from level 0 to level $level
	 */
	public static function getXpToReachLevel(int $level) : int{
		if($level <= 16){
			return $level ** 2 + $level * 6;
		}elseif($level <= 31){
			return (int) ($level ** 2 * 2.5 - 40.5 * $level + 360);
		}

		return (int) ($level ** 2 * 4.5 - 162.5 * $level + 2220);
	}

	/**
	 * Returns the amount of XP needed to reach $level + 1.
	 */
	public static function getXpToCompleteLevel(int $level) : int{
		if($level <= 15){
			return 2 * $level + 7;
		}elseif($level <= 30){
			return 5 * $level - 38;
		}else{
			return 9 * $level - 158;
		}
	}

	/**
	 * Calculates and returns the number of XP levels the specified amount of XP points are worth.
	 * This returns a floating-point number, the decimal part being the progress through the resulting level.
	 */
	public static function getLevelFromXp(int $xp) : float{
		if($xp < 0){
			throw new \InvalidArgumentException("XP must be at least 0");
		}
		if($xp <= self::getXpToReachLevel(16)){
			$a = 1;
			$b = 6;
			$c = 0;
		}elseif($xp <= self::getXpToReachLevel(31)){
			$a = 2.5;
			$b = -40.5;
			$c = 360;
		}else{
			$a = 4.5;
			$b = -162.5;
			$c = 2220;
		}

		$x = Math::solveQuadratic($a, $b, $c - $xp);
		if(count($x) === 0){
			throw new AssumptionFailedError("Expected at least 1 solution");
		}

		return max($x); //we're only interested in the positive solution
	}
}
