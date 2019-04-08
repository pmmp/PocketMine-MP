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

namespace pocketmine\utils;

use PHPUnit\Framework\TestCase;
use pocketmine\utils\TextFormat as T;
use function array_keys;
use function implode;

class TextFormatTest extends TestCase{

	public function compressProvider() : \Generator{
		yield [T::GREEN . T::AQUA . T::RED . T::LIGHT_PURPLE . T::YELLOW . T::WHITE . T::BLACK . T::DARK_BLUE . "&hello " . T::AQUA . "world" . T::RESET, T::DARK_BLUE . "&hello " . T::AQUA . "world" . T::RESET];
		yield [T::BLACK . T::DARK_BLUE . T::DARK_GREEN . "&" . T::RESET . "hello", T::DARK_GREEN . "&" . T::RESET . "hello"];
		yield [T::DARK_AQUA . T::DARK_RED . T::DARK_PURPLE . T::ITALIC . T::RESET . "Hello World", "Hello World"];
		yield [T::RESET . "hello", "hello"];
		yield ["hello" . T::RESET, "hello"];
		yield [implode("|", array_keys(T::COLORS)) . "|", implode("|", array_keys(T::COLORS)) . "|"];
	}

	/**
	 * @dataProvider compressProvider
	 * @param string $input
	 * @param string $expected
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCompress(string $input, string $expected) : void{
		self::assertSame($expected, TextFormat::compress($input));
	}
}
