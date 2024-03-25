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

namespace pocketmine\inventory;

use Closure;
use pocketmine\item\Item;
use pocketmine\utils\ObjectSet;

/**
 * A "slot safe inventory" is an inventory that has a set of rules that determine whether an item can be placed in a
 * particular slot. This ensures that the inventory's internal state is always valid and consistent.
 */
interface SlotSafeInventory{
	/**
	 * Return a set of validators that will be used to determine whether an item can be placed in a particular slot.
	 * One validator need to return true for the transaction to be allowed.
	 * If all the validators returns false, the transaction will be cancelled.
	 *
	 * There is no guarantee that the validators will be called in any particular order.
	 * All validators need to be stateless and not depend on the order in which they are called.
	 *
	 * @phpstan-return ObjectSet<Closure(Inventory $inventory, Item $item, int $slot): bool>
	 */
	public function getSlotValidators() : ObjectSet;
}
