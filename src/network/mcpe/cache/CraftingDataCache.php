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

namespace pocketmine\network\mcpe\cache;

use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\FurnaceType;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\crafting\ShapelessRecipeType;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\types\recipe\CraftingRecipeBlockName;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe as ProtocolFurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipeBlockName;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\PotionContainerChangeRecipe as ProtocolPotionContainerChangeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\PotionTypeRecipe as ProtocolPotionTypeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeUnlockingRequirement;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe as ProtocolShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe as ProtocolShapelessRecipe;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use function array_map;
use function spl_object_id;

final class CraftingDataCache{
	use SingletonTrait;

	/**
	 * @var CraftingDataPacket[]
	 * @phpstan-var array<int, CraftingDataPacket>
	 */
	private array $caches = [];

	public function getCache(CraftingManager $manager) : CraftingDataPacket{
		$id = spl_object_id($manager);
		if(!isset($this->caches[$id])){
			$manager->getDestructorCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$manager->getRecipeRegisteredCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->buildCraftingDataCache($manager);
		}
		return $this->caches[$id];
	}

	/**
	 * Rebuilds the cached CraftingDataPacket.
	 */
	private function buildCraftingDataCache(CraftingManager $manager) : CraftingDataPacket{
		Timings::$craftingDataCacheRebuild->startTiming();

		$nullUUID = Uuid::fromString(Uuid::NIL);
		$converter = TypeConverter::getInstance();
		$recipesWithTypeIds = [];

		$noUnlockingRequirement = new RecipeUnlockingRequirement(null);
		foreach($manager->getCraftingRecipeIndex() as $index => $recipe){
			if($recipe instanceof ShapelessRecipe){
				$typeTag = match($recipe->getType()){
					ShapelessRecipeType::CRAFTING => CraftingRecipeBlockName::CRAFTING_TABLE,
					ShapelessRecipeType::STONECUTTER => CraftingRecipeBlockName::STONECUTTER,
					ShapelessRecipeType::CARTOGRAPHY => CraftingRecipeBlockName::CARTOGRAPHY_TABLE,
					ShapelessRecipeType::SMITHING => CraftingRecipeBlockName::SMITHING_TABLE,
				};
				$recipesWithTypeIds[] = new ProtocolShapelessRecipe(
					CraftingDataPacket::ENTRY_SHAPELESS,
					Binary::writeInt($index),
					array_map($converter->coreRecipeIngredientToNet(...), $recipe->getIngredientList()),
					array_map($converter->coreItemStackToNet(...), $recipe->getResults()),
					$nullUUID,
					$typeTag,
					50,
					$noUnlockingRequirement,
					$index
				);
			}elseif($recipe instanceof ShapedRecipe){
				$inputs = [];

				for($row = 0, $height = $recipe->getHeight(); $row < $height; ++$row){
					for($column = 0, $width = $recipe->getWidth(); $column < $width; ++$column){
						$inputs[$row][$column] = $converter->coreRecipeIngredientToNet($recipe->getIngredient($column, $row));
					}
				}
				$recipesWithTypeIds[] = $r = new ProtocolShapedRecipe(
					CraftingDataPacket::ENTRY_SHAPED,
					Binary::writeInt($index),
					$inputs,
					array_map($converter->coreItemStackToNet(...), $recipe->getResults()),
					$nullUUID,
					CraftingRecipeBlockName::CRAFTING_TABLE,
					50,
					true,
					$noUnlockingRequirement,
					$index,
				);
			}else{
				//TODO: probably special recipe types
			}
		}

		foreach(FurnaceType::cases() as $furnaceType){
			$typeTag = match($furnaceType){
				FurnaceType::FURNACE => FurnaceRecipeBlockName::FURNACE,
				FurnaceType::BLAST_FURNACE => FurnaceRecipeBlockName::BLAST_FURNACE,
				FurnaceType::SMOKER => FurnaceRecipeBlockName::SMOKER,
				FurnaceType::CAMPFIRE => FurnaceRecipeBlockName::CAMPFIRE,
				FurnaceType::SOUL_CAMPFIRE => FurnaceRecipeBlockName::SOUL_CAMPFIRE
			};
			foreach($manager->getFurnaceRecipeManager($furnaceType)->getAll() as $recipe){
				$input = $converter->coreRecipeIngredientToNet($recipe->getInput())->getDescriptor();
				if(!$input instanceof IntIdMetaItemDescriptor){
					throw new AssumptionFailedError();
				}
				$recipesWithTypeIds[] = new ProtocolFurnaceRecipe(
					CraftingDataPacket::ENTRY_FURNACE_DATA,
					$input->getId(),
					$input->getMeta(),
					$converter->coreItemStackToNet($recipe->getResult()),
					$typeTag
				);
			}
		}

		$potionTypeRecipes = [];
		foreach($manager->getPotionTypeRecipes() as $recipe){
			$input = $converter->coreRecipeIngredientToNet($recipe->getInput())->getDescriptor();
			$ingredient = $converter->coreRecipeIngredientToNet($recipe->getIngredient())->getDescriptor();
			if(!$input instanceof IntIdMetaItemDescriptor || !$ingredient instanceof IntIdMetaItemDescriptor){
				throw new AssumptionFailedError();
			}
			$output = $converter->coreItemStackToNet($recipe->getOutput());
			$potionTypeRecipes[] = new ProtocolPotionTypeRecipe(
				$input->getId(),
				$input->getMeta(),
				$ingredient->getId(),
				$ingredient->getMeta(),
				$output->getId(),
				$output->getMeta()
			);
		}

		$potionContainerChangeRecipes = [];
		$itemTypeDictionary = $converter->getItemTypeDictionary();
		foreach($manager->getPotionContainerChangeRecipes() as $recipe){
			$input = $itemTypeDictionary->fromStringId($recipe->getInputItemId());
			$ingredient = $converter->coreRecipeIngredientToNet($recipe->getIngredient())->getDescriptor();
			if(!$ingredient instanceof IntIdMetaItemDescriptor){
				throw new AssumptionFailedError();
			}
			$output = $itemTypeDictionary->fromStringId($recipe->getOutputItemId());
			$potionContainerChangeRecipes[] = new ProtocolPotionContainerChangeRecipe(
				$input,
				$ingredient->getId(),
				$output
			);
		}

		Timings::$craftingDataCacheRebuild->stopTiming();
		return CraftingDataPacket::create($recipesWithTypeIds, $potionTypeRecipes, $potionContainerChangeRecipes, [], true);
	}
}
