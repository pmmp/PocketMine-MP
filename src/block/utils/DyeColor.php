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

use pocketmine\color\Color;
use pocketmine\utils\LegacyEnumShimTrait;
use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static DyeColor BLACK()
 * @method static DyeColor BLUE()
 * @method static DyeColor BROWN()
 * @method static DyeColor CYAN()
 * @method static DyeColor GRAY()
 * @method static DyeColor GREEN()
 * @method static DyeColor LIGHT_BLUE()
 * @method static DyeColor LIGHT_GRAY()
 * @method static DyeColor LIME()
 * @method static DyeColor MAGENTA()
 * @method static DyeColor ORANGE()
 * @method static DyeColor PINK()
 * @method static DyeColor PURPLE()
 * @method static DyeColor RED()
 * @method static DyeColor WHITE()
 * @method static DyeColor YELLOW()
 *
 * @phpstan-type TMetadata array{0: string, 1: Color}
 */
enum DyeColor{
	use LegacyEnumShimTrait;

	case WHITE;
	case ORANGE;
	case MAGENTA;
	case LIGHT_BLUE;
	case YELLOW;
	case LIME;
	case PINK;
	case GRAY;
	case LIGHT_GRAY;
	case CYAN;
	case PURPLE;
	case BLUE;
	case BROWN;
	case GREEN;
	case RED;
	case BLACK;

	/**
	 * This function exists only to permit the use of named arguments and to make the code easier to read in PhpStorm.
	 *
	 * @phpstan-return TMetadata
	 */
	private static function meta(string $displayName, Color $rgbValue) : array{
		return [$displayName, $rgbValue];
	}

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this){
			self::WHITE => self::meta("White", new Color(0xf0, 0xf0, 0xf0)),
			self::ORANGE => self::meta("Orange", new Color(0xf9, 0x80, 0x1d)),
			self::MAGENTA => self::meta("Magenta", new Color(0xc7, 0x4e, 0xbd)),
			self::LIGHT_BLUE => self::meta("Light Blue", new Color(0x3a, 0xb3, 0xda)),
			self::YELLOW => self::meta("Yellow", new Color(0xfe, 0xd8, 0x3d)),
			self::LIME => self::meta("Lime", new Color(0x80, 0xc7, 0x1f)),
			self::PINK => self::meta("Pink", new Color(0xf3, 0x8b, 0xaa)),
			self::GRAY => self::meta("Gray", new Color(0x47, 0x4f, 0x52)),
			self::LIGHT_GRAY => self::meta("Light Gray", new Color(0x9d, 0x9d, 0x97)),
			self::CYAN => self::meta("Cyan", new Color(0x16, 0x9c, 0x9c)),
			self::PURPLE => self::meta("Purple", new Color(0x89, 0x32, 0xb8)),
			self::BLUE => self::meta("Blue", new Color(0x3c, 0x44, 0xaa)),
			self::BROWN => self::meta("Brown", new Color(0x83, 0x54, 0x32)),
			self::GREEN => self::meta("Green", new Color(0x5e, 0x7c, 0x16)),
			self::RED => self::meta("Red", new Color(0xb0, 0x2e, 0x26)),
			self::BLACK => self::meta("Black", new Color(0x1d, 0x1d, 0x21)),
		};
	}

	public function getDisplayName() : string{
		return $this->getMetadata()[0];
	}

	public function getRgbValue() : Color{
		return $this->getMetadata()[1];
	}
}
