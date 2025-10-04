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

use pocketmine\inventory\Inventory;

/**
 * Blocks which have an associated inventory of contents
 * Default implementation provided by {@see ContainerTrait}
 */
interface Container extends MenuAccessor{
	/**
	 * Returns whether an item with the given key as its custom name can be used to access the container's contents.
	 */
	public function canOpenWith(string $key) : bool;

	/**
	 * Returns the inventory of this container.
	 * Note: This may return NULL if the container's tile was missing or incorrect. This is rare, but may occur as a
	 * result of plugins incorrectly creating blocks, or legacy world data.
	 */
	public function getInventory() : ?Inventory;
}
