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
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
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
	 * @param Item[] $items
	 */
	private static function containsUnknownItems(array $items) : bool{
		$factory = ItemFactory::getInstance();
		foreach($items as $item){
			if($item instanceof Durable){
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
		$item = Item::jsonDeserialize($data);

		return self::containsUnknownItems([$item]) ? null : new ExactRecipeIngredient($item);
	}

	public static function make(string $filePath) : CraftingManager{
		$recipes = json_decode(Utils::assumeNotFalse(file_get_contents($filePath), "Missing required resource file"), true);
		if(!is_array($recipes)){
			throw new AssumptionFailedError("recipes.json root should contain a map of recipe types");
		}
		$result = new CraftingManager();

		$ingredientDeserializerFunc = \Closure::fromCallable([self::class, "deserializeIngredient"]);
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
			$inputs = [];
			foreach($recipe["input"] as $inputData){
				$input = $ingredientDeserializerFunc($inputData);
				if($input === null){ //unknown input item
					continue;
				}
				$inputs[] = $input;
			}
			$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownItems($outputs)){
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
					continue;
				}
				$inputs[$symbol] = $input;
			}
			$outputs = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownItems($outputs)){
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
			$input = self::deserializeIngredient($recipe["input"]);
			if($input === null || self::containsUnknownItems([$output])){
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
			$input = ItemFactory::getInstance()->get($recipe["input_item_id"]);
			$ingredient = Item::jsonDeserialize($recipe["ingredient"]);
			$output = ItemFactory::getInstance()->get($recipe["output_item_id"]);

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
