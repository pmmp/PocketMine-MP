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

namespace pocketmine\promise;

use PHPUnit\Framework\TestCase;

class PromiseTest extends TestCase{

	public function testAllPreResolved() : void{
		$resolver = new PromiseResolver();
		$resolver->resolve(1);

		$allPromise = Promise::all([$resolver->getPromise()]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::assertEquals([1], $value);
			},
			function() use (&$done) : void{
				$done = true;
				self::fail("Promise was rejected");
			}
		);
		self::assertTrue($done);
	}

	public function testAllPostResolved() : void{
		$resolver = new PromiseResolver();

		$allPromise = Promise::all([$resolver->getPromise()]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::assertEquals([1], $value);
			},
			function() use (&$done) : void{
				$done = true;
				self::fail("Promise was rejected");
			}
		);
		$resolver->resolve(1);
		self::assertTrue($done);
	}

	public function testAllNoPromises() : void{
		$allPromise = Promise::all([]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::assertEquals([], $value);
			},
			function() use (&$done) : void{
				$done = true;
				self::fail("Promise was rejected");
			}
		);
		self::assertTrue($done);
	}
}
