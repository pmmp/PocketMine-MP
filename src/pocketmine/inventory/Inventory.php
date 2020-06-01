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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

interface Inventory{
	public const MAX_STACK = 64;

	public function getSize() : int;

	public function getMaxStackSize() : int;

	public function setMaxStackSize(int $size) : void;

	public function getName() : string;

	public function getTitle() : string;

	public function getItem(int $index) : Item;

	/**
	 * Puts an Item in a slot.
	 * If a plugin refuses the update or $index is invalid, it'll return false
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
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array;

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items, bool $send = true) : void;

	/**
	 * Drops the contents of the inventory into the specified Level at the specified position and clears the inventory
	 * contents.
	 */
	public function dropContents(Level $level, Vector3 $position) : void;

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void;

	/**
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void;

	/**
	 * Checks if the inventory contains any Item with the same material data.
	 * It will check id, amount, and metadata (if not null)
	 */
	public function contains(Item $item) : bool;

	/**
	 * Will return all the Items that has the same id and metadata (if not null).
	 * Won't check amount
	 *
	 * @return Item[]
	 */
	public function all(Item $item) : array;

	/**
	 * Returns the first slot number containing an item with the same ID, damage (if not any-damage), NBT (if not empty)
	 * and count >= to the count of the specified item stack.
	 *
	 * If $exact is true, only items with equal ID, damage, NBT and count will match.
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
	 * Will remove all the Items that has the same id and metadata (if not null)
	 */
	public function remove(Item $item) : void;

	/**
	 * Will clear a specific slot
	 */
	public function clear(int $index, bool $send = true) : bool;

	/**
	 * Clears all the slots
	 */
	public function clearAll(bool $send = true) : void;

	/**
	 * Gets all the Players viewing the inventory
	 * Players will view their inventory at all times, even when not open.
	 *
	 * @return Player[]
	 */
	public function getViewers() : array;

	public function onOpen(Player $who) : void;

	/**
	 * Tries to open the inventory to a player
	 */
	public function open(Player $who) : bool;

	public function close(Player $who) : void;

	public function onClose(Player $who) : void;

	public function onSlotChange(int $index, Item $before, bool $send) : void;

	/**
	 * Returns whether the specified slot exists in the inventory.
	 */
	public function slotExists(int $slot) : bool;

	public function getEventProcessor() : ?InventoryEventProcessor;

	public function setEventProcessor(?InventoryEventProcessor $eventProcessor) : void;
}
