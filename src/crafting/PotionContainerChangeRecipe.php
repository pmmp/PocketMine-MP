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
use pocketmine\item\ItemFactory;

class PotionContainerChangeRecipe implements BrewingRecipe{

	public function __construct(
		private int $inputItemId,
		private Item $ingredient,
		private int $outputItemId
	){
		$this->ingredient = clone $ingredient;
	}

	public function getInputItemId() : int{
		return $this->inputItemId;
	}

	public function getIngredient() : Item{
		return clone $this->ingredient;
	}

	public function getOutputItemId() : int{
		return $this->outputItemId;
	}

	public function getResultFor(Item $input) : ?Item{
		return $input->getId() === $this->getInputItemId() ? ItemFactory::getInstance()->get($this->getOutputItemId(), $input->getMeta()) : null;
	}
}
