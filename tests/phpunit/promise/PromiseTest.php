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

final class PromiseTest extends TestCase{

	public function testPromiseNull() : void{
		$resolver = new PromiseResolver();
		$resolver->resolve(null);
		$resolver->getPromise()->onCompletion(
			function(mixed $value) : void{
				self::assertNull($value);
			},
			function() : void{
				self::fail("Promise should not be rejected");
			}
		);
	}

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
		self::assertFalse($done);
		$resolver->resolve(1);
		self::assertTrue($done);
	}

	public function testAllResolve() : void{
		$resolver1 = new PromiseResolver();
		$resolver2 = new PromiseResolver();

		$allPromise = Promise::all([$resolver1->getPromise(), $resolver2->getPromise()]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::assertEquals([1, 2], $value);
			},
			function() use (&$done) : void{
				$done = true;
				self::fail("Promise was rejected");
			}
		);
		self::assertFalse($done);
		$resolver1->resolve(1);
		self::assertFalse($done);
		$resolver2->resolve(2);
		self::assertTrue($done);
	}

	public function testAllPartialReject() : void{
		$resolver1 = new PromiseResolver();
		$resolver2 = new PromiseResolver();

		$allPromise = Promise::all([$resolver1->getPromise(), $resolver2->getPromise()]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::fail("Promise was unexpectedly resolved");
			},
			function() use (&$done) : void{
				$done = true;
			}
		);
		self::assertFalse($done);
		$resolver2->reject();
		self::assertTrue($done, "All promise should be rejected immediately after the first constituent rejection");
		$resolver1->resolve(1);
	}

	/**
	 * Promise::all() should return a rejected promise if any of the input promises were rejected at the call time
	 */
	public function testAllPartialPreReject() : void{
		$resolver1 = new PromiseResolver();
		$resolver2 = new PromiseResolver();
		$resolver2->reject();

		$allPromise = Promise::all([$resolver1->getPromise(), $resolver2->getPromise()]);
		$done = false;
		$allPromise->onCompletion(
			function($value) use (&$done) : void{
				$done = true;
				self::fail("Promise was unexpectedly resolved");
			},
			function() use (&$done) : void{
				$done = true;
			}
		);
		self::assertTrue($done, "All promise should be rejected immediately after the first constituent rejection");
		$resolver1->resolve(1);
	}
}
