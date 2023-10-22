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

/**
 * This interface is implemented by an Event subclass if and only if it can be called asynchronously.
 *
 * Used with {@see AsyncEventTrait} to provide a way to call an event asynchronously.
 * When an event is called asynchronously, the event handlers are called by priority level.
 * When all the promises of a priority level have been resolved, the next priority level is called.
 */
interface AsyncEvent{
	/**
	 * Be prudent, calling an event asynchronously can produce unexpected results.
	 * During the execution of the event, the server, the player and the event context may have changed state.
	 *
	 * @phpstan-return Promise<null>
	 */
	public function callAsync() : Promise;
}
