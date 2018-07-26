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
	public const MAX_STACK = 64;

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
	public function setMaxStackSize(int $size) : void;

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
	 * @param bool $send
	 *
	 * @return bool
	 */
	public function setItem(int $index, Item $item, bool $send = true) : bool;

	/**
	 * Stores the given Items in the inventory. This will try to fill
	 * existing stacks and empty slots as well as it can.
	 *
	 * Returns the Items that did not fit.
	 *
	 * @param Item ...$slots
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
	 * @param Item ...$slots
	 *
	 * @return Item[]
	 */
	public function removeItem(Item ...$slots) : array;

	/**
	 * @param bool $includeEmpty
	 *
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array;

	/**
	 * @param Item[] $items
	 * @param bool   $send
	 */
	public function setContents(array $items, bool $send = true) : void;

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void;

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void;

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
	 * Returns the first slot number containing an item with the same ID, damage (if not any-damage), NBT (if not empty)
	 * and count >= to the count of the specified item stack.
	 *
	 * If $exact is true, only items with equal ID, damage, NBT and count will match.
	 *
	 * @param Item $item
	 * @param bool $exact
	 *
	 * @return int
	 */
	public function first(Item $item, bool $exact = false) : int;

	/**
	 * Returns the first empty slot, or -1 if not found
	 *
	 * @return int
	 */
	public function firstEmpty() : int;

	/**
	 * Returns whether the given slot is empty.
	 *
	 * @param int $index
	 *
	 * @return bool
	 */
	public function isSlotEmpty(int $index) : bool;

	/**
	 * Will remove all the Items that has the same id and metadata (if not null)
	 *
	 * @param Item $item
	 */
	public function remove(Item $item) : void;

	/**
	 * Will clear a specific slot
	 *
	 * @param int  $index
	 * @param bool $send
	 *
	 * @return bool
	 */
	public function clear(int $index, bool $send = true) : bool;

	/**
	 * Clears all the slots
	 *
	 * @param bool $send
	 */
	public function clearAll(bool $send = true) : void;

	/**
	 * Gets all the Players viewing the inventory
	 * Players will view their inventory at all times, even when not open.
	 *
	 * @return Player[]
	 */
	public function getViewers() : array;

	/**
	 * @param Player $who
	 */
	public function onOpen(Player $who) : void;

	/**
	 * Tries to open the inventory to a player
	 *
	 * @param Player $who
	 *
	 * @return bool
	 */
	public function open(Player $who) : bool;

	public function close(Player $who) : void;

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who) : void;

	/**
	 * @param int  $index
	 * @param Item $before
	 * @param bool $send
	 */
	public function onSlotChange(int $index, Item $before, bool $send) : void;

	/**
	 * Returns whether the specified slot exists in the inventory.
	 *
	 * @param int $slot
	 * @return bool
	 */
	public function slotExists(int $slot) : bool;

	/**
	 * @return null|InventoryEventProcessor
	 */
	public function getEventProcessor() : ?InventoryEventProcessor;

	/**
	 * @param null|InventoryEventProcessor $eventProcessor
	 */
	public function setEventProcessor(?InventoryEventProcessor $eventProcessor) : void;
}