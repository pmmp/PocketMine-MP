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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function array_map;
use function file_get_contents;
use function is_array;
use function json_decode;

final class CraftingManagerFromDataHelper{

	/**
	 * @param Item[] $items
	 */
	private static function containsUnknownOutputs(array $items) : bool{
		$factory = ItemFactory::getInstance();
		foreach($items as $item){
			if($item->hasAnyDamageValue()){
				throw new \InvalidArgumentException("Recipe outputs must not have wildcard meta values");
			}
			if(!$factory->isRegistered($item->getId(), $item->getMeta())){
				return true;
			}
		}

		return false;
	}

	public static function make(string $filePath) : CraftingManager{
		$recipes = json_decode(Utils::assumeNotFalse(file_get_contents($filePath), "Missing required resource file"), true);
		if(!is_array($recipes)){
			throw new AssumptionFailedError("recipes.json root should contain a map of recipe types");
		}
		$result = new CraftingManager();

		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'jsonDeserialize']);

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
			$output = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownOutputs($output)){
				continue;
			}
			$result->registerShapelessRecipe(new ShapelessRecipe(
				array_map($itemDeserializerFunc, $recipe["input"]),
				$output,
				$recipeType
			));
		}
		foreach($recipes["shaped"] as $recipe){
			if($recipe["block"] !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
				continue;
			}
			$output = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownOutputs($output)){
				continue;
			}
			$result->registerShapedRecipe(new ShapedRecipe(
				$recipe["shape"],
				array_map($itemDeserializerFunc, $recipe["input"]),
				$output
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
			$output = Item::jsonDeserialize($recipe["output"]);
			if(self::containsUnknownOutputs([$output])){
				continue;
			}
			$result->getFurnaceRecipeManager($furnaceType)->register(new FurnaceRecipe(
				$output,
				Item::jsonDeserialize($recipe["input"]))
			);
		}
		foreach($recipes["potion_type"] as $recipe){
			$output = Item::jsonDeserialize($recipe["output"]);
			if(self::containsUnknownOutputs([$output])){
				continue;
			}
			$result->registerPotionTypeRecipe(new PotionTypeRecipe(
				Item::jsonDeserialize($recipe["input"]),
				Item::jsonDeserialize($recipe["ingredient"]),
				$output
			));
		}
		foreach($recipes["potion_container_change"] as $recipe){
			if(!ItemFactory::getInstance()->isRegistered($recipe["output_item_id"])){
				continue;
			}
			$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe(
				$recipe["input_item_id"],
				Item::jsonDeserialize($recipe["ingredient"]),
				$recipe["output_item_id"]
			));
		}

		return $result;
	}
}
