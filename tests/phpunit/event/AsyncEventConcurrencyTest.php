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
use pocketmine\event\fixtures\TestGrandchildAsyncEvent;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use function count;

final class AsyncEventConcurrencyTest extends TestCase{

	private Plugin $mockPlugin;
	private PluginManager $pluginManager;

	//this one gets its own class because it requires a bunch of context variables

	/**
	 * @var PromiseResolver[]
	 * @phpstan-var array<int, PromiseResolver<null>>
	 */
	private array $resolvers = [];

	private bool $activeExclusiveHandler = false;
	private bool $activeConcurrentHandler = false;

	private int $done = 0;

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

	/**
	 * @phpstan-return Promise<null>
	 */
	private function handler(bool &$flag, string $label) : Promise{
		$flag = true;
		/** @var PromiseResolver<null> $resolver */
		$resolver = new PromiseResolver();
		$this->resolvers[] = $resolver;
		$resolver->getPromise()->onCompletion(
			function() use (&$flag) : void{
				$flag = false;
				$this->done++;
			},
			fn() => self::fail("Not expecting this to be rejected for $label")
		);
		return $resolver->getPromise();
	}

	public function testConcurrency() : void{
		$this->pluginManager->registerAsyncEvent(
			TestGrandchildAsyncEvent::class,
			function(TestGrandchildAsyncEvent $event) : Promise{
				self::assertFalse($this->activeExclusiveHandler, "Concurrent handler can't run while exclusive handlers are waiting to complete");

				return $this->handler($this->activeConcurrentHandler, "concurrent");
			},
			EventPriority::NORMAL,
			$this->mockPlugin,
			//non-exclusive - this must be completed before any exclusive handlers are run (or run after them)
		);
		for($i = 0; $i < 2; $i++){
			$this->pluginManager->registerAsyncEvent(
				TestGrandchildAsyncEvent::class,
				function(TestGrandchildAsyncEvent $event) use ($i) : Promise{
					self::assertFalse($this->activeExclusiveHandler, "Exclusive handler $i can't run alongside other exclusive handlers");
					self::assertFalse($this->activeConcurrentHandler, "Exclusive handler $i can't run alongside concurrent handler");

					return $this->handler($this->activeExclusiveHandler, "exclusive $i");
				},
				EventPriority::NORMAL,
				$this->mockPlugin,
				exclusiveCall: true
			);
		}

		(new TestGrandchildAsyncEvent())->call();

		while(count($this->resolvers) > 0 && $this->done < 3){
			foreach($this->resolvers as $k => $resolver){
				unset($this->resolvers[$k]);
				//don't clear the array here - resolving this will trigger adding the next resolver
				$resolver->resolve(null);
			}
		}

		self::assertSame(3, $this->done, "Expected feedback from exactly 3 handlers");
	}
}
