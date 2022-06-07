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

use pocketmine\item\Durable;
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
	private static function containsUnknownItems(array $items) : bool{
		$factory = ItemFactory::getInstance();
		foreach($items as $item){
			if($item instanceof Durable || $item->hasAnyDamageValue()){
				//TODO: this check is imperfect and might cause problems if meta 0 isn't used for some reason
				if(!$factory->isRegistered($item->getId())){
					return true;
				}
			}elseif(!$factory->isRegistered($item->getId(), $item->getMeta())){
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
			$inputs = array_map($itemDeserializerFunc, $recipe["input"]);
			$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownItems($inputs) || self::containsUnknownItems($outputs)){
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
			$inputs = array_map($itemDeserializerFunc, $recipe["input"]);
			$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownItems($inputs) || self::containsUnknownItems($outputs)){
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
			$output = Item::jsonDeserialize($recipe["output"]);
			$input = Item::jsonDeserialize($recipe["input"]);
			if(self::containsUnknownItems([$output, $input])){
				continue;
			}
			$result->getFurnaceRecipeManager($furnaceType)->register(new FurnaceRecipe(
				$output,
				$input
			));
		}
		foreach($recipes["potion_type"] as $recipe){
			$input = Item::jsonDeserialize($recipe["input"]);
			$ingredient = Item::jsonDeserialize($recipe["ingredient"]);
			$output = Item::jsonDeserialize($recipe["output"]);

			if(self::containsUnknownItems([$input, $ingredient, $output])){
				continue;
			}
			$result->registerPotionTypeRecipe(new PotionTypeRecipe(
				$input,
				$ingredient,
				$output
			));
		}
		foreach($recipes["potion_container_change"] as $recipe){
			$input = ItemFactory::getInstance()->get($recipe["input_item_id"], -1);
			$ingredient = Item::jsonDeserialize($recipe["ingredient"]);
			$output = ItemFactory::getInstance()->get($recipe["output_item_id"], -1);

			if(self::containsUnknownItems([$input, $ingredient, $output])){
				continue;
			}
			$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe(
				$input->getId(),
				$ingredient,
				$output->getId()
			));
		}

		return $result;
	}
}
