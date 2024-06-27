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

interface CraftingRecipe{
	/**
	 * Returns a list of items needed to craft this recipe. This MUST NOT include Air items or items with a zero count.
	 *
	 * @return RecipeIngredient[]
	 */
	public function getIngredientList() : array;

	/**
	 * Returns a list of results this recipe will produce when the inputs in the given crafting grid are consumed.
	 *
	 * @return Item[]
	 */
	public function getResultsFor(CraftingGrid $grid) : array;

	/**
	 * Returns whether the given crafting grid meets the requirements to craft this recipe.
	 */
	public function matchesCraftingGrid(CraftingGrid $grid) : bool;
}
