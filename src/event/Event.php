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

/**
 * Event related classes
 */
namespace pocketmine\event;

use pocketmine\timings\Timings;
use function count;
use function get_class;

abstract class Event{
	private const MAX_EVENT_CALL_DEPTH = 50;

	private static int $eventCallDepth = 1;

	protected ?string $eventName = null;

	final public function getEventName() : string{
		return $this->eventName ?? get_class($this);
	}

	/**
	 * Calls event handlers registered for this event.
	 *
	 * @throws \RuntimeException if event call recursion reaches the max depth limit
	 */
	public function call() : void{
		if(self::$eventCallDepth >= self::MAX_EVENT_CALL_DEPTH){
			//this exception will be caught by the parent event call if all else fails
			throw new \RuntimeException("Recursive event call detected (reached max depth of " . self::MAX_EVENT_CALL_DEPTH . " calls)");
		}

		$timings = Timings::getEventTimings($this);
		$timings->startTiming();

		$handlers = HandlerListManager::global()->getHandlersFor(static::class);

		++self::$eventCallDepth;
		try{
			foreach($handlers as $registration){
				$registration->callEvent($this);
			}
		}finally{
			--self::$eventCallDepth;
			$timings->stopTiming();
		}
	}

	/**
	 * Returns whether the current class context has any registered global handlers.
	 * This can be used in hot code paths to avoid unnecessary event object creation.
	 *
	 * Usage: SomeEventClass::hasHandlers()
	 */
	public static function hasHandlers() : bool{
		return count(HandlerListManager::global()->getHandlersFor(static::class)) > 0;
	}
}
