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

enum BannerPatternType{
	case BORDER;
	case BRICKS;
	case CIRCLE;
	case CREEPER;
	case CROSS;
	case CURLY_BORDER;
	case DIAGONAL_LEFT;
	case DIAGONAL_RIGHT;
	case DIAGONAL_UP_LEFT;
	case DIAGONAL_UP_RIGHT;
	case FLOWER;
	case GRADIENT;
	case GRADIENT_UP;
	case HALF_HORIZONTAL;
	case HALF_HORIZONTAL_BOTTOM;
	case HALF_VERTICAL;
	case HALF_VERTICAL_RIGHT;
	case MOJANG;
	case RHOMBUS;
	case SKULL;
	case SMALL_STRIPES;
	case SQUARE_BOTTOM_LEFT;
	case SQUARE_BOTTOM_RIGHT;
	case SQUARE_TOP_LEFT;
	case SQUARE_TOP_RIGHT;
	case STRAIGHT_CROSS;
	case STRIPE_BOTTOM;
	case STRIPE_CENTER;
	case STRIPE_DOWNLEFT;
	case STRIPE_DOWNRIGHT;
	case STRIPE_LEFT;
	case STRIPE_MIDDLE;
	case STRIPE_RIGHT;
	case STRIPE_TOP;
	case TRIANGLE_BOTTOM;
	case TRIANGLE_TOP;
	case TRIANGLES_BOTTOM;
	case TRIANGLES_TOP;
}
