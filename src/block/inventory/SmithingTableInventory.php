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

namespace pocketmine\block\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\Item;
use pocketmine\world\Position;

final class SmithingTableInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;

	public const SLOT_ADDITION = 1;

	public const SLOT_TEMPLATE = 2;

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(3);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getAddition() : Item{
		return $this->getItem(self::SLOT_ADDITION);
	}

	public function getTemplate() : Item{
		return $this->getItem(self::SLOT_TEMPLATE);
	}
}
