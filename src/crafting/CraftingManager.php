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
use pocketmine\utils\DestructorCallbackTrait;
use pocketmine\utils\ObjectSet;
use function json_encode;
use function usort;

class CraftingManager{
	use DestructorCallbackTrait;

	/** @var ShapedRecipe[][] */
	protected $shapedRecipes = [];
	/** @var ShapelessRecipe[][] */
	protected $shapelessRecipes = [];

	/**
	 * @var FurnaceRecipeManager[]
	 * @phpstan-var array<int, FurnaceRecipeManager>
	 */
	protected $furnaceRecipeManagers;

	/**
	 * @var ObjectSet
	 * @phpstan-var ObjectSet<\Closure() : void>
	 */
	private $recipeRegisteredCallbacks;

	public function __construct(){
		$this->recipeRegisteredCallbacks = new ObjectSet();
		foreach(FurnaceType::getAll() as $furnaceType){
			$this->furnaceRecipeManagers[$furnaceType->id()] = new FurnaceRecipeManager();
		}

		$recipeRegisteredCallbacks = $this->recipeRegisteredCallbacks;
		foreach($this->furnaceRecipeManagers as $furnaceRecipeManager){
			$furnaceRecipeManager->getRecipeRegisteredCallbacks()->add(static function(FurnaceRecipe $recipe) use ($recipeRegisteredCallbacks) : void{
				foreach($recipeRegisteredCallbacks as $callback){
					$callback();
				}
			});
		}
	}

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getRecipeRegisteredCallbacks() : ObjectSet{ return $this->recipeRegisteredCallbacks; }

	/**
	 * Function used to arrange Shapeless Recipe ingredient lists into a consistent order.
	 */
	public static function sort(Item $i1, Item $i2) : int{
		//Use spaceship operator to compare each property, then try the next one if they are equivalent.
		($retval = $i1->getId() <=> $i2->getId()) === 0 && ($retval = $i1->getMeta() <=> $i2->getMeta()) === 0 && ($retval = $i1->getCount() <=> $i2->getCount()) === 0;

		return $retval;
	}

	/**
	 * @param Item[] $items
	 *
	 * @return Item[]
	 */
	private static function pack(array $items) : array{
		/** @var Item[] $result */
		$result = [];

		foreach($items as $i => $item){
			foreach($result as $otherItem){
				if($item->canStackWith($otherItem)){
					$otherItem->setCount($otherItem->getCount() + $item->getCount());
					continue 2;
				}
			}

			//No matching item found
			$result[] = clone $item;
		}

		return $result;
	}

	/**
	 * @param Item[] $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		$outputs = self::pack($outputs);
		usort($outputs, [self::class, "sort"]);
		foreach($outputs as $o){
			//this reduces accuracy of hash, but it's necessary to deal with recipe book shift-clicking stupidity
			$o->setCount(1);
		}

		return json_encode($outputs);
	}

	/**
	 * @return ShapelessRecipe[][]
	 */
	public function getShapelessRecipes() : array{
		return $this->shapelessRecipes;
	}

	/**
	 * @return ShapedRecipe[][]
	 */
	public function getShapedRecipes() : array{
		return $this->shapedRecipes;
	}

	public function getFurnaceRecipeManager(FurnaceType $furnaceType) : FurnaceRecipeManager{
		return $this->furnaceRecipeManagers[$furnaceType->id()];
	}

	public function registerShapedRecipe(ShapedRecipe $recipe) : void{
		$this->shapedRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe) : void{
		$this->shapelessRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	/**
	 * @param Item[]       $outputs
	 */
	public function matchRecipe(CraftingGrid $grid, array $outputs) : ?CraftingRecipe{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		if(isset($this->shapedRecipes[$outputHash])){
			foreach($this->shapedRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		if(isset($this->shapelessRecipes[$outputHash])){
			foreach($this->shapelessRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		return null;
	}

	/**
	 * @param Item[] $outputs
	 *
	 * @return CraftingRecipe[]|\Generator
	 * @phpstan-return \Generator<int, CraftingRecipe, void, void>
	 */
	public function matchRecipeByOutputs(array $outputs) : \Generator{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		if(isset($this->shapedRecipes[$outputHash])){
			foreach($this->shapedRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}

		if(isset($this->shapelessRecipes[$outputHash])){
			foreach($this->shapelessRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}
	}
}
