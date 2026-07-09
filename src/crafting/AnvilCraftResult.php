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

namespace pocketmine\crafting;

use pocketmine\item\Item;

/**
 * This class is here to hold the result of an anvil crafting process.
 */
final class AnvilCraftResult{
	/**
	 * @param int       $xpCost          The experience points cost required to craft the output item. (positive integer, 0 means no cost)
	 * @param Item      $output          The item given as output of the crafting process.
	 * @param Item|null $sacrificeResult If the given item is considered as null (count <= 0), the value will be set to null.
	 */
	public function __construct(
		private int $xpCost,
		private Item $output,
		private ?Item $sacrificeResult
	){
		if($this->xpCost < 0){
			throw new \InvalidArgumentException("XP cost cannot be negative");
		}
		if($this->sacrificeResult !== null && $this->sacrificeResult->isNull()){
			$this->sacrificeResult = null;
		}
	}

	/**
	 * Represent the amount of experience points required to craft the output item.
	 */
	public function getXpCost() : int{
		return $this->xpCost;
	}

	/**
	 * Represent the item given as output of the crafting process.
	 */
	public function getOutput() : Item{
		return $this->output;
	}

	/**
	 * This result has to be null if the sacrifice slot need to be emptied.
	 * If not null, it represent the item that will be left in the sacrifice slot after the crafting process.
	 */
	public function getSacrificeResult() : ?Item{
		return $this->sacrificeResult;
	}
}
