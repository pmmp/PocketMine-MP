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


class EnchantmentList{

	/** @var \SplFixedArray|EnchantmentEntry[] */
	private $enchantments;

	public function __construct(int $size){
		$this->enchantments = new \SplFixedArray($size);
	}

	/**
	 * @param int              $slot
	 * @param EnchantmentEntry $entry
	 */
	public function setSlot(int $slot, EnchantmentEntry $entry) : void{
		$this->enchantments[$slot] = $entry;
	}

	/**
	 * @param int $slot
	 *
	 * @return EnchantmentEntry
	 */
	public function getSlot(int $slot) : EnchantmentEntry{
		return $this->enchantments[$slot];
	}

	public function getSize() : int{
		return $this->enchantments->getSize();
	}
}
