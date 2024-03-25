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

enum StructureAnimationMode{
	case NONE;
	case BY_LAYER;
	case BY_BLOCK;

	/**
	 * @internal
	 * @throws \ValueError
	 */
	public static function fromInt(int $mode) : StructureAnimationMode{
		return match($mode){
			0 => self::NONE,
			1 => self::BY_LAYER,
			2 => self::BY_BLOCK,
			default => throw new \ValueError("Unknown structure animation mode " . $mode),
		};
	}

	/**
	 * @internal
	 */
	public function toInt() : int{
		return match($this){
			self::NONE => 0,
			self::BY_LAYER => 1,
			self::BY_BLOCK => 2,
		};
	}
};
