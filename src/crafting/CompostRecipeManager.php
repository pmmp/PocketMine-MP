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

final class CompostRecipeManager{

	/** @var CompostRecipe[] */
	protected array $compostRecipes = [];

	/** @phpstan-var ObjectSet<\Closure(FurnaceRecipe) : void> */
	private ObjectSet $recipeRegisteredCallbacks;

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
		return $this->compostRecipes;
	}

	public function register(CompostRecipe $recipe) : void{
		$compost = $recipe->getCompost();
		$this->compostRecipes[$compost->getId() . ":" . ($compost->hasAnyDamageValue() ? "?" : $compost->getMeta())] = $recipe;
		foreach($this->recipeRegisteredCallbacks as $callback){
			$callback($recipe);
		}
	}

	public function match(Item $item) : ?CompostRecipe{
		return $this->compostRecipes[$item->getId() . ":" . $compost->getMeta()] ?? $this->compostRecipes[$compost->getId() . ":?"] ?? null;
	}
}
