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

enum StructureBlockType{
	case DATA;
	case SAVE;
	case LOAD;
	case CORNER;
	case INVALID;
	case EXPORT;

	/**
	 *  @throws \UnexpectedValueException
	 */
	public static function fromInt(int $type) : StructureBlockType{
		return match($type){
			0 => self::DATA,
			1 => self::SAVE,
			2 => self::LOAD,
			3 => self::CORNER,
			4 => self::INVALID,
			5 => self::EXPORT,
			default => throw new \UnexpectedValueException("Unknown structure block type " . $type),
		};
	}

	public static function toInt(StructureBlockType $type) : int{
		return match($type){
			self::DATA => 0,
			self::SAVE => 1,
			self::LOAD => 2,
			self::CORNER => 3,
			self::INVALID => 4,
			self::EXPORT => 5,
		};
	}
}
