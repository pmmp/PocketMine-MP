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

namespace pocketmine\plugin;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @purpose (api map test purpose)
 */
class ApiMapTest extends TestCase{
	public function testGetSet() : void{
		$apiMap = new ApiMap;
		$apiMap->provideApi(TestCase::class, null, $this);
		self::assertSame($this, $apiMap->getApi(TestCase::class));
		self::assertNull($apiMap->getApi(self::class));
	}

	public function testWrongInheritance() : void{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('$impl is an instance of pocketmine\plugin\ApiMap, which does not extend/implement pocketmine\plugin\ApiMapTest');
		$apiMap = new ApiMap;
		$apiMap->provideApi(self::class, null, new ApiMap);
	}

	public function testConflict() : void{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("Multiple plugins (PocketMine, PocketMine) are providing (api map test purpose). Please disable one of them or check configuration.");

		$apiMap = new ApiMap;
		$apiMap->provideApi(self::class, null, $this);
		$apiMap->provideApi(self::class, null, $this);
	}

	public function testDefault() : void{
		$apiMap = new ApiMap;

		$foo = new stdClass;
		$bar = new stdClass;

		$apiMap->provideApi(stdClass::class, null, $foo, true);
		$apiMap->provideApi(stdClass::class, null, $bar, false);
		self::assertSame($bar, $apiMap->getApi(stdClass::class));


		$apiMap = new ApiMap;

		$foo = new stdClass;
		$bar = new stdClass;

		$apiMap->provideApi(stdClass::class, null, $foo, false);
		$apiMap->provideApi(stdClass::class, null, $bar, true);
		self::assertSame($foo, $apiMap->getApi(stdClass::class));
	}
}

