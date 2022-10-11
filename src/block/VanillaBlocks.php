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

use pocketmine\block\BlockLegacyIds as Ids;
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
 * @method static GlazedTerracotta BLACK_GLAZED_TERRACOTTA()
 * @method static Furnace BLAST_FURNACE()
 * @method static GlazedTerracotta BLUE_GLAZED_TERRACOTTA()
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
 * @method static GlazedTerracotta BROWN_GLAZED_TERRACOTTA()
 * @method static BrownMushroom BROWN_MUSHROOM()
 * @method static BrownMushroomBlock BROWN_MUSHROOM_BLOCK()
 * @method static Cactus CACTUS()
 * @method static Cake CAKE()
 * @method static Carpet CARPET()
 * @method static Carrot CARROTS()
 * @method static CarvedPumpkin CARVED_PUMPKIN()
 * @method static ChemicalHeat CHEMICAL_HEAT()
 * @method static Chest CHEST()
 * @method static SimplePillar CHISELED_QUARTZ()
 * @method static Opaque CHISELED_RED_SANDSTONE()
 * @method static Opaque CHISELED_SANDSTONE()
 * @method static Opaque CHISELED_STONE_BRICKS()
 * @method static Clay CLAY()
 * @method static Coal COAL()
 * @method static CoalOre COAL_ORE()
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
 * @method static Opaque CRACKED_STONE_BRICKS()
 * @method static CraftingTable CRAFTING_TABLE()
 * @method static Opaque CUT_RED_SANDSTONE()
 * @method static Slab CUT_RED_SANDSTONE_SLAB()
 * @method static Opaque CUT_SANDSTONE()
 * @method static Slab CUT_SANDSTONE_SLAB()
 * @method static GlazedTerracotta CYAN_GLAZED_TERRACOTTA()
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
 * @method static GlazedTerracotta GRAY_GLAZED_TERRACOTTA()
 * @method static GlazedTerracotta GREEN_GLAZED_TERRACOTTA()
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
 * @method static GlazedTerracotta LIGHT_BLUE_GLAZED_TERRACOTTA()
 * @method static GlazedTerracotta LIGHT_GRAY_GLAZED_TERRACOTTA()
 * @method static DoublePlant LILAC()
 * @method static Flower LILY_OF_THE_VALLEY()
 * @method static WaterLily LILY_PAD()
 * @method static GlazedTerracotta LIME_GLAZED_TERRACOTTA()
 * @method static LitPumpkin LIT_PUMPKIN()
 * @method static Loom LOOM()
 * @method static GlazedTerracotta MAGENTA_GLAZED_TERRACOTTA()
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
 * @method static GlazedTerracotta ORANGE_GLAZED_TERRACOTTA()
 * @method static Flower ORANGE_TULIP()
 * @method static Flower OXEYE_DAISY()
 * @method static PackedIce PACKED_ICE()
 * @method static DoublePlant PEONY()
 * @method static GlazedTerracotta PINK_GLAZED_TERRACOTTA()
 * @method static Flower PINK_TULIP()
 * @method static Podzol PODZOL()
 * @method static Opaque POLISHED_ANDESITE()
 * @method static Slab POLISHED_ANDESITE_SLAB()
 * @method static Stair POLISHED_ANDESITE_STAIRS()
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
 * @method static GlazedTerracotta PURPLE_GLAZED_TERRACOTTA()
 * @method static Torch PURPLE_TORCH()
 * @method static Opaque PURPUR()
 * @method static SimplePillar PURPUR_PILLAR()
 * @method static Slab PURPUR_SLAB()
 * @method static Stair PURPUR_STAIRS()
 * @method static Opaque QUARTZ()
 * @method static SimplePillar QUARTZ_PILLAR()
 * @method static Slab QUARTZ_SLAB()
 * @method static Stair QUARTZ_STAIRS()
 * @method static Rail RAIL()
 * @method static Redstone REDSTONE()
 * @method static RedstoneComparator REDSTONE_COMPARATOR()
 * @method static RedstoneLamp REDSTONE_LAMP()
 * @method static RedstoneOre REDSTONE_ORE()
 * @method static RedstoneRepeater REDSTONE_REPEATER()
 * @method static RedstoneTorch REDSTONE_TORCH()
 * @method static RedstoneWire REDSTONE_WIRE()
 * @method static GlazedTerracotta RED_GLAZED_TERRACOTTA()
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
 * @method static Log STRIPPED_ACACIA_LOG()
 * @method static Wood STRIPPED_ACACIA_WOOD()
 * @method static Log STRIPPED_BIRCH_LOG()
 * @method static Wood STRIPPED_BIRCH_WOOD()
 * @method static Log STRIPPED_DARK_OAK_LOG()
 * @method static Wood STRIPPED_DARK_OAK_WOOD()
 * @method static Log STRIPPED_JUNGLE_LOG()
 * @method static Wood STRIPPED_JUNGLE_WOOD()
 * @method static Log STRIPPED_OAK_LOG()
 * @method static Wood STRIPPED_OAK_WOOD()
 * @method static Log STRIPPED_SPRUCE_LOG()
 * @method static Wood STRIPPED_SPRUCE_WOOD()
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
 * @method static GlazedTerracotta WHITE_GLAZED_TERRACOTTA()
 * @method static Flower WHITE_TULIP()
 * @method static Wool WOOL()
 * @method static GlazedTerracotta YELLOW_GLAZED_TERRACOTTA()
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
	 * @phpstan-return array<string, Block>
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
		self::register("acacia_door", $factory->get(Ids::ACACIA_DOOR_BLOCK, 0));
		self::register("acacia_fence", $factory->get(Ids::FENCE, 4));
		self::register("acacia_fence_gate", $factory->get(Ids::ACACIA_FENCE_GATE, 0));
		self::register("acacia_leaves", $factory->get(Ids::LEAVES2, 0));
		self::register("acacia_log", $factory->get(Ids::LOG2, 0));
		self::register("acacia_planks", $factory->get(Ids::PLANKS, 4));
		self::register("acacia_pressure_plate", $factory->get(Ids::ACACIA_PRESSURE_PLATE, 0));
		self::register("acacia_sapling", $factory->get(Ids::SAPLING, 4));
		self::register("acacia_sign", $factory->get(Ids::ACACIA_STANDING_SIGN, 0));
		self::register("acacia_slab", $factory->get(Ids::WOODEN_SLAB, 4));
		self::register("acacia_stairs", $factory->get(Ids::ACACIA_STAIRS, 0));
		self::register("acacia_trapdoor", $factory->get(Ids::ACACIA_TRAPDOOR, 0));
		self::register("acacia_wall_sign", $factory->get(Ids::ACACIA_WALL_SIGN, 2));
		self::register("acacia_wood", $factory->get(Ids::WOOD, 4));
		self::register("activator_rail", $factory->get(Ids::ACTIVATOR_RAIL, 0));
		self::register("air", $factory->get(Ids::AIR, 0));
		self::register("all_sided_mushroom_stem", $factory->get(Ids::BROWN_MUSHROOM_BLOCK, 15));
		self::register("allium", $factory->get(Ids::POPPY, 2));
		self::register("andesite", $factory->get(Ids::STONE, 5));
		self::register("andesite_slab", $factory->get(Ids::STONE_SLAB3, 3));
		self::register("andesite_stairs", $factory->get(Ids::ANDESITE_STAIRS, 0));
		self::register("andesite_wall", $factory->get(Ids::COBBLESTONE_WALL, 4));
		self::register("anvil", $factory->get(Ids::ANVIL, 0));
		self::register("azure_bluet", $factory->get(Ids::POPPY, 3));
		self::register("bamboo", $factory->get(Ids::BAMBOO, 0));
		self::register("bamboo_sapling", $factory->get(Ids::BAMBOO_SAPLING, 0));
		self::register("banner", $factory->get(Ids::STANDING_BANNER, 0));
		self::register("barrel", $factory->get(Ids::BARREL, 0));
		self::register("barrier", $factory->get(Ids::BARRIER, 0));
		self::register("beacon", $factory->get(Ids::BEACON, 0));
		self::register("bed", $factory->get(Ids::BED_BLOCK, 0));
		self::register("bedrock", $factory->get(Ids::BEDROCK, 0));
		self::register("beetroots", $factory->get(Ids::BEETROOT_BLOCK, 0));
		self::register("bell", $factory->get(Ids::BELL, 0));
		self::register("birch_button", $factory->get(Ids::BIRCH_BUTTON, 0));
		self::register("birch_door", $factory->get(Ids::BIRCH_DOOR_BLOCK, 0));
		self::register("birch_fence", $factory->get(Ids::FENCE, 2));
		self::register("birch_fence_gate", $factory->get(Ids::BIRCH_FENCE_GATE, 0));
		self::register("birch_leaves", $factory->get(Ids::LEAVES, 2));
		self::register("birch_log", $factory->get(Ids::LOG, 2));
		self::register("birch_planks", $factory->get(Ids::PLANKS, 2));
		self::register("birch_pressure_plate", $factory->get(Ids::BIRCH_PRESSURE_PLATE, 0));
		self::register("birch_sapling", $factory->get(Ids::SAPLING, 2));
		self::register("birch_sign", $factory->get(Ids::BIRCH_STANDING_SIGN, 0));
		self::register("birch_slab", $factory->get(Ids::WOODEN_SLAB, 2));
		self::register("birch_stairs", $factory->get(Ids::BIRCH_STAIRS, 0));
		self::register("birch_trapdoor", $factory->get(Ids::BIRCH_TRAPDOOR, 0));
		self::register("birch_wall_sign", $factory->get(Ids::BIRCH_WALL_SIGN, 2));
		self::register("birch_wood", $factory->get(Ids::WOOD, 2));
		self::register("black_glazed_terracotta", $factory->get(Ids::BLACK_GLAZED_TERRACOTTA, 2));
		self::register("blast_furnace", $factory->get(Ids::BLAST_FURNACE, 2));
		self::register("blue_glazed_terracotta", $factory->get(Ids::BLUE_GLAZED_TERRACOTTA, 2));
		self::register("blue_ice", $factory->get(Ids::BLUE_ICE, 0));
		self::register("blue_orchid", $factory->get(Ids::POPPY, 1));
		self::register("blue_torch", $factory->get(Ids::COLORED_TORCH_BP, 5));
		self::register("bone_block", $factory->get(Ids::BONE_BLOCK, 0));
		self::register("bookshelf", $factory->get(Ids::BOOKSHELF, 0));
		self::register("brewing_stand", $factory->get(Ids::BREWING_STAND_BLOCK, 0));
		self::register("brick_slab", $factory->get(Ids::STONE_SLAB, 4));
		self::register("brick_stairs", $factory->get(Ids::BRICK_STAIRS, 0));
		self::register("brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 6));
		self::register("bricks", $factory->get(Ids::BRICK_BLOCK, 0));
		self::register("brown_glazed_terracotta", $factory->get(Ids::BROWN_GLAZED_TERRACOTTA, 2));
		self::register("brown_mushroom", $factory->get(Ids::BROWN_MUSHROOM, 0));
		self::register("brown_mushroom_block", $factory->get(Ids::BROWN_MUSHROOM_BLOCK, 0));
		self::register("cactus", $factory->get(Ids::CACTUS, 0));
		self::register("cake", $factory->get(Ids::CAKE_BLOCK, 0));
		self::register("carpet", $factory->get(Ids::CARPET, 0));
		self::register("carrots", $factory->get(Ids::CARROTS, 0));
		self::register("carved_pumpkin", $factory->get(Ids::CARVED_PUMPKIN, 0));
		self::register("chemical_heat", $factory->get(Ids::CHEMICAL_HEAT, 0));
		self::register("chest", $factory->get(Ids::CHEST, 2));
		self::register("chiseled_quartz", $factory->get(Ids::QUARTZ_BLOCK, 1));
		self::register("chiseled_red_sandstone", $factory->get(Ids::RED_SANDSTONE, 1));
		self::register("chiseled_sandstone", $factory->get(Ids::SANDSTONE, 1));
		self::register("chiseled_stone_bricks", $factory->get(Ids::STONEBRICK, 3));
		self::register("clay", $factory->get(Ids::CLAY_BLOCK, 0));
		self::register("coal", $factory->get(Ids::COAL_BLOCK, 0));
		self::register("coal_ore", $factory->get(Ids::COAL_ORE, 0));
		self::register("cobblestone", $factory->get(Ids::COBBLESTONE, 0));
		self::register("cobblestone_slab", $factory->get(Ids::STONE_SLAB, 3));
		self::register("cobblestone_stairs", $factory->get(Ids::COBBLESTONE_STAIRS, 0));
		self::register("cobblestone_wall", $factory->get(Ids::COBBLESTONE_WALL, 0));
		self::register("cobweb", $factory->get(Ids::COBWEB, 0));
		self::register("cocoa_pod", $factory->get(Ids::COCOA, 0));
		self::register("compound_creator", $factory->get(Ids::CHEMISTRY_TABLE, 0));
		self::register("concrete", $factory->get(Ids::CONCRETE, 0));
		self::register("concrete_powder", $factory->get(Ids::CONCRETEPOWDER, 0));
		self::register("coral", $factory->get(Ids::CORAL, 0));
		self::register("coral_block", $factory->get(Ids::CORAL_BLOCK, 0));
		self::register("coral_fan", $factory->get(Ids::CORAL_FAN, 0));
		self::register("cornflower", $factory->get(Ids::POPPY, 9));
		self::register("cracked_stone_bricks", $factory->get(Ids::STONEBRICK, 2));
		self::register("crafting_table", $factory->get(Ids::CRAFTING_TABLE, 0));
		self::register("cut_red_sandstone", $factory->get(Ids::RED_SANDSTONE, 2));
		self::register("cut_red_sandstone_slab", $factory->get(Ids::STONE_SLAB4, 4));
		self::register("cut_sandstone", $factory->get(Ids::SANDSTONE, 2));
		self::register("cut_sandstone_slab", $factory->get(Ids::STONE_SLAB4, 3));
		self::register("cyan_glazed_terracotta", $factory->get(Ids::CYAN_GLAZED_TERRACOTTA, 2));
		self::register("dandelion", $factory->get(Ids::DANDELION, 0));
		self::register("dark_oak_button", $factory->get(Ids::DARK_OAK_BUTTON, 0));
		self::register("dark_oak_door", $factory->get(Ids::DARK_OAK_DOOR_BLOCK, 0));
		self::register("dark_oak_fence", $factory->get(Ids::FENCE, 5));
		self::register("dark_oak_fence_gate", $factory->get(Ids::DARK_OAK_FENCE_GATE, 0));
		self::register("dark_oak_leaves", $factory->get(Ids::LEAVES2, 1));
		self::register("dark_oak_log", $factory->get(Ids::LOG2, 1));
		self::register("dark_oak_planks", $factory->get(Ids::PLANKS, 5));
		self::register("dark_oak_pressure_plate", $factory->get(Ids::DARK_OAK_PRESSURE_PLATE, 0));
		self::register("dark_oak_sapling", $factory->get(Ids::SAPLING, 5));
		self::register("dark_oak_sign", $factory->get(Ids::DARKOAK_STANDING_SIGN, 0));
		self::register("dark_oak_slab", $factory->get(Ids::WOODEN_SLAB, 5));
		self::register("dark_oak_stairs", $factory->get(Ids::DARK_OAK_STAIRS, 0));
		self::register("dark_oak_trapdoor", $factory->get(Ids::DARK_OAK_TRAPDOOR, 0));
		self::register("dark_oak_wall_sign", $factory->get(Ids::DARKOAK_WALL_SIGN, 2));
		self::register("dark_oak_wood", $factory->get(Ids::WOOD, 5));
		self::register("dark_prismarine", $factory->get(Ids::PRISMARINE, 1));
		self::register("dark_prismarine_slab", $factory->get(Ids::STONE_SLAB2, 3));
		self::register("dark_prismarine_stairs", $factory->get(Ids::DARK_PRISMARINE_STAIRS, 0));
		self::register("daylight_sensor", $factory->get(Ids::DAYLIGHT_DETECTOR, 0));
		self::register("dead_bush", $factory->get(Ids::DEADBUSH, 0));
		self::register("detector_rail", $factory->get(Ids::DETECTOR_RAIL, 0));
		self::register("diamond", $factory->get(Ids::DIAMOND_BLOCK, 0));
		self::register("diamond_ore", $factory->get(Ids::DIAMOND_ORE, 0));
		self::register("diorite", $factory->get(Ids::STONE, 3));
		self::register("diorite_slab", $factory->get(Ids::STONE_SLAB3, 4));
		self::register("diorite_stairs", $factory->get(Ids::DIORITE_STAIRS, 0));
		self::register("diorite_wall", $factory->get(Ids::COBBLESTONE_WALL, 3));
		self::register("dirt", $factory->get(Ids::DIRT, 0));
		self::register("double_tallgrass", $factory->get(Ids::DOUBLE_PLANT, 2));
		self::register("dragon_egg", $factory->get(Ids::DRAGON_EGG, 0));
		self::register("dried_kelp", $factory->get(Ids::DRIED_KELP_BLOCK, 0));
		self::register("dyed_shulker_box", $factory->get(Ids::SHULKER_BOX, 0));
		self::register("element_actinium", $factory->get(Ids::ELEMENT_89, 0));
		self::register("element_aluminum", $factory->get(Ids::ELEMENT_13, 0));
		self::register("element_americium", $factory->get(Ids::ELEMENT_95, 0));
		self::register("element_antimony", $factory->get(Ids::ELEMENT_51, 0));
		self::register("element_argon", $factory->get(Ids::ELEMENT_18, 0));
		self::register("element_arsenic", $factory->get(Ids::ELEMENT_33, 0));
		self::register("element_astatine", $factory->get(Ids::ELEMENT_85, 0));
		self::register("element_barium", $factory->get(Ids::ELEMENT_56, 0));
		self::register("element_berkelium", $factory->get(Ids::ELEMENT_97, 0));
		self::register("element_beryllium", $factory->get(Ids::ELEMENT_4, 0));
		self::register("element_bismuth", $factory->get(Ids::ELEMENT_83, 0));
		self::register("element_bohrium", $factory->get(Ids::ELEMENT_107, 0));
		self::register("element_boron", $factory->get(Ids::ELEMENT_5, 0));
		self::register("element_bromine", $factory->get(Ids::ELEMENT_35, 0));
		self::register("element_cadmium", $factory->get(Ids::ELEMENT_48, 0));
		self::register("element_calcium", $factory->get(Ids::ELEMENT_20, 0));
		self::register("element_californium", $factory->get(Ids::ELEMENT_98, 0));
		self::register("element_carbon", $factory->get(Ids::ELEMENT_6, 0));
		self::register("element_cerium", $factory->get(Ids::ELEMENT_58, 0));
		self::register("element_cesium", $factory->get(Ids::ELEMENT_55, 0));
		self::register("element_chlorine", $factory->get(Ids::ELEMENT_17, 0));
		self::register("element_chromium", $factory->get(Ids::ELEMENT_24, 0));
		self::register("element_cobalt", $factory->get(Ids::ELEMENT_27, 0));
		self::register("element_constructor", $factory->get(Ids::CHEMISTRY_TABLE, 8));
		self::register("element_copernicium", $factory->get(Ids::ELEMENT_112, 0));
		self::register("element_copper", $factory->get(Ids::ELEMENT_29, 0));
		self::register("element_curium", $factory->get(Ids::ELEMENT_96, 0));
		self::register("element_darmstadtium", $factory->get(Ids::ELEMENT_110, 0));
		self::register("element_dubnium", $factory->get(Ids::ELEMENT_105, 0));
		self::register("element_dysprosium", $factory->get(Ids::ELEMENT_66, 0));
		self::register("element_einsteinium", $factory->get(Ids::ELEMENT_99, 0));
		self::register("element_erbium", $factory->get(Ids::ELEMENT_68, 0));
		self::register("element_europium", $factory->get(Ids::ELEMENT_63, 0));
		self::register("element_fermium", $factory->get(Ids::ELEMENT_100, 0));
		self::register("element_flerovium", $factory->get(Ids::ELEMENT_114, 0));
		self::register("element_fluorine", $factory->get(Ids::ELEMENT_9, 0));
		self::register("element_francium", $factory->get(Ids::ELEMENT_87, 0));
		self::register("element_gadolinium", $factory->get(Ids::ELEMENT_64, 0));
		self::register("element_gallium", $factory->get(Ids::ELEMENT_31, 0));
		self::register("element_germanium", $factory->get(Ids::ELEMENT_32, 0));
		self::register("element_gold", $factory->get(Ids::ELEMENT_79, 0));
		self::register("element_hafnium", $factory->get(Ids::ELEMENT_72, 0));
		self::register("element_hassium", $factory->get(Ids::ELEMENT_108, 0));
		self::register("element_helium", $factory->get(Ids::ELEMENT_2, 0));
		self::register("element_holmium", $factory->get(Ids::ELEMENT_67, 0));
		self::register("element_hydrogen", $factory->get(Ids::ELEMENT_1, 0));
		self::register("element_indium", $factory->get(Ids::ELEMENT_49, 0));
		self::register("element_iodine", $factory->get(Ids::ELEMENT_53, 0));
		self::register("element_iridium", $factory->get(Ids::ELEMENT_77, 0));
		self::register("element_iron", $factory->get(Ids::ELEMENT_26, 0));
		self::register("element_krypton", $factory->get(Ids::ELEMENT_36, 0));
		self::register("element_lanthanum", $factory->get(Ids::ELEMENT_57, 0));
		self::register("element_lawrencium", $factory->get(Ids::ELEMENT_103, 0));
		self::register("element_lead", $factory->get(Ids::ELEMENT_82, 0));
		self::register("element_lithium", $factory->get(Ids::ELEMENT_3, 0));
		self::register("element_livermorium", $factory->get(Ids::ELEMENT_116, 0));
		self::register("element_lutetium", $factory->get(Ids::ELEMENT_71, 0));
		self::register("element_magnesium", $factory->get(Ids::ELEMENT_12, 0));
		self::register("element_manganese", $factory->get(Ids::ELEMENT_25, 0));
		self::register("element_meitnerium", $factory->get(Ids::ELEMENT_109, 0));
		self::register("element_mendelevium", $factory->get(Ids::ELEMENT_101, 0));
		self::register("element_mercury", $factory->get(Ids::ELEMENT_80, 0));
		self::register("element_molybdenum", $factory->get(Ids::ELEMENT_42, 0));
		self::register("element_moscovium", $factory->get(Ids::ELEMENT_115, 0));
		self::register("element_neodymium", $factory->get(Ids::ELEMENT_60, 0));
		self::register("element_neon", $factory->get(Ids::ELEMENT_10, 0));
		self::register("element_neptunium", $factory->get(Ids::ELEMENT_93, 0));
		self::register("element_nickel", $factory->get(Ids::ELEMENT_28, 0));
		self::register("element_nihonium", $factory->get(Ids::ELEMENT_113, 0));
		self::register("element_niobium", $factory->get(Ids::ELEMENT_41, 0));
		self::register("element_nitrogen", $factory->get(Ids::ELEMENT_7, 0));
		self::register("element_nobelium", $factory->get(Ids::ELEMENT_102, 0));
		self::register("element_oganesson", $factory->get(Ids::ELEMENT_118, 0));
		self::register("element_osmium", $factory->get(Ids::ELEMENT_76, 0));
		self::register("element_oxygen", $factory->get(Ids::ELEMENT_8, 0));
		self::register("element_palladium", $factory->get(Ids::ELEMENT_46, 0));
		self::register("element_phosphorus", $factory->get(Ids::ELEMENT_15, 0));
		self::register("element_platinum", $factory->get(Ids::ELEMENT_78, 0));
		self::register("element_plutonium", $factory->get(Ids::ELEMENT_94, 0));
		self::register("element_polonium", $factory->get(Ids::ELEMENT_84, 0));
		self::register("element_potassium", $factory->get(Ids::ELEMENT_19, 0));
		self::register("element_praseodymium", $factory->get(Ids::ELEMENT_59, 0));
		self::register("element_promethium", $factory->get(Ids::ELEMENT_61, 0));
		self::register("element_protactinium", $factory->get(Ids::ELEMENT_91, 0));
		self::register("element_radium", $factory->get(Ids::ELEMENT_88, 0));
		self::register("element_radon", $factory->get(Ids::ELEMENT_86, 0));
		self::register("element_rhenium", $factory->get(Ids::ELEMENT_75, 0));
		self::register("element_rhodium", $factory->get(Ids::ELEMENT_45, 0));
		self::register("element_roentgenium", $factory->get(Ids::ELEMENT_111, 0));
		self::register("element_rubidium", $factory->get(Ids::ELEMENT_37, 0));
		self::register("element_ruthenium", $factory->get(Ids::ELEMENT_44, 0));
		self::register("element_rutherfordium", $factory->get(Ids::ELEMENT_104, 0));
		self::register("element_samarium", $factory->get(Ids::ELEMENT_62, 0));
		self::register("element_scandium", $factory->get(Ids::ELEMENT_21, 0));
		self::register("element_seaborgium", $factory->get(Ids::ELEMENT_106, 0));
		self::register("element_selenium", $factory->get(Ids::ELEMENT_34, 0));
		self::register("element_silicon", $factory->get(Ids::ELEMENT_14, 0));
		self::register("element_silver", $factory->get(Ids::ELEMENT_47, 0));
		self::register("element_sodium", $factory->get(Ids::ELEMENT_11, 0));
		self::register("element_strontium", $factory->get(Ids::ELEMENT_38, 0));
		self::register("element_sulfur", $factory->get(Ids::ELEMENT_16, 0));
		self::register("element_tantalum", $factory->get(Ids::ELEMENT_73, 0));
		self::register("element_technetium", $factory->get(Ids::ELEMENT_43, 0));
		self::register("element_tellurium", $factory->get(Ids::ELEMENT_52, 0));
		self::register("element_tennessine", $factory->get(Ids::ELEMENT_117, 0));
		self::register("element_terbium", $factory->get(Ids::ELEMENT_65, 0));
		self::register("element_thallium", $factory->get(Ids::ELEMENT_81, 0));
		self::register("element_thorium", $factory->get(Ids::ELEMENT_90, 0));
		self::register("element_thulium", $factory->get(Ids::ELEMENT_69, 0));
		self::register("element_tin", $factory->get(Ids::ELEMENT_50, 0));
		self::register("element_titanium", $factory->get(Ids::ELEMENT_22, 0));
		self::register("element_tungsten", $factory->get(Ids::ELEMENT_74, 0));
		self::register("element_uranium", $factory->get(Ids::ELEMENT_92, 0));
		self::register("element_vanadium", $factory->get(Ids::ELEMENT_23, 0));
		self::register("element_xenon", $factory->get(Ids::ELEMENT_54, 0));
		self::register("element_ytterbium", $factory->get(Ids::ELEMENT_70, 0));
		self::register("element_yttrium", $factory->get(Ids::ELEMENT_39, 0));
		self::register("element_zero", $factory->get(Ids::ELEMENT_0, 0));
		self::register("element_zinc", $factory->get(Ids::ELEMENT_30, 0));
		self::register("element_zirconium", $factory->get(Ids::ELEMENT_40, 0));
		self::register("emerald", $factory->get(Ids::EMERALD_BLOCK, 0));
		self::register("emerald_ore", $factory->get(Ids::EMERALD_ORE, 0));
		self::register("enchanting_table", $factory->get(Ids::ENCHANTING_TABLE, 0));
		self::register("end_portal_frame", $factory->get(Ids::END_PORTAL_FRAME, 0));
		self::register("end_rod", $factory->get(Ids::END_ROD, 0));
		self::register("end_stone", $factory->get(Ids::END_STONE, 0));
		self::register("end_stone_brick_slab", $factory->get(Ids::STONE_SLAB3, 0));
		self::register("end_stone_brick_stairs", $factory->get(Ids::END_BRICK_STAIRS, 0));
		self::register("end_stone_brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 10));
		self::register("end_stone_bricks", $factory->get(Ids::END_BRICKS, 0));
		self::register("ender_chest", $factory->get(Ids::ENDER_CHEST, 2));
		self::register("fake_wooden_slab", $factory->get(Ids::STONE_SLAB, 2));
		self::register("farmland", $factory->get(Ids::FARMLAND, 0));
		self::register("fern", $factory->get(Ids::TALLGRASS, 2));
		self::register("fire", $factory->get(Ids::FIRE, 0));
		self::register("fletching_table", $factory->get(Ids::FLETCHING_TABLE, 0));
		self::register("flower_pot", $factory->get(Ids::FLOWER_POT_BLOCK, 0));
		self::register("frosted_ice", $factory->get(Ids::FROSTED_ICE, 0));
		self::register("furnace", $factory->get(Ids::FURNACE, 2));
		self::register("glass", $factory->get(Ids::GLASS, 0));
		self::register("glass_pane", $factory->get(Ids::GLASS_PANE, 0));
		self::register("glowing_obsidian", $factory->get(Ids::GLOWINGOBSIDIAN, 0));
		self::register("glowstone", $factory->get(Ids::GLOWSTONE, 0));
		self::register("gold", $factory->get(Ids::GOLD_BLOCK, 0));
		self::register("gold_ore", $factory->get(Ids::GOLD_ORE, 0));
		self::register("granite", $factory->get(Ids::STONE, 1));
		self::register("granite_slab", $factory->get(Ids::STONE_SLAB3, 6));
		self::register("granite_stairs", $factory->get(Ids::GRANITE_STAIRS, 0));
		self::register("granite_wall", $factory->get(Ids::COBBLESTONE_WALL, 2));
		self::register("grass", $factory->get(Ids::GRASS, 0));
		self::register("grass_path", $factory->get(Ids::GRASS_PATH, 0));
		self::register("gravel", $factory->get(Ids::GRAVEL, 0));
		self::register("gray_glazed_terracotta", $factory->get(Ids::GRAY_GLAZED_TERRACOTTA, 2));
		self::register("green_glazed_terracotta", $factory->get(Ids::GREEN_GLAZED_TERRACOTTA, 2));
		self::register("green_torch", $factory->get(Ids::COLORED_TORCH_RG, 13));
		self::register("hardened_clay", $factory->get(Ids::HARDENED_CLAY, 0));
		self::register("hardened_glass", $factory->get(Ids::HARD_GLASS, 0));
		self::register("hardened_glass_pane", $factory->get(Ids::HARD_GLASS_PANE, 0));
		self::register("hay_bale", $factory->get(Ids::HAY_BALE, 0));
		self::register("hopper", $factory->get(Ids::HOPPER_BLOCK, 0));
		self::register("ice", $factory->get(Ids::ICE, 0));
		self::register("infested_chiseled_stone_brick", $factory->get(Ids::MONSTER_EGG, 5));
		self::register("infested_cobblestone", $factory->get(Ids::MONSTER_EGG, 1));
		self::register("infested_cracked_stone_brick", $factory->get(Ids::MONSTER_EGG, 4));
		self::register("infested_mossy_stone_brick", $factory->get(Ids::MONSTER_EGG, 3));
		self::register("infested_stone", $factory->get(Ids::MONSTER_EGG, 0));
		self::register("infested_stone_brick", $factory->get(Ids::MONSTER_EGG, 2));
		self::register("info_update", $factory->get(Ids::INFO_UPDATE, 0));
		self::register("info_update2", $factory->get(Ids::INFO_UPDATE2, 0));
		self::register("invisible_bedrock", $factory->get(Ids::INVISIBLEBEDROCK, 0));
		self::register("iron", $factory->get(Ids::IRON_BLOCK, 0));
		self::register("iron_bars", $factory->get(Ids::IRON_BARS, 0));
		self::register("iron_door", $factory->get(Ids::IRON_DOOR_BLOCK, 0));
		self::register("iron_ore", $factory->get(Ids::IRON_ORE, 0));
		self::register("iron_trapdoor", $factory->get(Ids::IRON_TRAPDOOR, 0));
		self::register("item_frame", $factory->get(Ids::FRAME_BLOCK, 0));
		self::register("jukebox", $factory->get(Ids::JUKEBOX, 0));
		self::register("jungle_button", $factory->get(Ids::JUNGLE_BUTTON, 0));
		self::register("jungle_door", $factory->get(Ids::JUNGLE_DOOR_BLOCK, 0));
		self::register("jungle_fence", $factory->get(Ids::FENCE, 3));
		self::register("jungle_fence_gate", $factory->get(Ids::JUNGLE_FENCE_GATE, 0));
		self::register("jungle_leaves", $factory->get(Ids::LEAVES, 3));
		self::register("jungle_log", $factory->get(Ids::LOG, 3));
		self::register("jungle_planks", $factory->get(Ids::PLANKS, 3));
		self::register("jungle_pressure_plate", $factory->get(Ids::JUNGLE_PRESSURE_PLATE, 0));
		self::register("jungle_sapling", $factory->get(Ids::SAPLING, 3));
		self::register("jungle_sign", $factory->get(Ids::JUNGLE_STANDING_SIGN, 0));
		self::register("jungle_slab", $factory->get(Ids::WOODEN_SLAB, 3));
		self::register("jungle_stairs", $factory->get(Ids::JUNGLE_STAIRS, 0));
		self::register("jungle_trapdoor", $factory->get(Ids::JUNGLE_TRAPDOOR, 0));
		self::register("jungle_wall_sign", $factory->get(Ids::JUNGLE_WALL_SIGN, 2));
		self::register("jungle_wood", $factory->get(Ids::WOOD, 3));
		self::register("lab_table", $factory->get(Ids::CHEMISTRY_TABLE, 12));
		self::register("ladder", $factory->get(Ids::LADDER, 2));
		self::register("lantern", $factory->get(Ids::LANTERN, 0));
		self::register("lapis_lazuli", $factory->get(Ids::LAPIS_BLOCK, 0));
		self::register("lapis_lazuli_ore", $factory->get(Ids::LAPIS_ORE, 0));
		self::register("large_fern", $factory->get(Ids::DOUBLE_PLANT, 3));
		self::register("lava", $factory->get(Ids::FLOWING_LAVA, 0));
		self::register("lectern", $factory->get(Ids::LECTERN, 0));
		self::register("legacy_stonecutter", $factory->get(Ids::STONECUTTER, 0));
		self::register("lever", $factory->get(Ids::LEVER, 0));
		self::register("light_blue_glazed_terracotta", $factory->get(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, 2));
		self::register("light_gray_glazed_terracotta", $factory->get(Ids::SILVER_GLAZED_TERRACOTTA, 2));
		self::register("lilac", $factory->get(Ids::DOUBLE_PLANT, 1));
		self::register("lily_of_the_valley", $factory->get(Ids::POPPY, 10));
		self::register("lily_pad", $factory->get(Ids::LILY_PAD, 0));
		self::register("lime_glazed_terracotta", $factory->get(Ids::LIME_GLAZED_TERRACOTTA, 2));
		self::register("lit_pumpkin", $factory->get(Ids::JACK_O_LANTERN, 0));
		self::register("loom", $factory->get(Ids::LOOM, 0));
		self::register("magenta_glazed_terracotta", $factory->get(Ids::MAGENTA_GLAZED_TERRACOTTA, 2));
		self::register("magma", $factory->get(Ids::MAGMA, 0));
		self::register("material_reducer", $factory->get(Ids::CHEMISTRY_TABLE, 4));
		self::register("melon", $factory->get(Ids::MELON_BLOCK, 0));
		self::register("melon_stem", $factory->get(Ids::MELON_STEM, 0));
		self::register("mob_head", $factory->get(Ids::MOB_HEAD_BLOCK, 2));
		self::register("monster_spawner", $factory->get(Ids::MOB_SPAWNER, 0));
		self::register("mossy_cobblestone", $factory->get(Ids::MOSSY_COBBLESTONE, 0));
		self::register("mossy_cobblestone_slab", $factory->get(Ids::STONE_SLAB2, 5));
		self::register("mossy_cobblestone_stairs", $factory->get(Ids::MOSSY_COBBLESTONE_STAIRS, 0));
		self::register("mossy_cobblestone_wall", $factory->get(Ids::COBBLESTONE_WALL, 1));
		self::register("mossy_stone_brick_slab", $factory->get(Ids::STONE_SLAB4, 0));
		self::register("mossy_stone_brick_stairs", $factory->get(Ids::MOSSY_STONE_BRICK_STAIRS, 0));
		self::register("mossy_stone_brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 8));
		self::register("mossy_stone_bricks", $factory->get(Ids::STONEBRICK, 1));
		self::register("mushroom_stem", $factory->get(Ids::BROWN_MUSHROOM_BLOCK, 10));
		self::register("mycelium", $factory->get(Ids::MYCELIUM, 0));
		self::register("nether_brick_fence", $factory->get(Ids::NETHER_BRICK_FENCE, 0));
		self::register("nether_brick_slab", $factory->get(Ids::STONE_SLAB, 7));
		self::register("nether_brick_stairs", $factory->get(Ids::NETHER_BRICK_STAIRS, 0));
		self::register("nether_brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 9));
		self::register("nether_bricks", $factory->get(Ids::NETHER_BRICK_BLOCK, 0));
		self::register("nether_portal", $factory->get(Ids::PORTAL, 1));
		self::register("nether_quartz_ore", $factory->get(Ids::NETHER_QUARTZ_ORE, 0));
		self::register("nether_reactor_core", $factory->get(Ids::NETHERREACTOR, 0));
		self::register("nether_wart", $factory->get(Ids::NETHER_WART_PLANT, 0));
		self::register("nether_wart_block", $factory->get(Ids::NETHER_WART_BLOCK, 0));
		self::register("netherrack", $factory->get(Ids::NETHERRACK, 0));
		self::register("note_block", $factory->get(Ids::NOTEBLOCK, 0));
		self::register("oak_button", $factory->get(Ids::WOODEN_BUTTON, 0));
		self::register("oak_door", $factory->get(Ids::OAK_DOOR_BLOCK, 0));
		self::register("oak_fence", $factory->get(Ids::FENCE, 0));
		self::register("oak_fence_gate", $factory->get(Ids::FENCE_GATE, 0));
		self::register("oak_leaves", $factory->get(Ids::LEAVES, 0));
		self::register("oak_log", $factory->get(Ids::LOG, 0));
		self::register("oak_planks", $factory->get(Ids::PLANKS, 0));
		self::register("oak_pressure_plate", $factory->get(Ids::WOODEN_PRESSURE_PLATE, 0));
		self::register("oak_sapling", $factory->get(Ids::SAPLING, 0));
		self::register("oak_sign", $factory->get(Ids::SIGN_POST, 0));
		self::register("oak_slab", $factory->get(Ids::WOODEN_SLAB, 0));
		self::register("oak_stairs", $factory->get(Ids::OAK_STAIRS, 0));
		self::register("oak_trapdoor", $factory->get(Ids::TRAPDOOR, 0));
		self::register("oak_wall_sign", $factory->get(Ids::WALL_SIGN, 2));
		self::register("oak_wood", $factory->get(Ids::WOOD, 0));
		self::register("obsidian", $factory->get(Ids::OBSIDIAN, 0));
		self::register("orange_glazed_terracotta", $factory->get(Ids::ORANGE_GLAZED_TERRACOTTA, 2));
		self::register("orange_tulip", $factory->get(Ids::POPPY, 5));
		self::register("oxeye_daisy", $factory->get(Ids::POPPY, 8));
		self::register("packed_ice", $factory->get(Ids::PACKED_ICE, 0));
		self::register("peony", $factory->get(Ids::DOUBLE_PLANT, 5));
		self::register("pink_glazed_terracotta", $factory->get(Ids::PINK_GLAZED_TERRACOTTA, 2));
		self::register("pink_tulip", $factory->get(Ids::POPPY, 7));
		self::register("podzol", $factory->get(Ids::PODZOL, 0));
		self::register("polished_andesite", $factory->get(Ids::STONE, 6));
		self::register("polished_andesite_slab", $factory->get(Ids::STONE_SLAB3, 2));
		self::register("polished_andesite_stairs", $factory->get(Ids::POLISHED_ANDESITE_STAIRS, 0));
		self::register("polished_diorite", $factory->get(Ids::STONE, 4));
		self::register("polished_diorite_slab", $factory->get(Ids::STONE_SLAB3, 5));
		self::register("polished_diorite_stairs", $factory->get(Ids::POLISHED_DIORITE_STAIRS, 0));
		self::register("polished_granite", $factory->get(Ids::STONE, 2));
		self::register("polished_granite_slab", $factory->get(Ids::STONE_SLAB3, 7));
		self::register("polished_granite_stairs", $factory->get(Ids::POLISHED_GRANITE_STAIRS, 0));
		self::register("poppy", $factory->get(Ids::POPPY, 0));
		self::register("potatoes", $factory->get(Ids::POTATOES, 0));
		self::register("powered_rail", $factory->get(Ids::GOLDEN_RAIL, 0));
		self::register("prismarine", $factory->get(Ids::PRISMARINE, 0));
		self::register("prismarine_bricks", $factory->get(Ids::PRISMARINE, 2));
		self::register("prismarine_bricks_slab", $factory->get(Ids::STONE_SLAB2, 4));
		self::register("prismarine_bricks_stairs", $factory->get(Ids::PRISMARINE_BRICKS_STAIRS, 0));
		self::register("prismarine_slab", $factory->get(Ids::STONE_SLAB2, 2));
		self::register("prismarine_stairs", $factory->get(Ids::PRISMARINE_STAIRS, 0));
		self::register("prismarine_wall", $factory->get(Ids::COBBLESTONE_WALL, 11));
		self::register("pumpkin", $factory->get(Ids::PUMPKIN, 0));
		self::register("pumpkin_stem", $factory->get(Ids::PUMPKIN_STEM, 0));
		self::register("purple_glazed_terracotta", $factory->get(Ids::PURPLE_GLAZED_TERRACOTTA, 2));
		self::register("purple_torch", $factory->get(Ids::COLORED_TORCH_BP, 13));
		self::register("purpur", $factory->get(Ids::PURPUR_BLOCK, 0));
		self::register("purpur_pillar", $factory->get(Ids::PURPUR_BLOCK, 2));
		self::register("purpur_slab", $factory->get(Ids::STONE_SLAB2, 1));
		self::register("purpur_stairs", $factory->get(Ids::PURPUR_STAIRS, 0));
		self::register("quartz", $factory->get(Ids::QUARTZ_BLOCK, 0));
		self::register("quartz_pillar", $factory->get(Ids::QUARTZ_BLOCK, 2));
		self::register("quartz_slab", $factory->get(Ids::STONE_SLAB, 6));
		self::register("quartz_stairs", $factory->get(Ids::QUARTZ_STAIRS, 0));
		self::register("rail", $factory->get(Ids::RAIL, 0));
		self::register("red_glazed_terracotta", $factory->get(Ids::RED_GLAZED_TERRACOTTA, 2));
		self::register("red_mushroom", $factory->get(Ids::RED_MUSHROOM, 0));
		self::register("red_mushroom_block", $factory->get(Ids::RED_MUSHROOM_BLOCK, 0));
		self::register("red_nether_brick_slab", $factory->get(Ids::STONE_SLAB2, 7));
		self::register("red_nether_brick_stairs", $factory->get(Ids::RED_NETHER_BRICK_STAIRS, 0));
		self::register("red_nether_brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 13));
		self::register("red_nether_bricks", $factory->get(Ids::RED_NETHER_BRICK, 0));
		self::register("red_sand", $factory->get(Ids::SAND, 1));
		self::register("red_sandstone", $factory->get(Ids::RED_SANDSTONE, 0));
		self::register("red_sandstone_slab", $factory->get(Ids::STONE_SLAB2, 0));
		self::register("red_sandstone_stairs", $factory->get(Ids::RED_SANDSTONE_STAIRS, 0));
		self::register("red_sandstone_wall", $factory->get(Ids::COBBLESTONE_WALL, 12));
		self::register("red_torch", $factory->get(Ids::COLORED_TORCH_RG, 5));
		self::register("red_tulip", $factory->get(Ids::POPPY, 4));
		self::register("redstone", $factory->get(Ids::REDSTONE_BLOCK, 0));
		self::register("redstone_comparator", $factory->get(Ids::COMPARATOR_BLOCK, 0));
		self::register("redstone_lamp", $factory->get(Ids::REDSTONE_LAMP, 0));
		self::register("redstone_ore", $factory->get(Ids::REDSTONE_ORE, 0));
		self::register("redstone_repeater", $factory->get(Ids::REPEATER_BLOCK, 0));
		self::register("redstone_torch", $factory->get(Ids::LIT_REDSTONE_TORCH, 5));
		self::register("redstone_wire", $factory->get(Ids::REDSTONE_WIRE, 0));
		self::register("reserved6", $factory->get(Ids::RESERVED6, 0));
		self::register("rose_bush", $factory->get(Ids::DOUBLE_PLANT, 4));
		self::register("sand", $factory->get(Ids::SAND, 0));
		self::register("sandstone", $factory->get(Ids::SANDSTONE, 0));
		self::register("sandstone_slab", $factory->get(Ids::STONE_SLAB, 1));
		self::register("sandstone_stairs", $factory->get(Ids::SANDSTONE_STAIRS, 0));
		self::register("sandstone_wall", $factory->get(Ids::COBBLESTONE_WALL, 5));
		self::register("sea_lantern", $factory->get(Ids::SEALANTERN, 0));
		self::register("sea_pickle", $factory->get(Ids::SEA_PICKLE, 0));
		self::register("shulker_box", $factory->get(Ids::UNDYED_SHULKER_BOX, 0));
		self::register("slime", $factory->get(Ids::SLIME, 0));
		self::register("smoker", $factory->get(Ids::SMOKER, 2));
		self::register("smooth_quartz", $factory->get(Ids::QUARTZ_BLOCK, 3));
		self::register("smooth_quartz_slab", $factory->get(Ids::STONE_SLAB4, 1));
		self::register("smooth_quartz_stairs", $factory->get(Ids::SMOOTH_QUARTZ_STAIRS, 0));
		self::register("smooth_red_sandstone", $factory->get(Ids::RED_SANDSTONE, 3));
		self::register("smooth_red_sandstone_slab", $factory->get(Ids::STONE_SLAB3, 1));
		self::register("smooth_red_sandstone_stairs", $factory->get(Ids::SMOOTH_RED_SANDSTONE_STAIRS, 0));
		self::register("smooth_sandstone", $factory->get(Ids::SANDSTONE, 3));
		self::register("smooth_sandstone_slab", $factory->get(Ids::STONE_SLAB2, 6));
		self::register("smooth_sandstone_stairs", $factory->get(Ids::SMOOTH_SANDSTONE_STAIRS, 0));
		self::register("smooth_stone", $factory->get(Ids::SMOOTH_STONE, 0));
		self::register("smooth_stone_slab", $factory->get(Ids::STONE_SLAB, 0));
		self::register("snow", $factory->get(Ids::SNOW, 0));
		self::register("snow_layer", $factory->get(Ids::SNOW_LAYER, 0));
		self::register("soul_sand", $factory->get(Ids::SOUL_SAND, 0));
		self::register("sponge", $factory->get(Ids::SPONGE, 0));
		self::register("spruce_button", $factory->get(Ids::SPRUCE_BUTTON, 0));
		self::register("spruce_door", $factory->get(Ids::SPRUCE_DOOR_BLOCK, 0));
		self::register("spruce_fence", $factory->get(Ids::FENCE, 1));
		self::register("spruce_fence_gate", $factory->get(Ids::SPRUCE_FENCE_GATE, 0));
		self::register("spruce_leaves", $factory->get(Ids::LEAVES, 1));
		self::register("spruce_log", $factory->get(Ids::LOG, 1));
		self::register("spruce_planks", $factory->get(Ids::PLANKS, 1));
		self::register("spruce_pressure_plate", $factory->get(Ids::SPRUCE_PRESSURE_PLATE, 0));
		self::register("spruce_sapling", $factory->get(Ids::SAPLING, 1));
		self::register("spruce_sign", $factory->get(Ids::SPRUCE_STANDING_SIGN, 0));
		self::register("spruce_slab", $factory->get(Ids::WOODEN_SLAB, 1));
		self::register("spruce_stairs", $factory->get(Ids::SPRUCE_STAIRS, 0));
		self::register("spruce_trapdoor", $factory->get(Ids::SPRUCE_TRAPDOOR, 0));
		self::register("spruce_wall_sign", $factory->get(Ids::SPRUCE_WALL_SIGN, 2));
		self::register("spruce_wood", $factory->get(Ids::WOOD, 1));
		self::register("stained_clay", $factory->get(Ids::STAINED_CLAY, 0));
		self::register("stained_glass", $factory->get(Ids::STAINED_GLASS, 0));
		self::register("stained_glass_pane", $factory->get(Ids::STAINED_GLASS_PANE, 0));
		self::register("stained_hardened_glass", $factory->get(Ids::HARD_STAINED_GLASS, 0));
		self::register("stained_hardened_glass_pane", $factory->get(Ids::HARD_STAINED_GLASS_PANE, 0));
		self::register("stone", $factory->get(Ids::STONE, 0));
		self::register("stone_brick_slab", $factory->get(Ids::STONE_SLAB, 5));
		self::register("stone_brick_stairs", $factory->get(Ids::STONE_BRICK_STAIRS, 0));
		self::register("stone_brick_wall", $factory->get(Ids::COBBLESTONE_WALL, 7));
		self::register("stone_bricks", $factory->get(Ids::STONEBRICK, 0));
		self::register("stone_button", $factory->get(Ids::STONE_BUTTON, 0));
		self::register("stone_pressure_plate", $factory->get(Ids::STONE_PRESSURE_PLATE, 0));
		self::register("stone_slab", $factory->get(Ids::STONE_SLAB4, 2));
		self::register("stone_stairs", $factory->get(Ids::NORMAL_STONE_STAIRS, 0));
		self::register("stonecutter", $factory->get(Ids::STONECUTTER_BLOCK, 2));
		self::register("stripped_acacia_log", $factory->get(Ids::STRIPPED_ACACIA_LOG, 0));
		self::register("stripped_acacia_wood", $factory->get(Ids::WOOD, 12));
		self::register("stripped_birch_log", $factory->get(Ids::STRIPPED_BIRCH_LOG, 0));
		self::register("stripped_birch_wood", $factory->get(Ids::WOOD, 10));
		self::register("stripped_dark_oak_log", $factory->get(Ids::STRIPPED_DARK_OAK_LOG, 0));
		self::register("stripped_dark_oak_wood", $factory->get(Ids::WOOD, 13));
		self::register("stripped_jungle_log", $factory->get(Ids::STRIPPED_JUNGLE_LOG, 0));
		self::register("stripped_jungle_wood", $factory->get(Ids::WOOD, 11));
		self::register("stripped_oak_log", $factory->get(Ids::STRIPPED_OAK_LOG, 0));
		self::register("stripped_oak_wood", $factory->get(Ids::WOOD, 8));
		self::register("stripped_spruce_log", $factory->get(Ids::STRIPPED_SPRUCE_LOG, 0));
		self::register("stripped_spruce_wood", $factory->get(Ids::WOOD, 9));
		self::register("sugarcane", $factory->get(Ids::REEDS_BLOCK, 0));
		self::register("sunflower", $factory->get(Ids::DOUBLE_PLANT, 0));
		self::register("sweet_berry_bush", $factory->get(Ids::SWEET_BERRY_BUSH, 0));
		self::register("tall_grass", $factory->get(Ids::TALLGRASS, 1));
		self::register("tnt", $factory->get(Ids::TNT, 0));
		self::register("torch", $factory->get(Ids::TORCH, 5));
		self::register("trapped_chest", $factory->get(Ids::TRAPPED_CHEST, 2));
		self::register("tripwire", $factory->get(Ids::TRIPWIRE, 0));
		self::register("tripwire_hook", $factory->get(Ids::TRIPWIRE_HOOK, 0));
		self::register("underwater_torch", $factory->get(Ids::UNDERWATER_TORCH, 5));
		self::register("vines", $factory->get(Ids::VINE, 0));
		self::register("wall_banner", $factory->get(Ids::WALL_BANNER, 2));
		self::register("wall_coral_fan", $factory->get(Ids::CORAL_FAN_HANG, 0));
		self::register("water", $factory->get(Ids::FLOWING_WATER, 0));
		self::register("weighted_pressure_plate_heavy", $factory->get(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, 0));
		self::register("weighted_pressure_plate_light", $factory->get(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, 0));
		self::register("wheat", $factory->get(Ids::WHEAT_BLOCK, 0));
		self::register("white_glazed_terracotta", $factory->get(Ids::WHITE_GLAZED_TERRACOTTA, 2));
		self::register("white_tulip", $factory->get(Ids::POPPY, 6));
		self::register("wool", $factory->get(Ids::WOOL, 0));
		self::register("yellow_glazed_terracotta", $factory->get(Ids::YELLOW_GLAZED_TERRACOTTA, 2));
	}
}
