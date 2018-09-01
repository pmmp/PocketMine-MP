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

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerItemHeldEvent extends PlayerEvent implements Cancellable{
	/** @var Item */
	private $item;
	/** @var int */
	private $hotbarSlot;

	public function __construct(Player $player, Item $item, int $hotbarSlot){
		$this->player = $player;
		$this->item = $item;
		$this->hotbarSlot = $hotbarSlot;
	}

	/**
	 * Returns the hotbar slot the player is attempting to hold.
	 *
	 * NOTE: This event is called BEFORE the slot is equipped server-side. Setting the player's held item during this
	 * event will result in the **old** slot being changed, not this one.
	 *
	 * To change the item in the slot that the player is attempting to hold, set the slot that this function reports.
	 *
	 * @return int
	 */
	public function getSlot() : int{
		return $this->hotbarSlot;
	}

	/**
	 * Returns the item in the slot that the player is trying to equip.
	 *
	 * @return Item
	 */
	public function getItem() : Item{
		return $this->item;
	}
}
