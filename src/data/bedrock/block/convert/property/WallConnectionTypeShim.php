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

namespace pocketmine\data\bedrock\block\convert\property;

use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\bedrock\block\BlockStateStringValues;

/**
 * Internally we use null for no connections, but accepting this in the mapping code would require a fair amount of
 * extra complexity for this one case. This shim allows us to use the regular systems for handling walls.
 * TODO: get rid of this in PM6 and make the internal enum have a NONE case
 */
enum WallConnectionTypeShim{
	case NONE;
	case SHORT;
	case TALL;

	/**
	 * TODO: Would've just put this as enum values, but enum backing values can't reference constants in other files in
	 * PHP 8.1 :(
	 */
	public function getValue() : string{
		return match($this){
			self::NONE => BlockStateStringValues::WALL_CONNECTION_TYPE_EAST_NONE,
			self::SHORT => BlockStateStringValues::WALL_CONNECTION_TYPE_EAST_SHORT,
			self::TALL => BlockStateStringValues::WALL_CONNECTION_TYPE_EAST_TALL,
		};
	}

	public function deserialize() : ?WallConnectionType{
		return match($this){
			self::NONE => null,
			self::SHORT => WallConnectionType::SHORT,
			self::TALL => WallConnectionType::TALL,
		};
	}

	public static function serialize(?WallConnectionType $value) : self{
		return match($value){
			null => self::NONE,
			WallConnectionType::SHORT => self::SHORT,
			WallConnectionType::TALL => self::TALL,
		};
	}
}
