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

use pocketmine\block\BlockTypeIds as Ids;
use pocketmine\utils\CloningRegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static WoodenButton ACACIA_BUTTON()
 * @method static WoodenDoor ACACIA_DOOR()
 * @method static WoodenFence ACACIA_FENCE()
 * @method static FenceGate ACACIA_FENCE_GATE()
 * @method static Leaves ACACIA_LEAVES()
 * @method static Wood ACACIA_LOG()
 * @method static Planks ACACIA_PLANKS()
 * @method static WoodenPressurePlate ACACIA_PRESSURE_PLATE()
 * @method static Sapling ACACIA_SAPLING()
 * @method static FloorSign ACACIA_SIGN()
 * @method static WoodenSlab ACACIA_SLAB()
 * @method static WoodenStairs ACACIA_STAIRS()
 * @method static WoodenTrapdoor ACACIA_TRAPDOOR()
 * @method static WallSign ACACIA_WALL_SIGN()
 * @method static Wood ACACIA_WOOD()
 * @method static ActivatorRail ACTIVATOR_RAIL()
 * @method static Air AIR()
 * @method static Flower ALLIUM()
 * @method static MushroomStem ALL_SIDED_MUSHROOM_STEM()
 * @method static Opaque AMETHYST()
 * @method static Opaque ANCIENT_DEBRIS()
 * @method static Opaque ANDESITE()
 * @method static Slab ANDESITE_SLAB()
 * @method static Stair ANDESITE_STAIRS()
 * @method static Wall ANDESITE_WALL()
 * @method static Anvil ANVIL()
 * @method static Flower AZURE_BLUET()
 * @method static Bamboo BAMBOO()
 * @method static BambooSapling BAMBOO_SAPLING()
 * @method static FloorBanner BANNER()
 * @method static Barrel BARREL()
 * @method static Transparent BARRIER()
 * @method static SimplePillar BASALT()
 * @method static Beacon BEACON()
 * @method static Bed BED()
 * @method static Bedrock BEDROCK()
 * @method static Beetroot BEETROOTS()
 * @method static Bell BELL()
 * @method static WoodenButton BIRCH_BUTTON()
 * @method static WoodenDoor BIRCH_DOOR()
 * @method static WoodenFence BIRCH_FENCE()
 * @method static FenceGate BIRCH_FENCE_GATE()
 * @method static Leaves BIRCH_LEAVES()
 * @method static Wood BIRCH_LOG()
 * @method static Planks BIRCH_PLANKS()
 * @method static WoodenPressurePlate BIRCH_PRESSURE_PLATE()
 * @method static Sapling BIRCH_SAPLING()
 * @method static FloorSign BIRCH_SIGN()
 * @method static WoodenSlab BIRCH_SLAB()
 * @method static WoodenStairs BIRCH_STAIRS()
 * @method static WoodenTrapdoor BIRCH_TRAPDOOR()
 * @method static WallSign BIRCH_WALL_SIGN()
 * @method static Wood BIRCH_WOOD()
 * @method static Opaque BLACKSTONE()
 * @method static Slab BLACKSTONE_SLAB()
 * @method static Stair BLACKSTONE_STAIRS()
 * @method static Wall BLACKSTONE_WALL()
 * @method static Furnace BLAST_FURNACE()
 * @method static BlueIce BLUE_ICE()
 * @method static Flower BLUE_ORCHID()
 * @method static Torch BLUE_TORCH()
 * @method static BoneBlock BONE_BLOCK()
 * @method static Bookshelf BOOKSHELF()
 * @method static BrewingStand BREWING_STAND()
 * @method static Opaque BRICKS()
 * @method static Slab BRICK_SLAB()
 * @method static Stair BRICK_STAIRS()
 * @method static Wall BRICK_WALL()
 * @method static BrownMushroom BROWN_MUSHROOM()
 * @method static BrownMushroomBlock BROWN_MUSHROOM_BLOCK()
 * @method static Cactus CACTUS()
 * @method static Cake CAKE()
 * @method static Opaque CALCITE()
 * @method static Carpet CARPET()
 * @method static Carrot CARROTS()
 * @method static CarvedPumpkin CARVED_PUMPKIN()
 * @method static ChemicalHeat CHEMICAL_HEAT()
 * @method static Chest CHEST()
 * @method static Opaque CHISELED_DEEPSLATE()
 * @method static Opaque CHISELED_NETHER_BRICKS()
 * @method static Opaque CHISELED_POLISHED_BLACKSTONE()
 * @method static SimplePillar CHISELED_QUARTZ()
 * @method static Opaque CHISELED_RED_SANDSTONE()
 * @method static Opaque CHISELED_SANDSTONE()
 * @method static Opaque CHISELED_STONE_BRICKS()
 * @method static Clay CLAY()
 * @method static Coal COAL()
 * @method static CoalOre COAL_ORE()
 * @method static Opaque COBBLED_DEEPSLATE()
 * @method static Slab COBBLED_DEEPSLATE_SLAB()
 * @method static Stair COBBLED_DEEPSLATE_STAIRS()
 * @method static Wall COBBLED_DEEPSLATE_WALL()
 * @method static Opaque COBBLESTONE()
 * @method static Slab COBBLESTONE_SLAB()
 * @method static Stair COBBLESTONE_STAIRS()
 * @method static Wall COBBLESTONE_WALL()
 * @method static Cobweb COBWEB()
 * @method static CocoaBlock COCOA_POD()
 * @method static ChemistryTable COMPOUND_CREATOR()
 * @method static Concrete CONCRETE()
 * @method static ConcretePowder CONCRETE_POWDER()
 * @method static CopperOre COPPER_ORE()
 * @method static Coral CORAL()
 * @method static CoralBlock CORAL_BLOCK()
 * @method static FloorCoralFan CORAL_FAN()
 * @method static Flower CORNFLOWER()
 * @method static Opaque CRACKED_DEEPSLATE_BRICKS()
 * @method static Opaque CRACKED_DEEPSLATE_TILES()
 * @method static Opaque CRACKED_NETHER_BRICKS()
 * @method static Opaque CRACKED_POLISHED_BLACKSTONE_BRICKS()
 * @method static Opaque CRACKED_STONE_BRICKS()
 * @method static CraftingTable CRAFTING_TABLE()
 * @method static WoodenButton CRIMSON_BUTTON()
 * @method static WoodenDoor CRIMSON_DOOR()
 * @method static WoodenFence CRIMSON_FENCE()
 * @method static FenceGate CRIMSON_FENCE_GATE()
 * @method static Wood CRIMSON_HYPHAE()
 * @method static Planks CRIMSON_PLANKS()
 * @method static WoodenPressurePlate CRIMSON_PRESSURE_PLATE()
 * @method static FloorSign CRIMSON_SIGN()
 * @method static WoodenSlab CRIMSON_SLAB()
 * @method static WoodenStairs CRIMSON_STAIRS()
 * @method static Wood CRIMSON_STEM()
 * @method static WoodenTrapdoor CRIMSON_TRAPDOOR()
 * @method static WallSign CRIMSON_WALL_SIGN()
 * @method static Opaque CUT_RED_SANDSTONE()
 * @method static Slab CUT_RED_SANDSTONE_SLAB()
 * @method static Opaque CUT_SANDSTONE()
 * @method static Slab CUT_SANDSTONE_SLAB()
 * @method static Flower DANDELION()
 * @method static WoodenButton DARK_OAK_BUTTON()
 * @method static WoodenDoor DARK_OAK_DOOR()
 * @method static WoodenFence DARK_OAK_FENCE()
 * @method static FenceGate DARK_OAK_FENCE_GATE()
 * @method static Leaves DARK_OAK_LEAVES()
 * @method static Wood DARK_OAK_LOG()
 * @method static Planks DARK_OAK_PLANKS()
 * @method static WoodenPressurePlate DARK_OAK_PRESSURE_PLATE()
 * @method static Sapling DARK_OAK_SAPLING()
 * @method static FloorSign DARK_OAK_SIGN()
 * @method static WoodenSlab DARK_OAK_SLAB()
 * @method static WoodenStairs DARK_OAK_STAIRS()
 * @method static WoodenTrapdoor DARK_OAK_TRAPDOOR()
 * @method static WallSign DARK_OAK_WALL_SIGN()
 * @method static Wood DARK_OAK_WOOD()
 * @method static Opaque DARK_PRISMARINE()
 * @method static Slab DARK_PRISMARINE_SLAB()
 * @method static Stair DARK_PRISMARINE_STAIRS()
 * @method static DaylightSensor DAYLIGHT_SENSOR()
 * @method static DeadBush DEAD_BUSH()
 * @method static SimplePillar DEEPSLATE()
 * @method static Opaque DEEPSLATE_BRICKS()
 * @method static Slab DEEPSLATE_BRICK_SLAB()
 * @method static Stair DEEPSLATE_BRICK_STAIRS()
 * @method static Wall DEEPSLATE_BRICK_WALL()
 * @method static CoalOre DEEPSLATE_COAL_ORE()
 * @method static CopperOre DEEPSLATE_COPPER_ORE()
 * @method static DiamondOre DEEPSLATE_DIAMOND_ORE()
 * @method static EmeraldOre DEEPSLATE_EMERALD_ORE()
 * @method static GoldOre DEEPSLATE_GOLD_ORE()
 * @method static IronOre DEEPSLATE_IRON_ORE()
 * @method static LapisOre DEEPSLATE_LAPIS_LAZULI_ORE()
 * @method static RedstoneOre DEEPSLATE_REDSTONE_ORE()
 * @method static Opaque DEEPSLATE_TILES()
 * @method static Slab DEEPSLATE_TILE_SLAB()
 * @method static Stair DEEPSLATE_TILE_STAIRS()
 * @method static Wall DEEPSLATE_TILE_WALL()
 * @method static DetectorRail DETECTOR_RAIL()
 * @method static Opaque DIAMOND()
 * @method static DiamondOre DIAMOND_ORE()
 * @method static Opaque DIORITE()
 * @method static Slab DIORITE_SLAB()
 * @method static Stair DIORITE_STAIRS()
 * @method static Wall DIORITE_WALL()
 * @method static Dirt DIRT()
 * @method static DoubleTallGrass DOUBLE_TALLGRASS()
 * @method static DragonEgg DRAGON_EGG()
 * @method static DriedKelp DRIED_KELP()
 * @method static DyedShulkerBox DYED_SHULKER_BOX()
 * @method static Element ELEMENT_ACTINIUM()
 * @method static Element ELEMENT_ALUMINUM()
 * @method static Element ELEMENT_AMERICIUM()
 * @method static Element ELEMENT_ANTIMONY()
 * @method static Element ELEMENT_ARGON()
 * @method static Element ELEMENT_ARSENIC()
 * @method static Element ELEMENT_ASTATINE()
 * @method static Element ELEMENT_BARIUM()
 * @method static Element ELEMENT_BERKELIUM()
 * @method static Element ELEMENT_BERYLLIUM()
 * @method static Element ELEMENT_BISMUTH()
 * @method static Element ELEMENT_BOHRIUM()
 * @method static Element ELEMENT_BORON()
 * @method static Element ELEMENT_BROMINE()
 * @method static Element ELEMENT_CADMIUM()
 * @method static Element ELEMENT_CALCIUM()
 * @method static Element ELEMENT_CALIFORNIUM()
 * @method static Element ELEMENT_CARBON()
 * @method static Element ELEMENT_CERIUM()
 * @method static Element ELEMENT_CESIUM()
 * @method static Element ELEMENT_CHLORINE()
 * @method static Element ELEMENT_CHROMIUM()
 * @method static Element ELEMENT_COBALT()
 * @method static ChemistryTable ELEMENT_CONSTRUCTOR()
 * @method static Element ELEMENT_COPERNICIUM()
 * @method static Element ELEMENT_COPPER()
 * @method static Element ELEMENT_CURIUM()
 * @method static Element ELEMENT_DARMSTADTIUM()
 * @method static Element ELEMENT_DUBNIUM()
 * @method static Element ELEMENT_DYSPROSIUM()
 * @method static Element ELEMENT_EINSTEINIUM()
 * @method static Element ELEMENT_ERBIUM()
 * @method static Element ELEMENT_EUROPIUM()
 * @method static Element ELEMENT_FERMIUM()
 * @method static Element ELEMENT_FLEROVIUM()
 * @method static Element ELEMENT_FLUORINE()
 * @method static Element ELEMENT_FRANCIUM()
 * @method static Element ELEMENT_GADOLINIUM()
 * @method static Element ELEMENT_GALLIUM()
 * @method static Element ELEMENT_GERMANIUM()
 * @method static Element ELEMENT_GOLD()
 * @method static Element ELEMENT_HAFNIUM()
 * @method static Element ELEMENT_HASSIUM()
 * @method static Element ELEMENT_HELIUM()
 * @method static Element ELEMENT_HOLMIUM()
 * @method static Element ELEMENT_HYDROGEN()
 * @method static Element ELEMENT_INDIUM()
 * @method static Element ELEMENT_IODINE()
 * @method static Element ELEMENT_IRIDIUM()
 * @method static Element ELEMENT_IRON()
 * @method static Element ELEMENT_KRYPTON()
 * @method static Element ELEMENT_LANTHANUM()
 * @method static Element ELEMENT_LAWRENCIUM()
 * @method static Element ELEMENT_LEAD()
 * @method static Element ELEMENT_LITHIUM()
 * @method static Element ELEMENT_LIVERMORIUM()
 * @method static Element ELEMENT_LUTETIUM()
 * @method static Element ELEMENT_MAGNESIUM()
 * @method static Element ELEMENT_MANGANESE()
 * @method static Element ELEMENT_MEITNERIUM()
 * @method static Element ELEMENT_MENDELEVIUM()
 * @method static Element ELEMENT_MERCURY()
 * @method static Element ELEMENT_MOLYBDENUM()
 * @method static Element ELEMENT_MOSCOVIUM()
 * @method static Element ELEMENT_NEODYMIUM()
 * @method static Element ELEMENT_NEON()
 * @method static Element ELEMENT_NEPTUNIUM()
 * @method static Element ELEMENT_NICKEL()
 * @method static Element ELEMENT_NIHONIUM()
 * @method static Element ELEMENT_NIOBIUM()
 * @method static Element ELEMENT_NITROGEN()
 * @method static Element ELEMENT_NOBELIUM()
 * @method static Element ELEMENT_OGANESSON()
 * @method static Element ELEMENT_OSMIUM()
 * @method static Element ELEMENT_OXYGEN()
 * @method static Element ELEMENT_PALLADIUM()
 * @method static Element ELEMENT_PHOSPHORUS()
 * @method static Element ELEMENT_PLATINUM()
 * @method static Element ELEMENT_PLUTONIUM()
 * @method static Element ELEMENT_POLONIUM()
 * @method static Element ELEMENT_POTASSIUM()
 * @method static Element ELEMENT_PRASEODYMIUM()
 * @method static Element ELEMENT_PROMETHIUM()
 * @method static Element ELEMENT_PROTACTINIUM()
 * @method static Element ELEMENT_RADIUM()
 * @method static Element ELEMENT_RADON()
 * @method static Element ELEMENT_RHENIUM()
 * @method static Element ELEMENT_RHODIUM()
 * @method static Element ELEMENT_ROENTGENIUM()
 * @method static Element ELEMENT_RUBIDIUM()
 * @method static Element ELEMENT_RUTHENIUM()
 * @method static Element ELEMENT_RUTHERFORDIUM()
 * @method static Element ELEMENT_SAMARIUM()
 * @method static Element ELEMENT_SCANDIUM()
 * @method static Element ELEMENT_SEABORGIUM()
 * @method static Element ELEMENT_SELENIUM()
 * @method static Element ELEMENT_SILICON()
 * @method static Element ELEMENT_SILVER()
 * @method static Element ELEMENT_SODIUM()
 * @method static Element ELEMENT_STRONTIUM()
 * @method static Element ELEMENT_SULFUR()
 * @method static Element ELEMENT_TANTALUM()
 * @method static Element ELEMENT_TECHNETIUM()
 * @method static Element ELEMENT_TELLURIUM()
 * @method static Element ELEMENT_TENNESSINE()
 * @method static Element ELEMENT_TERBIUM()
 * @method static Element ELEMENT_THALLIUM()
 * @method static Element ELEMENT_THORIUM()
 * @method static Element ELEMENT_THULIUM()
 * @method static Element ELEMENT_TIN()
 * @method static Element ELEMENT_TITANIUM()
 * @method static Element ELEMENT_TUNGSTEN()
 * @method static Element ELEMENT_URANIUM()
 * @method static Element ELEMENT_VANADIUM()
 * @method static Element ELEMENT_XENON()
 * @method static Element ELEMENT_YTTERBIUM()
 * @method static Element ELEMENT_YTTRIUM()
 * @method static Opaque ELEMENT_ZERO()
 * @method static Element ELEMENT_ZINC()
 * @method static Element ELEMENT_ZIRCONIUM()
 * @method static Opaque EMERALD()
 * @method static EmeraldOre EMERALD_ORE()
 * @method static EnchantingTable ENCHANTING_TABLE()
 * @method static EnderChest ENDER_CHEST()
 * @method static EndPortalFrame END_PORTAL_FRAME()
 * @method static EndRod END_ROD()
 * @method static Opaque END_STONE()
 * @method static Opaque END_STONE_BRICKS()
 * @method static Slab END_STONE_BRICK_SLAB()
 * @method static Stair END_STONE_BRICK_STAIRS()
 * @method static Wall END_STONE_BRICK_WALL()
 * @method static Slab FAKE_WOODEN_SLAB()
 * @method static Farmland FARMLAND()
 * @method static TallGrass FERN()
 * @method static Fire FIRE()
 * @method static FletchingTable FLETCHING_TABLE()
 * @method static FlowerPot FLOWER_POT()
 * @method static FrostedIce FROSTED_ICE()
 * @method static Furnace FURNACE()
 * @method static Glass GLASS()
 * @method static GlassPane GLASS_PANE()
 * @method static GlazedTerracotta GLAZED_TERRACOTTA()
 * @method static GlowingObsidian GLOWING_OBSIDIAN()
 * @method static Glowstone GLOWSTONE()
 * @method static Opaque GOLD()
 * @method static GoldOre GOLD_ORE()
 * @method static Opaque GRANITE()
 * @method static Slab GRANITE_SLAB()
 * @method static Stair GRANITE_STAIRS()
 * @method static Wall GRANITE_WALL()
 * @method static Grass GRASS()
 * @method static GrassPath GRASS_PATH()
 * @method static Gravel GRAVEL()
 * @method static Torch GREEN_TORCH()
 * @method static HardenedClay HARDENED_CLAY()
 * @method static HardenedGlass HARDENED_GLASS()
 * @method static HardenedGlassPane HARDENED_GLASS_PANE()
 * @method static HayBale HAY_BALE()
 * @method static Opaque HONEYCOMB()
 * @method static Hopper HOPPER()
 * @method static Ice ICE()
 * @method static InfestedStone INFESTED_CHISELED_STONE_BRICK()
 * @method static InfestedStone INFESTED_COBBLESTONE()
 * @method static InfestedStone INFESTED_CRACKED_STONE_BRICK()
 * @method static InfestedStone INFESTED_MOSSY_STONE_BRICK()
 * @method static InfestedStone INFESTED_STONE()
 * @method static InfestedStone INFESTED_STONE_BRICK()
 * @method static Opaque INFO_UPDATE()
 * @method static Opaque INFO_UPDATE2()
 * @method static Transparent INVISIBLE_BEDROCK()
 * @method static Opaque IRON()
 * @method static Thin IRON_BARS()
 * @method static Door IRON_DOOR()
 * @method static IronOre IRON_ORE()
 * @method static Trapdoor IRON_TRAPDOOR()
 * @method static ItemFrame ITEM_FRAME()
 * @method static Jukebox JUKEBOX()
 * @method static WoodenButton JUNGLE_BUTTON()
 * @method static WoodenDoor JUNGLE_DOOR()
 * @method static WoodenFence JUNGLE_FENCE()
 * @method static FenceGate JUNGLE_FENCE_GATE()
 * @method static Leaves JUNGLE_LEAVES()
 * @method static Wood JUNGLE_LOG()
 * @method static Planks JUNGLE_PLANKS()
 * @method static WoodenPressurePlate JUNGLE_PRESSURE_PLATE()
 * @method static Sapling JUNGLE_SAPLING()
 * @method static FloorSign JUNGLE_SIGN()
 * @method static WoodenSlab JUNGLE_SLAB()
 * @method static WoodenStairs JUNGLE_STAIRS()
 * @method static WoodenTrapdoor JUNGLE_TRAPDOOR()
 * @method static WallSign JUNGLE_WALL_SIGN()
 * @method static Wood JUNGLE_WOOD()
 * @method static ChemistryTable LAB_TABLE()
 * @method static Ladder LADDER()
 * @method static Lantern LANTERN()
 * @method static Opaque LAPIS_LAZULI()
 * @method static LapisOre LAPIS_LAZULI_ORE()
 * @method static DoubleTallGrass LARGE_FERN()
 * @method static Lava LAVA()
 * @method static Lectern LECTERN()
 * @method static Opaque LEGACY_STONECUTTER()
 * @method static Lever LEVER()
 * @method static Light LIGHT()
 * @method static DoublePlant LILAC()
 * @method static Flower LILY_OF_THE_VALLEY()
 * @method static WaterLily LILY_PAD()
 * @method static LitPumpkin LIT_PUMPKIN()
 * @method static Loom LOOM()
 * @method static Magma MAGMA()
 * @method static WoodenButton MANGROVE_BUTTON()
 * @method static WoodenDoor MANGROVE_DOOR()
 * @method static WoodenFence MANGROVE_FENCE()
 * @method static FenceGate MANGROVE_FENCE_GATE()
 * @method static Wood MANGROVE_LOG()
 * @method static Planks MANGROVE_PLANKS()
 * @method static WoodenPressurePlate MANGROVE_PRESSURE_PLATE()
 * @method static FloorSign MANGROVE_SIGN()
 * @method static WoodenSlab MANGROVE_SLAB()
 * @method static WoodenStairs MANGROVE_STAIRS()
 * @method static WoodenTrapdoor MANGROVE_TRAPDOOR()
 * @method static WallSign MANGROVE_WALL_SIGN()
 * @method static Wood MANGROVE_WOOD()
 * @method static ChemistryTable MATERIAL_REDUCER()
 * @method static Melon MELON()
 * @method static MelonStem MELON_STEM()
 * @method static Skull MOB_HEAD()
 * @method static MonsterSpawner MONSTER_SPAWNER()
 * @method static Opaque MOSSY_COBBLESTONE()
 * @method static Slab MOSSY_COBBLESTONE_SLAB()
 * @method static Stair MOSSY_COBBLESTONE_STAIRS()
 * @method static Wall MOSSY_COBBLESTONE_WALL()
 * @method static Opaque MOSSY_STONE_BRICKS()
 * @method static Slab MOSSY_STONE_BRICK_SLAB()
 * @method static Stair MOSSY_STONE_BRICK_STAIRS()
 * @method static Wall MOSSY_STONE_BRICK_WALL()
 * @method static Opaque MUD_BRICKS()
 * @method static Slab MUD_BRICK_SLAB()
 * @method static Stair MUD_BRICK_STAIRS()
 * @method static Wall MUD_BRICK_WALL()
 * @method static MushroomStem MUSHROOM_STEM()
 * @method static Mycelium MYCELIUM()
 * @method static Netherrack NETHERRACK()
 * @method static Opaque NETHER_BRICKS()
 * @method static Fence NETHER_BRICK_FENCE()
 * @method static Slab NETHER_BRICK_SLAB()
 * @method static Stair NETHER_BRICK_STAIRS()
 * @method static Wall NETHER_BRICK_WALL()
 * @method static NetherGoldOre NETHER_GOLD_ORE()
 * @method static NetherPortal NETHER_PORTAL()
 * @method static NetherQuartzOre NETHER_QUARTZ_ORE()
 * @method static NetherReactor NETHER_REACTOR_CORE()
 * @method static NetherWartPlant NETHER_WART()
 * @method static Opaque NETHER_WART_BLOCK()
 * @method static Note NOTE_BLOCK()
 * @method static WoodenButton OAK_BUTTON()
 * @method static WoodenDoor OAK_DOOR()
 * @method static WoodenFence OAK_FENCE()
 * @method static FenceGate OAK_FENCE_GATE()
 * @method static Leaves OAK_LEAVES()
 * @method static Wood OAK_LOG()
 * @method static Planks OAK_PLANKS()
 * @method static WoodenPressurePlate OAK_PRESSURE_PLATE()
 * @method static Sapling OAK_SAPLING()
 * @method static FloorSign OAK_SIGN()
 * @method static WoodenSlab OAK_SLAB()
 * @method static WoodenStairs OAK_STAIRS()
 * @method static WoodenTrapdoor OAK_TRAPDOOR()
 * @method static WallSign OAK_WALL_SIGN()
 * @method static Wood OAK_WOOD()
 * @method static Opaque OBSIDIAN()
 * @method static Flower ORANGE_TULIP()
 * @method static Flower OXEYE_DAISY()
 * @method static PackedIce PACKED_ICE()
 * @method static DoublePlant PEONY()
 * @method static Flower PINK_TULIP()
 * @method static Podzol PODZOL()
 * @method static Opaque POLISHED_ANDESITE()
 * @method static Slab POLISHED_ANDESITE_SLAB()
 * @method static Stair POLISHED_ANDESITE_STAIRS()
 * @method static SimplePillar POLISHED_BASALT()
 * @method static Opaque POLISHED_BLACKSTONE()
 * @method static Opaque POLISHED_BLACKSTONE_BRICKS()
 * @method static Slab POLISHED_BLACKSTONE_BRICK_SLAB()
 * @method static Stair POLISHED_BLACKSTONE_BRICK_STAIRS()
 * @method static Wall POLISHED_BLACKSTONE_BRICK_WALL()
 * @method static StoneButton POLISHED_BLACKSTONE_BUTTON()
 * @method static StonePressurePlate POLISHED_BLACKSTONE_PRESSURE_PLATE()
 * @method static Slab POLISHED_BLACKSTONE_SLAB()
 * @method static Stair POLISHED_BLACKSTONE_STAIRS()
 * @method static Wall POLISHED_BLACKSTONE_WALL()
 * @method static Opaque POLISHED_DEEPSLATE()
 * @method static Slab POLISHED_DEEPSLATE_SLAB()
 * @method static Stair POLISHED_DEEPSLATE_STAIRS()
 * @method static Wall POLISHED_DEEPSLATE_WALL()
 * @method static Opaque POLISHED_DIORITE()
 * @method static Slab POLISHED_DIORITE_SLAB()
 * @method static Stair POLISHED_DIORITE_STAIRS()
 * @method static Opaque POLISHED_GRANITE()
 * @method static Slab POLISHED_GRANITE_SLAB()
 * @method static Stair POLISHED_GRANITE_STAIRS()
 * @method static Flower POPPY()
 * @method static Potato POTATOES()
 * @method static PoweredRail POWERED_RAIL()
 * @method static Opaque PRISMARINE()
 * @method static Opaque PRISMARINE_BRICKS()
 * @method static Slab PRISMARINE_BRICKS_SLAB()
 * @method static Stair PRISMARINE_BRICKS_STAIRS()
 * @method static Slab PRISMARINE_SLAB()
 * @method static Stair PRISMARINE_STAIRS()
 * @method static Wall PRISMARINE_WALL()
 * @method static Pumpkin PUMPKIN()
 * @method static PumpkinStem PUMPKIN_STEM()
 * @method static Torch PURPLE_TORCH()
 * @method static Opaque PURPUR()
 * @method static SimplePillar PURPUR_PILLAR()
 * @method static Slab PURPUR_SLAB()
 * @method static Stair PURPUR_STAIRS()
 * @method static Opaque QUARTZ()
 * @method static Opaque QUARTZ_BRICKS()
 * @method static SimplePillar QUARTZ_PILLAR()
 * @method static Slab QUARTZ_SLAB()
 * @method static Stair QUARTZ_STAIRS()
 * @method static Rail RAIL()
 * @method static Opaque RAW_COPPER()
 * @method static Opaque RAW_GOLD()
 * @method static Opaque RAW_IRON()
 * @method static Redstone REDSTONE()
 * @method static RedstoneComparator REDSTONE_COMPARATOR()
 * @method static RedstoneLamp REDSTONE_LAMP()
 * @method static RedstoneOre REDSTONE_ORE()
 * @method static RedstoneRepeater REDSTONE_REPEATER()
 * @method static RedstoneTorch REDSTONE_TORCH()
 * @method static RedstoneWire REDSTONE_WIRE()
 * @method static RedMushroom RED_MUSHROOM()
 * @method static RedMushroomBlock RED_MUSHROOM_BLOCK()
 * @method static Opaque RED_NETHER_BRICKS()
 * @method static Slab RED_NETHER_BRICK_SLAB()
 * @method static Stair RED_NETHER_BRICK_STAIRS()
 * @method static Wall RED_NETHER_BRICK_WALL()
 * @method static Sand RED_SAND()
 * @method static Opaque RED_SANDSTONE()
 * @method static Slab RED_SANDSTONE_SLAB()
 * @method static Stair RED_SANDSTONE_STAIRS()
 * @method static Wall RED_SANDSTONE_WALL()
 * @method static Torch RED_TORCH()
 * @method static Flower RED_TULIP()
 * @method static Reserved6 RESERVED6()
 * @method static DoublePlant ROSE_BUSH()
 * @method static Sand SAND()
 * @method static Opaque SANDSTONE()
 * @method static Slab SANDSTONE_SLAB()
 * @method static Stair SANDSTONE_STAIRS()
 * @method static Wall SANDSTONE_WALL()
 * @method static SeaLantern SEA_LANTERN()
 * @method static SeaPickle SEA_PICKLE()
 * @method static Opaque SHROOMLIGHT()
 * @method static ShulkerBox SHULKER_BOX()
 * @method static Slime SLIME()
 * @method static Furnace SMOKER()
 * @method static Opaque SMOOTH_BASALT()
 * @method static Opaque SMOOTH_QUARTZ()
 * @method static Slab SMOOTH_QUARTZ_SLAB()
 * @method static Stair SMOOTH_QUARTZ_STAIRS()
 * @method static Opaque SMOOTH_RED_SANDSTONE()
 * @method static Slab SMOOTH_RED_SANDSTONE_SLAB()
 * @method static Stair SMOOTH_RED_SANDSTONE_STAIRS()
 * @method static Opaque SMOOTH_SANDSTONE()
 * @method static Slab SMOOTH_SANDSTONE_SLAB()
 * @method static Stair SMOOTH_SANDSTONE_STAIRS()
 * @method static Opaque SMOOTH_STONE()
 * @method static Slab SMOOTH_STONE_SLAB()
 * @method static Snow SNOW()
 * @method static SnowLayer SNOW_LAYER()
 * @method static SoulFire SOUL_FIRE()
 * @method static Lantern SOUL_LANTERN()
 * @method static SoulSand SOUL_SAND()
 * @method static Opaque SOUL_SOIL()
 * @method static Torch SOUL_TORCH()
 * @method static Sponge SPONGE()
 * @method static WoodenButton SPRUCE_BUTTON()
 * @method static WoodenDoor SPRUCE_DOOR()
 * @method static WoodenFence SPRUCE_FENCE()
 * @method static FenceGate SPRUCE_FENCE_GATE()
 * @method static Leaves SPRUCE_LEAVES()
 * @method static Wood SPRUCE_LOG()
 * @method static Planks SPRUCE_PLANKS()
 * @method static WoodenPressurePlate SPRUCE_PRESSURE_PLATE()
 * @method static Sapling SPRUCE_SAPLING()
 * @method static FloorSign SPRUCE_SIGN()
 * @method static WoodenSlab SPRUCE_SLAB()
 * @method static WoodenStairs SPRUCE_STAIRS()
 * @method static WoodenTrapdoor SPRUCE_TRAPDOOR()
 * @method static WallSign SPRUCE_WALL_SIGN()
 * @method static Wood SPRUCE_WOOD()
 * @method static StainedHardenedClay STAINED_CLAY()
 * @method static StainedGlass STAINED_GLASS()
 * @method static StainedGlassPane STAINED_GLASS_PANE()
 * @method static StainedHardenedGlass STAINED_HARDENED_GLASS()
 * @method static StainedHardenedGlassPane STAINED_HARDENED_GLASS_PANE()
 * @method static Opaque STONE()
 * @method static Stonecutter STONECUTTER()
 * @method static Opaque STONE_BRICKS()
 * @method static Slab STONE_BRICK_SLAB()
 * @method static Stair STONE_BRICK_STAIRS()
 * @method static Wall STONE_BRICK_WALL()
 * @method static StoneButton STONE_BUTTON()
 * @method static StonePressurePlate STONE_PRESSURE_PLATE()
 * @method static Slab STONE_SLAB()
 * @method static Stair STONE_STAIRS()
 * @method static Sugarcane SUGARCANE()
 * @method static DoublePlant SUNFLOWER()
 * @method static SweetBerryBush SWEET_BERRY_BUSH()
 * @method static TallGrass TALL_GRASS()
 * @method static TNT TNT()
 * @method static Torch TORCH()
 * @method static TrappedChest TRAPPED_CHEST()
 * @method static Tripwire TRIPWIRE()
 * @method static TripwireHook TRIPWIRE_HOOK()
 * @method static Opaque TUFF()
 * @method static UnderwaterTorch UNDERWATER_TORCH()
 * @method static Vine VINES()
 * @method static WallBanner WALL_BANNER()
 * @method static WallCoralFan WALL_CORAL_FAN()
 * @method static WoodenButton WARPED_BUTTON()
 * @method static WoodenDoor WARPED_DOOR()
 * @method static WoodenFence WARPED_FENCE()
 * @method static FenceGate WARPED_FENCE_GATE()
 * @method static Wood WARPED_HYPHAE()
 * @method static Planks WARPED_PLANKS()
 * @method static WoodenPressurePlate WARPED_PRESSURE_PLATE()
 * @method static FloorSign WARPED_SIGN()
 * @method static WoodenSlab WARPED_SLAB()
 * @method static WoodenStairs WARPED_STAIRS()
 * @method static Wood WARPED_STEM()
 * @method static WoodenTrapdoor WARPED_TRAPDOOR()
 * @method static WallSign WARPED_WALL_SIGN()
 * @method static Water WATER()
 * @method static WeightedPressurePlateHeavy WEIGHTED_PRESSURE_PLATE_HEAVY()
 * @method static WeightedPressurePlateLight WEIGHTED_PRESSURE_PLATE_LIGHT()
 * @method static Wheat WHEAT()
 * @method static Flower WHITE_TULIP()
 * @method static Wool WOOL()
 */
final class VanillaBlocks{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Block $block) : void{
		self::_registryRegister($name, $block);
	}

	/**
	 * @return Block[]
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Block[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		$factory = BlockFactory::getInstance();
		self::register("acacia_button", $factory->fromTypeId(Ids::ACACIA_BUTTON));
		self::register("acacia_door", $factory->fromTypeId(Ids::ACACIA_DOOR));
		self::register("acacia_fence", $factory->fromTypeId(Ids::ACACIA_FENCE));
		self::register("acacia_fence_gate", $factory->fromTypeId(Ids::ACACIA_FENCE_GATE));
		self::register("acacia_leaves", $factory->fromTypeId(Ids::ACACIA_LEAVES));
		self::register("acacia_log", $factory->fromTypeId(Ids::ACACIA_LOG));
		self::register("acacia_planks", $factory->fromTypeId(Ids::ACACIA_PLANKS));
		self::register("acacia_pressure_plate", $factory->fromTypeId(Ids::ACACIA_PRESSURE_PLATE));
		self::register("acacia_sapling", $factory->fromTypeId(Ids::ACACIA_SAPLING));
		self::register("acacia_sign", $factory->fromTypeId(Ids::ACACIA_SIGN));
		self::register("acacia_slab", $factory->fromTypeId(Ids::ACACIA_SLAB));
		self::register("acacia_stairs", $factory->fromTypeId(Ids::ACACIA_STAIRS));
		self::register("acacia_trapdoor", $factory->fromTypeId(Ids::ACACIA_TRAPDOOR));
		self::register("acacia_wall_sign", $factory->fromTypeId(Ids::ACACIA_WALL_SIGN));
		self::register("acacia_wood", $factory->fromTypeId(Ids::ACACIA_WOOD));
		self::register("activator_rail", $factory->fromTypeId(Ids::ACTIVATOR_RAIL));
		self::register("air", $factory->fromTypeId(Ids::AIR));
		self::register("all_sided_mushroom_stem", $factory->fromTypeId(Ids::ALL_SIDED_MUSHROOM_STEM));
		self::register("allium", $factory->fromTypeId(Ids::ALLIUM));
		self::register("amethyst", $factory->fromTypeId(Ids::AMETHYST));
		self::register("ancient_debris", $factory->fromTypeId(Ids::ANCIENT_DEBRIS));
		self::register("andesite", $factory->fromTypeId(Ids::ANDESITE));
		self::register("andesite_slab", $factory->fromTypeId(Ids::ANDESITE_SLAB));
		self::register("andesite_stairs", $factory->fromTypeId(Ids::ANDESITE_STAIRS));
		self::register("andesite_wall", $factory->fromTypeId(Ids::ANDESITE_WALL));
		self::register("anvil", $factory->fromTypeId(Ids::ANVIL));
		self::register("azure_bluet", $factory->fromTypeId(Ids::AZURE_BLUET));
		self::register("bamboo", $factory->fromTypeId(Ids::BAMBOO));
		self::register("bamboo_sapling", $factory->fromTypeId(Ids::BAMBOO_SAPLING));
		self::register("banner", $factory->fromTypeId(Ids::BANNER));
		self::register("barrel", $factory->fromTypeId(Ids::BARREL));
		self::register("barrier", $factory->fromTypeId(Ids::BARRIER));
		self::register("basalt", $factory->fromTypeId(Ids::BASALT));
		self::register("beacon", $factory->fromTypeId(Ids::BEACON));
		self::register("bed", $factory->fromTypeId(Ids::BED));
		self::register("bedrock", $factory->fromTypeId(Ids::BEDROCK));
		self::register("beetroots", $factory->fromTypeId(Ids::BEETROOTS));
		self::register("bell", $factory->fromTypeId(Ids::BELL));
		self::register("birch_button", $factory->fromTypeId(Ids::BIRCH_BUTTON));
		self::register("birch_door", $factory->fromTypeId(Ids::BIRCH_DOOR));
		self::register("birch_fence", $factory->fromTypeId(Ids::BIRCH_FENCE));
		self::register("birch_fence_gate", $factory->fromTypeId(Ids::BIRCH_FENCE_GATE));
		self::register("birch_leaves", $factory->fromTypeId(Ids::BIRCH_LEAVES));
		self::register("birch_log", $factory->fromTypeId(Ids::BIRCH_LOG));
		self::register("birch_planks", $factory->fromTypeId(Ids::BIRCH_PLANKS));
		self::register("birch_pressure_plate", $factory->fromTypeId(Ids::BIRCH_PRESSURE_PLATE));
		self::register("birch_sapling", $factory->fromTypeId(Ids::BIRCH_SAPLING));
		self::register("birch_sign", $factory->fromTypeId(Ids::BIRCH_SIGN));
		self::register("birch_slab", $factory->fromTypeId(Ids::BIRCH_SLAB));
		self::register("birch_stairs", $factory->fromTypeId(Ids::BIRCH_STAIRS));
		self::register("birch_trapdoor", $factory->fromTypeId(Ids::BIRCH_TRAPDOOR));
		self::register("birch_wall_sign", $factory->fromTypeId(Ids::BIRCH_WALL_SIGN));
		self::register("birch_wood", $factory->fromTypeId(Ids::BIRCH_WOOD));
		self::register("blackstone", $factory->fromTypeId(Ids::BLACKSTONE));
		self::register("blackstone_slab", $factory->fromTypeId(Ids::BLACKSTONE_SLAB));
		self::register("blackstone_stairs", $factory->fromTypeId(Ids::BLACKSTONE_STAIRS));
		self::register("blackstone_wall", $factory->fromTypeId(Ids::BLACKSTONE_WALL));
		self::register("blast_furnace", $factory->fromTypeId(Ids::BLAST_FURNACE));
		self::register("blue_ice", $factory->fromTypeId(Ids::BLUE_ICE));
		self::register("blue_orchid", $factory->fromTypeId(Ids::BLUE_ORCHID));
		self::register("blue_torch", $factory->fromTypeId(Ids::BLUE_TORCH));
		self::register("bone_block", $factory->fromTypeId(Ids::BONE_BLOCK));
		self::register("bookshelf", $factory->fromTypeId(Ids::BOOKSHELF));
		self::register("brewing_stand", $factory->fromTypeId(Ids::BREWING_STAND));
		self::register("brick_slab", $factory->fromTypeId(Ids::BRICK_SLAB));
		self::register("brick_stairs", $factory->fromTypeId(Ids::BRICK_STAIRS));
		self::register("brick_wall", $factory->fromTypeId(Ids::BRICK_WALL));
		self::register("bricks", $factory->fromTypeId(Ids::BRICKS));
		self::register("brown_mushroom", $factory->fromTypeId(Ids::BROWN_MUSHROOM));
		self::register("brown_mushroom_block", $factory->fromTypeId(Ids::BROWN_MUSHROOM_BLOCK));
		self::register("cactus", $factory->fromTypeId(Ids::CACTUS));
		self::register("cake", $factory->fromTypeId(Ids::CAKE));
		self::register("calcite", $factory->fromTypeId(Ids::CALCITE));
		self::register("carpet", $factory->fromTypeId(Ids::CARPET));
		self::register("carrots", $factory->fromTypeId(Ids::CARROTS));
		self::register("carved_pumpkin", $factory->fromTypeId(Ids::CARVED_PUMPKIN));
		self::register("chemical_heat", $factory->fromTypeId(Ids::CHEMICAL_HEAT));
		self::register("chest", $factory->fromTypeId(Ids::CHEST));
		self::register("chiseled_deepslate", $factory->fromTypeId(Ids::CHISELED_DEEPSLATE));
		self::register("chiseled_nether_bricks", $factory->fromTypeId(Ids::CHISELED_NETHER_BRICKS));
		self::register("chiseled_polished_blackstone", $factory->fromTypeId(Ids::CHISELED_POLISHED_BLACKSTONE));
		self::register("chiseled_quartz", $factory->fromTypeId(Ids::CHISELED_QUARTZ));
		self::register("chiseled_red_sandstone", $factory->fromTypeId(Ids::CHISELED_RED_SANDSTONE));
		self::register("chiseled_sandstone", $factory->fromTypeId(Ids::CHISELED_SANDSTONE));
		self::register("chiseled_stone_bricks", $factory->fromTypeId(Ids::CHISELED_STONE_BRICKS));
		self::register("clay", $factory->fromTypeId(Ids::CLAY));
		self::register("coal", $factory->fromTypeId(Ids::COAL));
		self::register("coal_ore", $factory->fromTypeId(Ids::COAL_ORE));
		self::register("cobbled_deepslate", $factory->fromTypeId(Ids::COBBLED_DEEPSLATE));
		self::register("cobbled_deepslate_slab", $factory->fromTypeId(Ids::COBBLED_DEEPSLATE_SLAB));
		self::register("cobbled_deepslate_stairs", $factory->fromTypeId(Ids::COBBLED_DEEPSLATE_STAIRS));
		self::register("cobbled_deepslate_wall", $factory->fromTypeId(Ids::COBBLED_DEEPSLATE_WALL));
		self::register("cobblestone", $factory->fromTypeId(Ids::COBBLESTONE));
		self::register("cobblestone_slab", $factory->fromTypeId(Ids::COBBLESTONE_SLAB));
		self::register("cobblestone_stairs", $factory->fromTypeId(Ids::COBBLESTONE_STAIRS));
		self::register("cobblestone_wall", $factory->fromTypeId(Ids::COBBLESTONE_WALL));
		self::register("cobweb", $factory->fromTypeId(Ids::COBWEB));
		self::register("cocoa_pod", $factory->fromTypeId(Ids::COCOA_POD));
		self::register("compound_creator", $factory->fromTypeId(Ids::COMPOUND_CREATOR));
		self::register("concrete", $factory->fromTypeId(Ids::CONCRETE));
		self::register("concrete_powder", $factory->fromTypeId(Ids::CONCRETE_POWDER));
		self::register("copper_ore", $factory->fromTypeId(Ids::COPPER_ORE));
		self::register("coral", $factory->fromTypeId(Ids::CORAL));
		self::register("coral_block", $factory->fromTypeId(Ids::CORAL_BLOCK));
		self::register("coral_fan", $factory->fromTypeId(Ids::CORAL_FAN));
		self::register("cornflower", $factory->fromTypeId(Ids::CORNFLOWER));
		self::register("cracked_deepslate_bricks", $factory->fromTypeId(Ids::CRACKED_DEEPSLATE_BRICKS));
		self::register("cracked_deepslate_tiles", $factory->fromTypeId(Ids::CRACKED_DEEPSLATE_TILES));
		self::register("cracked_nether_bricks", $factory->fromTypeId(Ids::CRACKED_NETHER_BRICKS));
		self::register("cracked_polished_blackstone_bricks", $factory->fromTypeId(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS));
		self::register("cracked_stone_bricks", $factory->fromTypeId(Ids::CRACKED_STONE_BRICKS));
		self::register("crafting_table", $factory->fromTypeId(Ids::CRAFTING_TABLE));
		self::register("crimson_button", $factory->fromTypeId(Ids::CRIMSON_BUTTON));
		self::register("crimson_door", $factory->fromTypeId(Ids::CRIMSON_DOOR));
		self::register("crimson_fence", $factory->fromTypeId(Ids::CRIMSON_FENCE));
		self::register("crimson_fence_gate", $factory->fromTypeId(Ids::CRIMSON_FENCE_GATE));
		self::register("crimson_hyphae", $factory->fromTypeId(Ids::CRIMSON_HYPHAE));
		self::register("crimson_planks", $factory->fromTypeId(Ids::CRIMSON_PLANKS));
		self::register("crimson_pressure_plate", $factory->fromTypeId(Ids::CRIMSON_PRESSURE_PLATE));
		self::register("crimson_sign", $factory->fromTypeId(Ids::CRIMSON_SIGN));
		self::register("crimson_slab", $factory->fromTypeId(Ids::CRIMSON_SLAB));
		self::register("crimson_stairs", $factory->fromTypeId(Ids::CRIMSON_STAIRS));
		self::register("crimson_stem", $factory->fromTypeId(Ids::CRIMSON_STEM));
		self::register("crimson_trapdoor", $factory->fromTypeId(Ids::CRIMSON_TRAPDOOR));
		self::register("crimson_wall_sign", $factory->fromTypeId(Ids::CRIMSON_WALL_SIGN));
		self::register("cut_red_sandstone", $factory->fromTypeId(Ids::CUT_RED_SANDSTONE));
		self::register("cut_red_sandstone_slab", $factory->fromTypeId(Ids::CUT_RED_SANDSTONE_SLAB));
		self::register("cut_sandstone", $factory->fromTypeId(Ids::CUT_SANDSTONE));
		self::register("cut_sandstone_slab", $factory->fromTypeId(Ids::CUT_SANDSTONE_SLAB));
		self::register("dandelion", $factory->fromTypeId(Ids::DANDELION));
		self::register("dark_oak_button", $factory->fromTypeId(Ids::DARK_OAK_BUTTON));
		self::register("dark_oak_door", $factory->fromTypeId(Ids::DARK_OAK_DOOR));
		self::register("dark_oak_fence", $factory->fromTypeId(Ids::DARK_OAK_FENCE));
		self::register("dark_oak_fence_gate", $factory->fromTypeId(Ids::DARK_OAK_FENCE_GATE));
		self::register("dark_oak_leaves", $factory->fromTypeId(Ids::DARK_OAK_LEAVES));
		self::register("dark_oak_log", $factory->fromTypeId(Ids::DARK_OAK_LOG));
		self::register("dark_oak_planks", $factory->fromTypeId(Ids::DARK_OAK_PLANKS));
		self::register("dark_oak_pressure_plate", $factory->fromTypeId(Ids::DARK_OAK_PRESSURE_PLATE));
		self::register("dark_oak_sapling", $factory->fromTypeId(Ids::DARK_OAK_SAPLING));
		self::register("dark_oak_sign", $factory->fromTypeId(Ids::DARK_OAK_SIGN));
		self::register("dark_oak_slab", $factory->fromTypeId(Ids::DARK_OAK_SLAB));
		self::register("dark_oak_stairs", $factory->fromTypeId(Ids::DARK_OAK_STAIRS));
		self::register("dark_oak_trapdoor", $factory->fromTypeId(Ids::DARK_OAK_TRAPDOOR));
		self::register("dark_oak_wall_sign", $factory->fromTypeId(Ids::DARK_OAK_WALL_SIGN));
		self::register("dark_oak_wood", $factory->fromTypeId(Ids::DARK_OAK_WOOD));
		self::register("dark_prismarine", $factory->fromTypeId(Ids::DARK_PRISMARINE));
		self::register("dark_prismarine_slab", $factory->fromTypeId(Ids::DARK_PRISMARINE_SLAB));
		self::register("dark_prismarine_stairs", $factory->fromTypeId(Ids::DARK_PRISMARINE_STAIRS));
		self::register("daylight_sensor", $factory->fromTypeId(Ids::DAYLIGHT_SENSOR));
		self::register("dead_bush", $factory->fromTypeId(Ids::DEAD_BUSH));
		self::register("deepslate", $factory->fromTypeId(Ids::DEEPSLATE));
		self::register("deepslate_brick_slab", $factory->fromTypeId(Ids::DEEPSLATE_BRICK_SLAB));
		self::register("deepslate_brick_stairs", $factory->fromTypeId(Ids::DEEPSLATE_BRICK_STAIRS));
		self::register("deepslate_brick_wall", $factory->fromTypeId(Ids::DEEPSLATE_BRICK_WALL));
		self::register("deepslate_bricks", $factory->fromTypeId(Ids::DEEPSLATE_BRICKS));
		self::register("deepslate_coal_ore", $factory->fromTypeId(Ids::DEEPSLATE_COAL_ORE));
		self::register("deepslate_copper_ore", $factory->fromTypeId(Ids::DEEPSLATE_COPPER_ORE));
		self::register("deepslate_diamond_ore", $factory->fromTypeId(Ids::DEEPSLATE_DIAMOND_ORE));
		self::register("deepslate_emerald_ore", $factory->fromTypeId(Ids::DEEPSLATE_EMERALD_ORE));
		self::register("deepslate_gold_ore", $factory->fromTypeId(Ids::DEEPSLATE_GOLD_ORE));
		self::register("deepslate_iron_ore", $factory->fromTypeId(Ids::DEEPSLATE_IRON_ORE));
		self::register("deepslate_lapis_lazuli_ore", $factory->fromTypeId(Ids::DEEPSLATE_LAPIS_LAZULI_ORE));
		self::register("deepslate_redstone_ore", $factory->fromTypeId(Ids::DEEPSLATE_REDSTONE_ORE));
		self::register("deepslate_tile_slab", $factory->fromTypeId(Ids::DEEPSLATE_TILE_SLAB));
		self::register("deepslate_tile_stairs", $factory->fromTypeId(Ids::DEEPSLATE_TILE_STAIRS));
		self::register("deepslate_tile_wall", $factory->fromTypeId(Ids::DEEPSLATE_TILE_WALL));
		self::register("deepslate_tiles", $factory->fromTypeId(Ids::DEEPSLATE_TILES));
		self::register("detector_rail", $factory->fromTypeId(Ids::DETECTOR_RAIL));
		self::register("diamond", $factory->fromTypeId(Ids::DIAMOND));
		self::register("diamond_ore", $factory->fromTypeId(Ids::DIAMOND_ORE));
		self::register("diorite", $factory->fromTypeId(Ids::DIORITE));
		self::register("diorite_slab", $factory->fromTypeId(Ids::DIORITE_SLAB));
		self::register("diorite_stairs", $factory->fromTypeId(Ids::DIORITE_STAIRS));
		self::register("diorite_wall", $factory->fromTypeId(Ids::DIORITE_WALL));
		self::register("dirt", $factory->fromTypeId(Ids::DIRT));
		self::register("double_tallgrass", $factory->fromTypeId(Ids::DOUBLE_TALLGRASS));
		self::register("dragon_egg", $factory->fromTypeId(Ids::DRAGON_EGG));
		self::register("dried_kelp", $factory->fromTypeId(Ids::DRIED_KELP));
		self::register("dyed_shulker_box", $factory->fromTypeId(Ids::DYED_SHULKER_BOX));
		self::register("element_actinium", $factory->fromTypeId(Ids::ELEMENT_ACTINIUM));
		self::register("element_aluminum", $factory->fromTypeId(Ids::ELEMENT_ALUMINUM));
		self::register("element_americium", $factory->fromTypeId(Ids::ELEMENT_AMERICIUM));
		self::register("element_antimony", $factory->fromTypeId(Ids::ELEMENT_ANTIMONY));
		self::register("element_argon", $factory->fromTypeId(Ids::ELEMENT_ARGON));
		self::register("element_arsenic", $factory->fromTypeId(Ids::ELEMENT_ARSENIC));
		self::register("element_astatine", $factory->fromTypeId(Ids::ELEMENT_ASTATINE));
		self::register("element_barium", $factory->fromTypeId(Ids::ELEMENT_BARIUM));
		self::register("element_berkelium", $factory->fromTypeId(Ids::ELEMENT_BERKELIUM));
		self::register("element_beryllium", $factory->fromTypeId(Ids::ELEMENT_BERYLLIUM));
		self::register("element_bismuth", $factory->fromTypeId(Ids::ELEMENT_BISMUTH));
		self::register("element_bohrium", $factory->fromTypeId(Ids::ELEMENT_BOHRIUM));
		self::register("element_boron", $factory->fromTypeId(Ids::ELEMENT_BORON));
		self::register("element_bromine", $factory->fromTypeId(Ids::ELEMENT_BROMINE));
		self::register("element_cadmium", $factory->fromTypeId(Ids::ELEMENT_CADMIUM));
		self::register("element_calcium", $factory->fromTypeId(Ids::ELEMENT_CALCIUM));
		self::register("element_californium", $factory->fromTypeId(Ids::ELEMENT_CALIFORNIUM));
		self::register("element_carbon", $factory->fromTypeId(Ids::ELEMENT_CARBON));
		self::register("element_cerium", $factory->fromTypeId(Ids::ELEMENT_CERIUM));
		self::register("element_cesium", $factory->fromTypeId(Ids::ELEMENT_CESIUM));
		self::register("element_chlorine", $factory->fromTypeId(Ids::ELEMENT_CHLORINE));
		self::register("element_chromium", $factory->fromTypeId(Ids::ELEMENT_CHROMIUM));
		self::register("element_cobalt", $factory->fromTypeId(Ids::ELEMENT_COBALT));
		self::register("element_constructor", $factory->fromTypeId(Ids::ELEMENT_CONSTRUCTOR));
		self::register("element_copernicium", $factory->fromTypeId(Ids::ELEMENT_COPERNICIUM));
		self::register("element_copper", $factory->fromTypeId(Ids::ELEMENT_COPPER));
		self::register("element_curium", $factory->fromTypeId(Ids::ELEMENT_CURIUM));
		self::register("element_darmstadtium", $factory->fromTypeId(Ids::ELEMENT_DARMSTADTIUM));
		self::register("element_dubnium", $factory->fromTypeId(Ids::ELEMENT_DUBNIUM));
		self::register("element_dysprosium", $factory->fromTypeId(Ids::ELEMENT_DYSPROSIUM));
		self::register("element_einsteinium", $factory->fromTypeId(Ids::ELEMENT_EINSTEINIUM));
		self::register("element_erbium", $factory->fromTypeId(Ids::ELEMENT_ERBIUM));
		self::register("element_europium", $factory->fromTypeId(Ids::ELEMENT_EUROPIUM));
		self::register("element_fermium", $factory->fromTypeId(Ids::ELEMENT_FERMIUM));
		self::register("element_flerovium", $factory->fromTypeId(Ids::ELEMENT_FLEROVIUM));
		self::register("element_fluorine", $factory->fromTypeId(Ids::ELEMENT_FLUORINE));
		self::register("element_francium", $factory->fromTypeId(Ids::ELEMENT_FRANCIUM));
		self::register("element_gadolinium", $factory->fromTypeId(Ids::ELEMENT_GADOLINIUM));
		self::register("element_gallium", $factory->fromTypeId(Ids::ELEMENT_GALLIUM));
		self::register("element_germanium", $factory->fromTypeId(Ids::ELEMENT_GERMANIUM));
		self::register("element_gold", $factory->fromTypeId(Ids::ELEMENT_GOLD));
		self::register("element_hafnium", $factory->fromTypeId(Ids::ELEMENT_HAFNIUM));
		self::register("element_hassium", $factory->fromTypeId(Ids::ELEMENT_HASSIUM));
		self::register("element_helium", $factory->fromTypeId(Ids::ELEMENT_HELIUM));
		self::register("element_holmium", $factory->fromTypeId(Ids::ELEMENT_HOLMIUM));
		self::register("element_hydrogen", $factory->fromTypeId(Ids::ELEMENT_HYDROGEN));
		self::register("element_indium", $factory->fromTypeId(Ids::ELEMENT_INDIUM));
		self::register("element_iodine", $factory->fromTypeId(Ids::ELEMENT_IODINE));
		self::register("element_iridium", $factory->fromTypeId(Ids::ELEMENT_IRIDIUM));
		self::register("element_iron", $factory->fromTypeId(Ids::ELEMENT_IRON));
		self::register("element_krypton", $factory->fromTypeId(Ids::ELEMENT_KRYPTON));
		self::register("element_lanthanum", $factory->fromTypeId(Ids::ELEMENT_LANTHANUM));
		self::register("element_lawrencium", $factory->fromTypeId(Ids::ELEMENT_LAWRENCIUM));
		self::register("element_lead", $factory->fromTypeId(Ids::ELEMENT_LEAD));
		self::register("element_lithium", $factory->fromTypeId(Ids::ELEMENT_LITHIUM));
		self::register("element_livermorium", $factory->fromTypeId(Ids::ELEMENT_LIVERMORIUM));
		self::register("element_lutetium", $factory->fromTypeId(Ids::ELEMENT_LUTETIUM));
		self::register("element_magnesium", $factory->fromTypeId(Ids::ELEMENT_MAGNESIUM));
		self::register("element_manganese", $factory->fromTypeId(Ids::ELEMENT_MANGANESE));
		self::register("element_meitnerium", $factory->fromTypeId(Ids::ELEMENT_MEITNERIUM));
		self::register("element_mendelevium", $factory->fromTypeId(Ids::ELEMENT_MENDELEVIUM));
		self::register("element_mercury", $factory->fromTypeId(Ids::ELEMENT_MERCURY));
		self::register("element_molybdenum", $factory->fromTypeId(Ids::ELEMENT_MOLYBDENUM));
		self::register("element_moscovium", $factory->fromTypeId(Ids::ELEMENT_MOSCOVIUM));
		self::register("element_neodymium", $factory->fromTypeId(Ids::ELEMENT_NEODYMIUM));
		self::register("element_neon", $factory->fromTypeId(Ids::ELEMENT_NEON));
		self::register("element_neptunium", $factory->fromTypeId(Ids::ELEMENT_NEPTUNIUM));
		self::register("element_nickel", $factory->fromTypeId(Ids::ELEMENT_NICKEL));
		self::register("element_nihonium", $factory->fromTypeId(Ids::ELEMENT_NIHONIUM));
		self::register("element_niobium", $factory->fromTypeId(Ids::ELEMENT_NIOBIUM));
		self::register("element_nitrogen", $factory->fromTypeId(Ids::ELEMENT_NITROGEN));
		self::register("element_nobelium", $factory->fromTypeId(Ids::ELEMENT_NOBELIUM));
		self::register("element_oganesson", $factory->fromTypeId(Ids::ELEMENT_OGANESSON));
		self::register("element_osmium", $factory->fromTypeId(Ids::ELEMENT_OSMIUM));
		self::register("element_oxygen", $factory->fromTypeId(Ids::ELEMENT_OXYGEN));
		self::register("element_palladium", $factory->fromTypeId(Ids::ELEMENT_PALLADIUM));
		self::register("element_phosphorus", $factory->fromTypeId(Ids::ELEMENT_PHOSPHORUS));
		self::register("element_platinum", $factory->fromTypeId(Ids::ELEMENT_PLATINUM));
		self::register("element_plutonium", $factory->fromTypeId(Ids::ELEMENT_PLUTONIUM));
		self::register("element_polonium", $factory->fromTypeId(Ids::ELEMENT_POLONIUM));
		self::register("element_potassium", $factory->fromTypeId(Ids::ELEMENT_POTASSIUM));
		self::register("element_praseodymium", $factory->fromTypeId(Ids::ELEMENT_PRASEODYMIUM));
		self::register("element_promethium", $factory->fromTypeId(Ids::ELEMENT_PROMETHIUM));
		self::register("element_protactinium", $factory->fromTypeId(Ids::ELEMENT_PROTACTINIUM));
		self::register("element_radium", $factory->fromTypeId(Ids::ELEMENT_RADIUM));
		self::register("element_radon", $factory->fromTypeId(Ids::ELEMENT_RADON));
		self::register("element_rhenium", $factory->fromTypeId(Ids::ELEMENT_RHENIUM));
		self::register("element_rhodium", $factory->fromTypeId(Ids::ELEMENT_RHODIUM));
		self::register("element_roentgenium", $factory->fromTypeId(Ids::ELEMENT_ROENTGENIUM));
		self::register("element_rubidium", $factory->fromTypeId(Ids::ELEMENT_RUBIDIUM));
		self::register("element_ruthenium", $factory->fromTypeId(Ids::ELEMENT_RUTHENIUM));
		self::register("element_rutherfordium", $factory->fromTypeId(Ids::ELEMENT_RUTHERFORDIUM));
		self::register("element_samarium", $factory->fromTypeId(Ids::ELEMENT_SAMARIUM));
		self::register("element_scandium", $factory->fromTypeId(Ids::ELEMENT_SCANDIUM));
		self::register("element_seaborgium", $factory->fromTypeId(Ids::ELEMENT_SEABORGIUM));
		self::register("element_selenium", $factory->fromTypeId(Ids::ELEMENT_SELENIUM));
		self::register("element_silicon", $factory->fromTypeId(Ids::ELEMENT_SILICON));
		self::register("element_silver", $factory->fromTypeId(Ids::ELEMENT_SILVER));
		self::register("element_sodium", $factory->fromTypeId(Ids::ELEMENT_SODIUM));
		self::register("element_strontium", $factory->fromTypeId(Ids::ELEMENT_STRONTIUM));
		self::register("element_sulfur", $factory->fromTypeId(Ids::ELEMENT_SULFUR));
		self::register("element_tantalum", $factory->fromTypeId(Ids::ELEMENT_TANTALUM));
		self::register("element_technetium", $factory->fromTypeId(Ids::ELEMENT_TECHNETIUM));
		self::register("element_tellurium", $factory->fromTypeId(Ids::ELEMENT_TELLURIUM));
		self::register("element_tennessine", $factory->fromTypeId(Ids::ELEMENT_TENNESSINE));
		self::register("element_terbium", $factory->fromTypeId(Ids::ELEMENT_TERBIUM));
		self::register("element_thallium", $factory->fromTypeId(Ids::ELEMENT_THALLIUM));
		self::register("element_thorium", $factory->fromTypeId(Ids::ELEMENT_THORIUM));
		self::register("element_thulium", $factory->fromTypeId(Ids::ELEMENT_THULIUM));
		self::register("element_tin", $factory->fromTypeId(Ids::ELEMENT_TIN));
		self::register("element_titanium", $factory->fromTypeId(Ids::ELEMENT_TITANIUM));
		self::register("element_tungsten", $factory->fromTypeId(Ids::ELEMENT_TUNGSTEN));
		self::register("element_uranium", $factory->fromTypeId(Ids::ELEMENT_URANIUM));
		self::register("element_vanadium", $factory->fromTypeId(Ids::ELEMENT_VANADIUM));
		self::register("element_xenon", $factory->fromTypeId(Ids::ELEMENT_XENON));
		self::register("element_ytterbium", $factory->fromTypeId(Ids::ELEMENT_YTTERBIUM));
		self::register("element_yttrium", $factory->fromTypeId(Ids::ELEMENT_YTTRIUM));
		self::register("element_zero", $factory->fromTypeId(Ids::ELEMENT_ZERO));
		self::register("element_zinc", $factory->fromTypeId(Ids::ELEMENT_ZINC));
		self::register("element_zirconium", $factory->fromTypeId(Ids::ELEMENT_ZIRCONIUM));
		self::register("emerald", $factory->fromTypeId(Ids::EMERALD));
		self::register("emerald_ore", $factory->fromTypeId(Ids::EMERALD_ORE));
		self::register("enchanting_table", $factory->fromTypeId(Ids::ENCHANTING_TABLE));
		self::register("end_portal_frame", $factory->fromTypeId(Ids::END_PORTAL_FRAME));
		self::register("end_rod", $factory->fromTypeId(Ids::END_ROD));
		self::register("end_stone", $factory->fromTypeId(Ids::END_STONE));
		self::register("end_stone_brick_slab", $factory->fromTypeId(Ids::END_STONE_BRICK_SLAB));
		self::register("end_stone_brick_stairs", $factory->fromTypeId(Ids::END_STONE_BRICK_STAIRS));
		self::register("end_stone_brick_wall", $factory->fromTypeId(Ids::END_STONE_BRICK_WALL));
		self::register("end_stone_bricks", $factory->fromTypeId(Ids::END_STONE_BRICKS));
		self::register("ender_chest", $factory->fromTypeId(Ids::ENDER_CHEST));
		self::register("fake_wooden_slab", $factory->fromTypeId(Ids::FAKE_WOODEN_SLAB));
		self::register("farmland", $factory->fromTypeId(Ids::FARMLAND));
		self::register("fern", $factory->fromTypeId(Ids::FERN));
		self::register("fire", $factory->fromTypeId(Ids::FIRE));
		self::register("fletching_table", $factory->fromTypeId(Ids::FLETCHING_TABLE));
		self::register("flower_pot", $factory->fromTypeId(Ids::FLOWER_POT));
		self::register("frosted_ice", $factory->fromTypeId(Ids::FROSTED_ICE));
		self::register("furnace", $factory->fromTypeId(Ids::FURNACE));
		self::register("glass", $factory->fromTypeId(Ids::GLASS));
		self::register("glass_pane", $factory->fromTypeId(Ids::GLASS_PANE));
		self::register("glazed_terracotta", $factory->fromTypeId(Ids::GLAZED_TERRACOTTA));
		self::register("glowing_obsidian", $factory->fromTypeId(Ids::GLOWING_OBSIDIAN));
		self::register("glowstone", $factory->fromTypeId(Ids::GLOWSTONE));
		self::register("gold", $factory->fromTypeId(Ids::GOLD));
		self::register("gold_ore", $factory->fromTypeId(Ids::GOLD_ORE));
		self::register("granite", $factory->fromTypeId(Ids::GRANITE));
		self::register("granite_slab", $factory->fromTypeId(Ids::GRANITE_SLAB));
		self::register("granite_stairs", $factory->fromTypeId(Ids::GRANITE_STAIRS));
		self::register("granite_wall", $factory->fromTypeId(Ids::GRANITE_WALL));
		self::register("grass", $factory->fromTypeId(Ids::GRASS));
		self::register("grass_path", $factory->fromTypeId(Ids::GRASS_PATH));
		self::register("gravel", $factory->fromTypeId(Ids::GRAVEL));
		self::register("green_torch", $factory->fromTypeId(Ids::GREEN_TORCH));
		self::register("hardened_clay", $factory->fromTypeId(Ids::HARDENED_CLAY));
		self::register("hardened_glass", $factory->fromTypeId(Ids::HARDENED_GLASS));
		self::register("hardened_glass_pane", $factory->fromTypeId(Ids::HARDENED_GLASS_PANE));
		self::register("hay_bale", $factory->fromTypeId(Ids::HAY_BALE));
		self::register("honeycomb", $factory->fromTypeId(Ids::HONEYCOMB));
		self::register("hopper", $factory->fromTypeId(Ids::HOPPER));
		self::register("ice", $factory->fromTypeId(Ids::ICE));
		self::register("infested_chiseled_stone_brick", $factory->fromTypeId(Ids::INFESTED_CHISELED_STONE_BRICK));
		self::register("infested_cobblestone", $factory->fromTypeId(Ids::INFESTED_COBBLESTONE));
		self::register("infested_cracked_stone_brick", $factory->fromTypeId(Ids::INFESTED_CRACKED_STONE_BRICK));
		self::register("infested_mossy_stone_brick", $factory->fromTypeId(Ids::INFESTED_MOSSY_STONE_BRICK));
		self::register("infested_stone", $factory->fromTypeId(Ids::INFESTED_STONE));
		self::register("infested_stone_brick", $factory->fromTypeId(Ids::INFESTED_STONE_BRICK));
		self::register("info_update", $factory->fromTypeId(Ids::INFO_UPDATE));
		self::register("info_update2", $factory->fromTypeId(Ids::INFO_UPDATE2));
		self::register("invisible_bedrock", $factory->fromTypeId(Ids::INVISIBLE_BEDROCK));
		self::register("iron", $factory->fromTypeId(Ids::IRON));
		self::register("iron_bars", $factory->fromTypeId(Ids::IRON_BARS));
		self::register("iron_door", $factory->fromTypeId(Ids::IRON_DOOR));
		self::register("iron_ore", $factory->fromTypeId(Ids::IRON_ORE));
		self::register("iron_trapdoor", $factory->fromTypeId(Ids::IRON_TRAPDOOR));
		self::register("item_frame", $factory->fromTypeId(Ids::ITEM_FRAME));
		self::register("jukebox", $factory->fromTypeId(Ids::JUKEBOX));
		self::register("jungle_button", $factory->fromTypeId(Ids::JUNGLE_BUTTON));
		self::register("jungle_door", $factory->fromTypeId(Ids::JUNGLE_DOOR));
		self::register("jungle_fence", $factory->fromTypeId(Ids::JUNGLE_FENCE));
		self::register("jungle_fence_gate", $factory->fromTypeId(Ids::JUNGLE_FENCE_GATE));
		self::register("jungle_leaves", $factory->fromTypeId(Ids::JUNGLE_LEAVES));
		self::register("jungle_log", $factory->fromTypeId(Ids::JUNGLE_LOG));
		self::register("jungle_planks", $factory->fromTypeId(Ids::JUNGLE_PLANKS));
		self::register("jungle_pressure_plate", $factory->fromTypeId(Ids::JUNGLE_PRESSURE_PLATE));
		self::register("jungle_sapling", $factory->fromTypeId(Ids::JUNGLE_SAPLING));
		self::register("jungle_sign", $factory->fromTypeId(Ids::JUNGLE_SIGN));
		self::register("jungle_slab", $factory->fromTypeId(Ids::JUNGLE_SLAB));
		self::register("jungle_stairs", $factory->fromTypeId(Ids::JUNGLE_STAIRS));
		self::register("jungle_trapdoor", $factory->fromTypeId(Ids::JUNGLE_TRAPDOOR));
		self::register("jungle_wall_sign", $factory->fromTypeId(Ids::JUNGLE_WALL_SIGN));
		self::register("jungle_wood", $factory->fromTypeId(Ids::JUNGLE_WOOD));
		self::register("lab_table", $factory->fromTypeId(Ids::LAB_TABLE));
		self::register("ladder", $factory->fromTypeId(Ids::LADDER));
		self::register("lantern", $factory->fromTypeId(Ids::LANTERN));
		self::register("lapis_lazuli", $factory->fromTypeId(Ids::LAPIS_LAZULI));
		self::register("lapis_lazuli_ore", $factory->fromTypeId(Ids::LAPIS_LAZULI_ORE));
		self::register("large_fern", $factory->fromTypeId(Ids::LARGE_FERN));
		self::register("lava", $factory->fromTypeId(Ids::LAVA));
		self::register("lectern", $factory->fromTypeId(Ids::LECTERN));
		self::register("legacy_stonecutter", $factory->fromTypeId(Ids::LEGACY_STONECUTTER));
		self::register("lever", $factory->fromTypeId(Ids::LEVER));
		self::register("light", $factory->fromTypeId(Ids::LIGHT));
		self::register("lilac", $factory->fromTypeId(Ids::LILAC));
		self::register("lily_of_the_valley", $factory->fromTypeId(Ids::LILY_OF_THE_VALLEY));
		self::register("lily_pad", $factory->fromTypeId(Ids::LILY_PAD));
		self::register("lit_pumpkin", $factory->fromTypeId(Ids::LIT_PUMPKIN));
		self::register("loom", $factory->fromTypeId(Ids::LOOM));
		self::register("magma", $factory->fromTypeId(Ids::MAGMA));
		self::register("mangrove_button", $factory->fromTypeId(Ids::MANGROVE_BUTTON));
		self::register("mangrove_door", $factory->fromTypeId(Ids::MANGROVE_DOOR));
		self::register("mangrove_fence", $factory->fromTypeId(Ids::MANGROVE_FENCE));
		self::register("mangrove_fence_gate", $factory->fromTypeId(Ids::MANGROVE_FENCE_GATE));
		self::register("mangrove_log", $factory->fromTypeId(Ids::MANGROVE_LOG));
		self::register("mangrove_planks", $factory->fromTypeId(Ids::MANGROVE_PLANKS));
		self::register("mangrove_pressure_plate", $factory->fromTypeId(Ids::MANGROVE_PRESSURE_PLATE));
		self::register("mangrove_sign", $factory->fromTypeId(Ids::MANGROVE_SIGN));
		self::register("mangrove_slab", $factory->fromTypeId(Ids::MANGROVE_SLAB));
		self::register("mangrove_stairs", $factory->fromTypeId(Ids::MANGROVE_STAIRS));
		self::register("mangrove_trapdoor", $factory->fromTypeId(Ids::MANGROVE_TRAPDOOR));
		self::register("mangrove_wall_sign", $factory->fromTypeId(Ids::MANGROVE_WALL_SIGN));
		self::register("mangrove_wood", $factory->fromTypeId(Ids::MANGROVE_WOOD));
		self::register("material_reducer", $factory->fromTypeId(Ids::MATERIAL_REDUCER));
		self::register("melon", $factory->fromTypeId(Ids::MELON));
		self::register("melon_stem", $factory->fromTypeId(Ids::MELON_STEM));
		self::register("mob_head", $factory->fromTypeId(Ids::MOB_HEAD));
		self::register("monster_spawner", $factory->fromTypeId(Ids::MONSTER_SPAWNER));
		self::register("mossy_cobblestone", $factory->fromTypeId(Ids::MOSSY_COBBLESTONE));
		self::register("mossy_cobblestone_slab", $factory->fromTypeId(Ids::MOSSY_COBBLESTONE_SLAB));
		self::register("mossy_cobblestone_stairs", $factory->fromTypeId(Ids::MOSSY_COBBLESTONE_STAIRS));
		self::register("mossy_cobblestone_wall", $factory->fromTypeId(Ids::MOSSY_COBBLESTONE_WALL));
		self::register("mossy_stone_brick_slab", $factory->fromTypeId(Ids::MOSSY_STONE_BRICK_SLAB));
		self::register("mossy_stone_brick_stairs", $factory->fromTypeId(Ids::MOSSY_STONE_BRICK_STAIRS));
		self::register("mossy_stone_brick_wall", $factory->fromTypeId(Ids::MOSSY_STONE_BRICK_WALL));
		self::register("mossy_stone_bricks", $factory->fromTypeId(Ids::MOSSY_STONE_BRICKS));
		self::register("mud_brick_slab", $factory->fromTypeId(Ids::MUD_BRICK_SLAB));
		self::register("mud_brick_stairs", $factory->fromTypeId(Ids::MUD_BRICK_STAIRS));
		self::register("mud_brick_wall", $factory->fromTypeId(Ids::MUD_BRICK_WALL));
		self::register("mud_bricks", $factory->fromTypeId(Ids::MUD_BRICKS));
		self::register("mushroom_stem", $factory->fromTypeId(Ids::MUSHROOM_STEM));
		self::register("mycelium", $factory->fromTypeId(Ids::MYCELIUM));
		self::register("nether_brick_fence", $factory->fromTypeId(Ids::NETHER_BRICK_FENCE));
		self::register("nether_brick_slab", $factory->fromTypeId(Ids::NETHER_BRICK_SLAB));
		self::register("nether_brick_stairs", $factory->fromTypeId(Ids::NETHER_BRICK_STAIRS));
		self::register("nether_brick_wall", $factory->fromTypeId(Ids::NETHER_BRICK_WALL));
		self::register("nether_bricks", $factory->fromTypeId(Ids::NETHER_BRICKS));
		self::register("nether_gold_ore", $factory->fromTypeId(Ids::NETHER_GOLD_ORE));
		self::register("nether_portal", $factory->fromTypeId(Ids::NETHER_PORTAL));
		self::register("nether_quartz_ore", $factory->fromTypeId(Ids::NETHER_QUARTZ_ORE));
		self::register("nether_reactor_core", $factory->fromTypeId(Ids::NETHER_REACTOR_CORE));
		self::register("nether_wart", $factory->fromTypeId(Ids::NETHER_WART));
		self::register("nether_wart_block", $factory->fromTypeId(Ids::NETHER_WART_BLOCK));
		self::register("netherrack", $factory->fromTypeId(Ids::NETHERRACK));
		self::register("note_block", $factory->fromTypeId(Ids::NOTE_BLOCK));
		self::register("oak_button", $factory->fromTypeId(Ids::OAK_BUTTON));
		self::register("oak_door", $factory->fromTypeId(Ids::OAK_DOOR));
		self::register("oak_fence", $factory->fromTypeId(Ids::OAK_FENCE));
		self::register("oak_fence_gate", $factory->fromTypeId(Ids::OAK_FENCE_GATE));
		self::register("oak_leaves", $factory->fromTypeId(Ids::OAK_LEAVES));
		self::register("oak_log", $factory->fromTypeId(Ids::OAK_LOG));
		self::register("oak_planks", $factory->fromTypeId(Ids::OAK_PLANKS));
		self::register("oak_pressure_plate", $factory->fromTypeId(Ids::OAK_PRESSURE_PLATE));
		self::register("oak_sapling", $factory->fromTypeId(Ids::OAK_SAPLING));
		self::register("oak_sign", $factory->fromTypeId(Ids::OAK_SIGN));
		self::register("oak_slab", $factory->fromTypeId(Ids::OAK_SLAB));
		self::register("oak_stairs", $factory->fromTypeId(Ids::OAK_STAIRS));
		self::register("oak_trapdoor", $factory->fromTypeId(Ids::OAK_TRAPDOOR));
		self::register("oak_wall_sign", $factory->fromTypeId(Ids::OAK_WALL_SIGN));
		self::register("oak_wood", $factory->fromTypeId(Ids::OAK_WOOD));
		self::register("obsidian", $factory->fromTypeId(Ids::OBSIDIAN));
		self::register("orange_tulip", $factory->fromTypeId(Ids::ORANGE_TULIP));
		self::register("oxeye_daisy", $factory->fromTypeId(Ids::OXEYE_DAISY));
		self::register("packed_ice", $factory->fromTypeId(Ids::PACKED_ICE));
		self::register("peony", $factory->fromTypeId(Ids::PEONY));
		self::register("pink_tulip", $factory->fromTypeId(Ids::PINK_TULIP));
		self::register("podzol", $factory->fromTypeId(Ids::PODZOL));
		self::register("polished_andesite", $factory->fromTypeId(Ids::POLISHED_ANDESITE));
		self::register("polished_andesite_slab", $factory->fromTypeId(Ids::POLISHED_ANDESITE_SLAB));
		self::register("polished_andesite_stairs", $factory->fromTypeId(Ids::POLISHED_ANDESITE_STAIRS));
		self::register("polished_basalt", $factory->fromTypeId(Ids::POLISHED_BASALT));
		self::register("polished_blackstone", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE));
		self::register("polished_blackstone_brick_slab", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_BRICK_SLAB));
		self::register("polished_blackstone_brick_stairs", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS));
		self::register("polished_blackstone_brick_wall", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_BRICK_WALL));
		self::register("polished_blackstone_bricks", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_BRICKS));
		self::register("polished_blackstone_button", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_BUTTON));
		self::register("polished_blackstone_pressure_plate", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE));
		self::register("polished_blackstone_slab", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_SLAB));
		self::register("polished_blackstone_stairs", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_STAIRS));
		self::register("polished_blackstone_wall", $factory->fromTypeId(Ids::POLISHED_BLACKSTONE_WALL));
		self::register("polished_deepslate", $factory->fromTypeId(Ids::POLISHED_DEEPSLATE));
		self::register("polished_deepslate_slab", $factory->fromTypeId(Ids::POLISHED_DEEPSLATE_SLAB));
		self::register("polished_deepslate_stairs", $factory->fromTypeId(Ids::POLISHED_DEEPSLATE_STAIRS));
		self::register("polished_deepslate_wall", $factory->fromTypeId(Ids::POLISHED_DEEPSLATE_WALL));
		self::register("polished_diorite", $factory->fromTypeId(Ids::POLISHED_DIORITE));
		self::register("polished_diorite_slab", $factory->fromTypeId(Ids::POLISHED_DIORITE_SLAB));
		self::register("polished_diorite_stairs", $factory->fromTypeId(Ids::POLISHED_DIORITE_STAIRS));
		self::register("polished_granite", $factory->fromTypeId(Ids::POLISHED_GRANITE));
		self::register("polished_granite_slab", $factory->fromTypeId(Ids::POLISHED_GRANITE_SLAB));
		self::register("polished_granite_stairs", $factory->fromTypeId(Ids::POLISHED_GRANITE_STAIRS));
		self::register("poppy", $factory->fromTypeId(Ids::POPPY));
		self::register("potatoes", $factory->fromTypeId(Ids::POTATOES));
		self::register("powered_rail", $factory->fromTypeId(Ids::POWERED_RAIL));
		self::register("prismarine", $factory->fromTypeId(Ids::PRISMARINE));
		self::register("prismarine_bricks", $factory->fromTypeId(Ids::PRISMARINE_BRICKS));
		self::register("prismarine_bricks_slab", $factory->fromTypeId(Ids::PRISMARINE_BRICKS_SLAB));
		self::register("prismarine_bricks_stairs", $factory->fromTypeId(Ids::PRISMARINE_BRICKS_STAIRS));
		self::register("prismarine_slab", $factory->fromTypeId(Ids::PRISMARINE_SLAB));
		self::register("prismarine_stairs", $factory->fromTypeId(Ids::PRISMARINE_STAIRS));
		self::register("prismarine_wall", $factory->fromTypeId(Ids::PRISMARINE_WALL));
		self::register("pumpkin", $factory->fromTypeId(Ids::PUMPKIN));
		self::register("pumpkin_stem", $factory->fromTypeId(Ids::PUMPKIN_STEM));
		self::register("purple_torch", $factory->fromTypeId(Ids::PURPLE_TORCH));
		self::register("purpur", $factory->fromTypeId(Ids::PURPUR));
		self::register("purpur_pillar", $factory->fromTypeId(Ids::PURPUR_PILLAR));
		self::register("purpur_slab", $factory->fromTypeId(Ids::PURPUR_SLAB));
		self::register("purpur_stairs", $factory->fromTypeId(Ids::PURPUR_STAIRS));
		self::register("quartz", $factory->fromTypeId(Ids::QUARTZ));
		self::register("quartz_bricks", $factory->fromTypeId(Ids::QUARTZ_BRICKS));
		self::register("quartz_pillar", $factory->fromTypeId(Ids::QUARTZ_PILLAR));
		self::register("quartz_slab", $factory->fromTypeId(Ids::QUARTZ_SLAB));
		self::register("quartz_stairs", $factory->fromTypeId(Ids::QUARTZ_STAIRS));
		self::register("rail", $factory->fromTypeId(Ids::RAIL));
		self::register("raw_copper", $factory->fromTypeId(Ids::RAW_COPPER));
		self::register("raw_gold", $factory->fromTypeId(Ids::RAW_GOLD));
		self::register("raw_iron", $factory->fromTypeId(Ids::RAW_IRON));
		self::register("red_mushroom", $factory->fromTypeId(Ids::RED_MUSHROOM));
		self::register("red_mushroom_block", $factory->fromTypeId(Ids::RED_MUSHROOM_BLOCK));
		self::register("red_nether_brick_slab", $factory->fromTypeId(Ids::RED_NETHER_BRICK_SLAB));
		self::register("red_nether_brick_stairs", $factory->fromTypeId(Ids::RED_NETHER_BRICK_STAIRS));
		self::register("red_nether_brick_wall", $factory->fromTypeId(Ids::RED_NETHER_BRICK_WALL));
		self::register("red_nether_bricks", $factory->fromTypeId(Ids::RED_NETHER_BRICKS));
		self::register("red_sand", $factory->fromTypeId(Ids::RED_SAND));
		self::register("red_sandstone", $factory->fromTypeId(Ids::RED_SANDSTONE));
		self::register("red_sandstone_slab", $factory->fromTypeId(Ids::RED_SANDSTONE_SLAB));
		self::register("red_sandstone_stairs", $factory->fromTypeId(Ids::RED_SANDSTONE_STAIRS));
		self::register("red_sandstone_wall", $factory->fromTypeId(Ids::RED_SANDSTONE_WALL));
		self::register("red_torch", $factory->fromTypeId(Ids::RED_TORCH));
		self::register("red_tulip", $factory->fromTypeId(Ids::RED_TULIP));
		self::register("redstone", $factory->fromTypeId(Ids::REDSTONE));
		self::register("redstone_comparator", $factory->fromTypeId(Ids::REDSTONE_COMPARATOR));
		self::register("redstone_lamp", $factory->fromTypeId(Ids::REDSTONE_LAMP));
		self::register("redstone_ore", $factory->fromTypeId(Ids::REDSTONE_ORE));
		self::register("redstone_repeater", $factory->fromTypeId(Ids::REDSTONE_REPEATER));
		self::register("redstone_torch", $factory->fromTypeId(Ids::REDSTONE_TORCH));
		self::register("redstone_wire", $factory->fromTypeId(Ids::REDSTONE_WIRE));
		self::register("reserved6", $factory->fromTypeId(Ids::RESERVED6));
		self::register("rose_bush", $factory->fromTypeId(Ids::ROSE_BUSH));
		self::register("sand", $factory->fromTypeId(Ids::SAND));
		self::register("sandstone", $factory->fromTypeId(Ids::SANDSTONE));
		self::register("sandstone_slab", $factory->fromTypeId(Ids::SANDSTONE_SLAB));
		self::register("sandstone_stairs", $factory->fromTypeId(Ids::SANDSTONE_STAIRS));
		self::register("sandstone_wall", $factory->fromTypeId(Ids::SANDSTONE_WALL));
		self::register("sea_lantern", $factory->fromTypeId(Ids::SEA_LANTERN));
		self::register("sea_pickle", $factory->fromTypeId(Ids::SEA_PICKLE));
		self::register("shroomlight", $factory->fromTypeId(Ids::SHROOMLIGHT));
		self::register("shulker_box", $factory->fromTypeId(Ids::SHULKER_BOX));
		self::register("slime", $factory->fromTypeId(Ids::SLIME));
		self::register("smoker", $factory->fromTypeId(Ids::SMOKER));
		self::register("smooth_basalt", $factory->fromTypeId(Ids::SMOOTH_BASALT));
		self::register("smooth_quartz", $factory->fromTypeId(Ids::SMOOTH_QUARTZ));
		self::register("smooth_quartz_slab", $factory->fromTypeId(Ids::SMOOTH_QUARTZ_SLAB));
		self::register("smooth_quartz_stairs", $factory->fromTypeId(Ids::SMOOTH_QUARTZ_STAIRS));
		self::register("smooth_red_sandstone", $factory->fromTypeId(Ids::SMOOTH_RED_SANDSTONE));
		self::register("smooth_red_sandstone_slab", $factory->fromTypeId(Ids::SMOOTH_RED_SANDSTONE_SLAB));
		self::register("smooth_red_sandstone_stairs", $factory->fromTypeId(Ids::SMOOTH_RED_SANDSTONE_STAIRS));
		self::register("smooth_sandstone", $factory->fromTypeId(Ids::SMOOTH_SANDSTONE));
		self::register("smooth_sandstone_slab", $factory->fromTypeId(Ids::SMOOTH_SANDSTONE_SLAB));
		self::register("smooth_sandstone_stairs", $factory->fromTypeId(Ids::SMOOTH_SANDSTONE_STAIRS));
		self::register("smooth_stone", $factory->fromTypeId(Ids::SMOOTH_STONE));
		self::register("smooth_stone_slab", $factory->fromTypeId(Ids::SMOOTH_STONE_SLAB));
		self::register("snow", $factory->fromTypeId(Ids::SNOW));
		self::register("snow_layer", $factory->fromTypeId(Ids::SNOW_LAYER));
		self::register("soul_fire", $factory->fromTypeId(Ids::SOUL_FIRE));
		self::register("soul_lantern", $factory->fromTypeId(Ids::SOUL_LANTERN));
		self::register("soul_sand", $factory->fromTypeId(Ids::SOUL_SAND));
		self::register("soul_soil", $factory->fromTypeId(Ids::SOUL_SOIL));
		self::register("soul_torch", $factory->fromTypeId(Ids::SOUL_TORCH));
		self::register("sponge", $factory->fromTypeId(Ids::SPONGE));
		self::register("spruce_button", $factory->fromTypeId(Ids::SPRUCE_BUTTON));
		self::register("spruce_door", $factory->fromTypeId(Ids::SPRUCE_DOOR));
		self::register("spruce_fence", $factory->fromTypeId(Ids::SPRUCE_FENCE));
		self::register("spruce_fence_gate", $factory->fromTypeId(Ids::SPRUCE_FENCE_GATE));
		self::register("spruce_leaves", $factory->fromTypeId(Ids::SPRUCE_LEAVES));
		self::register("spruce_log", $factory->fromTypeId(Ids::SPRUCE_LOG));
		self::register("spruce_planks", $factory->fromTypeId(Ids::SPRUCE_PLANKS));
		self::register("spruce_pressure_plate", $factory->fromTypeId(Ids::SPRUCE_PRESSURE_PLATE));
		self::register("spruce_sapling", $factory->fromTypeId(Ids::SPRUCE_SAPLING));
		self::register("spruce_sign", $factory->fromTypeId(Ids::SPRUCE_SIGN));
		self::register("spruce_slab", $factory->fromTypeId(Ids::SPRUCE_SLAB));
		self::register("spruce_stairs", $factory->fromTypeId(Ids::SPRUCE_STAIRS));
		self::register("spruce_trapdoor", $factory->fromTypeId(Ids::SPRUCE_TRAPDOOR));
		self::register("spruce_wall_sign", $factory->fromTypeId(Ids::SPRUCE_WALL_SIGN));
		self::register("spruce_wood", $factory->fromTypeId(Ids::SPRUCE_WOOD));
		self::register("stained_clay", $factory->fromTypeId(Ids::STAINED_CLAY));
		self::register("stained_glass", $factory->fromTypeId(Ids::STAINED_GLASS));
		self::register("stained_glass_pane", $factory->fromTypeId(Ids::STAINED_GLASS_PANE));
		self::register("stained_hardened_glass", $factory->fromTypeId(Ids::STAINED_HARDENED_GLASS));
		self::register("stained_hardened_glass_pane", $factory->fromTypeId(Ids::STAINED_HARDENED_GLASS_PANE));
		self::register("stone", $factory->fromTypeId(Ids::STONE));
		self::register("stone_brick_slab", $factory->fromTypeId(Ids::STONE_BRICK_SLAB));
		self::register("stone_brick_stairs", $factory->fromTypeId(Ids::STONE_BRICK_STAIRS));
		self::register("stone_brick_wall", $factory->fromTypeId(Ids::STONE_BRICK_WALL));
		self::register("stone_bricks", $factory->fromTypeId(Ids::STONE_BRICKS));
		self::register("stone_button", $factory->fromTypeId(Ids::STONE_BUTTON));
		self::register("stone_pressure_plate", $factory->fromTypeId(Ids::STONE_PRESSURE_PLATE));
		self::register("stone_slab", $factory->fromTypeId(Ids::STONE_SLAB));
		self::register("stone_stairs", $factory->fromTypeId(Ids::STONE_STAIRS));
		self::register("stonecutter", $factory->fromTypeId(Ids::STONECUTTER));
		self::register("sugarcane", $factory->fromTypeId(Ids::SUGARCANE));
		self::register("sunflower", $factory->fromTypeId(Ids::SUNFLOWER));
		self::register("sweet_berry_bush", $factory->fromTypeId(Ids::SWEET_BERRY_BUSH));
		self::register("tall_grass", $factory->fromTypeId(Ids::TALL_GRASS));
		self::register("tnt", $factory->fromTypeId(Ids::TNT));
		self::register("torch", $factory->fromTypeId(Ids::TORCH));
		self::register("trapped_chest", $factory->fromTypeId(Ids::TRAPPED_CHEST));
		self::register("tripwire", $factory->fromTypeId(Ids::TRIPWIRE));
		self::register("tripwire_hook", $factory->fromTypeId(Ids::TRIPWIRE_HOOK));
		self::register("tuff", $factory->fromTypeId(Ids::TUFF));
		self::register("underwater_torch", $factory->fromTypeId(Ids::UNDERWATER_TORCH));
		self::register("vines", $factory->fromTypeId(Ids::VINES));
		self::register("wall_banner", $factory->fromTypeId(Ids::WALL_BANNER));
		self::register("wall_coral_fan", $factory->fromTypeId(Ids::WALL_CORAL_FAN));
		self::register("warped_button", $factory->fromTypeId(Ids::WARPED_BUTTON));
		self::register("warped_door", $factory->fromTypeId(Ids::WARPED_DOOR));
		self::register("warped_fence", $factory->fromTypeId(Ids::WARPED_FENCE));
		self::register("warped_fence_gate", $factory->fromTypeId(Ids::WARPED_FENCE_GATE));
		self::register("warped_hyphae", $factory->fromTypeId(Ids::WARPED_HYPHAE));
		self::register("warped_planks", $factory->fromTypeId(Ids::WARPED_PLANKS));
		self::register("warped_pressure_plate", $factory->fromTypeId(Ids::WARPED_PRESSURE_PLATE));
		self::register("warped_sign", $factory->fromTypeId(Ids::WARPED_SIGN));
		self::register("warped_slab", $factory->fromTypeId(Ids::WARPED_SLAB));
		self::register("warped_stairs", $factory->fromTypeId(Ids::WARPED_STAIRS));
		self::register("warped_stem", $factory->fromTypeId(Ids::WARPED_STEM));
		self::register("warped_trapdoor", $factory->fromTypeId(Ids::WARPED_TRAPDOOR));
		self::register("warped_wall_sign", $factory->fromTypeId(Ids::WARPED_WALL_SIGN));
		self::register("water", $factory->fromTypeId(Ids::WATER));
		self::register("weighted_pressure_plate_heavy", $factory->fromTypeId(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY));
		self::register("weighted_pressure_plate_light", $factory->fromTypeId(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT));
		self::register("wheat", $factory->fromTypeId(Ids::WHEAT));
		self::register("white_tulip", $factory->fromTypeId(Ids::WHITE_TULIP));
		self::register("wool", $factory->fromTypeId(Ids::WOOL));
	}
}
