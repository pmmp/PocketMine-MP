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

namespace PocketMine\Entity;

use PocketMine\Item\Item;

interface InventorySource{

	public function hasItem(Item $item, $checkDamage = true);

	public function canAddItem(Item $item);

	/**
	 * @param Item $item
	 *
	 * @return boolean hasBeenAdded
	 */
	public function addItem(Item $item);

	public function canRemoveItem(Item $item, $checkDamage = true);

	/**
	 * @param Item    $item
	 * @param boolean $checkDamage
	 *
	 * @return boolean hasBeenRemoved
	 */
	public function removeItem(Item $item, $checkDamage = true);

	public function getSlotCount();

	public function getAllSlots();

	public function getSlot($slot);

	public function setSlot($slot, Item $item);


}