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

use function uasort;

/**
 * @phpstan-extends BaseHandlerListManager<AsyncEvent, AsyncRegisteredListener>
 */
final class AsyncHandlerListManager extends BaseHandlerListManager{
	private static ?self $globalInstance = null;

	public static function global() : self{
		return self::$globalInstance ?? (self::$globalInstance = new self());
	}

	protected function getBaseEventClass() : string{
		return AsyncEvent::class;
	}

	/**
	 * @phpstan-param array<int, AsyncRegisteredListener> $listeners
	 * @phpstan-return array<int, AsyncRegisteredListener>
	 */
	private static function sortSamePriorityHandlers(array $listeners) : array{
		uasort($listeners, function(AsyncRegisteredListener $left, AsyncRegisteredListener $right) : int{
			//Promise::all() can be used more efficiently if concurrent handlers are grouped together.
			//It's not important whether they are grouped before or after exclusive handlers.
			return $left->canBeCalledConcurrently() <=> $right->canBeCalledConcurrently();
		});
		return $listeners;
	}

	protected function createHandlerList(string $event, ?HandlerList $parentList, RegisteredListenerCache $handlerCache) : HandlerList{
		return new HandlerList($event, $parentList, $handlerCache, self::sortSamePriorityHandlers(...));
	}
}
