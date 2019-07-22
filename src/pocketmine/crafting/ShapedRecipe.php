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
use pocketmine\utils\Utils;
use function array_values;
use function count;
use function implode;
use function strlen;
use function strpos;

class ShapedRecipe implements CraftingRecipe{
	/** @var string[] */
	private $shape = [];
	/** @var Item[] char => Item map */
	private $ingredientList = [];
	/** @var Item[] */
	private $results = [];

	/** @var int */
	private $height;
	/** @var int */
	private $width;

	/**
	 * Constructs a ShapedRecipe instance.
	 *
	 * @param string[] $shape <br>
	 *     Array of 1, 2, or 3 strings representing the rows of the recipe.
	 *     This accepts an array of 1, 2 or 3 strings. Each string should be of the same length and must be at most 3
	 *     characters long. Each character represents a unique type of ingredient. Spaces are interpreted as air.
	 * @param Item[]   $ingredients <br>
	 *     Char => Item map of items to be set into the shape.
	 *     This accepts an array of Items, indexed by character. Every unique character (except space) in the shape
	 *     array MUST have a corresponding item in this list. Space character is automatically treated as air.
	 * @param Item[]   $results List of items that this recipe produces when crafted.
	 *
	 * Note: Recipes **do not** need to be square. Do NOT add padding for empty rows/columns.
	 */
	public function __construct(array $shape, array $ingredients, array $results){
		$this->height = count($shape);
		if($this->height > 3 or $this->height <= 0){
			throw new \InvalidArgumentException("Shaped recipes may only have 1, 2 or 3 rows, not $this->height");
		}

		$shape = array_values($shape);

		$this->width = strlen($shape[0]);
		if($this->width > 3 or $this->width <= 0){
			throw new \InvalidArgumentException("Shaped recipes may only have 1, 2 or 3 columns, not $this->width");
		}

		foreach($shape as $y => $row){
			if(strlen($row) !== $this->width){
				throw new \InvalidArgumentException("Shaped recipe rows must all have the same length (expected $this->width, got " . strlen($row) . ")");
			}

			for($x = 0; $x < $this->width; ++$x){
				if($row[$x] !== ' ' and !isset($ingredients[$row[$x]])){
					throw new \InvalidArgumentException("No item specified for symbol '" . $row[$x] . "'");
				}
			}
		}

		$this->shape = $shape;

		foreach($ingredients as $char => $i){
			if(strpos(implode($this->shape), $char) === false){
				throw new \InvalidArgumentException("Symbol '$char' does not appear in the recipe shape");
			}

			$this->ingredientList[$char] = clone $i;
		}

		$this->results = Utils::cloneObjectArray($results);
	}

	public function getWidth() : int{
		return $this->width;
	}

	public function getHeight() : int{
		return $this->height;
	}

	/**
	 * @return Item[]
	 */
	public function getResults() : array{
		return Utils::cloneObjectArray($this->results);
	}

	/**
	 * @param CraftingGrid $grid
	 *
	 * @return Item[]
	 */
	public function getResultsFor(CraftingGrid $grid) : array{
		return $this->getResults();
	}

	/**
	 * @return Item[][]
	 */
	public function getIngredientMap() : array{
		$ingredients = [];

		for($y = 0; $y < $this->height; ++$y){
			for($x = 0; $x < $this->width; ++$x){
				$ingredients[$y][$x] = $this->getIngredient($x, $y);
			}
		}

		return $ingredients;
	}

	/**
	 * @return Item[]
	 */
	public function getIngredientList() : array{
		$ingredients = [];

		for($y = 0; $y < $this->height; ++$y){
			for($x = 0; $x < $this->width; ++$x){
				$ingredient = $this->getIngredient($x, $y);
				if(!$ingredient->isNull()){
					$ingredients[] = $ingredient;
				}
			}
		}

		return $ingredients;
	}

	/**
	 * @param int $x
	 * @param int $y
	 *
	 * @return Item
	 */
	public function getIngredient(int $x, int $y) : Item{
		$exists = $this->ingredientList[$this->shape[$y]{$x}] ?? null;
		return $exists !== null ? clone $exists : ItemFactory::air();
	}

	/**
	 * Returns an array of strings containing characters representing the recipe's shape.
	 * @return string[]
	 */
	public function getShape() : array{
		return $this->shape;
	}

	/**
	 * @param CraftingGrid $grid
	 * @param bool         $reverse
	 *
	 * @return bool
	 */
	private function matchInputMap(CraftingGrid $grid, bool $reverse) : bool{
		for($y = 0; $y < $this->height; ++$y){
			for($x = 0; $x < $this->width; ++$x){

				$given = $grid->getIngredient($reverse ? $this->width - $x - 1 : $x, $y);
				$required = $this->getIngredient($x, $y);
				if(!$required->equals($given, !$required->hasAnyDamageValue(), $required->hasNamedTag()) or $required->getCount() > $given->getCount()){
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param CraftingGrid $grid
	 *
	 * @return bool
	 */
	public function matchesCraftingGrid(CraftingGrid $grid) : bool{
		if($this->width !== $grid->getRecipeWidth() or $this->height !== $grid->getRecipeHeight()){
			return false;
		}

		return $this->matchInputMap($grid, false) or $this->matchInputMap($grid, true);
	}
}
