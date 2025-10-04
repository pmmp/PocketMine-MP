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

use pocketmine\data\bedrock\block\BlockLegacyMetadata;

enum RailShape : int{
	//TODO: these values really need to be removed
	case FLAT_AXIS_Z = BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH;
	case FLAT_AXIS_X = BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST;
	case ASCENDING_EAST = BlockLegacyMetadata::RAIL_ASCENDING_EAST;
	case ASCENDING_WEST = BlockLegacyMetadata::RAIL_ASCENDING_WEST;
	case ASCENDING_NORTH = BlockLegacyMetadata::RAIL_ASCENDING_NORTH;
	case ASCENDING_SOUTH = BlockLegacyMetadata::RAIL_ASCENDING_SOUTH;

	case CURVED_SOUTHEAST = BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST;
	case CURVED_SOUTHWEST = BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST;
	case CURVED_NORTHWEST = BlockLegacyMetadata::RAIL_CURVE_NORTHWEST;
	case CURVED_NORTHEAST = BlockLegacyMetadata::RAIL_CURVE_NORTHEAST;
}
