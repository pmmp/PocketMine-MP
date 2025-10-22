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

namespace pocketmine\block\utils;

/**
 * Blocks which have audiovisual behaviour (like chests) and remain in their "open" state for as long as at least 1
 * viewer is viewing the menu they provide access to
 */
interface AnimatedContainerLike extends MenuAccessor{
	/**
	 * Do actions when the container block is opened by a player.
	 * If you have a custom viewer counter (like ender chests), you should increment it here.
	 */
	public function onViewerAdded() : void;

	/**
	 * Do actions when the container block is closed by a player.
	 * As above, you should decrement your custom viewer counter here, if you have one.
	 */
	public function onViewerRemoved() : void;
}
