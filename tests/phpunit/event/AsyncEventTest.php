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
use pocketmine\event\fixtures\TestChildAsyncEvent;
use pocketmine\event\fixtures\TestGrandchildAsyncEvent;
use pocketmine\event\fixtures\TestParentAsyncEvent;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use function shuffle;

final class AsyncEventTest extends TestCase{
	private Plugin $mockPlugin;
	private PluginManager $pluginManager;

	protected function setUp() : void{
		AsyncHandlerListManager::global()->unregisterAll();

		//TODO: this is a really bad hack and could break any time if PluginManager decides to access its Server field
		//we really need to make it possible to register events without a Plugin or Server context
		$mockServer = $this->createMock(Server::class);
		$this->mockPlugin = self::createStub(Plugin::class);
		$this->mockPlugin->method('isEnabled')->willReturn(true);
		$this->pluginManager = new PluginManager($mockServer, null);
	}

	public static function tearDownAfterClass() : void{
		AsyncHandlerListManager::global()->unregisterAll();
	}

	public function testHandlerInheritance() : void{
		$expectedOrder = [
			TestGrandchildAsyncEvent::class,
			TestChildAsyncEvent::class,
			TestParentAsyncEvent::class
		];
		$classes = $expectedOrder;
		$actualOrder = [];
		shuffle($classes);
		foreach($classes as $class){
			$this->pluginManager->registerAsyncEvent(
				$class,
				function(AsyncEvent $event) use (&$actualOrder, $class) : ?Promise{
					$actualOrder[] = $class;
					return null;
				},
				EventPriority::NORMAL,
				$this->mockPlugin
			);
		}

		$event = new TestGrandchildAsyncEvent();
		$promise = $event->call();

		$resolved = false;
		$promise->onCompletion(
			function() use ($expectedOrder, $actualOrder, &$resolved){
				self::assertSame($expectedOrder, $actualOrder, "Expected event handlers to be called from most specific to least specific");
				$resolved = true;
			},
			fn() => self::fail("Not expecting this to be rejected")
		);

		self::assertTrue($resolved, "No promises were used, expected this promise to resolve immediately");
	}

	public function testPriorityLock() : void{
		$resolver = null;
		$firstCompleted = false;
		$run = 0;

		$this->pluginManager->registerAsyncEvent(
			TestGrandchildAsyncEvent::class,
			function(TestGrandchildAsyncEvent $event) use (&$resolver, &$firstCompleted, &$run) : Promise{
				$run++;
				/** @var PromiseResolver<null> $resolver */
				$resolver = new PromiseResolver();

				$resolver->getPromise()->onCompletion(
					function() use (&$firstCompleted) : void{ $firstCompleted = true; },
					fn() => self::fail("Not expecting this to be rejected")
				);

				return $resolver->getPromise();
			},
			EventPriority::LOW, //anything below NORMAL is fine
			$this->mockPlugin
		);
		$this->pluginManager->registerAsyncEvent(
			TestGrandchildAsyncEvent::class,
			function(TestGrandchildAsyncEvent $event) use (&$firstCompleted, &$run) : ?Promise{
				$run++;
				self::assertTrue($firstCompleted, "This shouldn't run until the previous priority is done");
				return null;
			},
			EventPriority::NORMAL,
			$this->mockPlugin
		);

		(new TestGrandchildAsyncEvent())->call();
		self::assertNotNull($resolver, "First handler didn't provide a resolver");
		$resolver->resolve(null);
		self::assertSame(2, $run, "Expected feedback from 2 handlers");
	}
}
