<?php


declare(strict_types=1);

namespace pocketmine\crafting;

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

		foreach(VanillaItems::getAll() as $item){
			if($item instanceof Durable){
				$itemId = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
				$manager->registerAnvilRecipe(new ItemSelfCombineRecipe(
					new MetaWildcardRecipeIngredient($itemId)
				));
			}
		}

		return $manager;
	}
}