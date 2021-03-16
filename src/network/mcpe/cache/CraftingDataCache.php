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
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe as ProtocolFurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe as ProtocolShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe as ProtocolShapelessRecipe;
use pocketmine\timings\Timings;
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
	private $caches = [];

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
		$pk = new CraftingDataPacket();
		$pk->cleanRecipes = true;

		$counter = 0;
		$nullUUID = Uuid::fromString(Uuid::NIL);
		$converter = TypeConverter::getInstance();
		foreach($manager->getShapelessRecipes() as $list){
			foreach($list as $recipe){
				$pk->entries[] = new ProtocolShapelessRecipe(
					CraftingDataPacket::ENTRY_SHAPELESS,
					Binary::writeInt($counter++),
					array_map(function(Item $item) use ($converter) : RecipeIngredient{
						return $converter->coreItemStackToRecipeIngredient($item);
					}, $recipe->getIngredientList()),
					array_map(function(Item $item) use ($converter) : ItemStack{
						return $converter->coreItemStackToNet($item);
					}, $recipe->getResults()),
					$nullUUID,
					"crafting_table",
					50,
					$counter
				);
			}
		}
		foreach($manager->getShapedRecipes() as $list){
			foreach($list as $recipe){
				$inputs = [];

				for($row = 0, $height = $recipe->getHeight(); $row < $height; ++$row){
					for($column = 0, $width = $recipe->getWidth(); $column < $width; ++$column){
						$inputs[$row][$column] = $converter->coreItemStackToRecipeIngredient($recipe->getIngredient($column, $row));
					}
				}
				$pk->entries[] = $r = new ProtocolShapedRecipe(
					CraftingDataPacket::ENTRY_SHAPED,
					Binary::writeInt($counter++),
					$inputs,
					array_map(function(Item $item) use ($converter) : ItemStack{
						return $converter->coreItemStackToNet($item);
					}, $recipe->getResults()),
					$nullUUID,
					"crafting_table",
					50,
					$counter
				);
			}
		}

		foreach($manager->getFurnaceRecipeManager()->getAll() as $recipe){
			$input = $converter->coreItemStackToNet($recipe->getInput());
			$pk->entries[] = new ProtocolFurnaceRecipe(
				CraftingDataPacket::ENTRY_FURNACE_DATA,
				$input->getId(),
				$input->getMeta(),
				$converter->coreItemStackToNet($recipe->getResult()),
				"furnace"
			);
		}

		Timings::$craftingDataCacheRebuild->stopTiming();
		return $pk;
	}
}
