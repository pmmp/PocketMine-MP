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

namespace pocketmine\item;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ToolTier DIAMOND()
 * @method static ToolTier GOLD()
 * @method static ToolTier IRON()
 * @method static ToolTier NETHERITE()
 * @method static ToolTier STONE()
 * @method static ToolTier WOOD()
 */
final class ToolTier{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("wood", 1, 60, 5, 2, 15),
			new self("gold", 2, 33, 5, 12, 22),
			new self("stone", 3, 132, 6, 4, 5),
			new self("iron", 4, 251, 7, 6, 14),
			new self("diamond", 5, 1562, 8, 8, 10),
			new self("netherite", 6, 2032, 9, 9, 15)
		);
	}

	private function __construct(
		string $name,
		private int $harvestLevel,
		private int $maxDurability,
		private int $baseAttackPoints,
		private int $baseEfficiency,
		private int $enchantability
	){
		$this->Enum___construct($name);
	}

	public function getHarvestLevel() : int{
		return $this->harvestLevel;
	}

	public function getMaxDurability() : int{
		return $this->maxDurability;
	}

	public function getBaseAttackPoints() : int{
		return $this->baseAttackPoints;
	}

	public function getBaseEfficiency() : int{
		return $this->baseEfficiency;
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int{
		return $this->enchantability;
	}
}
