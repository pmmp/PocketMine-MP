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
use pocketmine\event\fixtures\TestParentAsyncEvent;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use function is_int;

final class ConcurrentAsyncEventLimiterTest extends TestCase{

	private Plugin $mockPlugin;
	private PluginManager $pluginManager;

	private bool $handlerA = false;

	protected function setUp() : void{
		AsyncHandlerListManager::global()->unregisterAll();

		//TODO: this is a really bad hack and could break any time if PluginManager decides to access its Server field
		//we really need to make it possible to register events without a Plugin or Server context
		$mockServer = $this->createMock(Server::class);
		$this->mockPlugin = self::createStub(Plugin::class);
		$this->mockPlugin->method('isEnabled')->willReturn(true);
		$this->pluginManager = new PluginManager($mockServer, null);
	}

	public function tearDown() : void{
		AsyncHandlerListManager::global()->unregisterAll();
	}

	public function testMaxConcurrentCallsNotBlocking() : void{
		self::expectNotToPerformAssertions();
		$this->pluginManager->registerAsyncEvent(
			TestParentAsyncEvent::class,
			function(TestParentAsyncEvent $e) : ?Promise{
				return null;
			},
			EventPriority::NORMAL,
			$this->mockPlugin,
		);

		for($i = 0; $i < $this->getMaxConcurrentCalls() + 1; $i++){
			(new TestParentAsyncEvent())->call();
		}
	}

	public function testMaxConcurrentCallsBlocking() : void{
		$this->expectConcurrentCallLimitReached();
		$this->pluginManager->registerAsyncEvent(
			TestParentAsyncEvent::class,
			function(TestParentAsyncEvent $e) : Promise{
				/** @var PromiseResolver<null> $resolver */
				$resolver = new PromiseResolver();
				return $resolver->getPromise();
			},
			EventPriority::NORMAL,
			$this->mockPlugin,
		);

		for($i = 0; $i < $this->getMaxConcurrentCalls() + 1; $i++){
			(new TestParentAsyncEvent())->call();
		}
	}

	public function testMaxConcurrentCallsDifferentHandlers() : void{
		$resolvers = [];
		$this->pluginManager->registerAsyncEvent(
			TestParentAsyncEvent::class,
			function(TestParentAsyncEvent $e) use (&$resolvers) : ?Promise{
				if($this->handlerA){
					return null;
				}
				/** @var PromiseResolver<null> $resolver */
				$resolver = new PromiseResolver();
				$resolvers[] = $resolver;
				return $resolver->getPromise();
			},
			EventPriority::NORMAL,
			$this->mockPlugin,
		);
		$this->pluginManager->registerAsyncEvent(
			TestParentAsyncEvent::class,
			function(TestParentAsyncEvent $e) use (&$resolvers) : ?Promise{
				if(!$this->handlerA){
					return null;
				}
				/** @var PromiseResolver<null> $resolver */
				$resolver = new PromiseResolver();
				$resolvers[] = $resolver;
				return $resolver->getPromise();
			},
			EventPriority::NORMAL,
			$this->mockPlugin,
		);

		for($i = 0; $i < $this->getMaxConcurrentCalls() + 1; $i++){
			$this->handlerA = !$this->handlerA;
			(new TestParentAsyncEvent())->call();
		}
		self::assertCount($this->getMaxConcurrentCalls() + 1, $resolvers, "Expected all promises to be pending");

		//resolve all the pending promises to clear concurrent calls stack
		foreach($resolvers as $resolver){
			$resolver->resolve(null);
		}
		$resolvers = [];

		$this->expectConcurrentCallLimitReached();
		for($i = 0; $i < ($this->getMaxConcurrentCalls() * 2) + 1; $i++){
			$this->handlerA = !$this->handlerA;
			(new TestParentAsyncEvent())->call();
		}
	}

	private function getMaxConcurrentCalls() : int{
		$refClass = new \ReflectionClass(AsyncEvent::class);
		$value = $refClass->getConstant('MAX_CONCURRENT_CALLS');
		return is_int($value) ? $value : throw new \AssertionError("Max concurrent calls should be an integer");
	}

	private function expectConcurrentCallLimitReached() : void{
		self::expectException(\RuntimeException::class);
		self::expectExceptionMessageMatches("/Concurrent call limit reached/");
	}
}
