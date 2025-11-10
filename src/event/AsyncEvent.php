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
use pocketmine\utils\Utils;
use function count;
use function spl_object_id;

/**
 * This class is used to permit asynchronous event handling.
 *
 * When an event is called asynchronously, the event handlers are called by priority level.
 * When all the promises of a priority level have been resolved, the next priority level is called.
 */
abstract class AsyncEvent{
	/** @var array<int, int> $handlersCallState */
	private static array $handlersCallState = [];
	private const MAX_CONCURRENT_CALLS = 1000; //max number of concurrent calls to a single handler

	/**
	 * @phpstan-return Promise<static>
	 */
	final public function call() : Promise{
		$timings = Timings::getAsyncEventTimings($this);
		$timings->startTiming();

		try{
			/** @phpstan-var PromiseResolver<static> $globalResolver */
			$globalResolver = new PromiseResolver();

			$handlers = AsyncHandlerListManager::global()->getHandlersFor(static::class);
			if(count($handlers) > 0){
				$this->processRemainingHandlers($handlers, fn() => $globalResolver->resolve($this), $globalResolver->reject(...));
			}else{
				$globalResolver->resolve($this);
			}

			return $globalResolver->getPromise();
		}finally{
			$timings->stopTiming();
		}
	}

	/**
	 * @param AsyncRegisteredListener[] $handlers
	 * @phpstan-param array<int, AsyncRegisteredListener> $handlers
	 * @phpstan-param \Closure() : void $resolve
	 * @phpstan-param \Closure() : void $reject
	 */
	private function processRemainingHandlers(array $handlers, \Closure $resolve, \Closure $reject) : void{
		$currentPriority = null;
		$awaitPromises = [];
		foreach($handlers as $k => $handler){
			$priority = $handler->getPriority();
			if(count($awaitPromises) > 0 && $currentPriority !== null && $currentPriority !== $priority){
				//wait for concurrent promises from previous priority to complete
				break;
			}

			$currentPriority = $priority;
			if(!isset(self::$handlersCallState[$handlerId = spl_object_id($handler)])){
				self::$handlersCallState[$handlerId] = 0;
			}
			if(self::$handlersCallState[$handlerId] >= self::MAX_CONCURRENT_CALLS){
				throw new \RuntimeException("Concurrent call limit reached for handler " .
					Utils::getNiceClosureName($handler->getHandler()) . "(" . Utils::getNiceClassName($this) . ")" .
					" (max: " . self::MAX_CONCURRENT_CALLS . ")");
			}
			$removeCallback = static function() use ($handlerId) : void{
				--self::$handlersCallState[$handlerId];
			};
			if($handler->canBeCalledConcurrently()){
				unset($handlers[$k]);
				++self::$handlersCallState[$handlerId];
				$promise = $handler->callAsync($this);
				if($promise !== null){
					$promise->onCompletion($removeCallback, $removeCallback);
					$awaitPromises[] = $promise;
				}else{
					$removeCallback();
				}
			}else{
				if(count($awaitPromises) > 0){
					//wait for concurrent promises to complete
					break;
				}

				unset($handlers[$k]);
				++self::$handlersCallState[$handlerId];
				$promise = $handler->callAsync($this);
				if($promise !== null){
					$promise->onCompletion($removeCallback, $removeCallback);
					$promise->onCompletion(
						onSuccess: fn() => $this->processRemainingHandlers($handlers, $resolve, $reject),
						onFailure: $reject
					);
					return;
				}
				$removeCallback();
			}
		}

		if(count($awaitPromises) > 0){
			Promise::all($awaitPromises)->onCompletion(
				onSuccess: fn() => $this->processRemainingHandlers($handlers, $resolve, $reject),
				onFailure: $reject
			);
		}else{
			$resolve();
		}
	}
}
