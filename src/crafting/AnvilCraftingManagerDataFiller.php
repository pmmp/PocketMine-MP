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

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\item\Durable;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\item\VanillaItems;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class AnvilCraftingManagerDataFiller{
	public static function fillData(CraftingManager $manager) : CraftingManager{
		foreach([
			[
				VanillaItems::DIAMOND(),
				[VanillaArmorMaterials::DIAMOND(), ToolTier::DIAMOND]
			], [
				VanillaItems::GOLD_INGOT(),
				[VanillaArmorMaterials::GOLD(), ToolTier::GOLD]
			], [
				VanillaItems::IRON_INGOT(),
				[VanillaArmorMaterials::IRON(), ToolTier::IRON]
			], [
				VanillaItems::NETHERITE_INGOT(),
				[VanillaArmorMaterials::NETHERITE(), ToolTier::NETHERITE]
			], [
				VanillaItems::SCUTE(),
				[VanillaArmorMaterials::TURTLE(), null]
			], [
				VanillaItems::LEATHER(),
				[VanillaArmorMaterials::LEATHER(), null]
			]
		] as [$item, [$armorMaterial, $toolTier]]){
			$manager->registerAnvilRecipe(new MaterialRepairRecipe(
				new ArmorRecipeIngredient($armorMaterial),
				new ExactRecipeIngredient($item)
			));
			if($toolTier !== null){
				$manager->registerAnvilRecipe(new MaterialRepairRecipe(
					new TieredToolRecipeIngredient($toolTier),
					new ExactRecipeIngredient($item)
				));
			}
		}

		$manager->registerAnvilRecipe(new ItemSelfCombineRecipe(
			new MetaWildcardRecipeIngredient(ItemTypeNames::ENCHANTED_BOOK)
		));

		foreach(VanillaItems::getAll() as $item){
			if($item instanceof Durable){
				$itemId = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
				$manager->registerAnvilRecipe(new ItemSelfCombineRecipe(
					new MetaWildcardRecipeIngredient($itemId)
				));
				$manager->registerAnvilRecipe(new ItemDifferentCombineRecipe(
					new MetaWildcardRecipeIngredient($itemId),
					new MetaWildcardRecipeIngredient(ItemTypeNames::ENCHANTED_BOOK)
				));
			}
		}

		return $manager;
	}
}
