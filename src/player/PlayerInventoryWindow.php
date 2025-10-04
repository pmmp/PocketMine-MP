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

namespace pocketmine\player;

use pocketmine\inventory\Inventory;

/**
 * Window for player-owned inventories. The player can access these at all times.
 */
final class PlayerInventoryWindow extends InventoryWindow{

	public const TYPE_INVENTORY = 0;
	public const TYPE_OFFHAND = 1;
	public const TYPE_ARMOR = 2;
	public const TYPE_CURSOR = 3;
	public const TYPE_CRAFTING = 4;

	public function __construct(
		Player $viewer,
		Inventory $inventory,
		private int $type
	){
		parent::__construct($viewer, $inventory);
	}

	/**
	 * Returns the type of player inventory in this window.
	 */
	public function getType() : int{ return $this->type; }
}
