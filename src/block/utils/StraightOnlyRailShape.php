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

enum StraightOnlyRailShape : int{
	case FLAT_AXIS_Z = RailShape::FLAT_AXIS_Z->value;
	case FLAT_AXIS_X = RailShape::FLAT_AXIS_X->value;
	case ASCENDING_EAST = RailShape::ASCENDING_EAST->value;
	case ASCENDING_WEST = RailShape::ASCENDING_WEST->value;
	case ASCENDING_NORTH = RailShape::ASCENDING_NORTH->value;
	case ASCENDING_SOUTH = RailShape::ASCENDING_SOUTH->value;
}
