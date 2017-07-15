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

/**
 * Handles the creation of virtual inventories or mapped to an InventoryHolder
 */
namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;

interface Inventory{
	const MAX_STACK = 64;

	/**
	 * @return int
	 */
	public function getSize() : int;

	/**
	 * @return int
	 */
	public function getMaxStackSize() : int;

	/**
	 * @param int $size
	 */
	public function setMaxStackSize(int $size);

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return string
	 */
	public function getTitle() : string;

	/**
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem(int $index) : Item;

	/**
	 * Puts an Item in a slot.
	 * If a plugin refuses the update or $index is invalid, it'll return false
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem(int $index, Item $item) : bool;

	/**
	 * Stores the given Items in the inventory. This will try to fill
	 * existing stacks and empty slots as well as it can.
	 *
	 * Returns the Items that did not fit.
	 *
	 * @param Item[] ...$slots
	 *
	 * @return Item[]
	 */
	public function addItem(Item ...$slots) : array;

	/**
	 * Checks if a given Item can be added to the inventory
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function canAddItem(Item $item) : bool;

	/**
	 * Removes the given Item from the inventory.
	 * It will return the Items that couldn't be removed.
	 *
	 * @param Item[] ...$slots
	 *
	 * @return Item[]
	 */
	public function removeItem(Item ...$slots) : array;

	/**
	 * @return Item[]
	 */
	public function getContents() : array;

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items);

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target);

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target);

	/**
	 * Checks if the inventory contains any Item with the same material data.
	 * It will check id, amount, and metadata (if not null)
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function contains(Item $item) : bool;

	/**
	 * Will return all the Items that has the same id and metadata (if not null).
	 * Won't check amount
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function all(Item $item) : array;

	/**
	 * Will return the first slot has the same id and metadata (if not null) as the Item.
	 * -1 if not found, will check amount
	 *
	 * @param Item $item
	 *
	 * @return int
	 */
	public function first(Item $item) : int;

	/**
	 * Returns the first empty slot, or -1 if not found
	 *
	 * @return int
	 */
	public function firstEmpty() : int;

	/**
	 * Will remove all the Items that has the same id and metadata (if not null)
	 *
	 * @param Item $item
	 */
	public function remove(Item $item);

	/**
	 * Will clear a specific slot
	 *
	 * @param int $index
	 *
	 * @return bool
	 */
	public function clear(int $index) : bool;

	/**
	 * Clears all the slots
	 */
	public function clearAll();

	/**
	 * Gets all the Players viewing the inventory
	 * Players will view their inventory at all times, even when not open.
	 *
	 * @return Player[]
	 */
	public function getViewers() : array;

	/**
	 * @return InventoryType
	 */
	public function getType() : InventoryType;

	/**
	 * @return InventoryHolder
	 */
	public function getHolder();

	/**
	 * @param Player $who
	 */
	public function onOpen(Player $who);

	/**
	 * Tries to open the inventory to a player
	 *
	 * @param Player $who
	 *
	 * @return bool
	 */
	public function open(Player $who) : bool;

	public function close(Player $who);

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who);

	/**
	 * @param int  $index
	 * @param Item $before
	 */
	public function onSlotChange($index, $before);
}
