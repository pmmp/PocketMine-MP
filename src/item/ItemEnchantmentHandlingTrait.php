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

/**
 * This trait encapsulates all enchantment handling needed for itemstacks.
 * The primary purpose of this trait is providing scope isolation for the methods it contains.
 */
trait ItemEnchantmentHandlingTrait{
	/** @var EnchantmentInstance[] */
	protected $enchantments = [];

	/**
	 * @return bool
	 */
	public function hasEnchantments() : bool{
		return !empty($this->enchantments);
	}

	/**
	 * @param Enchantment $enchantment
	 * @param int         $level
	 *
	 * @return bool
	 */
	public function hasEnchantment(Enchantment $enchantment, int $level = -1) : bool{
		$id = $enchantment->getId();
		return isset($this->enchantments[$id]) and ($level === -1 or $this->enchantments[$id]->getLevel() === $level);
	}

	/**
	 * @param Enchantment $enchantment
	 *
	 * @return EnchantmentInstance|null
	 */
	public function getEnchantment(Enchantment $enchantment) : ?EnchantmentInstance{
		return $this->enchantments[$enchantment->getId()] ?? null;
	}

	/**
	 * @param Enchantment $enchantment
	 * @param int         $level
	 *
	 * @return $this
	 */
	public function removeEnchantment(Enchantment $enchantment, int $level = -1) : self{
		$instance = $this->getEnchantment($enchantment);
		if($instance !== null and ($level === -1 or $instance->getLevel() === $level)){
			unset($this->enchantments[$enchantment->getId()]);
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
	 * @param EnchantmentInstance $enchantment
	 *
	 * @return $this
	 */
	public function addEnchantment(EnchantmentInstance $enchantment) : self{
		$this->enchantments[$enchantment->getId()] = $enchantment;
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
	 *
	 * @param Enchantment $enchantment
	 *
	 * @return int
	 */
	public function getEnchantmentLevel(Enchantment $enchantment) : int{
		return ($instance = $this->getEnchantment($enchantment)) !== null ? $instance->getLevel() : 0;
	}
}
