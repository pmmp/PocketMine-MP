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

use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function array_map;
use function file_get_contents;
use function is_array;
use function is_int;
use function json_decode;

final class CraftingManagerFromDataHelper{
	/**
	 * @param mixed[] $data
	 */
	private static function deserializeIngredient(array $data) : ?RecipeIngredient{
		if(!isset($data["id"]) || !is_int($data["id"])){
			throw new \InvalidArgumentException("Invalid input data, expected int ID");
		}
		if(isset($data["damage"]) && $data["damage"] === -1){
			try{
				$typeData = GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataInt($data["id"], 0, 1, null);
			}catch(ItemTypeDeserializeException){
				//probably unknown item
				return null;
			}

			return new MetaWildcardRecipeIngredient($typeData->getTypeData()->getName());
		}

		//TODO: we need to stop using jsonDeserialize for this
		try{
			$item = Item::legacyJsonDeserialize($data);
		}catch(SavedDataLoadingException){
			//unknown item
			return null;
		}

		return new ExactRecipeIngredient($item);
	}

	public static function make(string $filePath) : CraftingManager{
		$recipes = json_decode(Utils::assumeNotFalse(file_get_contents($filePath), "Missing required resource file"), true);
		if(!is_array($recipes)){
			throw new AssumptionFailedError("recipes.json root should contain a map of recipe types");
		}
		$result = new CraftingManager();

		$ingredientDeserializerFunc = \Closure::fromCallable([self::class, "deserializeIngredient"]);
		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'legacyJsonDeserialize']);

		foreach($recipes["shapeless"] as $recipe){
			$recipeType = match($recipe["block"]){
				"crafting_table" => ShapelessRecipeType::CRAFTING(),
				"stonecutter" => ShapelessRecipeType::STONECUTTER(),
				//TODO: Cartography Table
				default => null
			};
			if($recipeType === null){
				continue;
			}
			$inputs = [];
			foreach($recipe["input"] as $inputData){
				$input = $ingredientDeserializerFunc($inputData);
				if($input === null){ //unknown input item
					continue 2;
				}
				$inputs[] = $input;
			}
			try{
				$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			}catch(SavedDataLoadingException){
				//unknown output item
				continue;
			}
			$result->registerShapelessRecipe(new ShapelessRecipe(
				$inputs,
				$outputs,
				$recipeType
			));
		}
		foreach($recipes["shaped"] as $recipe){
			if($recipe["block"] !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
				continue;
			}
			$inputs = [];
			foreach($recipe["input"] as $symbol => $inputData){
				$input = $ingredientDeserializerFunc($inputData);
				if($input === null){ //unknown input item
					continue 2;
				}
				$inputs[$symbol] = $input;
			}
			try{
				$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			}catch(SavedDataLoadingException){
				//unknown output item
				continue;
			}
			$result->registerShapedRecipe(new ShapedRecipe(
				$recipe["shape"],
				$inputs,
				$outputs
			));
		}
		foreach($recipes["smelting"] as $recipe){
			$furnaceType = match ($recipe["block"]){
				"furnace" => FurnaceType::FURNACE(),
				"blast_furnace" => FurnaceType::BLAST_FURNACE(),
				"smoker" => FurnaceType::SMOKER(),
				//TODO: campfire
				default => null
			};
			if($furnaceType === null){
				continue;
			}
			try{
				$output = Item::legacyJsonDeserialize($recipe["output"]);
			}catch(SavedDataLoadingException){
				continue;
			}
			$input = self::deserializeIngredient($recipe["input"]);
			if($input === null){
				continue;
			}
			$result->getFurnaceRecipeManager($furnaceType)->register(new FurnaceRecipe(
				$output,
				$input
			));
		}
		foreach($recipes["potion_type"] as $recipe){
			try{
				$input = Item::legacyJsonDeserialize($recipe["input"]);
				$ingredient = Item::legacyJsonDeserialize($recipe["ingredient"]);
				$output = Item::legacyJsonDeserialize($recipe["output"]);
			}catch(SavedDataLoadingException){
				//unknown item
				continue;
			}
			$result->registerPotionTypeRecipe(new PotionTypeRecipe(
				$input,
				$ingredient,
				$output
			));
		}
		foreach($recipes["potion_container_change"] as $recipe){
			try{
				$ingredient = Item::legacyJsonDeserialize($recipe["ingredient"]);
			}catch(SavedDataLoadingException){
				//unknown item
				continue;
			}

			//TODO: we'll be able to get rid of these conversions once the crafting data is updated
			$inputId = LegacyItemIdToStringIdMap::getInstance()->legacyToString($recipe["input_item_id"]);
			$outputId = LegacyItemIdToStringIdMap::getInstance()->legacyToString($recipe["output_item_id"]);
			if($inputId === null || $outputId === null){
				//unknown item
				continue;
			}
			$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe(
				$inputId,
				$ingredient,
				$outputId
			));
		}

		return $result;
	}
}
