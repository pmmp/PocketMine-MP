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

namespace pocketmine\inventory;

use pocketmine\block\Planks;
use pocketmine\block\Stone;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\item\Item;
use pocketmine\utils\UUID;

class CraftingManager{

	/** @var Recipe[] */
	public $recipes = [];

	/** @var Recipe[][] */
	protected $recipeLookup = [];

	/** @var FurnaceRecipe[] */
	public $furnaceRecipes = [];

	private static $RECIPE_COUNT = 0;

	public function __construct(){

		$this->registerStonecutter();
		$this->registerFurnace();


		$this->registerDyes();
		$this->registerIngots();
		$this->registerTools();
		$this->registerWeapons();
		$this->registerArmor();
		$this->registerFood();

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::CLAY_BLOCK, 0, 1)))->addIngredient(Item::get(Item::CLAY, 0, 4)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WORKBENCH, 0, 1),
			"XX",
			"XX"
		))->setIngredient("X", Item::get(Item::WOODEN_PLANK, null)));

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::GLOWSTONE_BLOCK, 0, 1)))->addIngredient(Item::get(Item::GLOWSTONE_DUST, 0, 4)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::LIT_PUMPKIN, 0, 1)))->addIngredient(Item::get(Item::PUMPKIN, 0, 1))->addIngredient(Item::get(Item::TORCH, 0, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::SNOW_BLOCK, 0, 1),
			"XX",
			"XX"
		))->setIngredient("X", Item::get(Item::SNOWBALL)));

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::SNOW_LAYER, 0, 6)))->addIngredient(Item::get(Item::SNOW_BLOCK, 0, 3)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::STICK, 0, 4),
			"X ",
			"X "
		))->setIngredient("X", Item::get(Item::WOODEN_PLANK, null)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::STONECUTTER, 0, 1),
			"XX",
			"XX"
		))->setIngredient("X", Item::get(Item::COBBLESTONE)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::OAK, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD, Wood::OAK, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD, Wood::SPRUCE, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD, Wood::BIRCH, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD, Wood::JUNGLE, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD2, Wood2::ACACIA, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 4),
			"X"
		))->setIngredient("X", Item::get(Item::WOOD2, Wood2::DARK_OAK, 1)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::WOOL, 0, 1),
			"XX",
			"XX"
		))->setIngredient("X", Item::get(Item::STRING, 0, 4)));

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::TORCH, 0, 4)))->addIngredient(Item::get(Item::COAL, 0, 1))->addIngredient(Item::get(Item::STICK, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::TORCH, 0, 4)))->addIngredient(Item::get(Item::COAL, 1, 1))->addIngredient(Item::get(Item::STICK, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::SUGAR, 0, 1)))->addIngredient(Item::get(Item::SUGARCANE, 0, 1)));


		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BED, 0, 1)))->addIngredient(Item::get(Item::WOOL, null, 3))->addIngredient(Item::get(Item::WOODEN_PLANK, null, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::CHEST, 0, 1)))->addIngredient(Item::get(Item::WOODEN_PLANK, null, 8)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, 0, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::OAK, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, Planks::SPRUCE, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, Planks::BIRCH, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, Planks::JUNGLE, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, Planks::ACACIA, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE, Planks::DARK_OAK, 3)))->addIngredient(Item::get(Item::STICK, 0, 2))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::OAK, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE_SPRUCE, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE_BIRCH, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE_JUNGLE, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE_DARK_OAK, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FENCE_GATE_ACACIA, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 4))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 2)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::FURNACE, 0, 1)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 8)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::GLASS_PANE, 0, 16)))->addIngredient(Item::get(Item::GLASS, 0, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::LADDER, 0, 2)))->addIngredient(Item::get(Item::STICK, 0, 7)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::NETHER_REACTOR, 0, 1)))->addIngredient(Item::get(Item::DIAMOND, 0, 3))->addIngredient(Item::get(Item::IRON_INGOT, 0, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::TRAPDOOR, 0, 2)))->addIngredient(Item::get(Item::WOODEN_PLANK, null, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOODEN_DOOR, 0, 1)))->addIngredient(Item::get(Item::WOODEN_PLANK, null, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOODEN_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::OAK, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::OAK, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::OAK, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::SPRUCE_WOOD_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::SPRUCE, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::SPRUCE, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BIRCH_WOOD_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::BIRCH, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::BIRCH, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::JUNGLE_WOOD_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::JUNGLE, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::JUNGLE, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::ACACIA_WOOD_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::ACACIA, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::ACACIA, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::DARK_OAK_WOOD_STAIRS, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 6)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::WOOD_SLAB, Planks::DARK_OAK, 6)))->addIngredient(Item::get(Item::WOODEN_PLANK, Planks::DARK_OAK, 3)));

		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BUCKET, 0, 1)))->addIngredient(Item::get(Item::IRON_INGOT, 0, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::CLOCK, 0, 1)))->addIngredient(Item::get(Item::GOLD_INGOT, 0, 4))->addIngredient(Item::get(Item::REDSTONE_DUST, 0, 1)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::COMPASS, 0, 1)))->addIngredient(Item::get(Item::IRON_INGOT, 0, 4))->addIngredient(Item::get(Item::REDSTONE_DUST, 0, 1)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::TNT, 0, 1)))->addIngredient(Item::get(Item::GUNPOWDER, 0, 5))->addIngredient(Item::get(Item::SAND, null, 4)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BOWL, 0, 4)))->addIngredient(Item::get(Item::WOODEN_PLANKS, null, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::MINECART, 0, 1)))->addIngredient(Item::get(Item::IRON_INGOT, 0, 5)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BOOK, 0, 1)))->addIngredient(Item::get(Item::PAPER, 0, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BOOKSHELF, 0, 1)))->addIngredient(Item::get(Item::WOODEN_PLANK, null, 6))->addIngredient(Item::get(Item::BOOK, 0, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::PAINTING, 0, 1)))->addIngredient(Item::get(Item::STICK, 0, 8))->addIngredient(Item::get(Item::WOOL, null, 1)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::PAPER, 0, 1)))->addIngredient(Item::get(Item::SUGARCANE, 0, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::SIGN, 0, 3)))->addIngredient(Item::get(Item::STICK, 0, 1))->addIngredient(Item::get(Item::WOODEN_PLANKS, null, 6))); //TODO: check if it gives one sign or three
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::IRON_BARS, 0, 16)))->addIngredient(Item::get(Item::IRON_INGOT, 0, 6)));
	}

	protected function registerFurnace(){
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::STONE, 0, 1), Item::get(Item::COBBLESTONE, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::STONE_BRICK, 2, 1), Item::get(Item::STONE_BRICK, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::GLASS, 0, 1), Item::get(Item::SAND, null, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::COAL, 1, 1), Item::get(Item::TRUNK, null, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::GOLD_INGOT, 0, 1), Item::get(Item::GOLD_ORE, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::IRON_INGOT, 0, 1), Item::get(Item::IRON_ORE, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::EMERALD, 0, 1), Item::get(Item::EMERALD_ORE, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::DIAMOND, 0, 1), Item::get(Item::DIAMOND_ORE, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::NETHER_BRICK, 0, 1), Item::get(Item::NETHERRACK, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::COOKED_PORKCHOP, 0, 1), Item::get(Item::RAW_PORKCHOP, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::BRICK, 0, 1), Item::get(Item::CLAY, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::COOKED_FISH, 0, 1), Item::get(Item::RAW_FISH, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::COOKED_FISH, 1, 1), Item::get(Item::RAW_FISH, 1, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::DYE, 2, 1), Item::get(Item::CACTUS, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::DYE, 1, 1), Item::get(Item::RED_MUSHROOM, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::STEAK, 0, 1), Item::get(Item::RAW_BEEF, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::COOKED_CHICKEN, 0, 1), Item::get(Item::RAW_CHICKEN, 0, 1)));
		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::BAKED_POTATO, 0, 1), Item::get(Item::POTATO, 0, 1)));

		$this->registerRecipe(new FurnaceRecipe(Item::get(Item::HARDENED_CLAY, 0, 1), Item::get(Item::CLAY_BLOCK, 0, 1)));
	}

	protected function registerStonecutter(){
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::QUARTZ_BLOCK, 0, 1)))->addIngredient(Item::get(Item::QUARTZ, 0, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::BRICK_STAIRS, 0, 4)))->addIngredient(Item::get(Item::BRICKS_BLOCK, 0, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::BRICKS_BLOCK, 0, 1)))->addIngredient(Item::get(Item::BRICK, 0, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 4, 6)))->addIngredient(Item::get(Item::BRICKS_BLOCK, 0, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::QUARTZ_BLOCK, 1, 1)))->addIngredient(Item::get(Item::SLAB, 6, 2)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 3, 6)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::COBBLESTONE_WALL, 0, 6)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::COBBLESTONE_WALL, 1, 6)))->addIngredient(Item::get(Item::MOSS_STONE, 0, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::NETHER_BRICKS, 0, 1)))->addIngredient(Item::get(Item::NETHER_BRICK, 0, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::NETHER_BRICKS_STAIRS, 0, 4)))->addIngredient(Item::get(Item::NETHER_BRICKS, 0, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::QUARTZ_BLOCK, 2, 2)))->addIngredient(Item::get(Item::QUARTZ_BLOCK, 0, 2)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 6, 6)))->addIngredient(Item::get(Item::QUARTZ_BLOCK, 0, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SANDSTONE_STAIRS, 0, 4)))->addIngredient(Item::get(Item::SANDSTONE, 0, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SANDSTONE, 0, 1)))->addIngredient(Item::get(Item::SAND, 0, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SANDSTONE, 2, 4)))->addIngredient(Item::get(Item::SANDSTONE, 0, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SANDSTONE, 1, 1)))->addIngredient(Item::get(Item::SLAB, 1, 2)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 1, 6)))->addIngredient(Item::get(Item::SANDSTONE, 0, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE_BRICK_STAIRS, 0, 4)))->addIngredient(Item::get(Item::STONE_BRICK, null, 6)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE_BRICK, 0, 4)))->addIngredient(Item::get(Item::STONE, null, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE_BRICK, 3, 1)))->addIngredient(Item::get(Item::SLAB, 5, 2)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE_BRICK, 1, 1)))->addIngredient(Item::get(Item::STONE_BRICK, 0, 1))->addIngredient(Item::get(Item::VINES, 0, 1)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 5, 6)))->addIngredient(Item::get(Item::STONE_BRICK, null, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::SLAB, 0, 6)))->addIngredient(Item::get(Item::STONE, null, 3)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::COBBLESTONE_STAIRS, 0, 4)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 6)));

		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::POLISHED_GRANITE, 4)))->addIngredient(Item::get(Item::STONE, Stone::GRANITE, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::POLISHED_DIORITE, 4)))->addIngredient(Item::get(Item::STONE, Stone::DIORITE, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::POLISHED_ANDESITE, 4)))->addIngredient(Item::get(Item::STONE, Stone::ANDESITE, 4)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::GRANITE, 1)))->addIngredient(Item::get(Item::STONE, Stone::DIORITE, 1))->addIngredient(Item::get(Item::QUARTZ, 0, 1)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::DIORITE, 2)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 2))->addIngredient(Item::get(Item::QUARTZ, 0, 2)));
		$this->registerRecipe((new StonecutterShapelessRecipe(Item::get(Item::STONE, Stone::ANDESITE, 2)))->addIngredient(Item::get(Item::COBBLESTONE, 0, 1))->addIngredient(Item::get(Item::STONE, Stone::DIORITE, 1)));
	}

	protected function registerFood(){
		//TODO: check COOKIES
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::MELON_SEEDS, 0, 1)))->addIngredient(Item::get(Item::MELON_SLICE, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::PUMPKIN_SEEDS, 0, 4)))->addIngredient(Item::get(Item::PUMPKIN, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::PUMPKIN_PIE, 0, 1)))->addIngredient(Item::get(Item::PUMPKIN, 0, 1))->addIngredient(Item::get(Item::EGG, 0, 1))->addIngredient(Item::get(Item::SUGAR, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::MUSHROOM_STEW, 0, 1)))->addIngredient(Item::get(Item::BOWL, 0, 1))->addIngredient(Item::get(Item::BROWN_MUSHROOM, 0, 1))->addIngredient(Item::get(Item::RED_MUSHROOM, 0, 1)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::MELON_BLOCK, 0, 1)))->addIngredient(Item::get(Item::MELON_SLICE, 0, 9)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::BEETROOT_SOUP, 0, 1)))->addIngredient(Item::get(Item::BEETROOT, 0, 4))->addIngredient(Item::get(Item::BOWL, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::BREAD, 0, 1)))->addIngredient(Item::get(Item::WHEAT, 0, 3)));
		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::CAKE, 0, 1)))->addIngredient(Item::get(Item::WHEAT, 0, 3))->addIngredient(Item::get(Item::BUCKET, 1, 3))->addIngredient(Item::get(Item::EGG, 0, 1))->addIngredient(Item::get(Item::SUGAR, 0, 2)));
	}

	protected function registerArmor(){
		$types = [
			[Item::LEATHER, Item::FIRE, Item::IRON_INGOT, Item::DIAMOND, Item::GOLD_INGOT],
			[Item::LEATHER_CAP, Item::CHAIN_HELMET, Item::IRON_HELMET, Item::DIAMOND_HELMET, Item::GOLD_HELMET],
			[Item::LEATHER_TUNIC, Item::CHAIN_CHESTPLATE, Item::IRON_CHESTPLATE, Item::DIAMOND_CHESTPLATE, Item::GOLD_CHESTPLATE],
			[Item::LEATHER_PANTS, Item::CHAIN_LEGGINGS, Item::IRON_LEGGINGS, Item::DIAMOND_LEGGINGS, Item::GOLD_LEGGINGS],
			[Item::LEATHER_BOOTS, Item::CHAIN_BOOTS, Item::IRON_BOOTS, Item::DIAMOND_BOOTS, Item::GOLD_BOOTS],
		];

		$shapes = [
			[
				"XXX",
				"X X",
				"   "
			],
			[
				"X X",
				"XXX",
				"XXX"
			],
			[
				"XXX",
				"X X",
				"X X"
			],
			[
				"   ",
				"X X",
				"X X"
			]
		];

		for($i = 1; $i < 5; ++$i){
			foreach($types[$i] as $j => $type){
				$this->registerRecipe((new BigShapedRecipe(Item::get($type, 0, 1), ...$shapes[$i - 1]))->setIngredient("X", Item::get($types[0][$j], 0, 1)));
			}
		}
	}

	protected function registerWeapons(){
		$types = [
			[Item::WOODEN_PLANK, Item::COBBLESTONE, Item::IRON_INGOT, Item::DIAMOND, Item::GOLD_INGOT],
			[Item::WOODEN_SWORD, Item::STONE_SWORD, Item::IRON_SWORD, Item::DIAMOND_SWORD, Item::GOLD_SWORD],
		];


		for($i = 1; $i < 2; ++$i){
			foreach($types[$i] as $j => $type){
				$this->registerRecipe((new BigShapedRecipe(Item::get($type, 0, 1),
					" X ",
					" X ",
					" I "
				))->setIngredient("X", Item::get($types[0][$j], null))->setIngredient("I", Item::get(Item::STICK)));
			}
		}

		$this->registerRecipe((new BigShapedRecipe(Item::get(Item::ARROW, 0, 1),
			" F ",
			" S ",
			" P "
		))->setIngredient("S", Item::get(Item::STICK))->setIngredient("F", Item::get(Item::FLINT))->setIngredient("P", Item::get(Item::FEATHER)));

		$this->registerRecipe((new BigShapedRecipe(Item::get(Item::BOW, 0, 1),
			" X~",
			"X ~",
			" X~"
		))->setIngredient("~", Item::get(Item::STRING))->setIngredient("X", Item::get(Item::STICK)));
	}

	protected function registerTools(){
		$types = [
			[Item::WOODEN_PLANK, Item::COBBLESTONE, Item::IRON_INGOT, Item::DIAMOND, Item::GOLD_INGOT],
			[Item::WOODEN_PICKAXE, Item::STONE_PICKAXE, Item::IRON_PICKAXE, Item::DIAMOND_PICKAXE, Item::GOLD_PICKAXE],
			[Item::WOODEN_SHOVEL, Item::STONE_SHOVEL, Item::IRON_SHOVEL, Item::DIAMOND_SHOVEL, Item::GOLD_SHOVEL],
			[Item::WOODEN_AXE, Item::STONE_AXE, Item::IRON_AXE, Item::DIAMOND_AXE, Item::GOLD_AXE],
			[Item::WOODEN_HOE, Item::STONE_HOE, Item::IRON_HOE, Item::DIAMOND_HOE, Item::GOLD_HOE],
		];
		$shapes = [
			[
				"XXX",
				" I ",
				" I "
			],
			[
				" X ",
				" I ",
				" I "
			],
			[
				"XX ",
				"XI ",
				" I "
			],
			[
				"XX ",
				" I ",
				" I "
			]
		];

		for($i = 1; $i < 5; ++$i){
			foreach($types[$i] as $j => $type){
				$this->registerRecipe((new BigShapedRecipe(Item::get($type, 0, 1), ...$shapes[$i - 1]))->setIngredient("X", Item::get($types[0][$j], null))->setIngredient("I", Item::get(Item::STICK)));
			}
		}

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::FLINT_AND_STEEL, 0, 1),
			" S",
			"F "
		))->setIngredient("F", Item::get(Item::FLINT))->setIngredient("S", Item::get(Item::IRON_INGOT)));

		$this->registerRecipe((new ShapedRecipe(Item::get(Item::SHEARS, 0, 1),
			" X",
			"X "
		))->setIngredient("X", Item::get(Item::IRON_INGOT)));
	}

	protected function registerDyes(){
		for($i = 0; $i < 16; ++$i){
			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOOL, 15 - $i, 1)))->addIngredient(Item::get(Item::DYE, $i, 1))->addIngredient(Item::get(Item::WOOL, 0, 1)));
			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::STAINED_CLAY, 15 - $i, 8)))->addIngredient(Item::get(Item::DYE, $i, 1))->addIngredient(Item::get(Item::HARDENED_CLAY, 0, 8)));
			//TODO: add glass things?
			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOOL, 15 - $i, 1)))->addIngredient(Item::get(Item::DYE, $i, 1))->addIngredient(Item::get(Item::WOOL, 0, 1)));
			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOOL, 15 - $i, 1)))->addIngredient(Item::get(Item::DYE, $i, 1))->addIngredient(Item::get(Item::WOOL, 0, 1)));
			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::WOOL, 15 - $i, 1)))->addIngredient(Item::get(Item::DYE, $i, 1))->addIngredient(Item::get(Item::WOOL, 0, 1)));

			$this->registerRecipe((new ShapelessRecipe(Item::get(Item::CARPET, $i, 3)))->addIngredient(Item::get(Item::WOOL, $i, 2)));
		}

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 11, 2)))->addIngredient(Item::get(Item::DANDELION, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 15, 3)))->addIngredient(Item::get(Item::BONE, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 3, 2)))->addIngredient(Item::get(Item::DYE, 14, 1))->addIngredient(Item::get(Item::DYE, 0, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 3, 3)))->addIngredient(Item::get(Item::DYE, 1, 1))->addIngredient(Item::get(Item::DYE, 0, 1))->addIngredient(Item::get(Item::DYE, 11, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 9, 2)))->addIngredient(Item::get(Item::DYE, 15, 1))->addIngredient(Item::get(Item::DYE, 1, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 14, 2)))->addIngredient(Item::get(Item::DYE, 11, 1))->addIngredient(Item::get(Item::DYE, 1, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 10, 2)))->addIngredient(Item::get(Item::DYE, 2, 1))->addIngredient(Item::get(Item::DYE, 15, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 12, 2)))->addIngredient(Item::get(Item::DYE, 4, 1))->addIngredient(Item::get(Item::DYE, 15, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 6, 2)))->addIngredient(Item::get(Item::DYE, 4, 1))->addIngredient(Item::get(Item::DYE, 2, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 5, 2)))->addIngredient(Item::get(Item::DYE, 4, 1))->addIngredient(Item::get(Item::DYE, 1, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 13, 3)))->addIngredient(Item::get(Item::DYE, 4, 1))->addIngredient(Item::get(Item::DYE, 1, 1))->addIngredient(Item::get(Item::DYE, 15, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 1, 1)))->addIngredient(Item::get(Item::BEETROOT, 0, 1)));

		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 13, 4)))->addIngredient(Item::get(Item::DYE, 15, 1))->addIngredient(Item::get(Item::DYE, 1, 2))->addIngredient(Item::get(Item::DYE, 4, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 13, 2)))->addIngredient(Item::get(Item::DYE, 5, 1))->addIngredient(Item::get(Item::DYE, 9, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 8, 2)))->addIngredient(Item::get(Item::DYE, 0, 1))->addIngredient(Item::get(Item::DYE, 15, 1)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 7, 3)))->addIngredient(Item::get(Item::DYE, 0, 1))->addIngredient(Item::get(Item::DYE, 15, 2)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 7, 2)))->addIngredient(Item::get(Item::DYE, 0, 1))->addIngredient(Item::get(Item::DYE, 8, 1)));

	}

	protected function registerIngots(){
		$ingots = [
			Item::GOLD_BLOCK => Item::GOLD_INGOT,
			Item::IRON_BLOCK => Item::IRON_INGOT,
			Item::DIAMOND_BLOCK => Item::DIAMOND,
			Item::EMERALD_BLOCK => Item::EMERALD,
			Item::REDSTONE_BLOCK => Item::REDSTONE_DUST,
			Item::COAL_BLOCK => Item::COAL,
			Item::HAY_BALE => Item::WHEAT,
		];

		foreach($ingots as $block => $ingot){
			$this->registerRecipe((new BigShapelessRecipe(Item::get($block, 0, 1)))->addIngredient(Item::get($ingot, 0, 9)));
			$this->registerRecipe((new ShapelessRecipe(Item::get($ingot, 0, 9)))->addIngredient(Item::get($block, 0, 1)));
		}


		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::LAPIS_BLOCK, 0, 1)))->addIngredient(Item::get(Item::DYE, 4, 9)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::DYE, 4, 9)))->addIngredient(Item::get(Item::LAPIS_BLOCK, 0, 1)));

		$this->registerRecipe((new BigShapelessRecipe(Item::get(Item::GOLD_INGOT, 0, 1)))->addIngredient(Item::get(Item::GOLD_NUGGET, 0, 9)));
		$this->registerRecipe((new ShapelessRecipe(Item::get(Item::GOLD_NUGGET, 0, 9)))->addIngredient(Item::get(Item::GOLD_INGOT, 0, 1)));

	}

	public function sort(Item $i1, Item $i2){
		if($i1->getId() > $i2->getId()){
			return 1;
		}elseif($i1->getId() < $i2->getId()){
			return -1;
		}elseif($i1->getDamage() > $i2->getDamage()){
			return 1;
		}elseif($i1->getDamage() < $i2->getDamage()){
			return -1;
		}elseif($i1->getCount() > $i2->getCount()){
			return 1;
		}elseif($i1->getCount() < $i2->getCount()){
			return -1;
		}else{
			return 0;
		}
	}

	/**
	 * @param UUID $id
	 * @return Recipe
	 */
	public function getRecipe(UUID $id){
		$index = $id->toBinary();
		return isset($this->recipes[$index]) ? $this->recipes[$index] : null;
	}

	/**
	 * @return Recipe[]
	 */
	public function getRecipes(){
		return $this->recipes;
	}

	/**
	 * @return FurnaceRecipe[]
	 */
	public function getFurnaceRecipes(){
		return $this->furnaceRecipes;
	}

	/**
	 * @param Item $input
	 *
	 * @return FurnaceRecipe
	 */
	public function matchFurnaceRecipe(Item $input){
		if(isset($this->furnaceRecipes[$input->getId() . ":" . $input->getDamage()])){
			return $this->furnaceRecipes[$input->getId() . ":" . $input->getDamage()];
		}elseif(isset($this->furnaceRecipes[$input->getId() . ":?"])){
			return $this->furnaceRecipes[$input->getId() . ":?"];
		}

		return null;
	}

	/**
	 * @param ShapedRecipe $recipe
	 */
	public function registerShapedRecipe(ShapedRecipe $recipe){
		$result = $recipe->getResult();
		$this->recipes[$recipe->getId()->toBinary()] = $recipe;
		$ingredients = $recipe->getIngredientMap();
		$hash = "";
		foreach($ingredients as $v){
			foreach($v as $item){
				if($item !== null){
					/** @var Item $item */
					$hash .= $item->getId() . ":" . ($item->getDamage() === null ? "?" : $item->getDamage()) . "x" . $item->getCount() . ",";
				}
			}

			$hash .= ";";
		}

		$this->recipeLookup[$result->getId() . ":" . $result->getDamage()][$hash] = $recipe;
	}

	/**
	 * @param ShapelessRecipe $recipe
	 */
	public function registerShapelessRecipe(ShapelessRecipe $recipe){
		$result = $recipe->getResult();
		$this->recipes[$recipe->getId()->toBinary()] = $recipe;
		$hash = "";
		$ingredients = $recipe->getIngredientList();
		usort($ingredients, [$this, "sort"]);
		foreach($ingredients as $item){
			$hash .= $item->getId() . ":" . ($item->getDamage() === null ? "?" : $item->getDamage()) . "x" . $item->getCount() . ",";
		}
		$this->recipeLookup[$result->getId() . ":" . $result->getDamage()][$hash] = $recipe;
	}

	/**
	 * @param FurnaceRecipe $recipe
	 */
	public function registerFurnaceRecipe(FurnaceRecipe $recipe){
		$input = $recipe->getInput();
		$this->furnaceRecipes[$input->getId() . ":" . ($input->getDamage() === null ? "?" : $input->getDamage())] = $recipe;
	}

	/**
	 * @param ShapelessRecipe $recipe
	 * @return bool
	 */
	public function matchRecipe(ShapelessRecipe $recipe){
		if(!isset($this->recipeLookup[$idx = $recipe->getResult()->getId() . ":" . $recipe->getResult()->getDamage()])){
			return false;
		}

		$hash = "";
		$ingredients = $recipe->getIngredientList();
		usort($ingredients, [$this, "sort"]);
		foreach($ingredients as $item){
			$hash .= $item->getId() . ":" . ($item->getDamage() === null ? "?" : $item->getDamage()) . "x" . $item->getCount() . ",";
		}

		if(isset($this->recipeLookup[$idx][$hash])){
			return true;
		}

		$hasRecipe = null;
		foreach($this->recipeLookup[$idx] as $recipe){
			if($recipe instanceof ShapelessRecipe){
				if($recipe->getIngredientCount() !== count($ingredients)){
					continue;
				}
				$checkInput = $recipe->getIngredientList();
				foreach($ingredients as $item){
					$amount = $item->getCount();
					foreach($checkInput as $k => $checkItem){
						if($checkItem->equals($item, $checkItem->getDamage() === null ? false : true, $checkItem->getCompoundTag() === null ? false : true)){
							$remove = min($checkItem->getCount(), $amount);
							$checkItem->setCount($checkItem->getCount() - $remove);
							if($checkItem->getCount() === 0){
								unset($checkInput[$k]);
							}
							$amount -= $remove;
							if($amount === 0){
								break;
							}
						}
					}
				}

				if(count($checkInput) === 0){
					$hasRecipe = $recipe;
					break;
				}
			}
			if($hasRecipe instanceof Recipe){
				break;
			}
		}

		return $hasRecipe !== null;

	}

	/**
	 * @param Recipe $recipe
	 */
	public function registerRecipe(Recipe $recipe){
		$recipe->setId(UUID::fromData(++self::$RECIPE_COUNT, $recipe->getResult()->getId(), $recipe->getResult()->getDamage(), $recipe->getResult()->getCount(), $recipe->getResult()->getCompoundTag()));

		if($recipe instanceof ShapedRecipe){
			$this->registerShapedRecipe($recipe);
		}elseif($recipe instanceof ShapelessRecipe){
			$this->registerShapelessRecipe($recipe);
		}elseif($recipe instanceof FurnaceRecipe){
			$this->registerFurnaceRecipe($recipe);
		}
	}

}
