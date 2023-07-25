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

namespace pocketmine\block\utils;

use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static BrewingStandSlot EAST()
 * @method static BrewingStandSlot NORTHWEST()
 * @method static BrewingStandSlot SOUTHWEST()
 */
final class BrewingStandSlot{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("east", BrewingStandInventory::SLOT_BOTTLE_LEFT),
			new self("northwest", BrewingStandInventory::SLOT_BOTTLE_MIDDLE),
			new self("southwest", BrewingStandInventory::SLOT_BOTTLE_RIGHT)
		);
	}

	private function __construct(string $enumName, private int $slotNumber){
		$this->Enum___construct($enumName);
	}

	/**
	 * Returns the brewing stand inventory slot number associated with this visual slot.
	 */
	public function getSlotNumber() : int{ return $this->slotNumber; }
}
