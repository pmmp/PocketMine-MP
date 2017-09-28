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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\UUID;

class ShapedRecipe implements CraftingRecipe{
	/** @var Item */
	private $primaryResult;
	/** @var Item[] */
	private $extraResults = [];

	/** @var UUID|null */
	private $id = null;

	/** @var string[] */
	private $shape = [];
	/** @var Item[] char => Item map */
	private $ingredientList = [];

	/**
	 * Constructs a ShapedRecipe instance.
	 *
	 * @param Item     $primaryResult
	 * @param string[] $shape<br>
	 *     Array of 1, 2, or 3 strings representing the rows of the recipe.
	 *     This accepts an array of 1, 2 or 3 strings. Each string should be of the same length and must be at most 3
	 *     characters long. Each character represents a unique type of ingredient. Spaces are interpreted as air.
	 * @param Item[]   $ingredients<br>
	 *     Char => Item map of items to be set into the shape.
	 *     This accepts an array of Items, indexed by character. Every unique character (except space) in the shape
	 *     array MUST have a corresponding item in this list. Space character is automatically treated as air.
	 * @param Item[]   $extraResults<br>
	 *     List of additional result items to leave in the crafting grid afterwards. Used for things like cake recipe
	 *     empty buckets.
	 *
	 * Note: Recipes **do not** need to be square. Do NOT add padding for empty rows/columns.
	 */
	public function __construct(Item $primaryResult, array $shape, array $ingredients, array $extraResults = []){
		$rowCount = count($shape);
		if($rowCount > 3 or $rowCount <= 0){
			throw new \InvalidArgumentException("Shaped recipes may only have 1, 2 or 3 rows, not $rowCount");
		}

		$shape = array_values($shape);

		$columnCount = strlen($shape[0]);
		if($columnCount > 3 or $rowCount <= 0){
			throw new \InvalidArgumentException("Shaped recipes may only have 1, 2 or 3 columns, not $columnCount");
		}

		foreach($shape as $y => $row){
			if(strlen($row) !== $columnCount){
				throw new \InvalidArgumentException("Shaped recipe rows must all have the same length (expected $columnCount, got " . strlen($row) . ")");
			}

			for($x = 0; $x < $columnCount; ++$x){
				if($row{$x} !== ' ' and !isset($ingredients[$row{$x}])){
					throw new \InvalidArgumentException("No item specified for symbol '" . $row{$x} . "'");
				}
			}
		}
		$this->primaryResult = clone $primaryResult;
		foreach($extraResults as $item){
			$this->extraResults[] = clone $item;
		}

		$this->shape = $shape;

		foreach($ingredients as $char => $i){
			$this->setIngredient($char, $i);
		}
	}

	public function getWidth() : int{
		return strlen($this->shape[0]);
	}

	public function getHeight() : int{
		return count($this->shape);
	}

	/**
	 * @return Item
	 */
	public function getResult() : Item{
		return $this->primaryResult;
	}

	/**
	 * @return Item[]
	 */
	public function getExtraResults() : array{
		return $this->extraResults;
	}

	/**
	 * @return Item[]
	 */
	public function getAllResults() : array{
		$results = $this->extraResults;
		array_unshift($results, $this->primaryResult);
		return $results;
	}

	/**
	 * @return UUID|null
	 */
	public function getId() : ?UUID{
		return $this->id;
	}

	public function setId(UUID $id){
		if($this->id !== null){
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}

	/**
	 * @param string $key
	 * @param Item   $item
	 *
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setIngredient(string $key, Item $item){
		if(strpos(implode($this->shape), $key) === false){
			throw new \InvalidArgumentException("Symbol '$key' does not appear in the recipe shape");
		}

		$this->ingredientList[$key] = clone $item;

		return $this;
	}

	/**
	 * @return Item[][]
	 */
	public function getIngredientMap() : array{
		$ingredients = [];

		for($y = 0, $y2 = $this->getHeight(); $y < $y2; ++$y){
			for($x = 0, $x2 = $this->getWidth(); $x < $x2; ++$x){
				$ingredients[$y][$x] = $this->getIngredient($x, $y);
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
		return $exists !== null ? clone $exists : ItemFactory::get(Item::AIR, 0, 0);
	}

	/**
	 * Returns an array of strings containing characters representing the recipe's shape.
	 * @return string[]
	 */
	public function getShape() : array{
		return $this->shape;
	}

	public function registerToCraftingManager(CraftingManager $manager) : void{
		$manager->registerShapedRecipe($this);
	}

	public function requiresCraftingTable() : bool{
		return $this->getHeight() > 2 or $this->getWidth() > 2;
	}

	/**
	 * @param Item[][] $input
	 *
	 * @return bool
	 */
	private function matchInputMap(array $input) : bool{
		$map = $this->getIngredientMap();

		//match the given items to the requested items
		for($y = 0, $y2 = $this->getHeight(); $y < $y2; ++$y){
			for($x = 0, $x2 = $this->getWidth(); $x < $x2; ++$x){
				$given = $input[$y][$x] ?? null;
				$required = $map[$y][$x];

				if($given === null or !$required->equals($given, !$required->hasAnyDamageValue(), $required->hasCompoundTag()) or $required->getCount() !== $given->getCount()){
					return false;
				}

				unset($input[$y][$x]);
			}
		}

		//check if there are any items left in the grid outside of the recipe
		/** @var Item[] $row */
		foreach($input as $y => $row){
			foreach($row as $x => $needItem){
				if(!$needItem->isNull()){
					return false; //too many input ingredients
				}
			}
		}

		return true;
	}

	/**
	 * @param Item[][] $input
	 * @param Item[][] $output
	 *
	 * @return bool
	 */
	public function matchItems(array $input, array $output) : bool{
		if(
			!$this->matchInputMap($input) and //as-is
			!$this->matchInputMap(array_map(function(array $row) : array{ return array_reverse($row, false); }, $input)) //mirrored
		){
			return false;
		}

		//and then, finally, check that the output items are good:

		/** @var Item[] $haveItems */
		$haveItems = array_merge(...$output);
		$needItems = $this->getExtraResults();
		foreach($haveItems as $j => $haveItem){
			if($haveItem->isNull()){
				unset($haveItems[$j]);
				continue;
			}

			foreach($needItems as $i => $needItem){
				if($needItem->equals($haveItem, !$needItem->hasAnyDamageValue(), $needItem->hasCompoundTag()) and $needItem->getCount() === $haveItem->getCount()){
					unset($haveItems[$j], $needItems[$i]);
					break;
				}
			}
		}

		return count($haveItems) === 0 and count($needItems) === 0;
	}
}
