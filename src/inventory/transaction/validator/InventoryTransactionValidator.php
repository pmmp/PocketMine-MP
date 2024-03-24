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

namespace pocketmine\inventory\transaction\validator;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\SlotSafeInventory;
use pocketmine\item\Item;

/**
 * This interface is used to validate the transactions of a "safe" inventory. {@see SlotSafeInventory}
 *
 * In this way, each inventory can determine whether or not an item has the right to be placed in a particular slot.
 */
interface InventoryTransactionValidator{
	/**
	 * @return bool Return true, if the item CAN be placed in the given slot of the given inventory.
	 * Otherwise, return false and the transaction will be cancelled.
	 */
	public function validate(Inventory $inventory, Item $item, int $slot) : bool;
}
