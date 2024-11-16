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

namespace pocketmine\block;

use PHPUnit\Framework\TestCase;
use pocketmine\utils\Utils;
use function array_unique;
use function max;

class BlockTypeIdsTest extends TestCase{

	public function testFirstUnused() : void{
		$reflect = new \ReflectionClass(BlockTypeIds::class);

		$constants = $reflect->getConstants();
		unset($constants['FIRST_UNUSED_BLOCK_ID']);

		self::assertSame($reflect->getConstant('FIRST_UNUSED_BLOCK_ID'), max($constants) + 1, "FIRST_UNUSED_BLOCK_ID must be one higher than the highest fixed type ID");
	}

	public function testNoDuplicates() : void{
		$idTable = (new \ReflectionClass(BlockTypeIds::class))->getConstants();

		self::assertSameSize($idTable, array_unique($idTable), "Every BlockTypeID must be unique");
	}

	public function testVanillaBlocksParity() : void{
		$reflect = new \ReflectionClass(BlockTypeIds::class);

		foreach(Utils::stringifyKeys(VanillaBlocks::getAll()) as $name => $block){
			$expected = $block->getTypeId();
			$actual = $reflect->getConstant($name);
			self::assertNotFalse($actual, "VanillaBlocks::$name() does not have a BlockTypeIds constant");
			self::assertSame($expected, $actual, "VanillaBlocks::$name() does not match BlockTypeIds::$name");
		}
	}
}
