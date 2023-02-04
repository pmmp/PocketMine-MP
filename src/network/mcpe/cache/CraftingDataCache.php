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
use pocketmine\crafting\RecipeIngredient;
use pocketmine\crafting\ShapelessRecipeType;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\recipe\CraftingRecipeBlockName;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe as ProtocolFurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipeBlockName;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\PotionContainerChangeRecipe as ProtocolPotionContainerChangeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\PotionTypeRecipe as ProtocolPotionTypeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe as ProtocolShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe as ProtocolShapelessRecipe;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\ProtocolSingletonTrait;
use Ramsey\Uuid\Uuid;
use function array_map;
use function in_array;
use function spl_object_id;

final class CraftingDataCache{
	use ProtocolSingletonTrait;

	/**
	 * @var CraftingDataPacket[]
	 * @phpstan-var array<int, CraftingDataPacket>
	 */
	private array $caches = [];

	private int $protocolId;

	public function __construct(int $protocolId){
		$this->protocolId = $protocolId;
	}

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

		$counter = 0;
		$nullUUID = Uuid::fromString(Uuid::NIL);
		$converter = TypeConverter::getInstance();
		$recipesWithTypeIds = [];
		foreach($manager->getShapelessRecipes() as $list){
			foreach($list as $recipe){
				$typeTag = match($recipe->getType()->id()){
					ShapelessRecipeType::CRAFTING()->id() => CraftingRecipeBlockName::CRAFTING_TABLE,
					ShapelessRecipeType::STONECUTTER()->id() => CraftingRecipeBlockName::STONECUTTER,
					ShapelessRecipeType::CARTOGRAPHY()->id() => CraftingRecipeBlockName::CARTOGRAPHY_TABLE,
					ShapelessRecipeType::SMITHING()->id() => CraftingRecipeBlockName::SMITHING_TABLE,
					default => throw new AssumptionFailedError("Unreachable"),
				};

				$inputs = array_map(function(RecipeIngredient $item) use ($converter) : ?ProtocolRecipeIngredient{
					try {
						return $converter->coreRecipeIngredientToNet($this->protocolId, $item);
					} catch(\InvalidArgumentException $e){
						return null;
					}
				}, $recipe->getIngredientList());
				$outputs = array_map(function(Item $item) use ($converter) : ?ItemStack{
					try {
						return $converter->coreItemStackToNet($this->protocolId, $item);
					} catch(\InvalidArgumentException | AssumptionFailedError $e){
						return null;
					}
				}, $recipe->getResults());

				if(!$this->checkInputValidity($inputs) || !$this->checkOutputValidity($outputs)){
					continue;
				}

				$recipesWithTypeIds[] = new ProtocolShapelessRecipe(
					CraftingDataPacket::ENTRY_SHAPELESS,
					Binary::writeInt(++$counter),
					$inputs,
					$outputs,
					$nullUUID,
					$typeTag,
					50,
					$counter
				);
			}
		}
		foreach($manager->getShapedRecipes() as $list){
			foreach($list as $recipe){
				$inputs = [];

				try {
					for($row = 0, $height = $recipe->getHeight(); $row < $height; ++$row){
						for($column = 0, $width = $recipe->getWidth(); $column < $width; ++$column){
							$inputs[$row][$column] = $converter->coreRecipeIngredientToNet($this->protocolId, $recipe->getIngredient($column, $row));
						}
					}
				} catch(\InvalidArgumentException $e){
					continue;
				}

				$outputs = array_map(function(Item $item) use ($converter) : ?ItemStack{
					try {
						return $converter->coreItemStackToNet($this->protocolId, $item);
					} catch(\InvalidArgumentException | AssumptionFailedError $e){
						return null;
					}
				}, $recipe->getResults());

				if(!$this->checkOutputValidity($outputs)){
					continue;
				}

				$recipesWithTypeIds[] = new ProtocolShapedRecipe(
					CraftingDataPacket::ENTRY_SHAPED,
					Binary::writeInt(++$counter),
					$inputs,
					$outputs,
					$nullUUID,
					CraftingRecipeBlockName::CRAFTING_TABLE,
					50,
					$counter
				);
			}
		}

		foreach(FurnaceType::getAll() as $furnaceType){
			$typeTag = match($furnaceType->id()){
				FurnaceType::FURNACE()->id() => FurnaceRecipeBlockName::FURNACE,
				FurnaceType::BLAST_FURNACE()->id() => FurnaceRecipeBlockName::BLAST_FURNACE,
				FurnaceType::SMOKER()->id() => FurnaceRecipeBlockName::SMOKER,
				default => throw new AssumptionFailedError("Unreachable"),
			};
			foreach($manager->getFurnaceRecipeManager($furnaceType)->getAll() as $recipe){
				try {
					$input = $converter->coreRecipeIngredientToNet($this->protocolId, $recipe->getInput())->getDescriptor();
					$output = $converter->coreItemStackToNet($this->protocolId, $recipe->getResult());
				} catch(\InvalidArgumentException | AssumptionFailedError $e){
					continue;
				}

				if(!$input instanceof IntIdMetaItemDescriptor){
					throw new AssumptionFailedError();
				}
				$recipesWithTypeIds[] = new ProtocolFurnaceRecipe(
					CraftingDataPacket::ENTRY_FURNACE_DATA,
					$input->getId(),
					$input->getMeta(),
					$output,
					$typeTag
				);
			}
		}

		$potionTypeRecipes = [];
		foreach($manager->getPotionTypeRecipes() as $recipe){
			try {
				$input = $converter->coreRecipeIngredientToNet($this->protocolId, $recipe->getInput())->getDescriptor();
				$ingredient = $converter->coreRecipeIngredientToNet($this->protocolId, $recipe->getIngredient())->getDescriptor();
				if(!$input instanceof IntIdMetaItemDescriptor || !$ingredient instanceof IntIdMetaItemDescriptor){
					throw new AssumptionFailedError();
				}
				$output = $converter->coreItemStackToNet($this->protocolId, $recipe->getOutput());
			} catch(\InvalidArgumentException $e){
				continue;
			}

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
		$itemTypeDictionary = GlobalItemTypeDictionary::getInstance()->getDictionary();
		foreach($manager->getPotionContainerChangeRecipes() as $recipe){
			$input = $itemTypeDictionary->fromStringId($recipe->getInputItemId());
			try {
				$ingredient = $converter->coreRecipeIngredientToNet($this->protocolId, $recipe->getIngredient())->getDescriptor();
			} catch(\InvalidArgumentException $e){
				continue;
			}
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

	/**
	 * @param ProtocolRecipeIngredient[] $inputs
	 */
	private function checkInputValidity(array $inputs) : bool{
		return !in_array(null, $inputs, true);
	}

	/**
	 * @param ItemStack[] $outputs
	 */
	private function checkOutputValidity(array $outputs) : bool{
		return !in_array(null, $outputs, true);
	}

	public static function convertProtocol(int $protocolId) : int{
		return ItemTranslator::convertProtocol($protocolId);
	}
}
