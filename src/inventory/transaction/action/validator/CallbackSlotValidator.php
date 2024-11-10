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

namespace pocketmine\inventory\transaction\action\validator;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

class CallbackSlotValidator implements SlotValidator{
	/**
	 * @phpstan-param \Closure(Inventory, Item, int) : ?TransactionValidationException $validate
	 */
	public function __construct(
		private \Closure $validate
	){
		Utils::validateCallableSignature(function(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException{ return null; }, $validate);
	}

	public function validate(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException{
		return ($this->validate)($inventory, $item, $slot);
	}
}
