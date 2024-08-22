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

use pocketmine\inventory\transaction\action\validator\SlotValidator;
use pocketmine\utils\ObjectSet;

/**
 * A "slot validated inventory" has validators which may restrict items
 * from being placed in particular slots of the inventory when transactions are executed.
 *
 * @phpstan-type SlotValidators ObjectSet<SlotValidator>
 */
interface SlotValidatedInventory{
	/**
	 * Returns a set of validators that will be used to determine whether an item can be placed in a particular slot.
	 * All validators need to return null for the transaction to be allowed.
	 * If one of the validators returns an exception, the transaction will be cancelled.
	 *
	 * There is no guarantee that the validators will be called in any particular order.
	 *
	 * @phpstan-return SlotValidators
	 */
	public function getSlotValidators() : ObjectSet;
}
