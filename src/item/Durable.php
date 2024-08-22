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

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\nbt\tag\CompoundTag;
use function lcg_value;
use function min;

abstract class Durable extends Item{
	protected int $damage = 0;
	private bool $unbreakable = false;

	/**
	 * Returns whether this item will take damage when used.
	 */
	public function isUnbreakable() : bool{
		return $this->unbreakable;
	}

	/**
	 * Sets whether the item will take damage when used.
	 *
	 * @return $this
	 */
	public function setUnbreakable(bool $value = true) : self{
		$this->unbreakable = $value;
		return $this;
	}

	/**
	 * Applies damage to the item.
	 *
	 * @return bool if any damage was applied to the item
	 */
	public function applyDamage(int $amount) : bool{
		if($this->isUnbreakable() || $this->isBroken()){
			return false;
		}

		$amount -= $this->getUnbreakingDamageReduction($amount);

		$this->damage = min($this->damage + $amount, $this->getMaxDurability());
		if($this->isBroken()){
			$this->onBroken();
		}

		return true;
	}

	public function getDamage() : int{
		return $this->damage;
	}

	public function setDamage(int $damage) : Item{
		if($damage < 0 || $damage > $this->getMaxDurability()){
			throw new \InvalidArgumentException("Damage must be in range 0 - " . $this->getMaxDurability());
		}
		$this->damage = $damage;
		return $this;
	}

	protected function getUnbreakingDamageReduction(int $amount) : int{
		if(($unbreakingLevel = $this->getEnchantmentLevel(VanillaEnchantments::UNBREAKING())) > 0){
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for($i = 0; $i < $amount; ++$i){
				if(lcg_value() > $chance){
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	/**
	 * Called when the item's damage exceeds its maximum durability.
	 */
	protected function onBroken() : void{
		$this->pop();
		$this->setDamage(0); //the stack size may be greater than 1 if overstacked by a plugin
	}

	/**
	 * Returns the maximum amount of damage this item can take before it breaks.
	 */
	abstract public function getMaxDurability() : int;

	/**
	 * Returns whether the item is broken.
	 */
	public function isBroken() : bool{
		return $this->damage >= $this->getMaxDurability() || $this->isNull();
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		$this->unbreakable = $tag->getByte("Unbreakable", 0) !== 0;

		$damage = $tag->getInt("Damage", $this->damage);
		if($damage !== $this->damage && $damage >= 0 && $damage <= $this->getMaxDurability()){
			//TODO: out-of-bounds damage should be an error
			$this->setDamage($damage);
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		$this->unbreakable ? $tag->setByte("Unbreakable", 1) : $tag->removeTag("Unbreakable");
		$this->damage !== 0 ? $tag->setInt("Damage", $this->damage) : $tag->removeTag("Damage");
	}
}
