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

namespace pocketmine\inventory\transaction;

use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\CraftingRecipe;
use pocketmine\crafting\RecipeIngredient;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use function array_fill_keys;
use function array_keys;
use function array_pop;
use function count;
use function intdiv;
use function min;
use function uasort;

/**
 * This transaction type is specialized for crafting validation. It shares most of the same semantics of the base
 * inventory transaction type, but the requirement for validity is slightly different.
 *
 * It is expected that the actions in this transaction type will produce an **unbalanced result**, i.e. some inputs won't
 * have corresponding outputs, and vice versa. The reason why is because the unmatched inputs are recipe inputs, and
 * the unmatched outputs are recipe results.
 *
 * Therefore, the validity requirement is that the imbalance of the transaction should match the expected inputs and
 * outputs of a registered crafting recipe.
 *
 * This transaction allows multiple repetitions of the same recipe to be crafted in a single batch. In the case of batch
 * crafting, the number of unmatched inputs and outputs must be exactly divisible by the expected recipe ingredients and
 * results, with no remainder. Any leftovers are expected to be emitted back to the crafting grid.
 */
class CraftingTransaction extends InventoryTransaction{
	protected ?CraftingRecipe $recipe = null;
	protected ?int $repetitions = null;

	/**
	 * @var Item[]
	 * @phpstan-var list<Item>
	 */
	protected array $inputs = [];
	/**
	 * @var Item[]
	 * @phpstan-var list<Item>
	 */
	protected array $outputs = [];

	private CraftingManager $craftingManager;

	public function __construct(Player $source, CraftingManager $craftingManager, array $actions = [], ?CraftingRecipe $recipe = null, ?int $repetitions = null){
		parent::__construct($source, $actions);
		$this->craftingManager = $craftingManager;
		$this->recipe = $recipe;
		$this->repetitions = $repetitions;
	}

	/**
	 * @param Item[] $providedItems
	 * @return Item[]
	 *
	 * @phpstan-param list<Item> $providedItems
	 * @phpstan-return list<Item>
	 */
	private static function packItems(array $providedItems) : array{
		$packedProvidedItems = [];
		while(count($providedItems) > 0){
			$item = array_pop($providedItems);
			foreach($providedItems as $k => $otherItem){
				if($item->canStackWith($otherItem)){
					$item->setCount($item->getCount() + $otherItem->getCount());
					unset($providedItems[$k]);
				}
			}
			$packedProvidedItems[] = $item;
		}

		return $packedProvidedItems;
	}

	/**
	 * @param Item[]             $providedItems
	 * @param RecipeIngredient[] $recipeIngredients
	 *
	 * @phpstan-param list<Item> $providedItems
	 * @phpstan-param list<RecipeIngredient> $recipeIngredients
	 */
	public static function matchIngredients(array $providedItems, array $recipeIngredients, int $expectedIterations) : void{
		if(count($recipeIngredients) === 0){
			throw new TransactionValidationException("No recipe ingredients given");
		}
		if(count($providedItems) === 0){
			throw new TransactionValidationException("No transaction items given");
		}

		$packedProvidedItems = self::packItems(Utils::cloneObjectArray($providedItems));
		$packedProvidedItemMatches = array_fill_keys(array_keys($packedProvidedItems), 0);

		$recipeIngredientMatches = [];

		foreach($recipeIngredients as $ingredientIndex => $recipeIngredient){
			$acceptedItems = [];
			foreach($packedProvidedItems as $itemIndex => $packedItem){
				if($recipeIngredient->accepts($packedItem)){
					$packedProvidedItemMatches[$itemIndex]++;
					$acceptedItems[$itemIndex] = $itemIndex;
				}
			}

			if(count($acceptedItems) === 0){
				throw new TransactionValidationException("No provided items satisfy ingredient requirement $recipeIngredient");
			}

			$recipeIngredientMatches[$ingredientIndex] = $acceptedItems;
		}

		foreach($packedProvidedItemMatches as $itemIndex => $itemMatchCount){
			if($itemMatchCount === 0){
				$item = $packedProvidedItems[$itemIndex];
				throw new TransactionValidationException("Provided item $item is not accepted by any recipe ingredient");
			}
		}

		//Most picky ingredients first - avoid picky ingredient getting their items stolen by wildcard ingredients
		//TODO: this is still insufficient when multiple wildcard ingredients have overlaps, but we don't (yet) have to
		//worry about those.
		uasort($recipeIngredientMatches, fn(array $a, array $b) => count($a) <=> count($b));

		foreach($recipeIngredientMatches as $ingredientIndex => $acceptedItems){
			$needed = $expectedIterations;

			foreach($packedProvidedItems as $itemIndex => $item){
				if(!isset($acceptedItems[$itemIndex])){
					continue;
				}

				$taken = min($needed, $item->getCount());
				$needed -= $taken;
				$item->setCount($item->getCount() - $taken);

				if($item->getCount() === 0){
					unset($packedProvidedItems[$itemIndex]);
				}

				if($needed === 0){
					//validation passed!
					continue 2;
				}
			}

			$recipeIngredient = $recipeIngredients[$ingredientIndex];
			$actualIterations = $expectedIterations - $needed;
			throw new TransactionValidationException("Not enough items to satisfy recipe ingredient $recipeIngredient for $expectedIterations (only have enough items for $actualIterations iterations)");
		}

		if(count($packedProvidedItems) > 0){
			throw new TransactionValidationException("Not all provided items were used");
		}
	}

	/**
	 * @param Item[] $txItems
	 * @param Item[] $recipeItems
	 *
	 * @phpstan-param list<Item> $txItems
	 * @phpstan-param list<Item> $recipeItems
	 *
	 * @throws TransactionValidationException
	 */
	protected function matchOutputs(array $txItems, array $recipeItems) : int{
		if(count($recipeItems) === 0){
			throw new TransactionValidationException("No recipe items given");
		}
		if(count($txItems) === 0){
			throw new TransactionValidationException("No transaction items given");
		}

		$iterations = 0;
		while(count($recipeItems) > 0){
			/** @var Item $recipeItem */
			$recipeItem = array_pop($recipeItems);
			$needCount = $recipeItem->getCount();
			foreach($recipeItems as $i => $otherRecipeItem){
				if($otherRecipeItem->canStackWith($recipeItem)){ //make sure they have the same wildcards set
					$needCount += $otherRecipeItem->getCount();
					unset($recipeItems[$i]);
				}
			}

			$haveCount = 0;
			foreach($txItems as $j => $txItem){
				if($txItem->canStackWith($recipeItem)){
					$haveCount += $txItem->getCount();
					unset($txItems[$j]);
				}
			}

			if($haveCount % $needCount !== 0){
				//wrong count for this output, should divide exactly
				throw new TransactionValidationException("Expected an exact multiple of required $recipeItem (given: $haveCount, needed: $needCount)");
			}

			$multiplier = intdiv($haveCount, $needCount);
			if($multiplier < 1){
				throw new TransactionValidationException("Expected more than zero items matching $recipeItem (given: $haveCount, needed: $needCount)");
			}
			if($iterations === 0){
				$iterations = $multiplier;
			}elseif($multiplier !== $iterations){
				//wrong count for this output, should match previous outputs
				throw new TransactionValidationException("Expected $recipeItem x$iterations, but found x$multiplier");
			}
		}

		if(count($txItems) > 0){
			//all items should be destroyed in this process
			throw new TransactionValidationException("Expected 0 items left over, have " . count($txItems));
		}

		return $iterations;
	}

	private function validateRecipe(CraftingRecipe $recipe, ?int $expectedRepetitions) : int{
		//compute number of times recipe was crafted
		$repetitions = $this->matchOutputs($this->outputs, $recipe->getResultsFor($this->source->getCraftingGrid()));
		if($expectedRepetitions !== null && $repetitions !== $expectedRepetitions){
			throw new TransactionValidationException("Expected $expectedRepetitions repetitions, got $repetitions");
		}
		//assert that $repetitions x recipe ingredients should be consumed
		self::matchIngredients($this->inputs, $recipe->getIngredientList(), $repetitions);

		return $repetitions;
	}

	public function validate() : void{
		$this->squashDuplicateSlotChanges();
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		$this->matchItems($this->outputs, $this->inputs);

		if($this->recipe === null){
			$failed = 0;
			foreach($this->craftingManager->matchRecipeByOutputs($this->outputs) as $recipe){
				try{
					//compute number of times recipe was crafted
					$this->repetitions = $this->matchOutputs($this->outputs, $recipe->getResultsFor($this->source->getCraftingGrid()));
					//assert that $repetitions x recipe ingredients should be consumed
					self::matchIngredients($this->inputs, $recipe->getIngredientList(), $this->repetitions);

					//Success!
					$this->recipe = $recipe;
					break;
				}catch(TransactionValidationException $e){
					//failed
					++$failed;
				}
			}

			if($this->recipe === null){
				throw new TransactionValidationException("Unable to match a recipe to transaction (tried to match against $failed recipes)");
			}
		}else{
			$this->repetitions = $this->validateRecipe($this->recipe, $this->repetitions);
		}
	}

	protected function callExecuteEvent() : bool{
		$ev = new CraftItemEvent($this, $this->recipe, $this->repetitions, $this->inputs, $this->outputs);
		$ev->call();
		return !$ev->isCancelled();
	}
}
