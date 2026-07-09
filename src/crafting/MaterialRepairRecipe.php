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

use pocketmine\item\Durable;
use pocketmine\item\Item;
use function ceil;
use function floor;
use function max;
use function min;

/**
 * Represent a recipe that repair an item with a material in an anvil.
 */
class MaterialRepairRecipe implements AnvilRecipe{
	public function __construct(
		private RecipeIngredient $input,
		private RecipeIngredient $material
	){
	}

	public function getInput() : RecipeIngredient{
		return $this->input;
	}

	public function getMaterial() : RecipeIngredient{
		return $this->material;
	}

	public function getResultFor(Item $input, Item $material) : ?AnvilCraftResult{
		if($this->input->accepts($input) && $this->material->accepts($material) && $input instanceof Durable){
			$damage = $input->getDamage();
			if($damage !== 0){
				$quarter = min($damage, (int) floor($input->getMaxDurability() / 4));
				$numberRepair = min($material->getCount(), (int) ceil($damage / $quarter));
				$damage -= $quarter * $numberRepair;

				return new AnvilCraftResult(
					$numberRepair,
					(clone $input)->setDamage(max(0, $damage)),
					(clone $material)->setCount($material->getCount() - $numberRepair)
				);
			}
		}

		return null;
	}
}
