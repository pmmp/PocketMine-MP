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

final class DyeColor{

	/** @var DyeColor */
	private static $WHITE;
	/** @var DyeColor */
	private static $ORANGE;
	/** @var DyeColor */
	private static $MAGENTA;
	/** @var DyeColor */
	private static $LIGHT_BLUE;
	/** @var DyeColor */
	private static $YELLOW;
	/** @var DyeColor */
	private static $LIME;
	/** @var DyeColor */
	private static $PINK;
	/** @var DyeColor */
	private static $GRAY;
	/** @var DyeColor */
	private static $LIGHT_GRAY;
	/** @var DyeColor */
	private static $CYAN;
	/** @var DyeColor */
	private static $PURPLE;
	/** @var DyeColor */
	private static $BLUE;
	/** @var DyeColor */
	private static $BROWN;
	/** @var DyeColor */
	private static $GREEN;
	/** @var DyeColor */
	private static $RED;
	/** @var DyeColor */
	private static $BLACK;

	/* auto-generated code */

	public static function WHITE() : DyeColor{
		return self::$WHITE;
	}

	public static function ORANGE() : DyeColor{
		return self::$ORANGE;
	}

	public static function MAGENTA() : DyeColor{
		return self::$MAGENTA;
	}

	public static function LIGHT_BLUE() : DyeColor{
		return self::$LIGHT_BLUE;
	}

	public static function YELLOW() : DyeColor{
		return self::$YELLOW;
	}

	public static function LIME() : DyeColor{
		return self::$LIME;
	}

	public static function PINK() : DyeColor{
		return self::$PINK;
	}

	public static function GRAY() : DyeColor{
		return self::$GRAY;
	}

	public static function LIGHT_GRAY() : DyeColor{
		return self::$LIGHT_GRAY;
	}

	public static function CYAN() : DyeColor{
		return self::$CYAN;
	}

	public static function PURPLE() : DyeColor{
		return self::$PURPLE;
	}

	public static function BLUE() : DyeColor{
		return self::$BLUE;
	}

	public static function BROWN() : DyeColor{
		return self::$BROWN;
	}

	public static function GREEN() : DyeColor{
		return self::$GREEN;
	}

	public static function RED() : DyeColor{
		return self::$RED;
	}

	public static function BLACK() : DyeColor{
		return self::$BLACK;
	}

	/** @var DyeColor[] */
	private static $numericIdMap = [];
	/** @var DyeColor[] separate mapping that doesn't depend on magic numbers */
	private static $all = [];

	/**
	 * @internal
	 */
	public static function _init() : void{
		self::register(self::$WHITE = new DyeColor("White", 0));
		self::register(self::$ORANGE = new DyeColor("Orange", 1));
		self::register(self::$MAGENTA = new DyeColor("Magenta", 2));
		self::register(self::$LIGHT_BLUE = new DyeColor("Light Blue", 3));
		self::register(self::$YELLOW = new DyeColor("Yellow", 4));
		self::register(self::$LIME = new DyeColor("Lime", 5));
		self::register(self::$PINK = new DyeColor("Pink", 6));
		self::register(self::$GRAY = new DyeColor("Gray", 7));
		self::register(self::$LIGHT_GRAY = new DyeColor("Light Gray", 8));
		self::register(self::$CYAN = new DyeColor("Cyan", 9));
		self::register(self::$PURPLE = new DyeColor("Purple", 10));
		self::register(self::$BLUE = new DyeColor("Blue", 11));
		self::register(self::$BROWN = new DyeColor("Brown", 12));
		self::register(self::$GREEN = new DyeColor("Green", 13));
		self::register(self::$RED = new DyeColor("Red", 14));
		self::register(self::$BLACK = new DyeColor("Black", 15));
	}

	private static function register(DyeColor $color) : void{
		self::$numericIdMap[$color->getMagicNumber()] = $color;
		self::$all[] = $color;
	}

	/**
	 * Returns a set of all known dye colours.
	 *
	 * @return DyeColor[]
	 */
	public static function getAll() : array{
		return self::$all;
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

	private function __construct(string $displayName, int $magicNumber){
		$this->displayName = $displayName;
		$this->magicNumber = $magicNumber;
	}

	/**
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
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
DyeColor::_init();
