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

namespace pocketmine\item;

/**
 * Lock options for items in a player's inventory. These options are only respected when the items are in a player's
 * inventory. They are ignored when the item is a chest or other container.
 */
enum ItemLockMode{
	/**
	 * Unrestricted item movement (default)
	 */
	case NONE;
	/**
	 * The item can be moved to any storage slot of the main inventory (including the cursor), but cannot be dropped,
	 * moved to a container, crafted with, or otherwise removed from the inventory.
	 */
	case PLAYER_INVENTORY;
	/**
	 * Same as INVENTORY, but additionally prevents the item from being removed from its slot.
	 */
	case PLAYER_INVENTORY_SLOT;
}
