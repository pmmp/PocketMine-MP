<?php

declare(strict_types=1);

namespace pocketmine\crafting;

use pocketmine\item\Item;

class ItemDifferentCombineRecipe extends ItemCombineRecipe{
	public function __construct(
		private RecipeIngredient $base,
		private RecipeIngredient $material
	){
	}

	protected function validate(Item $input, Item $material) : bool{
		return $this->base->accepts($input) && $this->material->accepts($material);
	}
}