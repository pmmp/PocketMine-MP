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

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use function count;
use function spl_object_id;

/**
 * This trait encapsulates all enchantment handling needed for itemstacks.
 * The primary purpose of this trait is providing scope isolation for the methods it contains.
 */
trait ItemEnchantmentHandlingTrait{
	/** @var EnchantmentInstance[] */
	protected $enchantments = [];

	public function hasEnchantments() : bool{
		return count($this->enchantments) > 0;
	}

	public function hasEnchantment(Enchantment $enchantment, int $level = -1) : bool{
		$id = spl_object_id($enchantment);
		return isset($this->enchantments[$id]) && ($level === -1 || $this->enchantments[$id]->getLevel() === $level);
	}

	public function getEnchantment(Enchantment $enchantment) : ?EnchantmentInstance{
		return $this->enchantments[spl_object_id($enchantment)] ?? null;
	}

	/**
	 * @return $this
	 */
	public function removeEnchantment(Enchantment $enchantment, int $level = -1) : self{
		$instance = $this->getEnchantment($enchantment);
		if($instance !== null && ($level === -1 || $instance->getLevel() === $level)){
			unset($this->enchantments[spl_object_id($enchantment)]);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function removeEnchantments() : self{
		$this->enchantments = [];
		return $this;
	}

	/**
	 * @return $this
	 */
	public function addEnchantment(EnchantmentInstance $enchantment) : self{
		$this->enchantments[spl_object_id($enchantment->getType())] = $enchantment;
		return $this;
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	public function getEnchantments() : array{
		return $this->enchantments;
	}

	/**
	 * Returns the level of the enchantment on this item with the specified ID, or 0 if the item does not have the
	 * enchantment.
	 */
	public function getEnchantmentLevel(Enchantment $enchantment) : int{
		return ($instance = $this->getEnchantment($enchantment)) !== null ? $instance->getLevel() : 0;
	}
}
