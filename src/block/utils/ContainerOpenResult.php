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

enum ContainerOpenResult{
	/**
	 * Opening the container succeeded and the player is now viewing the contents
	 */
	case SUCCESS;
	/**
	 * No container tile (and therefore no content) was found on the block's position
	 */
	case CONTAINER_NOT_FOUND;
	/**
	 * The container's opening is obstructed (e.g a block on top of a chest's lid)
	 */
	case OBSTRUCTED;
	/**
	 * The container is locked and the used item doesn't have the correct custom name to unlock it
	 */
	case INCORRECT_KEY;
}
