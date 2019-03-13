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

use pocketmine\utils\Color;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self WHITE()
 * @method static self ORANGE()
 * @method static self MAGENTA()
 * @method static self LIGHT_BLUE()
 * @method static self YELLOW()
 * @method static self LIME()
 * @method static self PINK()
 * @method static self GRAY()
 * @method static self LIGHT_GRAY()
 * @method static self CYAN()
 * @method static self PURPLE()
 * @method static self BLUE()
 * @method static self BROWN()
 * @method static self GREEN()
 * @method static self RED()
 * @method static self BLACK()
 */
final class DyeColor{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var DyeColor[] */
	private static $numericIdMap = [];

	protected static function setup() : array{
		return [
			new DyeColor("white", "White", 0, new Color(0xf0, 0xf0, 0xf0)),
			new DyeColor("orange", "Orange", 1, new Color(0xf9, 0x80, 0x1d)),
			new DyeColor("magenta", "Magenta", 2, new Color(0xc7, 0x4e, 0xbd)),
			new DyeColor("light_blue", "Light Blue", 3, new Color(0x3a, 0xb3, 0xda)),
			new DyeColor("yellow", "Yellow", 4, new Color(0xfe, 0xd8, 0x3d)),
			new DyeColor("lime", "Lime", 5, new Color(0x80, 0xc7, 0x1f)),
			new DyeColor("pink", "Pink", 6, new Color(0xf3, 0x8b, 0xaa)),
			new DyeColor("gray", "Gray", 7, new Color(0x47, 0x4f, 0x52)),
			new DyeColor("light_gray", "Light Gray", 8, new Color(0x9d, 0x9d, 0x97)),
			new DyeColor("cyan", "Cyan", 9, new Color(0x16, 0x9c, 0x9c)),
			new DyeColor("purple", "Purple", 10, new Color(0x89, 0x32, 0xb8)),
			new DyeColor("blue", "Blue", 11, new Color(0x3c, 0x44, 0xaa)),
			new DyeColor("brown", "Brown", 12, new Color(0x83, 0x54, 0x32)),
			new DyeColor("green", "Green", 13, new Color(0x5e, 0x7c, 0x16)),
			new DyeColor("red", "Red", 14, new Color(0xb0, 0x2e, 0x26)),
			new DyeColor("black", "Black", 15, new Color(0x1d, 0x1d, 0x21)),
		];
	}

	protected static function register(DyeColor $color) : void{
		self::Enum_register($color);
		self::$numericIdMap[$color->getMagicNumber()] = $color;
	}

	/**
	 * Returns a DyeColor object matching the given magic number
	 * @internal
	 *
	 * @param int  $magicNumber
	 * @param bool $inverted Invert the ID before using it (useful for actual dye magic IDs)
	 *
	 * @return DyeColor
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber, bool $inverted = false) : DyeColor{
		self::checkInit();
		$real = $inverted ? ~$magicNumber & 0xf : $magicNumber;
		if(!isset(self::$numericIdMap[$real])){
			throw new \InvalidArgumentException("Unknown dye colour magic number $magicNumber");
		}
		return self::$numericIdMap[$real];
	}

	/** @var string */
	private $displayName;
	/** @var int */
	private $magicNumber;
	/** @var Color */
	private $rgbValue;

	private function __construct(string $enumName, string $displayName, int $magicNumber, Color $rgbValue){
		$this->Enum___construct($enumName);
		$this->displayName = $displayName;
		$this->magicNumber = $magicNumber;
		$this->rgbValue = $rgbValue;
	}

	/**
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * @return Color
	 */
	public function getRgbValue() : Color{
		return $this->rgbValue;
	}

	/**
	 * @return int
	 */
	public function getMagicNumber() : int{
		return $this->magicNumber;
	}

	/**
	 * @return int
	 */
	public function getInvertedMagicNumber() : int{
		return ~$this->magicNumber & 0xf;
	}
}
