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


namespace pocketmine\item;


use pocketmine\item\enchantment\Enchantment;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

abstract class Durable extends Item{

	protected $durability;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown", int $durability){
		$this->durability = $durability;
		parent::__construct($id, $meta, $count, $name);
	}

	/**
	 * Returns the maximum amount of damage this item can take before it breaks.
	 * @return int
	 */
	public function getMaxDurability() : int{
		return $this->durability;
	}

	/**
	 * Returns whether this item will take damage when used.
	 * @return bool
	 */
	public function isUnbreakable() : bool{
		$tag = $this->getNamedTagEntry("Unbreakable");
		return $tag !== null and $tag->getValue() !== 0;
	}

	/**
	 * Sets whether the item will take damage when used.
	 * @param bool $value
	 */
	public function setUnbreakable(bool $value = true){
		$tag = $this->getNamedTag() ?? new CompoundTag("", []);
		$tag->Unbreakable = new ByteTag("Unbreakable", (int) $value);
		$this->setNamedTag($tag);
	}

	/**
	 * Applies damage to the item.
	 * @param int $amount
	 *
	 * @return bool if any damage was applied to the item
	 */
	public function applyDamage(int $amount) : bool{
		if($this->isUnbreakable() or $this->isBroken()){
			return false;
		}

		if(($e = $this->getEnchantment(Enchantment::UNBREAKING)) !== null){
			$unbreakingLevel = $e->getLevel() + 1;
			if($unbreakingLevel > 1){
				for($i = 0; $i < $amount; ++$i){
					if(lcg_value() > 1 / $unbreakingLevel){
						$amount--;
					}
				}
			}
		}

		$this->meta += $amount;
		if($this->meta >= $this->durability){
			$this->pop();
		}

		return true;
	}

	/**
	 * Returns whether the item is broken.
	 * @return bool
	 */
	public function isBroken() : bool{
		return $this->meta >= $this->durability;
	}

	/**
	 * Returns the item that this item will turn into when its durability is reached or exceeded. This is usually Air, but in cases such as Elytra this may be the item itself.
	 * @return Item
	 */
	public function getBrokenEquivalent() : Item{
		return Item::get(Item::AIR, 0, 0);
	}
}