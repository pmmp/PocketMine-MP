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

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\item\Item;
use pocketmine\Server;

class ArmorInventoryEventProcessor implements InventoryEventProcessor{
	/** @var Entity */
	private $entity;

	public function __construct(Entity $entity){
		$this->entity = $entity;
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item{
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->entity, $oldItem, $newItem, $slot));
		if($ev->isCancelled()){
			return null;
		}

		return $ev->getNewItem();
	}
}