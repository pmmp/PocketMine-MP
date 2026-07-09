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

use Generator;
use PHPUnit\Framework\TestCase;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use function floor;

class AnvilCraftTest extends TestCase{
	public static function materialRepairRecipeProvider() : Generator{
		yield "No repair available" => [
			VanillaItems::DIAMOND_PICKAXE(),
			VanillaItems::DIAMOND(),
			null
		];

		yield "Repair one damage" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage(1),
			VanillaItems::DIAMOND(),
			new AnvilCraftResult(1, VanillaItems::DIAMOND_PICKAXE(), null)
		];

		yield "Repair one damage with more materials than expected" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage(1),
			VanillaItems::DIAMOND()->setCount(2),
			new AnvilCraftResult(1, VanillaItems::DIAMOND_PICKAXE(), VanillaItems::DIAMOND())
		];

		$diamondPickaxeQuarter = (int) floor(VanillaItems::DIAMOND_PICKAXE()->getMaxDurability() / 4);
		yield "Repair one quarter" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage($diamondPickaxeQuarter),
			VanillaItems::DIAMOND()->setCount(1),
			new AnvilCraftResult(1, VanillaItems::DIAMOND_PICKAXE(), null)
		];

		yield "Repair one quarter plus 1" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage($diamondPickaxeQuarter + 1),
			VanillaItems::DIAMOND()->setCount(1),
			new AnvilCraftResult(1, VanillaItems::DIAMOND_PICKAXE()->setDamage(1), null)
		];

		yield "Repair more than one quarter" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage($diamondPickaxeQuarter * 2),
			VanillaItems::DIAMOND()->setCount(2),
			new AnvilCraftResult(2, VanillaItems::DIAMOND_PICKAXE(), null)
		];

		yield "Repair more than one quarter with more materials than expected" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage($diamondPickaxeQuarter * 2),
			VanillaItems::DIAMOND()->setCount(3),
			new AnvilCraftResult(2, VanillaItems::DIAMOND_PICKAXE(), VanillaItems::DIAMOND()->setCount(1))
		];
	}

	/**
	 * @dataProvider materialRepairRecipeProvider
	 */
	public function testMaterialRepairRecipe(Item $base, Item $material, ?AnvilCraftResult $expected) : void{
		$recipe = new MaterialRepairRecipe(
			new WildcardRecipeIngredient(),
			new WildcardRecipeIngredient()
		);
		self::assertAnvilCraftResultEquals($expected, $recipe->getResultFor($base, $material));
	}

	public static function itemSelfCombineRecipeProvider() : Generator{
		yield "Combine two identical items without damage and enchants" => [
			VanillaItems::DIAMOND_PICKAXE(),
			VanillaItems::DIAMOND_PICKAXE(),
			null
		];

		yield "Enchant on base item and no enchants on material with no damage" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
			VanillaItems::DIAMOND_PICKAXE(),
			null
		];

		yield "No enchant on base item and one enchant on material" => [
			VanillaItems::DIAMOND_PICKAXE(),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
			new AnvilCraftResult(2, VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)), null)
		];

		yield "Combine two identical items with damage" => [
			VanillaItems::DIAMOND_PICKAXE()->setDamage(10),
			VanillaItems::DIAMOND_PICKAXE(),
			new AnvilCraftResult(1, VanillaItems::DIAMOND_PICKAXE()->setDamage(0), null)
		];

		yield "Combine two identical items with damage for material" => [
			VanillaItems::DIAMOND_PICKAXE(),
			VanillaItems::DIAMOND_PICKAXE()->setDamage(10),
			null
		];

		yield "Combine two identical items with different enchantments" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
			new AnvilCraftResult(2, VanillaItems::DIAMOND_PICKAXE()
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
				null)
		];

		yield "Combine two identical items with different enchantments with damage" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))->setDamage(10),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
			new AnvilCraftResult(4, VanillaItems::DIAMOND_PICKAXE()
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
				null)
		];

		yield "Combine two identical items with different enchantments with damage for material" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))->setDamage(10),
			new AnvilCraftResult(2, VanillaItems::DIAMOND_PICKAXE()
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
				null)
		];

		yield "Combine two identical items with same enchantment level" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1)),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1)),
			new AnvilCraftResult(8, VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 2)), null)
		];

		yield "Combine two identical items with same enchantment level and damage" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1))->setDamage(10),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1)),
			new AnvilCraftResult(10, VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 2))->setDamage(0), null)
		];

		yield "Combine two identical items with same enchantment level and damage for material" => [
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1)),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 1))->setDamage(10),
			new AnvilCraftResult(8, VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 2)), null)
		];
	}

	/**
	 * @dataProvider itemSelfCombineRecipeProvider
	 */
	public function testItemSelfCombineRecipe(Item $base, Item $combined, ?AnvilCraftResult $expected) : void{
		$recipe = new ItemSelfCombineRecipe(new WildcardRecipeIngredient());
		self::assertAnvilCraftResultEquals($expected, $recipe->getResultFor($base, $combined));
	}

	private static function assertAnvilCraftResultEquals(?AnvilCraftResult $expected, ?AnvilCraftResult $actual) : void{
		if($expected === null){
			self::assertNull($actual, "Recipe did not match expected result");
			return;
		}else{
			self::assertNotNull($actual, "Recipe did not match expected result");
		}
		self::assertEquals($expected->getXpCost(), $actual->getXpCost(), "XP cost did not match expected result");
		self::assertTrue($expected->getOutput()->equalsExact($actual->getOutput()), "Recipe output did not match expected result");
		$sacrificeResult = $expected->getSacrificeResult();
		if($sacrificeResult !== null){
			$resultExpected = $actual->getSacrificeResult();
			self::assertNotNull($resultExpected, "Recipe sacrifice result did not match expected result");
			self::assertTrue($sacrificeResult->equalsExact($resultExpected), "Recipe sacrifice result did not match expected result");
		}else{
			self::assertNull($actual->getSacrificeResult(), "Recipe sacrifice result did not match expected result");
		}
	}
}
