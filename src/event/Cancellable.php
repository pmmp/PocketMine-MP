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

/**
 * This interface is implemented by an Event subclass if and only if it can be cancelled.
 *
 * The cancellation of an event directly affects whether downstream event handlers
 * without `@handleCancelled` will be called with this event.
 * Implementations may provide a direct setter for cancellation (typically by using `CancellableTrait`)
 * or implement an alternative logic (such as a function on another data field) for `isCancelled()`.
 */
interface Cancellable{
	/**
	 * Returns whether this instance of the event is currently cancelled.
	 *
	 * If it is cancelled, only downstream handlers that declare `@handleCancelled` will be called with this event.
	 */
	public function isCancelled() : bool;
}
