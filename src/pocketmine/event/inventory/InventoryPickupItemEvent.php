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

namespace pocketmine\event\inventory;

use pocketmine\entity\object\ItemEntity;
use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;

class InventoryPickupItemEvent extends InventoryEvent implements Cancellable{
	/** @var ItemEntity */
	private $item;

	/**
	 * @param Inventory  $inventory
	 * @param ItemEntity $item
	 */
	public function __construct(Inventory $inventory, ItemEntity $item){
		$this->item = $item;
		parent::__construct($inventory);
	}

	/**
	 * @return ItemEntity
	 */
	public function getItem() : ItemEntity{
		return $this->item;
	}

}
