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

use pocketmine\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static MushroomBlockType ALL_CAP()
 * @method static MushroomBlockType CAP_EAST()
 * @method static MushroomBlockType CAP_MIDDLE()
 * @method static MushroomBlockType CAP_NORTH()
 * @method static MushroomBlockType CAP_NORTHEAST()
 * @method static MushroomBlockType CAP_NORTHWEST()
 * @method static MushroomBlockType CAP_SOUTH()
 * @method static MushroomBlockType CAP_SOUTHEAST()
 * @method static MushroomBlockType CAP_SOUTHWEST()
 * @method static MushroomBlockType CAP_WEST()
 * @method static MushroomBlockType PORES()
 */
enum MushroomBlockType{
	use LegacyEnumShimTrait;

	case PORES;
	case CAP_NORTHWEST;
	case CAP_NORTH;
	case CAP_NORTHEAST;
	case CAP_WEST;
	case CAP_MIDDLE;
	case CAP_EAST;
	case CAP_SOUTHWEST;
	case CAP_SOUTH;
	case CAP_SOUTHEAST;
	case ALL_CAP;
}
