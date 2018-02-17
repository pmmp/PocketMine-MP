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

namespace pocketmine\inventory;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\item\Item;
use pocketmine\Server;

abstract class EntityInventory extends BaseInventory{
	/** @var Entity */
	protected $holder;

	public function __construct(Entity $holder, array $items = [], int $size = null, string $title = null){
		$this->holder = $holder;
		parent::__construct($items, $size, $title);
	}

	protected function doSetItemEvents(int $index, Item $newItem) : ?Item{
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $newItem, $index));
		if($ev->isCancelled()){
			return null;
		}

		return $ev->getNewItem();
	}

	/**
	 * @return Entity
	 */
	public function getHolder(){
		return $this->holder;
	}
}
