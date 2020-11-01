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

namespace pocketmine\event;

use PHPUnit\Framework\TestCase;

class HandlerListManagerTest extends TestCase{

	/** @var \Closure */
	private $isValidFunc;
	/** @var \Closure */
	private $resolveParentFunc;

	public function setUp() : void{
		/** @see HandlerListManager::isValidClass() */
		$this->isValidFunc = (new \ReflectionMethod(HandlerListManager::class, 'isValidClass'))->getClosure();
		/** @see HandlerListManager::resolveNearestHandleableParent() */
		$this->resolveParentFunc = (new \ReflectionMethod(HandlerListManager::class, 'resolveNearestHandleableParent'))->getClosure();
	}

	/**
	 * @return \Generator|mixed[][]
	 * @phpstan-return \Generator<int, array{\ReflectionClass<Event>, bool, string}, void, void>
	 */
	public function isValidClassProvider() : \Generator{
		yield [new \ReflectionClass(Event::class), false, "event base should not be handleable"];
		yield [new \ReflectionClass(TestConcreteEvent::class), true, ""];
		yield [new \ReflectionClass(TestAbstractEvent::class), false, "abstract event cannot be handled"];
		yield [new \ReflectionClass(TestAbstractAllowHandleEvent::class), true, "abstract event declaring @allowHandle should be handleable"];
	}

	/**
	 * @dataProvider isValidClassProvider
	 *
	 * @phpstan-param \ReflectionClass<Event> $class
	 */
	public function testIsValidClass(\ReflectionClass $class, bool $isValid, string $reason) : void{
		self::assertSame($isValid, ($this->isValidFunc)($class), $reason);
	}

	/**
	 * @return \Generator|\ReflectionClass[][]
	 * @phpstan-return \Generator<int, array{\ReflectionClass<Event>, \ReflectionClass<Event>|null}, void, void>
	 */
	public function resolveParentClassProvider() : \Generator{
		yield [new \ReflectionClass(TestConcreteExtendsAllowHandleEvent::class), new \ReflectionClass(TestAbstractAllowHandleEvent::class)];
		yield [new \ReflectionClass(TestConcreteEvent::class), null];
		yield [new \ReflectionClass(TestConcreteExtendsAbstractEvent::class), null];
		yield [new \ReflectionClass(TestConcreteExtendsConcreteEvent::class), new \ReflectionClass(TestConcreteEvent::class)];
	}

	/**
	 * @dataProvider resolveParentClassProvider
	 *
	 * @phpstan-param \ReflectionClass<Event>      $class
	 * @phpstan-param \ReflectionClass<Event>|null $expect
	 */
	public function testResolveParentClass(\ReflectionClass $class, ?\ReflectionClass $expect) : void{
		if($expect === null){
			self::assertNull(($this->resolveParentFunc)($class));
		}else{
			self::assertSame(($this->resolveParentFunc)($class)->getName(), $expect->getName());
		}
	}
}
