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

namespace pocketmine\item;

use PHPUnit\Framework\TestCase;

class LegacyStringToItemParserTest extends TestCase{

	/**
	 * @return mixed[][]
	 * @phpstan-return list<array{string,int,int}>
	 */
	public function itemFromStringProvider() : array{
		return [
			["dye:4", ItemIds::DYE, 4],
			["351", ItemIds::DYE, 0],
			["351:4", ItemIds::DYE, 4],
			["stone:3", ItemIds::STONE, 3],
			["minecraft:string", ItemIds::STRING, 0],
			["diamond_pickaxe", ItemIds::DIAMOND_PICKAXE, 0],
			["diamond_pickaxe:5", ItemIds::DIAMOND_PICKAXE, 5]
		];
	}

	/**
	 * @dataProvider itemFromStringProvider
	 */
	public function testFromStringSingle(string $string, int $id, int $meta) : void{
		$item = LegacyStringToItemParser::getInstance()->parse($string);

		self::assertEquals($id, $item->getId());
		self::assertEquals($meta, $item->getMeta());
	}
}
