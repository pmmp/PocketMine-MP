<?php

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