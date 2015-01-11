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

namespace pocketmine\event\player;

use pocketmine\Event;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerItemHeldEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	private $item;
	private $slot;
	private $inventorySlot;

	public function __construct(Player $player, Item $item, $inventorySlot, $slot){
		$this->player = $player;
		$this->item = $item;
		$this->inventorySlot = (int) $inventorySlot;
		$this->slot = (int) $slot;
	}

	public function getSlot(){
		return $this->slot;
	}

	public function getInventorySlot(){
		return $this->inventorySlot;
	}

	public function getItem(){
		return $this->item;
	}

}