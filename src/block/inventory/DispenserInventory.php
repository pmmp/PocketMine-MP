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

namespace pocketmine\block\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\item\VanillaItems;
use pocketmine\world\Position;
use function array_rand;
use function count;

class DispenserInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	public function __construct(Position $holder, int $size = 9){
		$this->holder = $holder;
		parent::__construct($size);
	}

	/** @return int A random slot containing a non-empty item stack */
	public function getRandomSlot() : int{
		$slots = [];
		for($slot = 0; $slot < $this->getSize(); ++$slot){
			if(!$this->getItem($slot)->equals(VanillaItems::AIR())){
				$slots[] = $slot;
			}
		}
		return count($slots) > 0 ? $slots[array_rand($slots)] : -1;
	}
}
