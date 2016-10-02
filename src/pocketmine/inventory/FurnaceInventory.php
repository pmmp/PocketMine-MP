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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\tile\Furnace;

class FurnaceInventory extends ContainerInventory{
	public function __construct(Furnace $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::FURNACE));
	}

	/**
	 * @return Furnace
	 */
	public function getHolder(){
		return $this->holder;
	}

	/**
	 * @return Item
	 */
	public function getResult(){
		return $this->getItem(2);
	}

	/**
	 * @return Item
	 */
	public function getFuel(){
		return $this->getItem(1);
	}

	/**
	 * @return Item
	 */
	public function getSmelting(){
		return $this->getItem(0);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setResult(Item $item){
		return $this->setItem(2, $item);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setFuel(Item $item){
		return $this->setItem(1, $item);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setSmelting(Item $item){
		return $this->setItem(0, $item);
	}

	public function onSlotChange($index, $before){
		parent::onSlotChange($index, $before);

		$this->getHolder()->scheduleUpdate();
	}
}
