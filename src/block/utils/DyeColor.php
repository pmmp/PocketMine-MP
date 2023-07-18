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
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
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
 */
final class DyeColor{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new DyeColor("white", "White", new Color(0xf0, 0xf0, 0xf0)),
			new DyeColor("orange", "Orange", new Color(0xf9, 0x80, 0x1d)),
			new DyeColor("magenta", "Magenta", new Color(0xc7, 0x4e, 0xbd)),
			new DyeColor("light_blue", "Light Blue", new Color(0x3a, 0xb3, 0xda)),
			new DyeColor("yellow", "Yellow", new Color(0xfe, 0xd8, 0x3d)),
			new DyeColor("lime", "Lime", new Color(0x80, 0xc7, 0x1f)),
			new DyeColor("pink", "Pink", new Color(0xf3, 0x8b, 0xaa)),
			new DyeColor("gray", "Gray", new Color(0x47, 0x4f, 0x52)),
			new DyeColor("light_gray", "Light Gray", new Color(0x9d, 0x9d, 0x97)),
			new DyeColor("cyan", "Cyan", new Color(0x16, 0x9c, 0x9c)),
			new DyeColor("purple", "Purple", new Color(0x89, 0x32, 0xb8)),
			new DyeColor("blue", "Blue", new Color(0x3c, 0x44, 0xaa)),
			new DyeColor("brown", "Brown", new Color(0x83, 0x54, 0x32)),
			new DyeColor("green", "Green", new Color(0x5e, 0x7c, 0x16)),
			new DyeColor("red", "Red", new Color(0xb0, 0x2e, 0x26)),
			new DyeColor("black", "Black", new Color(0x1d, 0x1d, 0x21))
		);
	}

	private function __construct(
		string $enumName,
		private string $displayName,
		private Color $rgbValue
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{
		return $this->displayName;
	}

	public function getRgbValue() : Color{
		return $this->rgbValue;
	}
}
