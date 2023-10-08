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
use function array_map;
use function array_reduce;

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

	/**
	 * @phpstan-var FlagValue[]
	 */
	public const ALL_FACING = [
		self::FLAG_DOWN,
		self::FLAG_UP,
		self::FLAG_NORTH,
		self::FLAG_SOUTH,
		self::FLAG_WEST,
		self::FLAG_EAST,
	];

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
	 * @phpstan-param FlagValue $flag
	 * @phpstan-return FacingValue
	 * @throws AssertionError if the flag is not a valid facing flag
	 */
	public static function toFacing(int $flag) : int{
		return match($flag){
			self::FLAG_DOWN => Facing::DOWN,
			self::FLAG_UP => Facing::UP,
			self::FLAG_NORTH => Facing::NORTH,
			self::FLAG_SOUTH => Facing::SOUTH,
			self::FLAG_WEST => Facing::WEST,
			self::FLAG_EAST => Facing::EAST,
			default => throw new AssertionError("Unknown facing flag $flag"),
		};
	}

	/**
	 * @phpstan-param Flag $flag
	 *
	 * @return int[]
	 * @phpstan-return FlagValue[]
	 */
	public static function getSides(int $flag) : array{
		$sides = [];
		foreach(self::ALL_FACING as $facing){
			if(($flag & $facing) !== 0){
				$sides[] = $facing;
			}
		}
		return $sides;
	}

	/**
	 * @phpstan-param Flag $flag
	 * @phpstan-param FlagValue ...$others
	 */
	public static function contain(int $flag, int ...$others) : bool{
		return ($flag & array_reduce($others, fn(int $carry, int $other) => $carry | $other, 0)) !== 0;
	}

	/**
	 * @phpstan-param Flag $flag
	 * @phpstan-param FacingValue ...$facings
	 */
	public static function containFacing(int $flag, int ...$facings) : bool{
		return self::contain($flag, ...array_map(fn(int $facing) => self::fromFacing($facing), $facings));
	}
}
