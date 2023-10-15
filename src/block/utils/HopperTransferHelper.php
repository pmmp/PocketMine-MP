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
use pocketmine\item\Item;

class HopperTransferHelper{
	/**
	 * Find one item from the source inventory and transfer it to the target inventory.
	 * Check the items from the start to the end of the inventory.
	 * Returns true if an item was transferred, false otherwise.
	 */
	public static function transferOneItem(Inventory $sourceInventory, Inventory $targetInventory) : bool{
		foreach($sourceInventory->getContents() as $item){
			if(self::transferSpecificItem($sourceInventory, $targetInventory, $item)){
				return true;
			}
		}

		return false;
	}

	/**
	 * Transfer the one of the specified item from the source inventory to the target inventory.
	 * Returns true if the item was transferred, false otherwise.
	 */
	public static function transferSpecificItem(Inventory $sourceInventory, Inventory $targetInventory, Item $item) : bool{
		if($item->isNull()){
			return false;
		}

		$singleItem = $item->pop();

		if(!$targetInventory->canAddItem($singleItem)){
			return false;
		}

		$sourceInventory->removeItem($singleItem);
		$targetInventory->addItem($singleItem);

		return true;
	}
}
