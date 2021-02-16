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
use pocketmine\utils\ObjectSet;

final class FurnaceRecipeManager{
	/** @var FurnaceRecipe[] */
	protected $furnaceRecipes = [];

	/**
	 * @var ObjectSet
	 * @phpstan-var ObjectSet<\Closure(FurnaceRecipe) : void>
	 */
	private $recipeRegisteredCallbacks;

	public function __construct(){
		$this->recipeRegisteredCallbacks = new ObjectSet();
	}

	/**
	 * @phpstan-return ObjectSet<\Closure(FurnaceRecipe) : void>
	 */
	public function getRecipeRegisteredCallbacks() : ObjectSet{
		return $this->recipeRegisteredCallbacks;
	}

	/**
	 * @return FurnaceRecipe[]
	 */
	public function getAll() : array{
		return $this->furnaceRecipes;
	}

	public function register(FurnaceRecipe $recipe) : void{
		$input = $recipe->getInput();
		$this->furnaceRecipes[$input->getId() . ":" . ($input->hasAnyDamageValue() ? "?" : $input->getMeta())] = $recipe;
		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback($recipe);
		}
	}

	public function match(Item $input) : ?FurnaceRecipe{
		return $this->furnaceRecipes[$input->getId() . ":" . $input->getMeta()] ?? $this->furnaceRecipes[$input->getId() . ":?"] ?? null;
	}
}