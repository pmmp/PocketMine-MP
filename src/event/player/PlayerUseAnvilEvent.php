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
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Called when a player uses an anvil (renaming, repairing, combining items).
 * This event is called once per action even if multiple tasks are performed at once.
 */
class PlayerUseAnvilEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Item $baseItem,
		private ?Item $materialItem,
		private Item $resultItem,
		private ?string $customName,
		private int $xpCost
	){
		$this->player = $player;
	}

	/**
	 * Returns the item that the player is using as the base item (left slot).
	 */
	public function getBaseItem() : Item{
		return $this->baseItem;
	}

	/**
	 * Returns the item that the player is using as the material item (right slot), or null if there is no material item
	 * (e.g. when renaming an item).
	 */
	public function getMaterialItem() : ?Item{
		return $this->materialItem;
	}

	/**
	 * Returns the item that the player will receive as a result of the anvil operation.
	 */
	public function getResultItem() : Item{
		return $this->resultItem;
	}

	/**
	 * Returns the custom name that the player is setting on the item, or null if the player is not renaming the item.
	 *
	 * This value is defined when the base item is already renamed.
	 */
	public function getCustomName() : ?string{
		return $this->customName;
	}

	/**
	 * Returns the amount of XP levels that the player will spend on this anvil operation.
	 */
	public function getXpCost() : int{
		return $this->xpCost;
	}
}
