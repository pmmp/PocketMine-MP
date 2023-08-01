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

namespace pocketmine\item\enchantment;

use pocketmine\lang\Translatable;
use pocketmine\utils\NotCloneable;
use pocketmine\utils\NotSerializable;

/**
 * Manages enchantment type data.
 */
class Enchantment{
	use NotCloneable;
	use NotSerializable;

	/** @var \Closure(int $level) : int $minCost */
	private \Closure $minCost;

	/** @var \Closure(int $level, int $minCost) : int $maxCost */
	private \Closure $maxCost;

	/**
	 * @phpstan-param null|\Closure(int $level) : int               $minCost
	 * @phpstan-param null|\Closure(int $level, int $minCost) : int $maxCost
	 */
	public function __construct(
		private Translatable|string $name,
		private int $rarity,
		private int $primaryItemFlags,
		private int $secondaryItemFlags,
		private int $maxLevel,
		?\Closure $minCost = null,
		?\Closure $maxCost = null,
		private bool $isTreasure = false,
	){
		$this->maxCost = $maxCost ?? fn() => 1;
		$this->minCost = $minCost ?? fn() => 50;
	}

	/**
	 * Returns a translation key for this enchantment's name.
	 */
	public function getName() : Translatable|string{
		return $this->name;
	}

	/**
	 * Returns an int constant indicating how rare this enchantment type is.
	 */
	public function getRarity() : int{
		return $this->rarity;
	}

	/**
	 * Returns a bitset indicating what item types can have this item applied from an enchanting table.
	 */
	public function getPrimaryItemFlags() : int{
		return $this->primaryItemFlags;
	}

	/**
	 * Returns a bitset indicating what item types cannot have this item applied from an enchanting table, but can from
	 * an anvil.
	 */
	public function getSecondaryItemFlags() : int{
		return $this->secondaryItemFlags;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an enchanting table.
	 */
	public function hasPrimaryItemType(int $flag) : bool{
		return ($this->primaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an anvil, if it is not a primary item.
	 */
	public function hasSecondaryItemType(int $flag) : bool{
		return ($this->secondaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns the maximum level of this enchantment that can be found on an enchantment table.
	 */
	public function getMaxLevel() : int{
		return $this->maxLevel;
	}

	/**
	 * Returns whether this enchantment can be applied to the item along with the given enchantment.
	 */
	public function isCompatibleWith(Enchantment $other) : bool{
		return IncompatibleEnchantmentRegistry::getInstance()->areCompatible($this, $other);
	}

	/**
	 * Returns the minimum cost required for this enchantment with a particular level to be available in an
	 * enchanting table.
	 */
	public function getMinCost(int $level) : int{
		return ($this->minCost)($level);
	}

	/**
	 * Returns the maximum cost allowed for this enchantment with a particular level to be available in an
	 * enchanting table.
	 */
	public function getMaxCost(int $level) : int{
		return ($this->maxCost)($level, $this->getMinCost($level));
	}

	/**
	 * Returns whether this enchantment is a special enchantment that cannot be obtained using an enchanting table.
	 */
	public function isTreasure() : bool{
		return $this->isTreasure;
	}
}
