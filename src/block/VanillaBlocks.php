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
 * @method static Log ACACIA_LOG()
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
 * @method static Log BIRCH_LOG()
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
 * @method static Log DARK_OAK_LOG()
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
 * @method static Opaque GOLD_ORE()
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
 * @method static Opaque IRON_ORE()
 * @method static Trapdoor IRON_TRAPDOOR()
 * @method static ItemFrame ITEM_FRAME()
 * @method static Jukebox JUKEBOX()
 * @method static WoodenButton JUNGLE_BUTTON()
 * @method static WoodenDoor JUNGLE_DOOR()
 * @method static WoodenFence JUNGLE_FENCE()
 * @method static FenceGate JUNGLE_FENCE_GATE()
 * @method static Leaves JUNGLE_LEAVES()
 * @method static Log JUNGLE_LOG()
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
 * @method static MushroomStem MUSHROOM_STEM()
 * @method static Mycelium MYCELIUM()
 * @method static Netherrack NETHERRACK()
 * @method static Opaque NETHER_BRICKS()
 * @method static Fence NETHER_BRICK_FENCE()
 * @method static Slab NETHER_BRICK_SLAB()
 * @method static Stair NETHER_BRICK_STAIRS()
 * @method static Wall NETHER_BRICK_WALL()
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
 * @method static Log OAK_LOG()
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
 * @method static SoulSand SOUL_SAND()
 * @method static Sponge SPONGE()
 * @method static WoodenButton SPRUCE_BUTTON()
 * @method static WoodenDoor SPRUCE_DOOR()
 * @method static WoodenFence SPRUCE_FENCE()
 * @method static FenceGate SPRUCE_FENCE_GATE()
 * @method static Leaves SPRUCE_LEAVES()
 * @method static Log SPRUCE_LOG()
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
 * @method static UnderwaterTorch UNDERWATER_TORCH()
 * @method static Vine VINES()
 * @method static WallBanner WALL_BANNER()
 * @method static WallCoralFan WALL_CORAL_FAN()
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
		self::register("acacia_button", $factory->get(Ids::ACACIA_BUTTON, 0));
		self::register("acacia_door", $factory->get(Ids::ACACIA_DOOR, 0));
		self::register("acacia_fence", $factory->get(Ids::ACACIA_FENCE, 0));
		self::register("acacia_fence_gate", $factory->get(Ids::ACACIA_FENCE_GATE, 0));
		self::register("acacia_leaves", $factory->get(Ids::ACACIA_LEAVES, 0));
		self::register("acacia_log", $factory->get(Ids::ACACIA_LOG, 4));
		self::register("acacia_planks", $factory->get(Ids::ACACIA_PLANKS, 0));
		self::register("acacia_pressure_plate", $factory->get(Ids::ACACIA_PRESSURE_PLATE, 0));
		self::register("acacia_sapling", $factory->get(Ids::ACACIA_SAPLING, 0));
		self::register("acacia_sign", $factory->get(Ids::ACACIA_SIGN, 0));
		self::register("acacia_slab", $factory->get(Ids::ACACIA_SLAB, 0));
		self::register("acacia_stairs", $factory->get(Ids::ACACIA_STAIRS, 0));
		self::register("acacia_trapdoor", $factory->get(Ids::ACACIA_TRAPDOOR, 0));
		self::register("acacia_wall_sign", $factory->get(Ids::ACACIA_WALL_SIGN, 0));
		self::register("acacia_wood", $factory->get(Ids::ACACIA_WOOD, 0));
		self::register("activator_rail", $factory->get(Ids::ACTIVATOR_RAIL, 0));
		self::register("air", $factory->get(Ids::AIR, 0));
		self::register("all_sided_mushroom_stem", $factory->get(Ids::ALL_SIDED_MUSHROOM_STEM, 0));
		self::register("allium", $factory->get(Ids::ALLIUM, 0));
		self::register("amethyst", $factory->get(Ids::AMETHYST, 0));
		self::register("ancient_debris", $factory->get(Ids::ANCIENT_DEBRIS, 0));
		self::register("andesite", $factory->get(Ids::ANDESITE, 0));
		self::register("andesite_slab", $factory->get(Ids::ANDESITE_SLAB, 0));
		self::register("andesite_stairs", $factory->get(Ids::ANDESITE_STAIRS, 0));
		self::register("andesite_wall", $factory->get(Ids::ANDESITE_WALL, 0));
		self::register("anvil", $factory->get(Ids::ANVIL, 0));
		self::register("azure_bluet", $factory->get(Ids::AZURE_BLUET, 0));
		self::register("bamboo", $factory->get(Ids::BAMBOO, 0));
		self::register("bamboo_sapling", $factory->get(Ids::BAMBOO_SAPLING, 0));
		self::register("banner", $factory->get(Ids::BANNER, 0));
		self::register("barrel", $factory->get(Ids::BARREL, 0));
		self::register("barrier", $factory->get(Ids::BARRIER, 0));
		self::register("basalt", $factory->get(Ids::BASALT, 2));
		self::register("beacon", $factory->get(Ids::BEACON, 0));
		self::register("bed", $factory->get(Ids::BED, 13));
		self::register("bedrock", $factory->get(Ids::BEDROCK, 0));
		self::register("beetroots", $factory->get(Ids::BEETROOTS, 0));
		self::register("bell", $factory->get(Ids::BELL, 1));
		self::register("birch_button", $factory->get(Ids::BIRCH_BUTTON, 0));
		self::register("birch_door", $factory->get(Ids::BIRCH_DOOR, 0));
		self::register("birch_fence", $factory->get(Ids::BIRCH_FENCE, 0));
		self::register("birch_fence_gate", $factory->get(Ids::BIRCH_FENCE_GATE, 0));
		self::register("birch_leaves", $factory->get(Ids::BIRCH_LEAVES, 0));
		self::register("birch_log", $factory->get(Ids::BIRCH_LOG, 4));
		self::register("birch_planks", $factory->get(Ids::BIRCH_PLANKS, 0));
		self::register("birch_pressure_plate", $factory->get(Ids::BIRCH_PRESSURE_PLATE, 0));
		self::register("birch_sapling", $factory->get(Ids::BIRCH_SAPLING, 0));
		self::register("birch_sign", $factory->get(Ids::BIRCH_SIGN, 0));
		self::register("birch_slab", $factory->get(Ids::BIRCH_SLAB, 0));
		self::register("birch_stairs", $factory->get(Ids::BIRCH_STAIRS, 0));
		self::register("birch_trapdoor", $factory->get(Ids::BIRCH_TRAPDOOR, 0));
		self::register("birch_wall_sign", $factory->get(Ids::BIRCH_WALL_SIGN, 0));
		self::register("birch_wood", $factory->get(Ids::BIRCH_WOOD, 0));
		self::register("blackstone", $factory->get(Ids::BLACKSTONE, 0));
		self::register("blackstone_slab", $factory->get(Ids::BLACKSTONE_SLAB, 0));
		self::register("blackstone_stairs", $factory->get(Ids::BLACKSTONE_STAIRS, 0));
		self::register("blackstone_wall", $factory->get(Ids::BLACKSTONE_WALL, 0));
		self::register("blast_furnace", $factory->get(Ids::BLAST_FURNACE, 0));
		self::register("blue_ice", $factory->get(Ids::BLUE_ICE, 0));
		self::register("blue_orchid", $factory->get(Ids::BLUE_ORCHID, 0));
		self::register("blue_torch", $factory->get(Ids::BLUE_TORCH, 1));
		self::register("bone_block", $factory->get(Ids::BONE_BLOCK, 2));
		self::register("bookshelf", $factory->get(Ids::BOOKSHELF, 0));
		self::register("brewing_stand", $factory->get(Ids::BREWING_STAND, 0));
		self::register("brick_slab", $factory->get(Ids::BRICK_SLAB, 0));
		self::register("brick_stairs", $factory->get(Ids::BRICK_STAIRS, 0));
		self::register("brick_wall", $factory->get(Ids::BRICK_WALL, 0));
		self::register("bricks", $factory->get(Ids::BRICKS, 0));
		self::register("brown_mushroom", $factory->get(Ids::BROWN_MUSHROOM, 0));
		self::register("brown_mushroom_block", $factory->get(Ids::BROWN_MUSHROOM_BLOCK, 10));
		self::register("cactus", $factory->get(Ids::CACTUS, 0));
		self::register("cake", $factory->get(Ids::CAKE, 0));
		self::register("calcite", $factory->get(Ids::CALCITE, 0));
		self::register("carpet", $factory->get(Ids::CARPET, 14));
		self::register("carrots", $factory->get(Ids::CARROTS, 0));
		self::register("carved_pumpkin", $factory->get(Ids::CARVED_PUMPKIN, 0));
		self::register("chemical_heat", $factory->get(Ids::CHEMICAL_HEAT, 0));
		self::register("chest", $factory->get(Ids::CHEST, 0));
		self::register("chiseled_deepslate", $factory->get(Ids::CHISELED_DEEPSLATE, 0));
		self::register("chiseled_nether_bricks", $factory->get(Ids::CHISELED_NETHER_BRICKS, 0));
		self::register("chiseled_polished_blackstone", $factory->get(Ids::CHISELED_POLISHED_BLACKSTONE, 0));
		self::register("chiseled_quartz", $factory->get(Ids::CHISELED_QUARTZ, 2));
		self::register("chiseled_red_sandstone", $factory->get(Ids::CHISELED_RED_SANDSTONE, 0));
		self::register("chiseled_sandstone", $factory->get(Ids::CHISELED_SANDSTONE, 0));
		self::register("chiseled_stone_bricks", $factory->get(Ids::CHISELED_STONE_BRICKS, 0));
		self::register("clay", $factory->get(Ids::CLAY, 0));
		self::register("coal", $factory->get(Ids::COAL, 0));
		self::register("coal_ore", $factory->get(Ids::COAL_ORE, 0));
		self::register("cobbled_deepslate", $factory->get(Ids::COBBLED_DEEPSLATE, 0));
		self::register("cobbled_deepslate_slab", $factory->get(Ids::COBBLED_DEEPSLATE_SLAB, 0));
		self::register("cobbled_deepslate_stairs", $factory->get(Ids::COBBLED_DEEPSLATE_STAIRS, 0));
		self::register("cobbled_deepslate_wall", $factory->get(Ids::COBBLED_DEEPSLATE_WALL, 0));
		self::register("cobblestone", $factory->get(Ids::COBBLESTONE, 0));
		self::register("cobblestone_slab", $factory->get(Ids::COBBLESTONE_SLAB, 0));
		self::register("cobblestone_stairs", $factory->get(Ids::COBBLESTONE_STAIRS, 0));
		self::register("cobblestone_wall", $factory->get(Ids::COBBLESTONE_WALL, 0));
		self::register("cobweb", $factory->get(Ids::COBWEB, 0));
		self::register("cocoa_pod", $factory->get(Ids::COCOA_POD, 0));
		self::register("compound_creator", $factory->get(Ids::COMPOUND_CREATOR, 0));
		self::register("concrete", $factory->get(Ids::CONCRETE, 14));
		self::register("concrete_powder", $factory->get(Ids::CONCRETE_POWDER, 14));
		self::register("coral", $factory->get(Ids::CORAL, 4));
		self::register("coral_block", $factory->get(Ids::CORAL_BLOCK, 4));
		self::register("coral_fan", $factory->get(Ids::CORAL_FAN, 4));
		self::register("cornflower", $factory->get(Ids::CORNFLOWER, 0));
		self::register("cracked_deepslate_bricks", $factory->get(Ids::CRACKED_DEEPSLATE_BRICKS, 0));
		self::register("cracked_deepslate_tiles", $factory->get(Ids::CRACKED_DEEPSLATE_TILES, 0));
		self::register("cracked_nether_bricks", $factory->get(Ids::CRACKED_NETHER_BRICKS, 0));
		self::register("cracked_polished_blackstone_bricks", $factory->get(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS, 0));
		self::register("cracked_stone_bricks", $factory->get(Ids::CRACKED_STONE_BRICKS, 0));
		self::register("crafting_table", $factory->get(Ids::CRAFTING_TABLE, 0));
		self::register("cut_red_sandstone", $factory->get(Ids::CUT_RED_SANDSTONE, 0));
		self::register("cut_red_sandstone_slab", $factory->get(Ids::CUT_RED_SANDSTONE_SLAB, 0));
		self::register("cut_sandstone", $factory->get(Ids::CUT_SANDSTONE, 0));
		self::register("cut_sandstone_slab", $factory->get(Ids::CUT_SANDSTONE_SLAB, 0));
		self::register("dandelion", $factory->get(Ids::DANDELION, 0));
		self::register("dark_oak_button", $factory->get(Ids::DARK_OAK_BUTTON, 0));
		self::register("dark_oak_door", $factory->get(Ids::DARK_OAK_DOOR, 0));
		self::register("dark_oak_fence", $factory->get(Ids::DARK_OAK_FENCE, 0));
		self::register("dark_oak_fence_gate", $factory->get(Ids::DARK_OAK_FENCE_GATE, 0));
		self::register("dark_oak_leaves", $factory->get(Ids::DARK_OAK_LEAVES, 0));
		self::register("dark_oak_log", $factory->get(Ids::DARK_OAK_LOG, 4));
		self::register("dark_oak_planks", $factory->get(Ids::DARK_OAK_PLANKS, 0));
		self::register("dark_oak_pressure_plate", $factory->get(Ids::DARK_OAK_PRESSURE_PLATE, 0));
		self::register("dark_oak_sapling", $factory->get(Ids::DARK_OAK_SAPLING, 0));
		self::register("dark_oak_sign", $factory->get(Ids::DARK_OAK_SIGN, 0));
		self::register("dark_oak_slab", $factory->get(Ids::DARK_OAK_SLAB, 0));
		self::register("dark_oak_stairs", $factory->get(Ids::DARK_OAK_STAIRS, 0));
		self::register("dark_oak_trapdoor", $factory->get(Ids::DARK_OAK_TRAPDOOR, 0));
		self::register("dark_oak_wall_sign", $factory->get(Ids::DARK_OAK_WALL_SIGN, 0));
		self::register("dark_oak_wood", $factory->get(Ids::DARK_OAK_WOOD, 0));
		self::register("dark_prismarine", $factory->get(Ids::DARK_PRISMARINE, 0));
		self::register("dark_prismarine_slab", $factory->get(Ids::DARK_PRISMARINE_SLAB, 0));
		self::register("dark_prismarine_stairs", $factory->get(Ids::DARK_PRISMARINE_STAIRS, 0));
		self::register("daylight_sensor", $factory->get(Ids::DAYLIGHT_SENSOR, 0));
		self::register("dead_bush", $factory->get(Ids::DEAD_BUSH, 0));
		self::register("deepslate", $factory->get(Ids::DEEPSLATE, 2));
		self::register("deepslate_brick_slab", $factory->get(Ids::DEEPSLATE_BRICK_SLAB, 0));
		self::register("deepslate_brick_stairs", $factory->get(Ids::DEEPSLATE_BRICK_STAIRS, 0));
		self::register("deepslate_brick_wall", $factory->get(Ids::DEEPSLATE_BRICK_WALL, 0));
		self::register("deepslate_bricks", $factory->get(Ids::DEEPSLATE_BRICKS, 0));
		self::register("deepslate_tile_slab", $factory->get(Ids::DEEPSLATE_TILE_SLAB, 0));
		self::register("deepslate_tile_stairs", $factory->get(Ids::DEEPSLATE_TILE_STAIRS, 0));
		self::register("deepslate_tile_wall", $factory->get(Ids::DEEPSLATE_TILE_WALL, 0));
		self::register("deepslate_tiles", $factory->get(Ids::DEEPSLATE_TILES, 0));
		self::register("detector_rail", $factory->get(Ids::DETECTOR_RAIL, 0));
		self::register("diamond", $factory->get(Ids::DIAMOND, 0));
		self::register("diamond_ore", $factory->get(Ids::DIAMOND_ORE, 0));
		self::register("diorite", $factory->get(Ids::DIORITE, 0));
		self::register("diorite_slab", $factory->get(Ids::DIORITE_SLAB, 0));
		self::register("diorite_stairs", $factory->get(Ids::DIORITE_STAIRS, 0));
		self::register("diorite_wall", $factory->get(Ids::DIORITE_WALL, 0));
		self::register("dirt", $factory->get(Ids::DIRT, 0));
		self::register("double_tallgrass", $factory->get(Ids::DOUBLE_TALLGRASS, 0));
		self::register("dragon_egg", $factory->get(Ids::DRAGON_EGG, 0));
		self::register("dried_kelp", $factory->get(Ids::DRIED_KELP, 0));
		self::register("dyed_shulker_box", $factory->get(Ids::DYED_SHULKER_BOX, 14));
		self::register("element_actinium", $factory->get(Ids::ELEMENT_ACTINIUM, 0));
		self::register("element_aluminum", $factory->get(Ids::ELEMENT_ALUMINUM, 0));
		self::register("element_americium", $factory->get(Ids::ELEMENT_AMERICIUM, 0));
		self::register("element_antimony", $factory->get(Ids::ELEMENT_ANTIMONY, 0));
		self::register("element_argon", $factory->get(Ids::ELEMENT_ARGON, 0));
		self::register("element_arsenic", $factory->get(Ids::ELEMENT_ARSENIC, 0));
		self::register("element_astatine", $factory->get(Ids::ELEMENT_ASTATINE, 0));
		self::register("element_barium", $factory->get(Ids::ELEMENT_BARIUM, 0));
		self::register("element_berkelium", $factory->get(Ids::ELEMENT_BERKELIUM, 0));
		self::register("element_beryllium", $factory->get(Ids::ELEMENT_BERYLLIUM, 0));
		self::register("element_bismuth", $factory->get(Ids::ELEMENT_BISMUTH, 0));
		self::register("element_bohrium", $factory->get(Ids::ELEMENT_BOHRIUM, 0));
		self::register("element_boron", $factory->get(Ids::ELEMENT_BORON, 0));
		self::register("element_bromine", $factory->get(Ids::ELEMENT_BROMINE, 0));
		self::register("element_cadmium", $factory->get(Ids::ELEMENT_CADMIUM, 0));
		self::register("element_calcium", $factory->get(Ids::ELEMENT_CALCIUM, 0));
		self::register("element_californium", $factory->get(Ids::ELEMENT_CALIFORNIUM, 0));
		self::register("element_carbon", $factory->get(Ids::ELEMENT_CARBON, 0));
		self::register("element_cerium", $factory->get(Ids::ELEMENT_CERIUM, 0));
		self::register("element_cesium", $factory->get(Ids::ELEMENT_CESIUM, 0));
		self::register("element_chlorine", $factory->get(Ids::ELEMENT_CHLORINE, 0));
		self::register("element_chromium", $factory->get(Ids::ELEMENT_CHROMIUM, 0));
		self::register("element_cobalt", $factory->get(Ids::ELEMENT_COBALT, 0));
		self::register("element_constructor", $factory->get(Ids::ELEMENT_CONSTRUCTOR, 0));
		self::register("element_copernicium", $factory->get(Ids::ELEMENT_COPERNICIUM, 0));
		self::register("element_copper", $factory->get(Ids::ELEMENT_COPPER, 0));
		self::register("element_curium", $factory->get(Ids::ELEMENT_CURIUM, 0));
		self::register("element_darmstadtium", $factory->get(Ids::ELEMENT_DARMSTADTIUM, 0));
		self::register("element_dubnium", $factory->get(Ids::ELEMENT_DUBNIUM, 0));
		self::register("element_dysprosium", $factory->get(Ids::ELEMENT_DYSPROSIUM, 0));
		self::register("element_einsteinium", $factory->get(Ids::ELEMENT_EINSTEINIUM, 0));
		self::register("element_erbium", $factory->get(Ids::ELEMENT_ERBIUM, 0));
		self::register("element_europium", $factory->get(Ids::ELEMENT_EUROPIUM, 0));
		self::register("element_fermium", $factory->get(Ids::ELEMENT_FERMIUM, 0));
		self::register("element_flerovium", $factory->get(Ids::ELEMENT_FLEROVIUM, 0));
		self::register("element_fluorine", $factory->get(Ids::ELEMENT_FLUORINE, 0));
		self::register("element_francium", $factory->get(Ids::ELEMENT_FRANCIUM, 0));
		self::register("element_gadolinium", $factory->get(Ids::ELEMENT_GADOLINIUM, 0));
		self::register("element_gallium", $factory->get(Ids::ELEMENT_GALLIUM, 0));
		self::register("element_germanium", $factory->get(Ids::ELEMENT_GERMANIUM, 0));
		self::register("element_gold", $factory->get(Ids::ELEMENT_GOLD, 0));
		self::register("element_hafnium", $factory->get(Ids::ELEMENT_HAFNIUM, 0));
		self::register("element_hassium", $factory->get(Ids::ELEMENT_HASSIUM, 0));
		self::register("element_helium", $factory->get(Ids::ELEMENT_HELIUM, 0));
		self::register("element_holmium", $factory->get(Ids::ELEMENT_HOLMIUM, 0));
		self::register("element_hydrogen", $factory->get(Ids::ELEMENT_HYDROGEN, 0));
		self::register("element_indium", $factory->get(Ids::ELEMENT_INDIUM, 0));
		self::register("element_iodine", $factory->get(Ids::ELEMENT_IODINE, 0));
		self::register("element_iridium", $factory->get(Ids::ELEMENT_IRIDIUM, 0));
		self::register("element_iron", $factory->get(Ids::ELEMENT_IRON, 0));
		self::register("element_krypton", $factory->get(Ids::ELEMENT_KRYPTON, 0));
		self::register("element_lanthanum", $factory->get(Ids::ELEMENT_LANTHANUM, 0));
		self::register("element_lawrencium", $factory->get(Ids::ELEMENT_LAWRENCIUM, 0));
		self::register("element_lead", $factory->get(Ids::ELEMENT_LEAD, 0));
		self::register("element_lithium", $factory->get(Ids::ELEMENT_LITHIUM, 0));
		self::register("element_livermorium", $factory->get(Ids::ELEMENT_LIVERMORIUM, 0));
		self::register("element_lutetium", $factory->get(Ids::ELEMENT_LUTETIUM, 0));
		self::register("element_magnesium", $factory->get(Ids::ELEMENT_MAGNESIUM, 0));
		self::register("element_manganese", $factory->get(Ids::ELEMENT_MANGANESE, 0));
		self::register("element_meitnerium", $factory->get(Ids::ELEMENT_MEITNERIUM, 0));
		self::register("element_mendelevium", $factory->get(Ids::ELEMENT_MENDELEVIUM, 0));
		self::register("element_mercury", $factory->get(Ids::ELEMENT_MERCURY, 0));
		self::register("element_molybdenum", $factory->get(Ids::ELEMENT_MOLYBDENUM, 0));
		self::register("element_moscovium", $factory->get(Ids::ELEMENT_MOSCOVIUM, 0));
		self::register("element_neodymium", $factory->get(Ids::ELEMENT_NEODYMIUM, 0));
		self::register("element_neon", $factory->get(Ids::ELEMENT_NEON, 0));
		self::register("element_neptunium", $factory->get(Ids::ELEMENT_NEPTUNIUM, 0));
		self::register("element_nickel", $factory->get(Ids::ELEMENT_NICKEL, 0));
		self::register("element_nihonium", $factory->get(Ids::ELEMENT_NIHONIUM, 0));
		self::register("element_niobium", $factory->get(Ids::ELEMENT_NIOBIUM, 0));
		self::register("element_nitrogen", $factory->get(Ids::ELEMENT_NITROGEN, 0));
		self::register("element_nobelium", $factory->get(Ids::ELEMENT_NOBELIUM, 0));
		self::register("element_oganesson", $factory->get(Ids::ELEMENT_OGANESSON, 0));
		self::register("element_osmium", $factory->get(Ids::ELEMENT_OSMIUM, 0));
		self::register("element_oxygen", $factory->get(Ids::ELEMENT_OXYGEN, 0));
		self::register("element_palladium", $factory->get(Ids::ELEMENT_PALLADIUM, 0));
		self::register("element_phosphorus", $factory->get(Ids::ELEMENT_PHOSPHORUS, 0));
		self::register("element_platinum", $factory->get(Ids::ELEMENT_PLATINUM, 0));
		self::register("element_plutonium", $factory->get(Ids::ELEMENT_PLUTONIUM, 0));
		self::register("element_polonium", $factory->get(Ids::ELEMENT_POLONIUM, 0));
		self::register("element_potassium", $factory->get(Ids::ELEMENT_POTASSIUM, 0));
		self::register("element_praseodymium", $factory->get(Ids::ELEMENT_PRASEODYMIUM, 0));
		self::register("element_promethium", $factory->get(Ids::ELEMENT_PROMETHIUM, 0));
		self::register("element_protactinium", $factory->get(Ids::ELEMENT_PROTACTINIUM, 0));
		self::register("element_radium", $factory->get(Ids::ELEMENT_RADIUM, 0));
		self::register("element_radon", $factory->get(Ids::ELEMENT_RADON, 0));
		self::register("element_rhenium", $factory->get(Ids::ELEMENT_RHENIUM, 0));
		self::register("element_rhodium", $factory->get(Ids::ELEMENT_RHODIUM, 0));
		self::register("element_roentgenium", $factory->get(Ids::ELEMENT_ROENTGENIUM, 0));
		self::register("element_rubidium", $factory->get(Ids::ELEMENT_RUBIDIUM, 0));
		self::register("element_ruthenium", $factory->get(Ids::ELEMENT_RUTHENIUM, 0));
		self::register("element_rutherfordium", $factory->get(Ids::ELEMENT_RUTHERFORDIUM, 0));
		self::register("element_samarium", $factory->get(Ids::ELEMENT_SAMARIUM, 0));
		self::register("element_scandium", $factory->get(Ids::ELEMENT_SCANDIUM, 0));
		self::register("element_seaborgium", $factory->get(Ids::ELEMENT_SEABORGIUM, 0));
		self::register("element_selenium", $factory->get(Ids::ELEMENT_SELENIUM, 0));
		self::register("element_silicon", $factory->get(Ids::ELEMENT_SILICON, 0));
		self::register("element_silver", $factory->get(Ids::ELEMENT_SILVER, 0));
		self::register("element_sodium", $factory->get(Ids::ELEMENT_SODIUM, 0));
		self::register("element_strontium", $factory->get(Ids::ELEMENT_STRONTIUM, 0));
		self::register("element_sulfur", $factory->get(Ids::ELEMENT_SULFUR, 0));
		self::register("element_tantalum", $factory->get(Ids::ELEMENT_TANTALUM, 0));
		self::register("element_technetium", $factory->get(Ids::ELEMENT_TECHNETIUM, 0));
		self::register("element_tellurium", $factory->get(Ids::ELEMENT_TELLURIUM, 0));
		self::register("element_tennessine", $factory->get(Ids::ELEMENT_TENNESSINE, 0));
		self::register("element_terbium", $factory->get(Ids::ELEMENT_TERBIUM, 0));
		self::register("element_thallium", $factory->get(Ids::ELEMENT_THALLIUM, 0));
		self::register("element_thorium", $factory->get(Ids::ELEMENT_THORIUM, 0));
		self::register("element_thulium", $factory->get(Ids::ELEMENT_THULIUM, 0));
		self::register("element_tin", $factory->get(Ids::ELEMENT_TIN, 0));
		self::register("element_titanium", $factory->get(Ids::ELEMENT_TITANIUM, 0));
		self::register("element_tungsten", $factory->get(Ids::ELEMENT_TUNGSTEN, 0));
		self::register("element_uranium", $factory->get(Ids::ELEMENT_URANIUM, 0));
		self::register("element_vanadium", $factory->get(Ids::ELEMENT_VANADIUM, 0));
		self::register("element_xenon", $factory->get(Ids::ELEMENT_XENON, 0));
		self::register("element_ytterbium", $factory->get(Ids::ELEMENT_YTTERBIUM, 0));
		self::register("element_yttrium", $factory->get(Ids::ELEMENT_YTTRIUM, 0));
		self::register("element_zero", $factory->get(Ids::ELEMENT_ZERO, 0));
		self::register("element_zinc", $factory->get(Ids::ELEMENT_ZINC, 0));
		self::register("element_zirconium", $factory->get(Ids::ELEMENT_ZIRCONIUM, 0));
		self::register("emerald", $factory->get(Ids::EMERALD, 0));
		self::register("emerald_ore", $factory->get(Ids::EMERALD_ORE, 0));
		self::register("enchanting_table", $factory->get(Ids::ENCHANTING_TABLE, 0));
		self::register("end_portal_frame", $factory->get(Ids::END_PORTAL_FRAME, 0));
		self::register("end_rod", $factory->get(Ids::END_ROD, 0));
		self::register("end_stone", $factory->get(Ids::END_STONE, 0));
		self::register("end_stone_brick_slab", $factory->get(Ids::END_STONE_BRICK_SLAB, 0));
		self::register("end_stone_brick_stairs", $factory->get(Ids::END_STONE_BRICK_STAIRS, 0));
		self::register("end_stone_brick_wall", $factory->get(Ids::END_STONE_BRICK_WALL, 0));
		self::register("end_stone_bricks", $factory->get(Ids::END_STONE_BRICKS, 0));
		self::register("ender_chest", $factory->get(Ids::ENDER_CHEST, 0));
		self::register("fake_wooden_slab", $factory->get(Ids::FAKE_WOODEN_SLAB, 0));
		self::register("farmland", $factory->get(Ids::FARMLAND, 0));
		self::register("fern", $factory->get(Ids::FERN, 0));
		self::register("fire", $factory->get(Ids::FIRE, 0));
		self::register("fletching_table", $factory->get(Ids::FLETCHING_TABLE, 0));
		self::register("flower_pot", $factory->get(Ids::FLOWER_POT, 0));
		self::register("frosted_ice", $factory->get(Ids::FROSTED_ICE, 0));
		self::register("furnace", $factory->get(Ids::FURNACE, 0));
		self::register("glass", $factory->get(Ids::GLASS, 0));
		self::register("glass_pane", $factory->get(Ids::GLASS_PANE, 0));
		self::register("glazed_terracotta", $factory->get(Ids::GLAZED_TERRACOTTA, 0));
		self::register("glowing_obsidian", $factory->get(Ids::GLOWING_OBSIDIAN, 0));
		self::register("glowstone", $factory->get(Ids::GLOWSTONE, 0));
		self::register("gold", $factory->get(Ids::GOLD, 0));
		self::register("gold_ore", $factory->get(Ids::GOLD_ORE, 0));
		self::register("granite", $factory->get(Ids::GRANITE, 0));
		self::register("granite_slab", $factory->get(Ids::GRANITE_SLAB, 0));
		self::register("granite_stairs", $factory->get(Ids::GRANITE_STAIRS, 0));
		self::register("granite_wall", $factory->get(Ids::GRANITE_WALL, 0));
		self::register("grass", $factory->get(Ids::GRASS, 0));
		self::register("grass_path", $factory->get(Ids::GRASS_PATH, 0));
		self::register("gravel", $factory->get(Ids::GRAVEL, 0));
		self::register("green_torch", $factory->get(Ids::GREEN_TORCH, 1));
		self::register("hardened_clay", $factory->get(Ids::HARDENED_CLAY, 0));
		self::register("hardened_glass", $factory->get(Ids::HARDENED_GLASS, 0));
		self::register("hardened_glass_pane", $factory->get(Ids::HARDENED_GLASS_PANE, 0));
		self::register("hay_bale", $factory->get(Ids::HAY_BALE, 2));
		self::register("hopper", $factory->get(Ids::HOPPER, 0));
		self::register("ice", $factory->get(Ids::ICE, 0));
		self::register("infested_chiseled_stone_brick", $factory->get(Ids::INFESTED_CHISELED_STONE_BRICK, 0));
		self::register("infested_cobblestone", $factory->get(Ids::INFESTED_COBBLESTONE, 0));
		self::register("infested_cracked_stone_brick", $factory->get(Ids::INFESTED_CRACKED_STONE_BRICK, 0));
		self::register("infested_mossy_stone_brick", $factory->get(Ids::INFESTED_MOSSY_STONE_BRICK, 0));
		self::register("infested_stone", $factory->get(Ids::INFESTED_STONE, 0));
		self::register("infested_stone_brick", $factory->get(Ids::INFESTED_STONE_BRICK, 0));
		self::register("info_update", $factory->get(Ids::INFO_UPDATE, 0));
		self::register("info_update2", $factory->get(Ids::INFO_UPDATE2, 0));
		self::register("invisible_bedrock", $factory->get(Ids::INVISIBLE_BEDROCK, 0));
		self::register("iron", $factory->get(Ids::IRON, 0));
		self::register("iron_bars", $factory->get(Ids::IRON_BARS, 0));
		self::register("iron_door", $factory->get(Ids::IRON_DOOR, 0));
		self::register("iron_ore", $factory->get(Ids::IRON_ORE, 0));
		self::register("iron_trapdoor", $factory->get(Ids::IRON_TRAPDOOR, 0));
		self::register("item_frame", $factory->get(Ids::ITEM_FRAME, 0));
		self::register("jukebox", $factory->get(Ids::JUKEBOX, 0));
		self::register("jungle_button", $factory->get(Ids::JUNGLE_BUTTON, 0));
		self::register("jungle_door", $factory->get(Ids::JUNGLE_DOOR, 0));
		self::register("jungle_fence", $factory->get(Ids::JUNGLE_FENCE, 0));
		self::register("jungle_fence_gate", $factory->get(Ids::JUNGLE_FENCE_GATE, 0));
		self::register("jungle_leaves", $factory->get(Ids::JUNGLE_LEAVES, 0));
		self::register("jungle_log", $factory->get(Ids::JUNGLE_LOG, 4));
		self::register("jungle_planks", $factory->get(Ids::JUNGLE_PLANKS, 0));
		self::register("jungle_pressure_plate", $factory->get(Ids::JUNGLE_PRESSURE_PLATE, 0));
		self::register("jungle_sapling", $factory->get(Ids::JUNGLE_SAPLING, 0));
		self::register("jungle_sign", $factory->get(Ids::JUNGLE_SIGN, 0));
		self::register("jungle_slab", $factory->get(Ids::JUNGLE_SLAB, 0));
		self::register("jungle_stairs", $factory->get(Ids::JUNGLE_STAIRS, 0));
		self::register("jungle_trapdoor", $factory->get(Ids::JUNGLE_TRAPDOOR, 0));
		self::register("jungle_wall_sign", $factory->get(Ids::JUNGLE_WALL_SIGN, 0));
		self::register("jungle_wood", $factory->get(Ids::JUNGLE_WOOD, 0));
		self::register("lab_table", $factory->get(Ids::LAB_TABLE, 0));
		self::register("ladder", $factory->get(Ids::LADDER, 0));
		self::register("lantern", $factory->get(Ids::LANTERN, 0));
		self::register("lapis_lazuli", $factory->get(Ids::LAPIS_LAZULI, 0));
		self::register("lapis_lazuli_ore", $factory->get(Ids::LAPIS_LAZULI_ORE, 0));
		self::register("large_fern", $factory->get(Ids::LARGE_FERN, 0));
		self::register("lava", $factory->get(Ids::LAVA, 0));
		self::register("lectern", $factory->get(Ids::LECTERN, 0));
		self::register("legacy_stonecutter", $factory->get(Ids::LEGACY_STONECUTTER, 0));
		self::register("lever", $factory->get(Ids::LEVER, 5));
		self::register("light", $factory->get(Ids::LIGHT, 15));
		self::register("lilac", $factory->get(Ids::LILAC, 0));
		self::register("lily_of_the_valley", $factory->get(Ids::LILY_OF_THE_VALLEY, 0));
		self::register("lily_pad", $factory->get(Ids::LILY_PAD, 0));
		self::register("lit_pumpkin", $factory->get(Ids::LIT_PUMPKIN, 0));
		self::register("loom", $factory->get(Ids::LOOM, 0));
		self::register("magma", $factory->get(Ids::MAGMA, 0));
		self::register("material_reducer", $factory->get(Ids::MATERIAL_REDUCER, 0));
		self::register("melon", $factory->get(Ids::MELON, 0));
		self::register("melon_stem", $factory->get(Ids::MELON_STEM, 0));
		self::register("mob_head", $factory->get(Ids::MOB_HEAD, 19));
		self::register("monster_spawner", $factory->get(Ids::MONSTER_SPAWNER, 0));
		self::register("mossy_cobblestone", $factory->get(Ids::MOSSY_COBBLESTONE, 0));
		self::register("mossy_cobblestone_slab", $factory->get(Ids::MOSSY_COBBLESTONE_SLAB, 0));
		self::register("mossy_cobblestone_stairs", $factory->get(Ids::MOSSY_COBBLESTONE_STAIRS, 0));
		self::register("mossy_cobblestone_wall", $factory->get(Ids::MOSSY_COBBLESTONE_WALL, 0));
		self::register("mossy_stone_brick_slab", $factory->get(Ids::MOSSY_STONE_BRICK_SLAB, 0));
		self::register("mossy_stone_brick_stairs", $factory->get(Ids::MOSSY_STONE_BRICK_STAIRS, 0));
		self::register("mossy_stone_brick_wall", $factory->get(Ids::MOSSY_STONE_BRICK_WALL, 0));
		self::register("mossy_stone_bricks", $factory->get(Ids::MOSSY_STONE_BRICKS, 0));
		self::register("mushroom_stem", $factory->get(Ids::MUSHROOM_STEM, 0));
		self::register("mycelium", $factory->get(Ids::MYCELIUM, 0));
		self::register("nether_brick_fence", $factory->get(Ids::NETHER_BRICK_FENCE, 0));
		self::register("nether_brick_slab", $factory->get(Ids::NETHER_BRICK_SLAB, 0));
		self::register("nether_brick_stairs", $factory->get(Ids::NETHER_BRICK_STAIRS, 0));
		self::register("nether_brick_wall", $factory->get(Ids::NETHER_BRICK_WALL, 0));
		self::register("nether_bricks", $factory->get(Ids::NETHER_BRICKS, 0));
		self::register("nether_portal", $factory->get(Ids::NETHER_PORTAL, 0));
		self::register("nether_quartz_ore", $factory->get(Ids::NETHER_QUARTZ_ORE, 0));
		self::register("nether_reactor_core", $factory->get(Ids::NETHER_REACTOR_CORE, 0));
		self::register("nether_wart", $factory->get(Ids::NETHER_WART, 0));
		self::register("nether_wart_block", $factory->get(Ids::NETHER_WART_BLOCK, 0));
		self::register("netherrack", $factory->get(Ids::NETHERRACK, 0));
		self::register("note_block", $factory->get(Ids::NOTE_BLOCK, 0));
		self::register("oak_button", $factory->get(Ids::OAK_BUTTON, 0));
		self::register("oak_door", $factory->get(Ids::OAK_DOOR, 0));
		self::register("oak_fence", $factory->get(Ids::OAK_FENCE, 0));
		self::register("oak_fence_gate", $factory->get(Ids::OAK_FENCE_GATE, 0));
		self::register("oak_leaves", $factory->get(Ids::OAK_LEAVES, 0));
		self::register("oak_log", $factory->get(Ids::OAK_LOG, 4));
		self::register("oak_planks", $factory->get(Ids::OAK_PLANKS, 0));
		self::register("oak_pressure_plate", $factory->get(Ids::OAK_PRESSURE_PLATE, 0));
		self::register("oak_sapling", $factory->get(Ids::OAK_SAPLING, 0));
		self::register("oak_sign", $factory->get(Ids::OAK_SIGN, 0));
		self::register("oak_slab", $factory->get(Ids::OAK_SLAB, 0));
		self::register("oak_stairs", $factory->get(Ids::OAK_STAIRS, 0));
		self::register("oak_trapdoor", $factory->get(Ids::OAK_TRAPDOOR, 0));
		self::register("oak_wall_sign", $factory->get(Ids::OAK_WALL_SIGN, 0));
		self::register("oak_wood", $factory->get(Ids::OAK_WOOD, 0));
		self::register("obsidian", $factory->get(Ids::OBSIDIAN, 0));
		self::register("orange_tulip", $factory->get(Ids::ORANGE_TULIP, 0));
		self::register("oxeye_daisy", $factory->get(Ids::OXEYE_DAISY, 0));
		self::register("packed_ice", $factory->get(Ids::PACKED_ICE, 0));
		self::register("peony", $factory->get(Ids::PEONY, 0));
		self::register("pink_tulip", $factory->get(Ids::PINK_TULIP, 0));
		self::register("podzol", $factory->get(Ids::PODZOL, 0));
		self::register("polished_andesite", $factory->get(Ids::POLISHED_ANDESITE, 0));
		self::register("polished_andesite_slab", $factory->get(Ids::POLISHED_ANDESITE_SLAB, 0));
		self::register("polished_andesite_stairs", $factory->get(Ids::POLISHED_ANDESITE_STAIRS, 0));
		self::register("polished_basalt", $factory->get(Ids::POLISHED_BASALT, 2));
		self::register("polished_blackstone", $factory->get(Ids::POLISHED_BLACKSTONE, 0));
		self::register("polished_blackstone_brick_slab", $factory->get(Ids::POLISHED_BLACKSTONE_BRICK_SLAB, 0));
		self::register("polished_blackstone_brick_stairs", $factory->get(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS, 0));
		self::register("polished_blackstone_brick_wall", $factory->get(Ids::POLISHED_BLACKSTONE_BRICK_WALL, 0));
		self::register("polished_blackstone_bricks", $factory->get(Ids::POLISHED_BLACKSTONE_BRICKS, 0));
		self::register("polished_blackstone_button", $factory->get(Ids::POLISHED_BLACKSTONE_BUTTON, 0));
		self::register("polished_blackstone_pressure_plate", $factory->get(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE, 0));
		self::register("polished_blackstone_slab", $factory->get(Ids::POLISHED_BLACKSTONE_SLAB, 0));
		self::register("polished_blackstone_stairs", $factory->get(Ids::POLISHED_BLACKSTONE_STAIRS, 0));
		self::register("polished_blackstone_wall", $factory->get(Ids::POLISHED_BLACKSTONE_WALL, 0));
		self::register("polished_deepslate", $factory->get(Ids::POLISHED_DEEPSLATE, 0));
		self::register("polished_deepslate_slab", $factory->get(Ids::POLISHED_DEEPSLATE_SLAB, 0));
		self::register("polished_deepslate_stairs", $factory->get(Ids::POLISHED_DEEPSLATE_STAIRS, 0));
		self::register("polished_deepslate_wall", $factory->get(Ids::POLISHED_DEEPSLATE_WALL, 0));
		self::register("polished_diorite", $factory->get(Ids::POLISHED_DIORITE, 0));
		self::register("polished_diorite_slab", $factory->get(Ids::POLISHED_DIORITE_SLAB, 0));
		self::register("polished_diorite_stairs", $factory->get(Ids::POLISHED_DIORITE_STAIRS, 0));
		self::register("polished_granite", $factory->get(Ids::POLISHED_GRANITE, 0));
		self::register("polished_granite_slab", $factory->get(Ids::POLISHED_GRANITE_SLAB, 0));
		self::register("polished_granite_stairs", $factory->get(Ids::POLISHED_GRANITE_STAIRS, 0));
		self::register("poppy", $factory->get(Ids::POPPY, 0));
		self::register("potatoes", $factory->get(Ids::POTATOES, 0));
		self::register("powered_rail", $factory->get(Ids::POWERED_RAIL, 0));
		self::register("prismarine", $factory->get(Ids::PRISMARINE, 0));
		self::register("prismarine_bricks", $factory->get(Ids::PRISMARINE_BRICKS, 0));
		self::register("prismarine_bricks_slab", $factory->get(Ids::PRISMARINE_BRICKS_SLAB, 0));
		self::register("prismarine_bricks_stairs", $factory->get(Ids::PRISMARINE_BRICKS_STAIRS, 0));
		self::register("prismarine_slab", $factory->get(Ids::PRISMARINE_SLAB, 0));
		self::register("prismarine_stairs", $factory->get(Ids::PRISMARINE_STAIRS, 0));
		self::register("prismarine_wall", $factory->get(Ids::PRISMARINE_WALL, 0));
		self::register("pumpkin", $factory->get(Ids::PUMPKIN, 0));
		self::register("pumpkin_stem", $factory->get(Ids::PUMPKIN_STEM, 0));
		self::register("purple_torch", $factory->get(Ids::PURPLE_TORCH, 1));
		self::register("purpur", $factory->get(Ids::PURPUR, 0));
		self::register("purpur_pillar", $factory->get(Ids::PURPUR_PILLAR, 2));
		self::register("purpur_slab", $factory->get(Ids::PURPUR_SLAB, 0));
		self::register("purpur_stairs", $factory->get(Ids::PURPUR_STAIRS, 0));
		self::register("quartz", $factory->get(Ids::QUARTZ, 0));
		self::register("quartz_bricks", $factory->get(Ids::QUARTZ_BRICKS, 0));
		self::register("quartz_pillar", $factory->get(Ids::QUARTZ_PILLAR, 2));
		self::register("quartz_slab", $factory->get(Ids::QUARTZ_SLAB, 0));
		self::register("quartz_stairs", $factory->get(Ids::QUARTZ_STAIRS, 0));
		self::register("rail", $factory->get(Ids::RAIL, 0));
		self::register("raw_copper", $factory->get(Ids::RAW_COPPER, 0));
		self::register("raw_gold", $factory->get(Ids::RAW_GOLD, 0));
		self::register("raw_iron", $factory->get(Ids::RAW_IRON, 0));
		self::register("red_mushroom", $factory->get(Ids::RED_MUSHROOM, 0));
		self::register("red_mushroom_block", $factory->get(Ids::RED_MUSHROOM_BLOCK, 10));
		self::register("red_nether_brick_slab", $factory->get(Ids::RED_NETHER_BRICK_SLAB, 0));
		self::register("red_nether_brick_stairs", $factory->get(Ids::RED_NETHER_BRICK_STAIRS, 0));
		self::register("red_nether_brick_wall", $factory->get(Ids::RED_NETHER_BRICK_WALL, 0));
		self::register("red_nether_bricks", $factory->get(Ids::RED_NETHER_BRICKS, 0));
		self::register("red_sand", $factory->get(Ids::RED_SAND, 0));
		self::register("red_sandstone", $factory->get(Ids::RED_SANDSTONE, 0));
		self::register("red_sandstone_slab", $factory->get(Ids::RED_SANDSTONE_SLAB, 0));
		self::register("red_sandstone_stairs", $factory->get(Ids::RED_SANDSTONE_STAIRS, 0));
		self::register("red_sandstone_wall", $factory->get(Ids::RED_SANDSTONE_WALL, 0));
		self::register("red_torch", $factory->get(Ids::RED_TORCH, 1));
		self::register("red_tulip", $factory->get(Ids::RED_TULIP, 0));
		self::register("redstone", $factory->get(Ids::REDSTONE, 0));
		self::register("redstone_comparator", $factory->get(Ids::REDSTONE_COMPARATOR, 0));
		self::register("redstone_lamp", $factory->get(Ids::REDSTONE_LAMP, 0));
		self::register("redstone_ore", $factory->get(Ids::REDSTONE_ORE, 0));
		self::register("redstone_repeater", $factory->get(Ids::REDSTONE_REPEATER, 0));
		self::register("redstone_torch", $factory->get(Ids::REDSTONE_TORCH, 9));
		self::register("redstone_wire", $factory->get(Ids::REDSTONE_WIRE, 0));
		self::register("reserved6", $factory->get(Ids::RESERVED6, 0));
		self::register("rose_bush", $factory->get(Ids::ROSE_BUSH, 0));
		self::register("sand", $factory->get(Ids::SAND, 0));
		self::register("sandstone", $factory->get(Ids::SANDSTONE, 0));
		self::register("sandstone_slab", $factory->get(Ids::SANDSTONE_SLAB, 0));
		self::register("sandstone_stairs", $factory->get(Ids::SANDSTONE_STAIRS, 0));
		self::register("sandstone_wall", $factory->get(Ids::SANDSTONE_WALL, 0));
		self::register("sea_lantern", $factory->get(Ids::SEA_LANTERN, 0));
		self::register("sea_pickle", $factory->get(Ids::SEA_PICKLE, 0));
		self::register("shulker_box", $factory->get(Ids::SHULKER_BOX, 0));
		self::register("slime", $factory->get(Ids::SLIME, 0));
		self::register("smoker", $factory->get(Ids::SMOKER, 0));
		self::register("smooth_basalt", $factory->get(Ids::SMOOTH_BASALT, 0));
		self::register("smooth_quartz", $factory->get(Ids::SMOOTH_QUARTZ, 0));
		self::register("smooth_quartz_slab", $factory->get(Ids::SMOOTH_QUARTZ_SLAB, 0));
		self::register("smooth_quartz_stairs", $factory->get(Ids::SMOOTH_QUARTZ_STAIRS, 0));
		self::register("smooth_red_sandstone", $factory->get(Ids::SMOOTH_RED_SANDSTONE, 0));
		self::register("smooth_red_sandstone_slab", $factory->get(Ids::SMOOTH_RED_SANDSTONE_SLAB, 0));
		self::register("smooth_red_sandstone_stairs", $factory->get(Ids::SMOOTH_RED_SANDSTONE_STAIRS, 0));
		self::register("smooth_sandstone", $factory->get(Ids::SMOOTH_SANDSTONE, 0));
		self::register("smooth_sandstone_slab", $factory->get(Ids::SMOOTH_SANDSTONE_SLAB, 0));
		self::register("smooth_sandstone_stairs", $factory->get(Ids::SMOOTH_SANDSTONE_STAIRS, 0));
		self::register("smooth_stone", $factory->get(Ids::SMOOTH_STONE, 0));
		self::register("smooth_stone_slab", $factory->get(Ids::SMOOTH_STONE_SLAB, 0));
		self::register("snow", $factory->get(Ids::SNOW, 0));
		self::register("snow_layer", $factory->get(Ids::SNOW_LAYER, 0));
		self::register("soul_sand", $factory->get(Ids::SOUL_SAND, 0));
		self::register("sponge", $factory->get(Ids::SPONGE, 0));
		self::register("spruce_button", $factory->get(Ids::SPRUCE_BUTTON, 0));
		self::register("spruce_door", $factory->get(Ids::SPRUCE_DOOR, 0));
		self::register("spruce_fence", $factory->get(Ids::SPRUCE_FENCE, 0));
		self::register("spruce_fence_gate", $factory->get(Ids::SPRUCE_FENCE_GATE, 0));
		self::register("spruce_leaves", $factory->get(Ids::SPRUCE_LEAVES, 0));
		self::register("spruce_log", $factory->get(Ids::SPRUCE_LOG, 4));
		self::register("spruce_planks", $factory->get(Ids::SPRUCE_PLANKS, 0));
		self::register("spruce_pressure_plate", $factory->get(Ids::SPRUCE_PRESSURE_PLATE, 0));
		self::register("spruce_sapling", $factory->get(Ids::SPRUCE_SAPLING, 0));
		self::register("spruce_sign", $factory->get(Ids::SPRUCE_SIGN, 0));
		self::register("spruce_slab", $factory->get(Ids::SPRUCE_SLAB, 0));
		self::register("spruce_stairs", $factory->get(Ids::SPRUCE_STAIRS, 0));
		self::register("spruce_trapdoor", $factory->get(Ids::SPRUCE_TRAPDOOR, 0));
		self::register("spruce_wall_sign", $factory->get(Ids::SPRUCE_WALL_SIGN, 0));
		self::register("spruce_wood", $factory->get(Ids::SPRUCE_WOOD, 0));
		self::register("stained_clay", $factory->get(Ids::STAINED_CLAY, 14));
		self::register("stained_glass", $factory->get(Ids::STAINED_GLASS, 14));
		self::register("stained_glass_pane", $factory->get(Ids::STAINED_GLASS_PANE, 14));
		self::register("stained_hardened_glass", $factory->get(Ids::STAINED_HARDENED_GLASS, 14));
		self::register("stained_hardened_glass_pane", $factory->get(Ids::STAINED_HARDENED_GLASS_PANE, 14));
		self::register("stone", $factory->get(Ids::STONE, 0));
		self::register("stone_brick_slab", $factory->get(Ids::STONE_BRICK_SLAB, 0));
		self::register("stone_brick_stairs", $factory->get(Ids::STONE_BRICK_STAIRS, 0));
		self::register("stone_brick_wall", $factory->get(Ids::STONE_BRICK_WALL, 0));
		self::register("stone_bricks", $factory->get(Ids::STONE_BRICKS, 0));
		self::register("stone_button", $factory->get(Ids::STONE_BUTTON, 0));
		self::register("stone_pressure_plate", $factory->get(Ids::STONE_PRESSURE_PLATE, 0));
		self::register("stone_slab", $factory->get(Ids::STONE_SLAB, 0));
		self::register("stone_stairs", $factory->get(Ids::STONE_STAIRS, 0));
		self::register("stonecutter", $factory->get(Ids::STONECUTTER, 0));
		self::register("sugarcane", $factory->get(Ids::SUGARCANE, 0));
		self::register("sunflower", $factory->get(Ids::SUNFLOWER, 0));
		self::register("sweet_berry_bush", $factory->get(Ids::SWEET_BERRY_BUSH, 0));
		self::register("tall_grass", $factory->get(Ids::TALL_GRASS, 0));
		self::register("tnt", $factory->get(Ids::TNT, 0));
		self::register("torch", $factory->get(Ids::TORCH, 1));
		self::register("trapped_chest", $factory->get(Ids::TRAPPED_CHEST, 0));
		self::register("tripwire", $factory->get(Ids::TRIPWIRE, 0));
		self::register("tripwire_hook", $factory->get(Ids::TRIPWIRE_HOOK, 0));
		self::register("underwater_torch", $factory->get(Ids::UNDERWATER_TORCH, 1));
		self::register("vines", $factory->get(Ids::VINES, 0));
		self::register("wall_banner", $factory->get(Ids::WALL_BANNER, 0));
		self::register("wall_coral_fan", $factory->get(Ids::WALL_CORAL_FAN, 4));
		self::register("water", $factory->get(Ids::WATER, 0));
		self::register("weighted_pressure_plate_heavy", $factory->get(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY, 0));
		self::register("weighted_pressure_plate_light", $factory->get(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT, 0));
		self::register("wheat", $factory->get(Ids::WHEAT, 0));
		self::register("white_tulip", $factory->get(Ids::WHITE_TULIP, 0));
		self::register("wool", $factory->get(Ids::WOOL, 14));
	}
}
