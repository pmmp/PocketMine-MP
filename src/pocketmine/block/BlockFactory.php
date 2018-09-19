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

namespace pocketmine\block;

use pocketmine\block\utils\Color;
use pocketmine\block\utils\WoodType;
use pocketmine\item\Item;
use pocketmine\level\Position;

/**
 * Manages block registration and instance creation
 */
class BlockFactory{
	/** @var \SplFixedArray<Block> */
	private static $fullList = null;
	/** @var \SplFixedArray|\Closure[] */
	private static $getInterceptors = null;

	/** @var \SplFixedArray<bool> */
	public static $solid = null;
	/** @var \SplFixedArray<int> */
	public static $lightFilter = null;
	/** @var \SplFixedArray<bool> */
	public static $diffusesSkyLight = null;
	/** @var \SplFixedArray<float> */
	public static $blastResistance = null;

	/** @var \SplFixedArray|int[] */
	public static $stateMasks = null;

	/** @var int[] */
	public static $staticRuntimeIdMap = [];

	/** @var int[] */
	public static $legacyIdMap = [];

	/** @var int */
	private static $lastRuntimeId = 0;

	/**
	 * Initializes the block factory. By default this is called only once on server start, however you may wish to use
	 * this if you need to reset the block factory back to its original defaults for whatever reason.
	 */
	public static function init() : void{
		self::$fullList = new \SplFixedArray(4096);
		self::$getInterceptors = new \SplFixedArray(4096);

		self::$lightFilter = \SplFixedArray::fromArray(array_fill(0, 256, 1));
		self::$solid = \SplFixedArray::fromArray(array_fill(0, 256, false));
		self::$diffusesSkyLight = \SplFixedArray::fromArray(array_fill(0, 256, false));
		self::$blastResistance = \SplFixedArray::fromArray(array_fill(0, 256, 0));

		self::$stateMasks = new \SplFixedArray(256);

		self::registerBlock(new Air());

		self::setStateMask(Block::STONE, 0);
		//TODO: give smooth stone its own class (different drops)
		self::registerBlock(new Stone(Block::STONE, Stone::NORMAL, "Stone"));
		self::registerBlock(new Stone(Block::STONE, Stone::GRANITE, "Granite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_GRANITE, "Polished Granite"));
		self::registerBlock(new Stone(Block::STONE, Stone::DIORITE, "Diorite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_DIORITE, "Polished Diorite"));
		self::registerBlock(new Stone(Block::STONE, Stone::ANDESITE, "Andesite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_ANDESITE, "Polished Andesite"));

		self::registerBlock(new Grass());

		self::setStateMask(Block::DIRT, 0);
		//TODO: split these into separate classes
		self::registerBlock(new Dirt(Block::DIRT, Dirt::NORMAL, "Dirt"));
		self::registerBlock(new Dirt(Block::DIRT, Dirt::COARSE, "Coarse Dirt"));

		self::registerBlock(new Cobblestone());

		self::setStateMask(Block::PLANKS, 0);
		self::setStateMask(Block::SAPLING, 0x8); //ready bitflag
		self::setStateMask(Block::FENCE, 0);
		foreach(WoodType::ALL as $type){
			self::registerBlock(new Planks(Block::PLANKS, $type, WoodType::NAMES[$type] . " Planks"));
			self::registerBlock(new Sapling(Block::SAPLING, $type, WoodType::NAMES[$type] . " Sapling"));
			self::registerBlock(new WoodenFence(Block::FENCE, $type, WoodType::NAMES[$type] . " Fence"));
		}

		self::setStateMask(Block::LOG, 0xc); //axis
		self::setStateMask(Block::LOG2, 0xc);
		self::setStateMask(Block::LEAVES, 0xc); //checkdecay/nodecay
		self::setStateMask(Block::LEAVES2, 0xc);
		foreach(WoodType::ALL as $type){
			//TODO: find a better way to deal with this split
			self::registerBlock(new Wood($type >= 4 ? Block::WOOD2 : Block::WOOD, $type & 0x03, WoodType::NAMES[$type] . " Wood"));
			self::registerBlock(new Leaves($type >= 4 ? Block::LEAVES2 : Block::LEAVES, $type & 0x03, $type, WoodType::NAMES[$type] . " Leaves"));
		}

		self::registerBlock(new Bedrock());
		self::registerBlock(new Water());
		self::registerBlock(new StillWater());
		self::registerBlock(new Lava());
		self::registerBlock(new StillLava());

		self::setStateMask(Block::SAND, 0);
		self::registerBlock(new Sand(Block::SAND, 0, "Sand"));
		self::registerBlock(new Sand(Block::SAND, 1, "Red Sand"));

		self::registerBlock(new Gravel());
		self::registerBlock(new GoldOre());
		self::registerBlock(new IronOre());
		self::registerBlock(new CoalOre());
		self::registerBlock(new Sponge());
		self::registerBlock(new Glass(Block::GLASS, 0, "Glass"));
		self::registerBlock(new LapisOre());
		self::registerBlock(new Lapis());
		//TODO: DISPENSER

		static $sandstoneTypes = [
			Sandstone::NORMAL => "",
			Sandstone::CHISELED => "Chiseled ",
			Sandstone::SMOOTH => "Smooth "
		];
		self::setStateMask(Block::SANDSTONE, 0);
		self::setStateMask(Block::RED_SANDSTONE, 0);
		foreach($sandstoneTypes as $variant => $prefix){
			self::registerBlock(new Sandstone(Block::SANDSTONE, $variant, $prefix . "Sandstone"));
			self::registerBlock(new Sandstone(Block::RED_SANDSTONE, $variant, $prefix . "Red Sandstone"));
		}

		self::registerBlock(new NoteBlock());
		self::registerBlock(new Bed());
		self::registerBlock(new PoweredRail());
		self::registerBlock(new DetectorRail());
		//TODO: STICKY_PISTON
		self::registerBlock(new Cobweb());

		self::setStateMask(Block::TALL_GRASS, 0);
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 0, "Fern"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 1, "Tall Grass"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 2, "Fern"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 3, "Fern"));

		self::registerBlock(new DeadBush());
		//TODO: PISTON
		//TODO: PISTONARMCOLLISION

		foreach([
			Block::WOOL,
			Block::STAINED_CLAY,
			Block::STAINED_GLASS,
			Block::STAINED_GLASS_PANE,
			Block::CARPET,
			Block::CONCRETE,
			Block::CONCRETE_POWDER
		] as $id){
			self::setStateMask($id, 0);
		}
		foreach(Color::ALL as $color){
			self::registerBlock(new Wool(Block::WOOL, $color, Color::NAMES[$color] . " Wool"));
			self::registerBlock(new HardenedClay(Block::STAINED_CLAY, $color, Color::NAMES[$color] . " Stained Clay"));
			self::registerBlock(new Glass(Block::STAINED_GLASS, $color, Color::NAMES[$color] . " Stained Glass"));
			self::registerBlock(new GlassPane(Block::STAINED_GLASS_PANE, $color, Color::NAMES[$color] . " Stained Glass Pane"));
			self::registerBlock(new Carpet(Block::CARPET, $color, Color::NAMES[$color] . " Carpet"));
			self::registerBlock(new Concrete(Block::CONCRETE, $color, Color::NAMES[$color] . " Concrete"));
			self::registerBlock(new ConcretePowder(Block::CONCRETE_POWDER, $color, Color::NAMES[$color] . " Concrete Powder"));
		}

		self::registerBlock(new Dandelion());

		self::setStateMask(Block::RED_FLOWER, 0);
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_POPPY, "Poppy"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_BLUE_ORCHID, "Blue Orchid"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_ALLIUM, "Allium"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_AZURE_BLUET, "Azure Bluet"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_RED_TULIP, "Red Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_ORANGE_TULIP, "Orange Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_WHITE_TULIP, "White Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_PINK_TULIP, "Pink Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_OXEYE_DAISY, "Oxeye Daisy"));

		self::registerBlock(new BrownMushroom());
		self::registerBlock(new RedMushroom());
		self::registerBlock(new Gold());
		self::registerBlock(new Iron());

		foreach([
			Block::STONE_SLAB,
			Block::STONE_SLAB2,
			Block::DOUBLE_STONE_SLAB,
			Block::DOUBLE_STONE_SLAB2,
			Block::WOODEN_SLAB,
			Block::DOUBLE_WOODEN_SLAB
		] as $id){
			self::setStateMask($id, 0x8);
		}
		/** @var Slab[] $slabTypes */
		$slabTypes = [
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 0, "Stone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 1, "Sandstone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 2, "Fake Wooden"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 3, "Cobblestone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 4, "Brick"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 5, "Stone Brick"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 6, "Quartz"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 7, "Nether Brick"),
			new StoneSlab(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 0, "Red Sandstone"),
			new StoneSlab(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 1, "Purpur")
		];
		foreach(WoodType::ALL as $woodType){
			$slabTypes[] = new WoodenSlab($woodType);
		}
		foreach($slabTypes as $type){
			self::registerBlock($type);
			self::registerBlock(new DoubleSlab($type->getDoubleSlabId(), $type->getId(), $type->getVariant()));
		}

		self::registerBlock(new Bricks());
		self::registerBlock(new TNT());
		self::registerBlock(new Bookshelf());
		self::registerBlock(new MossyCobblestone());
		self::registerBlock(new Obsidian());
		self::registerBlock(new Torch());
		self::registerBlock(new Fire());
		self::registerBlock(new MonsterSpawner());
		self::registerBlock(new WoodenStairs(Block::OAK_STAIRS, 0, "Oak Stairs"));
		self::registerBlock(new Chest());
		//TODO: REDSTONE_WIRE
		self::registerBlock(new DiamondOre());
		self::registerBlock(new Diamond());
		self::registerBlock(new CraftingTable());
		self::registerBlock(new Wheat());
		self::registerBlock(new Farmland());

		self::registerBlock(new Furnace());
		self::addGetInterceptor(Block::BURNING_FURNACE, 0, function() : Block{
			$block = self::get(Block::FURNACE);
			if($block instanceof Furnace){
				$block->setLit();
			}
			return $block;
		});

		self::registerBlock(new SignPost());
		self::registerBlock(new WoodenDoor(Block::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR));
		self::registerBlock(new Ladder());
		self::registerBlock(new Rail());
		self::registerBlock(new CobblestoneStairs());
		self::registerBlock(new WallSign());
		self::registerBlock(new Lever());
		self::registerBlock(new StonePressurePlate());
		self::registerBlock(new IronDoor());
		self::registerBlock(new WoodenPressurePlate());
		self::registerBlock(new RedstoneOre());
		self::addGetInterceptor(Block::GLOWING_REDSTONE_ORE, 0, function() : Block{
			$block = self::get(Block::REDSTONE_ORE);
			if($block instanceof RedstoneOre){
				$block->setLit();
			}
			return $block;
		});

		self::registerBlock(new RedstoneTorch());
		self::addGetInterceptor(Block::UNLIT_REDSTONE_TORCH, 0, function() : Block{
			$block = self::get(Block::REDSTONE_TORCH);
			if($block instanceof RedstoneTorch){
				$block->setLit(false); //default state is lit
			}
			return $block;
		});

		self::registerBlock(new StoneButton());
		self::registerBlock(new SnowLayer());
		self::registerBlock(new Ice());
		self::registerBlock(new Snow());
		self::registerBlock(new Cactus());
		self::registerBlock(new Clay());
		self::registerBlock(new Sugarcane());
		//TODO: JUKEBOX

		self::registerBlock(new Pumpkin());
		self::registerBlock(new Netherrack());
		self::registerBlock(new SoulSand());
		self::registerBlock(new Glowstone());
		//TODO: PORTAL
		self::registerBlock(new LitPumpkin());
		self::registerBlock(new Cake());
		//TODO: REPEATER_BLOCK
		//TODO: POWERED_REPEATER
		//TODO: INVISIBLEBEDROCK
		self::registerBlock(new Trapdoor());
		//TODO: MONSTER_EGG

		self::setStateMask(Block::STONE_BRICKS, 0);
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::NORMAL, "Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::MOSSY, "Mossy Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::CRACKED, "Cracked Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::CHISELED, "Chiseled Stone Bricks"));

		self::registerBlock(new BrownMushroomBlock());
		self::registerBlock(new RedMushroomBlock());
		self::registerBlock(new IronBars());
		self::registerBlock(new GlassPane(Block::GLASS_PANE, 0, "Glass Pane"));
		self::registerBlock(new Melon());
		self::registerBlock(new PumpkinStem());
		self::registerBlock(new MelonStem());
		self::registerBlock(new Vine());
		self::registerBlock(new FenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"));
		self::registerBlock(new BrickStairs());
		self::registerBlock(new StoneBrickStairs());
		self::registerBlock(new Mycelium());
		self::registerBlock(new WaterLily());
		self::registerBlock(new NetherBrick(Block::NETHER_BRICK_BLOCK, 0, "Nether Bricks"));
		self::registerBlock(new NetherBrickFence());
		self::registerBlock(new NetherBrickStairs());
		self::registerBlock(new NetherWartPlant());
		self::registerBlock(new EnchantingTable());
		self::registerBlock(new BrewingStand());
		//TODO: CAULDRON_BLOCK
		//TODO: END_PORTAL
		self::registerBlock(new EndPortalFrame());
		self::registerBlock(new EndStone());
		//TODO: DRAGON_EGG
		self::registerBlock(new RedstoneLamp());
		self::addGetInterceptor(Block::LIT_REDSTONE_LAMP, 0, function() : Block{
			$block = self::get(Block::REDSTONE_LAMP);
			if($block instanceof RedstoneLamp){
				$block->setLit();
			}
			return $block;
		});

		//TODO: DROPPER
		self::registerBlock(new ActivatorRail());
		self::registerBlock(new CocoaBlock());
		self::registerBlock(new SandstoneStairs());
		self::registerBlock(new EmeraldOre());
		self::registerBlock(new EnderChest());
		self::registerBlock(new TripwireHook());
		self::registerBlock(new Tripwire());
		self::registerBlock(new Emerald());
		self::registerBlock(new WoodenStairs(Block::SPRUCE_STAIRS, 0, "Spruce Stairs"));
		self::registerBlock(new WoodenStairs(Block::BIRCH_STAIRS, 0, "Birch Stairs"));
		self::registerBlock(new WoodenStairs(Block::JUNGLE_STAIRS, 0, "Jungle Stairs"));
		//TODO: COMMAND_BLOCK
		//TODO: BEACON

		self::setStateMask(Block::COBBLESTONE_WALL, 0);
		self::registerBlock(new CobblestoneWall(Block::COBBLESTONE_WALL, CobblestoneWall::NONE_MOSSY_WALL, "Cobblestone Wall"));
		self::registerBlock(new CobblestoneWall(Block::COBBLESTONE_WALL, CobblestoneWall::MOSSY_WALL, "Mossy Cobblestone Wall"));

		self::registerBlock(new FlowerPot());
		self::registerBlock(new Carrot());
		self::registerBlock(new Potato());
		self::registerBlock(new WoodenButton());
		self::registerBlock(new Skull());

		self::setStateMask(Block::ANVIL, 0x3); //rotation
		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_NORMAL, "Anvil"));
		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_SLIGHTLY_DAMAGED, "Slightly Damaged Anvil"));
		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_VERY_DAMAGED, "Very Damaged Anvil"));

		self::registerBlock(new TrappedChest());
		self::registerBlock(new WeightedPressurePlateLight());
		self::registerBlock(new WeightedPressurePlateHeavy());
		//TODO: COMPARATOR_BLOCK
		//TODO: POWERED_COMPARATOR
		self::registerBlock(new DaylightSensor());
		self::addGetInterceptor(Block::DAYLIGHT_SENSOR_INVERTED, 0, function() : Block{
			$block = self::get(Block::DAYLIGHT_SENSOR);
			if($block instanceof DaylightSensor){
				$block->setInverted();
			}
			return $block;
		});

		self::registerBlock(new Redstone());
		self::registerBlock(new NetherQuartzOre());
		//TODO: HOPPER_BLOCK

		self::setStateMask(Block::QUARTZ_BLOCK, 0xc); //pillar axis
		self::setStateMask(Block::PURPUR_BLOCK, 0xc);
		static $quartzTypes = [
			Quartz::NORMAL => "",
			Quartz::CHISELED => "Chiseled ",
			Quartz::PILLAR => "Pillar "
		];
		foreach($quartzTypes as $variant => $prefix){
			self::registerBlock(new Quartz(Block::QUARTZ_BLOCK, $variant, $prefix . "Quartz Block"));
			self::registerBlock(new Purpur(Block::PURPUR_BLOCK, $variant, $prefix . "Purpur Block"));
		}

		self::registerBlock(new QuartzStairs());


		self::registerBlock(new WoodenStairs(Block::ACACIA_STAIRS, 0, "Acacia Stairs"));
		self::registerBlock(new WoodenStairs(Block::DARK_OAK_STAIRS, 0, "Dark Oak Stairs"));
		//TODO: SLIME

		self::registerBlock(new IronTrapdoor());

		self::setStateMask(Block::PRISMARINE, 0);
		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::NORMAL, "Prismarine"));
		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::DARK, "Dark Prismarine"));
		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::BRICKS, "Prismarine Bricks"));

		self::registerBlock(new SeaLantern());
		self::registerBlock(new HayBale());

		self::registerBlock(new HardenedClay(Block::HARDENED_CLAY, 0, "Hardened Clay"));
		self::registerBlock(new Coal());
		self::registerBlock(new PackedIce());

		self::setStateMask(Block::DOUBLE_PLANT, 0x8); //top flag
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 0, "Sunflower"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 1, "Lilac"));
		//TODO: double tallgrass and large fern have different behaviour than the others, so they should get their own classes
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 2, "Double Tallgrass"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 3, "Large Fern"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 4, "Rose Bush"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 5, "Peony"));

		self::registerBlock(new StandingBanner());
		self::registerBlock(new WallBanner());

		self::registerBlock(new RedSandstoneStairs());
		self::registerBlock(new FenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"));
		self::registerBlock(new FenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"));
		self::registerBlock(new FenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"));
		self::registerBlock(new FenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"));
		self::registerBlock(new FenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"));
		//TODO: REPEATING_COMMAND_BLOCK
		//TODO: CHAIN_COMMAND_BLOCK

		self::registerBlock(new WoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR));
		self::registerBlock(new WoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR));
		self::registerBlock(new WoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR));
		self::registerBlock(new WoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR));
		self::registerBlock(new WoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR));
		self::registerBlock(new GrassPath());
		self::registerBlock(new ItemFrame());
		//TODO: CHORUS_FLOWER

		self::registerBlock(new PurpurStairs());

		//TODO: UNDYED_SHULKER_BOX
		self::registerBlock(new EndStoneBricks());
		//TODO: FROSTED_ICE
		self::registerBlock(new EndRod());
		//TODO: END_GATEWAY

		self::registerBlock(new Magma());
		self::registerBlock(new NetherWartBlock());
		self::registerBlock(new NetherBrick(Block::RED_NETHER_BRICK, 0, "Red Nether Bricks"));
		self::registerBlock(new BoneBlock());

		//TODO: SHULKER_BOX
		self::registerBlock(new GlazedTerracotta(Block::PURPLE_GLAZED_TERRACOTTA, 0, "Purple Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::WHITE_GLAZED_TERRACOTTA, 0, "White Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::ORANGE_GLAZED_TERRACOTTA, 0, "Orange Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::MAGENTA_GLAZED_TERRACOTTA, 0, "Magenta Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::LIGHT_BLUE_GLAZED_TERRACOTTA, 0, "Light Blue Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::YELLOW_GLAZED_TERRACOTTA, 0, "Yellow Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::LIME_GLAZED_TERRACOTTA, 0, "Lime Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::PINK_GLAZED_TERRACOTTA, 0, "Pink Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::GRAY_GLAZED_TERRACOTTA, 0, "Grey Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::SILVER_GLAZED_TERRACOTTA, 0, "Light Grey Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::CYAN_GLAZED_TERRACOTTA, 0, "Cyan Glazed Terracotta"));

		self::registerBlock(new GlazedTerracotta(Block::BLUE_GLAZED_TERRACOTTA, 0, "Blue Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::BROWN_GLAZED_TERRACOTTA, 0, "Brown Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::GREEN_GLAZED_TERRACOTTA, 0, "Green Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::RED_GLAZED_TERRACOTTA, 0, "Red Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::BLACK_GLAZED_TERRACOTTA, 0, "Black Glazed Terracotta"));

		//TODO: CHORUS_PLANT

		self::registerBlock(new Podzol());
		self::registerBlock(new Beetroot());
		self::registerBlock(new Stonecutter());
		self::registerBlock(new GlowingObsidian());
		self::registerBlock(new NetherReactor());
		//TODO: INFO_UPDATE
		//TODO: INFO_UPDATE2
		//TODO: MOVINGBLOCK
		//TODO: OBSERVER
		//TODO: STRUCTURE_BLOCK

		//TODO: RESERVED6
	}

	/**
	 * Sets the mask used to strip state information from metadata for the given block ID. Used to extract variant
	 * information from blocks.
	 *
	 * Only blocks which declare non-zero variants need to have this set. For any block IDs without variants, all meta
	 * bits are assumed to be state bits.
	 *
	 * @param int $blockId
	 * @param int $mask
	 */
	public static function setStateMask(int $blockId, int $mask) : void{
		self::$stateMasks[$blockId] = $mask;
	}

	/**
	 * Registers a block type into the index. Plugins may use this method to register new block types or override
	 * existing ones.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param Block $block
	 * @param bool  $override Whether to override existing registrations
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public static function registerBlock(Block $block, bool $override = false) : void{
		$id = $block->getId();
		$variant = $block->getVariant();

		if(!$override and self::isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered block");
		}

		if(!($block instanceof UnknownBlock) and $variant !== 0 and self::$stateMasks[$id] === null){
			throw new \InvalidStateException("State bitmask not set for ID " . $id . ". Mask is required to register non-zero variants of blocks.");
		}

		self::$fullList[($id << 4) | $variant] = clone $block;
		if($variant === 0){
			//TODO: allow these to differ for different variants
			self::$solid[$id] = $block->isSolid();
			self::$lightFilter[$id] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
			self::$diffusesSkyLight[$id] = $block->diffusesSkyLight();
			self::$blastResistance[$id] = $block->getBlastResistance();
		}
	}

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 *
	 * @param int      $id
	 * @param int      $meta
	 * @param Position $pos
	 *
	 * @return Block
	 */
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
		if($meta < 0 or $meta > 0xf){
			throw new \InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		if(self::$stateMasks[$id] === null){
			$variant = 0;
		}else{
			$variant = $meta & ~self::$stateMasks[$id];
			$meta &= self::$stateMasks[$id];
		}

		$index = ($id << 4) | $variant;

		/** @var Block|null $block */
		$block = null;
		try{
			if(self::$getInterceptors[$index] !== null){
				$block = (self::$getInterceptors[$index])();
			}elseif(self::$fullList[$index] !== null){
				$block = clone self::$fullList[$index];
			}
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException("Block ID $id is out of bounds");
		}

		if($block !== null){
			try{
				$block->readStateFromMeta($meta);
			}catch(\InvalidArgumentException $e){
				$block = null; //TODO: improve invalid state handling
			}
		}

		if($block === null){
			$block = new UnknownBlock($id, $variant | $meta);
		}

		if($pos !== null){
			$block->x = $pos->getFloorX();
			$block->y = $pos->getFloorY();
			$block->z = $pos->getFloorZ();
			$block->level = $pos->level;
		}

		return $block;
	}

	public static function addGetInterceptor(int $id, int $variant, \Closure $interceptor) : void{
		self::$getInterceptors[($id << 4) | $variant] = $interceptor;
	}

	/**
	 * Returns whether a specified block ID is already registered in the block factory.
	 *
	 * @param int $id
	 * @param int $variant
	 *
	 * @return bool
	 */
	public static function isRegistered(int $id, int $variant = 0) : bool{
		$b = self::$fullList[($id << 4) | $variant];
		return $b !== null and !($b instanceof UnknownBlock);
	}

	public static function registerStaticRuntimeIdMappings() : void{
		/** @var mixed[] $runtimeIdMap */
		$runtimeIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "runtimeid_table.json"), true);
		foreach($runtimeIdMap as $k => $obj){
			self::registerMapping($k, $obj["id"], $obj["data"]);
		}
	}

	/**
	 * @internal
	 *
	 * @param int $id
	 * @param int $meta
	 *
	 * @return int
	 */
	public static function toStaticRuntimeId(int $id, int $meta = 0) : int{
		/*
		 * try id+meta first
		 * if not found, try id+0 (strip meta)
		 * if still not found, return update! block
		 */
		return self::$staticRuntimeIdMap[($id << 4) | $meta] ?? self::$staticRuntimeIdMap[$id << 4] ?? self::$staticRuntimeIdMap[BlockIds::INFO_UPDATE << 4];
	}

	/**
	 * @internal
	 *
	 * @param int $runtimeId
	 *
	 * @return int[] [id, meta]
	 */
	public static function fromStaticRuntimeId(int $runtimeId) : array{
		$v = self::$legacyIdMap[$runtimeId];
		return [$v >> 4, $v & 0xf];
	}

	private static function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		self::$staticRuntimeIdMap[($legacyId << 4) | $legacyMeta] = $staticRuntimeId;
		self::$legacyIdMap[$staticRuntimeId] = ($legacyId << 4) | $legacyMeta;
		self::$lastRuntimeId = max(self::$lastRuntimeId, $staticRuntimeId);
	}
}
