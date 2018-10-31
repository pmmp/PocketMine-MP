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

class Color{
	public const WHITE = 0;
	public const ORANGE = 1;
	public const MAGENTA = 2;
	public const LIGHT_BLUE = 3;
	public const YELLOW = 4;
	public const LIME = 5;
	public const PINK = 6;
	public const GRAY = 7;
	public const LIGHT_GRAY = 8;
	public const CYAN = 9;
	public const PURPLE = 10;
	public const BLUE = 11;
	public const BROWN = 12;
	public const GREEN = 13;
	public const RED = 14;
	public const BLACK = 15;

	public const ALL = [
		self::WHITE,
		self::ORANGE,
		self::MAGENTA,
		self::LIGHT_BLUE,
		self::YELLOW,
		self::LIME,
		self::PINK,
		self::GRAY,
		self::LIGHT_GRAY,
		self::CYAN,
		self::PURPLE,
		self::BLUE,
		self::BROWN,
		self::GREEN,
		self::RED,
		self::BLACK
	];

	public const NAMES = [
		self::WHITE => "White",
		self::ORANGE => "Orange",
		self::MAGENTA => "Magenta",
		self::LIGHT_BLUE => "Light Blue",
		self::YELLOW => "Yellow",
		self::LIME => "Lime",
		self::PINK => "Pink",
		self::GRAY => "Gray",
		self::LIGHT_GRAY => "Light Gray",
		self::CYAN => "Cyan",
		self::PURPLE => "Purple",
		self::BLUE => "Blue",
		self::BROWN => "Brown",
		self::GREEN => "Green",
		self::RED => "Red",
		self::BLACK => "Black"
	];
}
