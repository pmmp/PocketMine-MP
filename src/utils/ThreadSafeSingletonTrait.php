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

namespace pocketmine\utils;

use pmmp\thread\Thread;
use pmmp\thread\ThreadSafe;
use function get_debug_type;

/**
 * Used by singletons which have a common instance accessible on all threads for process-global state.
 * Note that the using class must extend {@link ThreadSafe} for this to work.
 */
trait ThreadSafeSingletonTrait{
	private static ?self $instanceCache = null;

	private static function make() : self{
		return new self();
	}

	public static function getInstance() : self{
		if(self::$instanceCache !== null){
			return self::$instanceCache;
		}

		$threadSafeGlobals = Thread::getSharedGlobals();
		return self::$instanceCache = $threadSafeGlobals->synchronized(function() use ($threadSafeGlobals) : self{
			$key = self::getProcessGlobalStateKey();
			$processGlobal = $threadSafeGlobals[$key];
			if($processGlobal === null){
				return $threadSafeGlobals[$key] = new self();
			}elseif($processGlobal instanceof self){
				return $processGlobal;
			}
			throw new \LogicException("Dynamic ID processGlobal in superglobals is of wrong type, expected " . self::class . ", but got " . get_debug_type($processGlobal));
		});
	}

	final protected static function getProcessGlobalStateKey() : string{
		return self::class . "::processGlobalState";
	}
}
