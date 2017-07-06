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

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerItemHeldEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var Item */
	private $item;
	/** @var int */
	private $hotbarSlot;
	/** @var int */
	private $inventorySlot;

	public function __construct(Player $player, Item $item, int $inventorySlot, int $hotbarSlot){
		$this->player = $player;
		$this->item = $item;
		$this->inventorySlot = $inventorySlot;
		$this->hotbarSlot = $hotbarSlot;
	}

	/**
	 * Returns the hotbar slot the player is attempting to hold.
	 * @return int
	 */
	public function getSlot() : int{
		return $this->hotbarSlot;
	}

	/**
	 * @return int
	 */
	public function getInventorySlot() : int{
		return $this->inventorySlot;
	}

	public function getItem() : Item{
		return $this->item;
	}

}