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

use pocketmine\math\Bearing;
use pocketmine\math\Facing;

final class BlockDataValidator{

	private function __construct(){

	}

	/**
	 * @param int $facing
	 *
	 * @return int
	 * @throws InvalidBlockStateException
	 */
	public static function readFacing(int $facing) : int{
		try{
			Facing::validate($facing);
		}catch(\InvalidArgumentException $e){
			throw new InvalidBlockStateException("Invalid facing $facing", 0, $e);
		}
		return $facing;
	}

	/**
	 * @param int $facing
	 *
	 * @return int
	 * @throws InvalidBlockStateException
	 */
	public static function readHorizontalFacing(int $facing) : int{
		$facing = self::readFacing($facing);
		if(Facing::axis($facing) === Facing::AXIS_Y){
			throw new InvalidBlockStateException("Invalid Y-axis facing $facing");
		}
		return $facing;
	}

	/**
	 * @param int $facing
	 *
	 * @return int
	 * @throws InvalidBlockStateException
	 */
	public static function readLegacyHorizontalFacing(int $facing) : int{
		try{
			$facing = Bearing::toFacing($facing);
		}catch(\InvalidArgumentException $e){
			throw new InvalidBlockStateException("Invalid legacy facing $facing");
		}
		return self::readHorizontalFacing($facing);
	}

	/**
	 * @param int $value
	 *
	 * @return int
	 * @throws InvalidBlockStateException
	 */
	public static function read5MinusHorizontalFacing(int $value) : int{
		return self::readHorizontalFacing(5 - ($value & 0x03));
	}

	public static function readBoundedInt(string $name, int $v, int $min, int $max) : int{
		if($v < $min or $v > $max){
			throw new InvalidBlockStateException("$name should be in range $min - $max, got $v");
		}
		return $v;
	}
}
