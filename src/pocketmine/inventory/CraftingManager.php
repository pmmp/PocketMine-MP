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

namespace pocketmine\inventory;

use pocketmine\block\Planks;
use pocketmine\block\Wood;
use pocketmine\item\Item;

class CraftingManager{

	/** @var Recipe[] */
	public $recipes = [];

	/** @var Recipe[][] */
	protected $recipeLookup = [];

	/** @var FurnaceRecipe[] */
	public $furnaceRecipes = [];

	public function __construct(){
		//TODO: add crafting recipes
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::OAK, 4)))->addIngredient(Item::get(Item::WOOD, Wood::OAK, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 4)))->addIngredient(Item::get(Item::WOOD, Wood::SPRUCE, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 4)))->addIngredient(Item::get(Item::WOOD, Wood::BIRCH, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 4)))->addIngredient(Item::get(Item::WOOD, Wood::JUNGLE, 1)));
		//$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 4)))->addIngredient(Item::get(Item::WOOD2, Wood2::ACACIA, 1)));
		//$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 4)))->addIngredient(Item::get(Item::WOOD2, Wood2::DARK_OAK, 1)));
	}

	public function sort(Item $i1, Item $i2){
		if($i1->getID() > $i2->getID()){
			return 1;
		}elseif($i1->getID() < $i2->getID()){
			return -1;
		}elseif($i1->getDamage() > $i2->getDamage()){
			return 1;
		}elseif($i1->getDamage() < $i2->getDamage()){
			return -1;
		}elseif($i1->getCount() > $i2->getCount()){
			return 1;
		}elseif($i1->getCount() < $i2->getCount()){
			return -1;
		}else{
			return 0;
		}
	}


	/**
	 * @return Recipe[]
	 */
	public function getRecipes(){
		return $this->recipes;
	}

	/**
	 * @param ShapedRecipe $recipe
	 */
	public function registerShapedRecipe(ShapedRecipe $recipe){

	}

	/**
	 * @param ShapelessRecipe $recipe
	 */
	public function registerShapelessRecipe(ShapelessRecipe $recipe){
		$result = $recipe->getResult();
		$this->recipes[spl_object_hash($recipe)] = $recipe;
		$hash = "";
		$ingredients = $recipe->getIngredientList();
		usort($ingredients, array($this, "sort"));
		foreach($ingredients as $item){
			$hash .= $item->getID().":".($item->getDamage() === null ? "?":$item->getDamage())."x".$item->getCount().",";
		}
		$this->recipeLookup[$result->getID().":".$result->getDamage()][$hash] = $recipe;
	}

	/**
	 * @param FurnaceRecipe $recipe
	 */
	public function registerFurnaceRecipe(FurnaceRecipe $recipe){

	}

	/**
	 * @param CraftingTransactionGroup $ts
	 *
	 * @return Recipe
	 */
	public function matchTransaction(CraftingTransactionGroup $ts){
		$result = $ts->getResult();

		if(!($result instanceof Item)){
			return false;
		}
		$k = $result->getID().":".$result->getDamage();

		if(!isset($this->recipeLookup[$k])){
			return false;
		}
		$hash = "";
		$input = $ts->getRecipe();
		usort($input, array($this, "sort"));
		foreach($input as $item){
			$hash .= $item->getID().":".($item->getDamage() === null ? "?":$item->getDamage())."x".$item->getCount().",";
		}
		if(!isset($this->recipeLookup[$k][$hash])){
			return false;
		}
		$checkResult = $this->recipeLookup[$k][$hash]->getResult();
		if($checkResult->equals($result, true) and $checkResult->getCount() === $result->getCount()){
			return $this->recipeLookup[$k][$hash];
		}
		return null;
	}

	/**
	 * @param Recipe $recipe
	 */
	public function registerRecipe(Recipe $recipe){
		if($recipe instanceof ShapedRecipe){
			$this->registerShapedRecipe($recipe);
		}elseif($recipe instanceof ShapelessRecipe){
			$this->registerShapelessRecipe($recipe);
		}elseif($recipe instanceof FurnaceRecipe){
			$this->registerFurnaceRecipe($recipe);
		}
	}

}