<?php

declare(strict_types=1);

namespace pocketmine\crafting;

use pocketmine\item\Item;

/**
 * This class is here to hold the result of an anvil crafting process.
 */
final class AnvilCraftResult{
	/**
	 * @param Item|null $sacrificeResult If the given item is considered as null (count <= 0), the value will be set to null.
	 */
	public function __construct(
		private int $xpCost,
		private Item $output,
		private ?Item $sacrificeResult
	){
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