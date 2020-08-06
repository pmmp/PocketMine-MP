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

use pocketmine\math\Axis;
use pocketmine\math\Facing;

final class BlockDataSerializer{

	private function __construct(){

	}

	/**
	 * @throws InvalidBlockStateException
	 */
	public static function readFacing(int $raw) : int{
		static $map = [ //this is for redundancy, for when/if the FACING constant values change
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST
		];
		if(!isset($map[$raw])){
			throw new InvalidBlockStateException("Invalid facing $raw");
		}
		return $map[$raw];
	}

	public static function writeFacing(int $facing) : int{
		static $map = [ //again, for redundancy
			Facing::DOWN => 0,
			Facing::UP => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		];
		if(!isset($map[$facing])){
			throw new \InvalidArgumentException("Invalid facing $facing");
		}
		return $map[$facing];
	}

	/**
	 * @throws InvalidBlockStateException
	 */
	public static function readHorizontalFacing(int $facing) : int{
		$facing = self::readFacing($facing);
		if(Facing::axis($facing) === Axis::Y){
			throw new InvalidBlockStateException("Invalid Y-axis facing $facing");
		}
		return $facing;
	}

	public static function writeHorizontalFacing(int $facing) : int{
		if(Facing::axis($facing) === Axis::Y){
			throw new \InvalidArgumentException("Invalid Y-axis facing");
		}
		return self::writeFacing($facing);
	}

	/**
	 * @throws InvalidBlockStateException
	 */
	public static function readLegacyHorizontalFacing(int $raw) : int{
		static $map = [ //again, for redundancy
			0 => Facing::SOUTH,
			1 => Facing::WEST,
			2 => Facing::NORTH,
			3 => Facing::EAST
		];
		if(!isset($map[$raw])){
			throw new InvalidBlockStateException("Invalid legacy facing $raw");
		}
		return $map[$raw];
	}

	public static function writeLegacyHorizontalFacing(int $facing) : int{
		static $map = [
			Facing::SOUTH => 0,
			Facing::WEST => 1,
			Facing::NORTH => 2,
			Facing::EAST => 3
		];
		if(!isset($map[$facing])){
			throw new \InvalidArgumentException("Invalid Y-axis facing");
		}
		return $map[$facing];
	}

	/**
	 * @throws InvalidBlockStateException
	 */
	public static function read5MinusHorizontalFacing(int $value) : int{
		return self::readHorizontalFacing(5 - ($value & 0x03));
	}

	public static function write5MinusHorizontalFacing(int $value) : int{
		return 5 - self::writeHorizontalFacing($value);
	}

	public static function readBoundedInt(string $name, int $v, int $min, int $max) : int{
		if($v < $min or $v > $max){
			throw new InvalidBlockStateException("$name should be in range $min - $max, got $v");
		}
		return $v;
	}
}
