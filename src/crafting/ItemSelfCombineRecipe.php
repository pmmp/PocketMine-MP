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
 * Represent a recipe that repair an item with a material in an anvil.
 */
class ItemSelfCombineRecipe extends ItemCombineRecipe{
	/**
	 * @param RecipeIngredient $target The item that will be concerned by the combinaison.
	 *                                 The input and material have to be accepted by this ingredient to be able to combine them.
	 */
	public function __construct(
		private RecipeIngredient $target
	){
	}

	protected function validate(Item $input, Item $material) : bool{
		return $this->target->accepts($input) && $this->target->accepts($material);
	}
}
