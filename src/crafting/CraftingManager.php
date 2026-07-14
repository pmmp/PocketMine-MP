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

use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\DestructorCallbackTrait;
use pocketmine\utils\ObjectSet;
use function array_shift;
use function array_search;
use function count;
use function implode;
use function ksort;
use function spl_object_id;
use const SORT_STRING;

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

	/** @phpstan-var ObjectSet<\Closure() : void> */
	private ObjectSet $recipeUnregisteredCallbacks;

	public function __construct(){
		$this->recipeRegisteredCallbacks = new ObjectSet();
		$this->recipeUnregisteredCallbacks = new ObjectSet();

		foreach(FurnaceType::cases() as $furnaceType){
			$this->furnaceRecipeManagers[spl_object_id($furnaceType)] = new FurnaceRecipeManager();
		}

		$recipeRegisteredCallbacks = $this->recipeRegisteredCallbacks;
		$recipeUnregisteredCallbacks = $this->recipeUnregisteredCallbacks;
		foreach($this->furnaceRecipeManagers as $furnaceRecipeManager){
			$furnaceRecipeManager->getRecipeRegisteredCallbacks()->add(static function(FurnaceRecipe $recipe) use ($recipeRegisteredCallbacks) : void{
				foreach($recipeRegisteredCallbacks as $callback){
					$callback();
				}
			});
			$furnaceRecipeManager->getRecipeUnregisteredCallbacks()->add(static function(FurnaceRecipe $recipe) use ($recipeUnregisteredCallbacks) : void{
				foreach($recipeUnregisteredCallbacks as $callback){
					$callback();
				}
			});
		}
	}

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getRecipeRegisteredCallbacks() : ObjectSet{ return $this->recipeRegisteredCallbacks; }

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getRecipeUnregisteredCallbacks() : ObjectSet{ return $this->recipeUnregisteredCallbacks; }

	private static function hashOutput(Item $output) : string{
		$write = new ByteBufferWriter();
		VarInt::writeSignedInt($write, $output->getStateId());
		//TODO: the NBT serializer allocates its own ByteBufferWriter, we should change the API in the future to
		//allow passing our own to avoid this extra allocation
		$write->writeByteArray((new LittleEndianNbtSerializer())->write(new TreeRoot($output->getNamedTag())));

		return $write->getData();
	}

	/**
	 * @param Item[] $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		if(count($outputs) === 1){
			return self::hashOutput(array_shift($outputs));
		}
		$unique = [];
		foreach($outputs as $o){
			//count is not written because the outputs might be from multiple repetitions of a single recipe
			//this reduces the accuracy of the hash, but it won't matter in most cases.
			$hash = self::hashOutput($o);
			$unique[$hash] = $hash;
		}
		ksort($unique, SORT_STRING);
		return implode("", $unique);
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

	public function unregisterShapedRecipe(ShapedRecipe $recipe) : void{
		$changed = false;
		$hash = self::hashOutputs($recipe->getResults());

		foreach($this->shapedRecipes[$hash] ?? [] as $i => $r){
			if($r === $recipe){
				array_splice($this->shapedRecipes[$hash], $i, 1);
				if(count($this->shapedRecipes[$hash]) === 0){
					unset($this->shapedRecipes[$hash]);
					$changed = true;
				}
				break;
			}
		}

		$index = array_search($recipe, $this->craftingRecipeIndex, true);
		if($index !== false){
			unset($this->craftingRecipeIndex[$index]);
			$changed = true;
		}

		if($changed){
			foreach($this->recipeUnregisteredCallbacks as $callback){
				$callback();
			}
		}
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe) : void{
		$this->shapelessRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;
		$this->craftingRecipeIndex[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function unregisterShapelessRecipe(ShapelessRecipe $recipe) : void{
		$changed = false;
		$hash = self::hashOutputs($recipe->getResults());

		foreach($this->shapelessRecipes[$hash] ?? [] as $i => $r){
			if($r->isEquivalent($recipe)){
				array_splice($this->shapelessRecipes[$hash], $i, 1);
				if(count($this->shapelessRecipes[$hash]) === 0){
					unset($this->shapelessRecipes[$hash]);
					$changed = true;
				}
				// We don't break as it can have many similar recipes ?
			}
		}

		foreach($this->craftingRecipeIndex as $index => $testRecipe){
			if($testRecipe instanceof ShapelessRecipe && $recipe->isEquivalent($testRecipe)){
				unset($this->craftingRecipeIndex[$index]);
				$changed = true;
			}
		}

		if($changed){
			foreach($this->recipeUnregisteredCallbacks as $callback){
				$callback();
			}
		}
	}

	public function registerPotionTypeRecipe(PotionTypeRecipe $recipe) : void{
		$this->potionTypeRecipes[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function unregisterPotionTypeRecipe(PotionTypeRecipe $recipe) : void{
		$recipeIndex = array_search($recipe, $this->potionTypeRecipes, true);
		if($recipeIndex !== false){
			array_splice($this->potionTypeRecipes, $recipeIndex, 1);

			foreach($this->recipeUnregisteredCallbacks as $callback){
				$callback();
			}
		}
	}

	public function registerPotionContainerChangeRecipe(PotionContainerChangeRecipe $recipe) : void{
		$this->potionContainerChangeRecipes[] = $recipe;

		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback();
		}
	}

	public function unregisterPotionContainerChangeRecipe(PotionContainerChangeRecipe $recipe) : void{
		$recipeIndex = array_search($recipe, $this->potionContainerChangeRecipes, true);
		if($recipeIndex !== false){
			array_splice($this->potionContainerChangeRecipes, $recipeIndex, 1);

			foreach($this->recipeUnregisteredCallbacks as $callback){
				$callback();
			}
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
