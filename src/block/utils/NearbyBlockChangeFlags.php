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

/**
 * @phpstan-type FlagValue NearbyBlockChangeFlags::*
 * @phpstan-type Flag int-mask-of<FlagValue>
 * @phpstan-type FacingValue int
 * FacingValue can be value-of<Facing::ALL> in the future when Facing functions no longer return generic integers.
 */
final class NearbyBlockChangeFlags{
	public function __construct(){
		// NOOP
	}

	public const DOWN = 1 << 0;
	public const UP = 1 << 1;
	public const NORTH = 1 << 2;
	public const SOUTH = 1 << 3;
	public const WEST = 1 << 4;
	public const EAST = 1 << 5;

	public const HORIZONTAL = self::NORTH | self::SOUTH | self::WEST | self::EAST;
	public const VERTICAL = self::DOWN | self::UP;
	public const ALL = self::HORIZONTAL | self::VERTICAL;

	/**
	 * @phpstan-param FacingValue $facing
	 *
	 * @phpstan-return FlagValue
	 */
	public static function fromFacing(int $facing) : int{
		return match($facing){
			Facing::DOWN => self::DOWN,
			Facing::UP => self::UP,
			Facing::NORTH => self::NORTH,
			Facing::SOUTH => self::SOUTH,
			Facing::WEST => self::WEST,
			Facing::EAST => self::EAST,
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
		if($flags === 0){
			return [];
		}
		$result = [];
		foreach([
			self::DOWN => Facing::DOWN,
			self::UP => Facing::UP,
			self::NORTH => Facing::NORTH,
			self::SOUTH => Facing::SOUTH,
			self::WEST => Facing::WEST,
			self::EAST => Facing::EAST,
		] as $flag => $facing){
			if(($flags & $flag) !== 0){
				$result[] = $facing;
			}
		}
		return $result;
	}

	/**
	 * @phpstan-param Flag $flag
	 * @phpstan-param FacingValue $facing
	 */
	public static function hasFace(int $flag, int $facing) : bool{
		return ($flag & self::fromFacing($facing)) !== 0;
	}
}
