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
 * @method static ToolTier STONE()
 * @method static ToolTier WOOD()
 */
final class ToolTier{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("wood", 1, 60, 5, 2),
			new self("gold", 2, 33, 5, 12),
			new self("stone", 3, 132, 6, 4),
			new self("iron", 4, 251, 7, 6),
			new self("diamond", 5, 1562, 8, 8)
		);
	}

	/** @var int */
	private $harvestLevel;
	/** @var int */
	private $maxDurability;
	/** @var int */
	private $baseAttackPoints;
	/** @var int */
	private $baseEfficiency;

	private function __construct(string $name, int $harvestLevel, int $maxDurability, int $baseAttackPoints, int $baseEfficiency){
		$this->Enum___construct($name);
		$this->harvestLevel = $harvestLevel;
		$this->maxDurability = $maxDurability;
		$this->baseAttackPoints = $baseAttackPoints;
		$this->baseEfficiency = $baseEfficiency;
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
}
