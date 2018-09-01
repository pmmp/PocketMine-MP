<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\item\Item;

/**
 * This interface can be used to listen for events on a specific Inventory.
 *
 * If you want to listen to changes on an inventory, create a class implementing this interface and implement its
 * methods, then register it onto the inventory or inventories that you want to receive events for.
 */
interface InventoryEventProcessor{

	/**
	 * Called prior to a slot in the given inventory changing. This is called by inventories that this listener is
	 * attached to.
	 *
	 * @param Inventory $inventory
	 * @param int       $slot
	 * @param Item      $oldItem
	 * @param Item      $newItem
	 *
	 * @return Item|null that should be used in place of $newItem, or null if the slot change should not proceed.
	 */
	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item;
}