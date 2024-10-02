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

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\timings\Timings;
use pocketmine\utils\ObjectSet;
use function array_shift;
use function assert;
use function count;

/**
 * This class is used to permit asynchronous event handling.
 *
 * When an event is called asynchronously, the event handlers are called by priority level.
 * When all the promises of a priority level have been resolved, the next priority level is called.
 */
abstract class AsyncEvent{
	/** @phpstan-var ObjectSet<Promise<null>> $promises */
	private ObjectSet $promises;
	/** @var array<class-string<AsyncEvent>, int> $delegatesCallDepth */
	private static array $delegatesCallDepth = [];
	private const MAX_EVENT_CALL_DEPTH = 50;

	/**
	 * @phpstan-return Promise<self>
	 */
	final public function call() : Promise{
		$this->promises = new ObjectSet();
		if(!isset(self::$delegatesCallDepth[$class = static::class])){
			self::$delegatesCallDepth[$class] = 0;
		}

		if(self::$delegatesCallDepth[$class] >= self::MAX_EVENT_CALL_DEPTH){
			//this exception will be caught by the parent event call if all else fails
			throw new \RuntimeException("Recursive event call detected (reached max depth of " . self::MAX_EVENT_CALL_DEPTH . " calls)");
		}

		$timings = Timings::getAsyncEventTimings($this);
		$timings->startTiming();

		++self::$delegatesCallDepth[$class];
		try{
			return $this->callAsyncDepth();
		}finally{
			--self::$delegatesCallDepth[$class];
			$timings->stopTiming();
		}
	}

	/**
	 * @phpstan-return Promise<self>
	 */
	private function callAsyncDepth() : Promise{
		/** @phpstan-var PromiseResolver<self> $globalResolver */
		$globalResolver = new PromiseResolver();

		$priorities = EventPriority::ALL;
		$testResolve = function () use (&$testResolve, &$priorities, $globalResolver){
			if(count($priorities) === 0){
				$globalResolver->resolve($this);
			}else{
				$this->callPriority(array_shift($priorities))->onCompletion(function() use ($testResolve) : void{
					$testResolve();
				}, function () use ($globalResolver) {
					$globalResolver->reject();
				});
			}
		};

		$testResolve();

		return $globalResolver->getPromise();
	}

	/**
	 * @phpstan-return Promise<null>
	 */
	private function callPriority(int $priority) : Promise{
		$handlers = HandlerListManager::global()->getListFor(static::class)->getListenersByPriority($priority);

		/** @phpstan-var PromiseResolver<null> $resolver */
		$resolver = new PromiseResolver();

		$nonConcurrentHandlers = [];
		foreach($handlers as $registration){
			assert($registration instanceof RegisteredAsyncListener);
			if($registration->canBeCalledConcurrently()){
				$result = $registration->callAsync($this);
				if($result !== null) {
					$this->promises->add($result);
				}
			}else{
				$nonConcurrentHandlers[] = $registration;
			}
		}

		$testResolve = function() use (&$nonConcurrentHandlers, &$testResolve, $resolver){
			if(count($nonConcurrentHandlers) === 0){
				$this->waitForPromises()->onCompletion(function() use ($resolver){
					$resolver->resolve(null);
				}, function() use ($resolver){
					$resolver->reject();
				});
			}else{
				$this->waitForPromises()->onCompletion(function() use (&$nonConcurrentHandlers, $testResolve){
					$handler = array_shift($nonConcurrentHandlers);
					assert($handler instanceof RegisteredAsyncListener);
					$result = $handler->callAsync($this);
					if($result !== null) {
						$this->promises->add($result);
					}
					$testResolve();
				}, function() use ($resolver) {
					$resolver->reject();
				});
			}
		};

		$testResolve();

		return $resolver->getPromise();
	}

	/**
	 * @phpstan-return Promise<array<int, null>>
	 */
	private function waitForPromises() : Promise{
		$array = $this->promises->toArray();
		$this->promises->clear();

		return Promise::all($array);
	}
}
