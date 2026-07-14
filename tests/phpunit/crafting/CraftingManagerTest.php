<?php

namespace pocketmine\crafting;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use pocketmine\item\VanillaItems;

class CraftingManagerTest extends TestCase{
	public static function shapelessRecipeProvider() : \Generator {
		$recipe1 = new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING);

		yield "Basic shapelessRecipe matching" => [[$recipe1], $recipe1, 0];

		yield "ShapelessRecipe not found" => [[$recipe1], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::IRON_INGOT())
		], [
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 1];

		yield "Unordered same ingredients" => [[$recipe1], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND())
		], [
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 0];

		yield "ShapelessRecipe with different ingredient count" => [[$recipe1], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 1];

		yield "ShapelessRecipe with different ingredient" => [[$recipe1], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::IRON_INGOT())
		], [
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 1];

		$recipeMultiResult = new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::EMERALD(),
			VanillaItems::IRON_INGOT()
		], ShapelessRecipeType::CRAFTING);

		yield "ShapelessRecipe with multiple results" => [[$recipeMultiResult], $recipeMultiResult, 0];

		yield "ShapelessRecipe with different result" => [[$recipeMultiResult], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::EMERALD(),
			VanillaItems::GOLD_INGOT()
		], ShapelessRecipeType::CRAFTING), 1];

		yield "ShapelessRecipe with different count of same result" => [[$recipeMultiResult], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::EMERALD(),
			VanillaItems::IRON_INGOT()->setCount(2)
		], ShapelessRecipeType::CRAFTING), 1];

		yield "ShapelessRecipe with different result order" => [[$recipeMultiResult], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT())
		], [
			VanillaItems::IRON_INGOT(),
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 0];

		yield "ShapelessRecipe with different result and ingredient order" => [[$recipeMultiResult], new ShapelessRecipe([
			new ExactRecipeIngredient(VanillaItems::GOLD_INGOT()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND())
		], [
			VanillaItems::IRON_INGOT(),
			VanillaItems::EMERALD()
		], ShapelessRecipeType::CRAFTING), 0];
	}

	/**
	 * @param ShapelessRecipe[] $recipes
	 */
	#[DataProvider('shapelessRecipeProvider')]
	public function testUnregisterShapelessRecipe(array $recipes, ShapelessRecipe $toRemove, int $expectedCount) : void {
		$manager = new CraftingManager();
		foreach($recipes as $recipe){
			$manager->registerShapelessRecipe($recipe);
		}

		self::assertCount(count($recipes), $manager->getShapelessRecipes());
		$manager->unregisterShapelessRecipe($toRemove);
		self::assertCount($expectedCount, $manager->getShapelessRecipes(), "Failed to unregister shapeless recipe");
	}
}