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
 * @method static BannerPatternType BORDER()
 * @method static BannerPatternType BRICKS()
 * @method static BannerPatternType CIRCLE()
 * @method static BannerPatternType CREEPER()
 * @method static BannerPatternType CROSS()
 * @method static BannerPatternType CURLY_BORDER()
 * @method static BannerPatternType DIAGONAL_LEFT()
 * @method static BannerPatternType DIAGONAL_RIGHT()
 * @method static BannerPatternType DIAGONAL_UP_LEFT()
 * @method static BannerPatternType DIAGONAL_UP_RIGHT()
 * @method static BannerPatternType FLOWER()
 * @method static BannerPatternType GRADIENT()
 * @method static BannerPatternType GRADIENT_UP()
 * @method static BannerPatternType HALF_HORIZONTAL()
 * @method static BannerPatternType HALF_HORIZONTAL_BOTTOM()
 * @method static BannerPatternType HALF_VERTICAL()
 * @method static BannerPatternType HALF_VERTICAL_RIGHT()
 * @method static BannerPatternType MOJANG()
 * @method static BannerPatternType RHOMBUS()
 * @method static BannerPatternType SKULL()
 * @method static BannerPatternType SMALL_STRIPES()
 * @method static BannerPatternType SQUARE_BOTTOM_LEFT()
 * @method static BannerPatternType SQUARE_BOTTOM_RIGHT()
 * @method static BannerPatternType SQUARE_TOP_LEFT()
 * @method static BannerPatternType SQUARE_TOP_RIGHT()
 * @method static BannerPatternType STRAIGHT_CROSS()
 * @method static BannerPatternType STRIPE_BOTTOM()
 * @method static BannerPatternType STRIPE_CENTER()
 * @method static BannerPatternType STRIPE_DOWNLEFT()
 * @method static BannerPatternType STRIPE_DOWNRIGHT()
 * @method static BannerPatternType STRIPE_LEFT()
 * @method static BannerPatternType STRIPE_MIDDLE()
 * @method static BannerPatternType STRIPE_RIGHT()
 * @method static BannerPatternType STRIPE_TOP()
 * @method static BannerPatternType TRIANGLES_BOTTOM()
 * @method static BannerPatternType TRIANGLES_TOP()
 * @method static BannerPatternType TRIANGLE_BOTTOM()
 * @method static BannerPatternType TRIANGLE_TOP()
 */
enum BannerPatternType{
	use LegacyEnumShimTrait;

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
	case FLOW;
	case FLOWER;
	case GLOBE;
	case GRADIENT;
	case GRADIENT_UP;
	case GUSTER;
	case HALF_HORIZONTAL;
	case HALF_HORIZONTAL_BOTTOM;
	case HALF_VERTICAL;
	case HALF_VERTICAL_RIGHT;
	case MOJANG;
	case PIGLIN;
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
