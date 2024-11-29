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
use pocketmine\utils\Utils;
use function count;

class ShapelessRecipe implements CraftingRecipe{
	/**
	 * @var RecipeIngredient[]
	 * @phpstan-var list<RecipeIngredient>
	 */
	private array $ingredients = [];
	/**
	 * @var Item[]
	 * @phpstan-var list<Item>
	 */
	private array $results;
	private ShapelessRecipeType $type;

	/**
	 * @param RecipeIngredient[] $ingredients No more than 9 total. This applies to sum of item stack counts, not count of array.
	 * @param Item[]             $results     List of result items created by this recipe.
	 *
	 * @phpstan-param list<RecipeIngredient> $ingredients
	 * @phpstan-param list<Item>             $results
	 */
	public function __construct(array $ingredients, array $results, ShapelessRecipeType $type){
		$this->type = $type;

		if(count($ingredients) > 9){
			throw new \InvalidArgumentException("Shapeless recipes cannot have more than 9 ingredients");
		}
		$this->ingredients = $ingredients;
		$this->results = Utils::cloneObjectArray($results);
	}

	/**
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	public function getResults() : array{
		return Utils::cloneObjectArray($this->results);
	}

	public function getResultsFor(CraftingGrid $grid) : array{
		return $this->getResults();
	}

	public function getType() : ShapelessRecipeType{
		return $this->type;
	}

	public function getIngredientList() : array{
		return $this->ingredients;
	}

	public function getIngredientCount() : int{
		return count($this->ingredients);
	}

	public function matchesCraftingGrid(CraftingGrid $grid) : bool{
		//don't pack the ingredients - shapeless recipes require that each ingredient be in a separate slot
		$input = $grid->getContents();

		foreach($this->ingredients as $ingredient){
			foreach($input as $j => $haveItem){
				if($ingredient->accepts($haveItem)){
					unset($input[$j]);
					continue 2;
				}
			}

			return false; //failed to match the needed item to a given item
		}

		return count($input) === 0; //crafting grid should be empty apart from the given ingredient stacks
	}
}
