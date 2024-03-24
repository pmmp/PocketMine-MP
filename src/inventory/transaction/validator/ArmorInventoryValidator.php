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

use pocketmine\block\BlockTypeIds;
use pocketmine\block\MobHead;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

class ArmorInventoryValidator implements InventoryTransactionValidator{
	public function validate(Inventory $inventory, Item $item, int $slot) : bool{
		return $inventory instanceof ArmorInventory && (
				($item instanceof Armor && $item->getArmorSlot() === $slot) ||
				($slot === ArmorInventory::SLOT_HEAD && $item instanceof ItemBlock && (
						$item->getBlock()->getTypeId() === BlockTypeIds::CARVED_PUMPKIN ||
						$item->getBlock() instanceof MobHead
					))
			);
	}
}
