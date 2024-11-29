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
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\DestructorCallbackTrait;
use pocketmine\utils\ObjectSet;
use function spl_object_id;
use function usort;

class CraftingManager{
	use DestructorCallbackTrait;

	/**
	 * @var ShapedRecipe[][]
	 * @phpstan-var array<string, list<ShapedRecipe>>
	 */
	protected array $shapedRecipes = [];
	/**
	 * @var ShapelessRecipe[][]
	 * @phpstan-var array<string, list<ShapelessRecipe>>
	 */
	protected array $shapelessRecipes = [];

	/**
	 * @var CraftingRecipe[]
	 * @phpstan-var array<int, CraftingRecipe>
	 */
	private array $craftingRecipeIndex = [];

	/**
	 * @var FurnaceRecipeManager[]
	 * @phpstan-var array<int, FurnaceRecipeManager>
	 */
	protected array $furnaceRecipeManagers = [];

	/**
	 * @var PotionTypeRecipe[][]
	 * @phpstan-var list<PotionTypeRecipe>
	 */
	protected array $potionTypeRecipes = [];

	/**
	 * @var PotionContainerChangeRecipe[]
	 * @phpstan-var list<PotionContainerChangeRecipe>
	 */
	protected array $potionContainerChangeRecipes = [];

	/**
	 * @var BrewingRecipe[][]
	 * @phpstan-var array<int, array<int, BrewingRecipe>>
	 */
	private array $brewingRecipeCache = [];

	/** @phpstan-var ObjectSet<\Closure() : void> */
	private ObjectSet $recipeRegisteredCallbacks;

	public function __construct(){
		$this->recipeRegisteredCallbacks = new ObjectSet();
		foreach(FurnaceType::cases() as $furnaceType){
			$this->furnaceRecipeManagers[spl_object_id($furnaceType)] = new FurnaceRecipeManager();
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
		($retval = $i1->getStateId() <=> $i2->getStateId()) === 0 && ($retval = $i1->getCount() <=> $i2->getCount()) === 0;

		return $retval;
	}

	/**
	 * @param Item[] $items
	 * @phpstan-param list<Item> $items
	 *
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	private static function pack(array $items) : array{
		$result = [];

		foreach($items as $item){
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
	 * @phpstan-param list<Item> $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		$outputs = self::pack($outputs);
		usort($outputs, [self::class, "sort"]);
		$result = new BinaryStream();
		foreach($outputs as $o){
			//count is not written because the outputs might be from multiple repetitions of a single recipe
			//this reduces the accuracy of the hash, but it won't matter in most cases.
			$result->putVarInt($o->getStateId());
			$result->put((new LittleEndianNbtSerializer())->write(new TreeRoot($o->getNamedTag())));
		}

		return $result->getBuffer();
	}

	/**
	 * @return ShapelessRecipe[][]
	 * @phpstan-return array<string, list<ShapelessRecipe>>
	 */
	public function getShapelessRecipes() : array{
		return $this->shapelessRecipes;
	}

	/**
	 * @return ShapedRecipe[][]
	 * @phpstan-return array<string, list<ShapedRecipe>>
	 */
	public function getShapedRecipes() : array{
		return $this->shapedRecipes;
	}

	/**
	 * @return CraftingRecipe[]
	 * @phpstan-return array<int, CraftingRecipe>
	 */
	public function getCraftingRecipeIndex() : array{
		return $this->craftingRecipeIndex;
	}

	public function getCraftingRecipeFromIndex(int $index) : ?CraftingRecipe{
		return $this->craftingRecipeIndex[$index] ?? null;
	}

	public function getFurnaceRecipeManager(FurnaceType $furnaceType) : FurnaceRecipeManager{
		return $this->furnaceRecipeManagers[spl_object_id($furnaceType)];
	}

	/**
	 * @return PotionTypeRecipe[]
	 * @phpstan-return list<PotionTypeRecipe>
	 */
	public function getPotionTypeRecipes() : array{
		return $this->potionTypeRecipes;
	}

	/**
	 * @return PotionContainerChangeRecipe[]
	 * @phpstan-return list<PotionContainerChangeRecipe>
	 */
	public function getPotionContainerChangeRecipes() : array{
		return $this->potionContainerChangeRecipes;
	}

	public function registerShapedRecipe(ShapedRecipe $recipe) : void{
		$this->shapedRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;
		$this->craftingRecipeIndex[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe) : void{
		$this->shapelessRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;
		$this->craftingRecipeIndex[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function registerPotionTypeRecipe(PotionTypeRecipe $recipe) : void{
		$this->potionTypeRecipes[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function registerPotionContainerChangeRecipe(PotionContainerChangeRecipe $recipe) : void{
		$this->potionContainerChangeRecipes[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	/**
	 * @param Item[] $outputs
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

	public function matchBrewingRecipe(Item $input, Item $ingredient) : ?BrewingRecipe{
		$inputHash = $input->getStateId();
		$ingredientHash = $ingredient->getStateId();
		$cached = $this->brewingRecipeCache[$inputHash][$ingredientHash] ?? null;
		if($cached !== null){
			return $cached;
		}

		foreach($this->potionContainerChangeRecipes as $recipe){
			if($recipe->getIngredient()->accepts($ingredient) && $recipe->getResultFor($input) !== null){
				return $this->brewingRecipeCache[$inputHash][$ingredientHash] = $recipe;
			}
		}

		foreach($this->potionTypeRecipes as $recipe){
			if($recipe->getIngredient()->accepts($ingredient) && $recipe->getResultFor($input) !== null){
				return $this->brewingRecipeCache[$inputHash][$ingredientHash] = $recipe;
			}
		}

		return null;
	}
}
