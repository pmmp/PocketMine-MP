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
use pocketmine\utils\ObjectSet;
use function array_shift;
use function count;

trait AsyncEventTrait {
	/** @phpstan-var ObjectSet<Promise<null>> */
	private ObjectSet $promises;

	/**
	 * @phpstan-param ObjectSet<Promise<null>>|null $promises
	 */
	private function initializePromises(?ObjectSet &$promises) : void{
		$promises ??= new ObjectSet();
		$this->promises = $promises;
	}

	public function addPromise(Promise $promise) : void{
		if(!isset($this->promises)){
			throw new \RuntimeException("Cannot add promises, be sure to initialize the promises set in the constructor");
		}
		$this->promises->add($promise);
	}

	final public static function callAsync(AsyncEvent&Event $event, ObjectSet $promiseSet) : Promise{
		$event->checkMaxDepthCall();

		/** @phpstan-var PromiseResolver<null> $globalResolver */
		$globalResolver = new PromiseResolver();

		$callable = function(int $priority) use ($event, $promiseSet) : Promise{
			$handlers = HandlerListManager::global()->getListFor(static::class)->getListenersByPriority($priority);
			$event->callHandlers($handlers);

			$array = $promiseSet->toArray();
			$promiseSet->clear();

			return Promise::all($array);
		};

		$priorities = EventPriority::ALL;
		$testResolve = function () use (&$testResolve, &$priorities, $callable, $globalResolver){
			if(count($priorities) === 0){
				$globalResolver->resolve(null);
			}else{
				$callable(array_shift($priorities))->onCompletion(function() use ($testResolve) : void{
					$testResolve();
				}, function () use ($globalResolver) {
					$globalResolver->reject();
				});
			}
		};

		$testResolve();

		return $globalResolver->getPromise();
	}
}
