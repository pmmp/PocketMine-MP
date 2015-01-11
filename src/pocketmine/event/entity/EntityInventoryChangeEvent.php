<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\Event;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;

class EntityInventoryChangeEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	private $oldItem;
	private $newItem;
	private $slot;

	public function __construct(Entity $entity, Item $oldItem, Item $newItem, $slot){
		$this->entity = $entity;
		$this->oldItem = $oldItem;
		$this->newItem = $newItem;
		$this->slot = (int) $slot;
	}

	public function getSlot(){
		return $this->slot;
	}

	public function getNewItem(){
		return $this->newItem;
	}

	public function setNewItem(Item $item){
		$this->newItem = $item;
	}

	public function getOldItem(){
		return $this->oldItem;
	}


}