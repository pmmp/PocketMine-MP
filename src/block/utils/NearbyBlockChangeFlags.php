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

use AssertionError;
use pocketmine\math\Facing;

/**
 * @phpstan-type FlagValue NearbyBlockChangeFlags::FLAG_*
 * @phpstan-type Flag int-mask-of<FlagValue>
 * @phpstan-type FacingValue int
 * FacingValue can be value-of<Facing::ALL> in the future when Facing functions no longer return generic integers.
 */
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

	public const FLAG_HORIZONTAL = self::FLAG_NORTH | self::FLAG_SOUTH | self::FLAG_WEST | self::FLAG_EAST;

	/**
	 * @phpstan-param FacingValue $facing
	 *
	 * @phpstan-return FlagValue
	 */
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

	/**
	 * @phpstan-param Flag $flags
	 *
	 * @return int[]
	 * @phpstan-return FacingValue[]
	 */
	public static function getFaces(int $flags) : array{
		$result = [];
		foreach([
			self::FLAG_DOWN => Facing::DOWN,
			self::FLAG_UP => Facing::UP,
			self::FLAG_NORTH => Facing::NORTH,
			self::FLAG_SOUTH => Facing::SOUTH,
			self::FLAG_WEST => Facing::WEST,
			self::FLAG_EAST => Facing::EAST,
		] as $flag => $facing){
			if(($flags & $flag) !== 0){
				$result[] = $facing;
			}
		}
		return $result;
	}

	/**
	 * @phpstan-param Flag $flag
	 * @phpstan-param FacingValue ...$facings
	 */
	public static function hasFaces(int $flag, int ...$facings) : bool{
		foreach($facings as $facing){
			if(($flag & self::fromFacing($facing)) !== 0){
				return true;
			}
		}

		return false;
	}
}
