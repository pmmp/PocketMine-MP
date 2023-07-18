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
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;

interface Inventory{
	public const MAX_STACK = 64;

	/**
	 * Returns the number of slots in the inventory.
	 */
	public function getSize() : int;

	/**
	 * Returns the maximum stack size for items in this inventory. Individual item types (such as armor or tools) may
	 * have a smaller maximum stack size.
	 */
	public function getMaxStackSize() : int;

	/**
	 * Sets the maximum stack size for items in this inventory.
	 */
	public function setMaxStackSize(int $size) : void;

	/**
	 * Returns the item in the specified slot.
	 */
	public function getItem(int $index) : Item;

	/**
	 * Puts an Item in a slot.
	 */
	public function setItem(int $index, Item $item) : void;

	/**
	 * Returns an array of all the itemstacks in the inventory, indexed by their slot number.
	 * Empty slots are not included unless includeEmpty is true.
	 *
	 * @return Item[]
	 * @phpstan-return array<int, Item>
	 */
	public function getContents(bool $includeEmpty = false) : array;

	/**
	 * Sets the contents of the inventory. Non-numeric offsets or offsets larger than the size of the inventory are
	 * ignored.
	 *
	 * @param Item[] $items
	 * @phpstan-param array<int, Item> $items
	 */
	public function setContents(array $items) : void;

	/**
	 * Stores the given Items in the inventory.
	 * This will add to any non-full existing stacks first, and then put the remaining items in empty slots if there are
	 * any available.
	 *
	 * Returns an array of items which could not fit in the inventory.
	 *
	 * @return Item[]
	 */
	public function addItem(Item ...$slots) : array;

	/**
	 * Checks if a given Item can be added to the inventory
	 */
	public function canAddItem(Item $item) : bool;

	/**
	 * Returns how many items from the given itemstack can be added to this inventory.
	 */
	public function getAddableItemQuantity(Item $item) : int;

	/**
	 * Returns whether the total amount of matching items is at least the stack size of the given item. Multiple stacks
	 * of the same item are added together.
	 *
	 * If the input item has specific NBT, only items with the same type and NBT will match. Otherwise, only the item
	 * type is checked.
	 */
	public function contains(Item $item) : bool;

	/**
	 * Returns all matching items in the inventory, irrespective of stack size. The returned array is indexed by slot
	 * number.
	 *
	 * If the input item has specific NBT, only items with the same type and NBT will match. Otherwise, only the item
	 * type is checked.
	 *
	 * @return Item[]
	 * @phpstan-return array<int, Item>
	 */
	public function all(Item $item) : array;

	/**
	 * Returns the first slot number containing a matching item with a stack size greater than or equal to the input item.
	 *
	 * If the input item has specific NBT, or if $exact is true, only items with the same type and NBT will match.
	 * Otherwise, only the item type is checked.
	 */
	public function first(Item $item, bool $exact = false) : int;

	/**
	 * Returns the first empty slot, or -1 if not found
	 */
	public function firstEmpty() : int;

	/**
	 * Returns whether the given slot is empty.
	 */
	public function isSlotEmpty(int $index) : bool;

	/**
	 * Clears all slots containing items equivalent to the given item.
	 *
	 * If the input item has specific NBT, only items with the same type and NBT will match. Otherwise, only the item
	 * type is checked.
	 */
	public function remove(Item $item) : void;

	/**
	 * Removes items from the inventory in the amounts specified by the given itemstacks.
	 * Returns an array of items that couldn't be removed.
	 *
	 * If the input item has specific NBT, only items with the same type and NBT will match. Otherwise, only the item
	 * type is checked.
	 *
	 * @return Item[]
	 */
	public function removeItem(Item ...$slots) : array;

	/**
	 * Will clear a specific slot
	 */
	public function clear(int $index) : void;

	/**
	 * Clears all the slots
	 */
	public function clearAll() : void;

	/**
	 * Swaps the specified slots.
	 */
	public function swap(int $slot1, int $slot2) : void;

	/**
	 * Gets all the Players viewing the inventory
	 * Players will view their inventory at all times, even when not open.
	 *
	 * @return Player[]
	 */
	public function getViewers() : array;

	/**
	 * Called when a player opens this inventory.
	 */
	public function onOpen(Player $who) : void;

	public function onClose(Player $who) : void;

	/**
	 * Returns whether the specified slot exists in the inventory.
	 */
	public function slotExists(int $slot) : bool;

	/**
	 * @return InventoryListener[]|ObjectSet
	 * @phpstan-return ObjectSet<InventoryListener>
	 */
	public function getListeners() : ObjectSet;
}
