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

use pocketmine\utils\RegistryTrait;
use pocketmine\utils\Utils;
use function assert;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
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
 * @method static Sign ACACIA_SIGN()
 * @method static WoodenSlab ACACIA_SLAB()
 * @method static WoodenStairs ACACIA_STAIRS()
 * @method static WoodenTrapdoor ACACIA_TRAPDOOR()
 * @method static Wood ACACIA_WOOD()
 * @method static ActivatorRail ACTIVATOR_RAIL()
 * @method static Air AIR()
 * @method static Flower ALLIUM()
 * @method static Opaque ANDESITE()
 * @method static Slab ANDESITE_SLAB()
 * @method static Stair ANDESITE_STAIRS()
 * @method static Wall ANDESITE_WALL()
 * @method static Anvil ANVIL()
 * @method static Flower AZURE_BLUET()
 * @method static Banner BANNER()
 * @method static Transparent BARRIER()
 * @method static Bed BED()
 * @method static Bedrock BEDROCK()
 * @method static Beetroot BEETROOTS()
 * @method static WoodenButton BIRCH_BUTTON()
 * @method static WoodenDoor BIRCH_DOOR()
 * @method static WoodenFence BIRCH_FENCE()
 * @method static FenceGate BIRCH_FENCE_GATE()
 * @method static Leaves BIRCH_LEAVES()
 * @method static Log BIRCH_LOG()
 * @method static Planks BIRCH_PLANKS()
 * @method static WoodenPressurePlate BIRCH_PRESSURE_PLATE()
 * @method static Sapling BIRCH_SAPLING()
 * @method static Sign BIRCH_SIGN()
 * @method static WoodenSlab BIRCH_SLAB()
 * @method static WoodenStairs BIRCH_STAIRS()
 * @method static WoodenTrapdoor BIRCH_TRAPDOOR()
 * @method static Wood BIRCH_WOOD()
 * @method static Carpet BLACK_CARPET()
 * @method static Concrete BLACK_CONCRETE()
 * @method static ConcretePowder BLACK_CONCRETE_POWDER()
 * @method static GlazedTerracotta BLACK_GLAZED_TERRACOTTA()
 * @method static HardenedClay BLACK_STAINED_CLAY()
 * @method static Glass BLACK_STAINED_GLASS()
 * @method static GlassPane BLACK_STAINED_GLASS_PANE()
 * @method static Wool BLACK_WOOL()
 * @method static Carpet BLUE_CARPET()
 * @method static Concrete BLUE_CONCRETE()
 * @method static ConcretePowder BLUE_CONCRETE_POWDER()
 * @method static GlazedTerracotta BLUE_GLAZED_TERRACOTTA()
 * @method static BlueIce BLUE_ICE()
 * @method static Flower BLUE_ORCHID()
 * @method static HardenedClay BLUE_STAINED_CLAY()
 * @method static Glass BLUE_STAINED_GLASS()
 * @method static GlassPane BLUE_STAINED_GLASS_PANE()
 * @method static Torch BLUE_TORCH()
 * @method static Wool BLUE_WOOL()
 * @method static BoneBlock BONE_BLOCK()
 * @method static Bookshelf BOOKSHELF()
 * @method static BrewingStand BREWING_STAND()
 * @method static Slab BRICK_SLAB()
 * @method static Stair BRICK_STAIRS()
 * @method static Wall BRICK_WALL()
 * @method static Opaque BRICKS()
 * @method static Carpet BROWN_CARPET()
 * @method static Concrete BROWN_CONCRETE()
 * @method static ConcretePowder BROWN_CONCRETE_POWDER()
 * @method static GlazedTerracotta BROWN_GLAZED_TERRACOTTA()
 * @method static BrownMushroom BROWN_MUSHROOM()
 * @method static BrownMushroomBlock BROWN_MUSHROOM_BLOCK()
 * @method static HardenedClay BROWN_STAINED_CLAY()
 * @method static Glass BROWN_STAINED_GLASS()
 * @method static GlassPane BROWN_STAINED_GLASS_PANE()
 * @method static Wool BROWN_WOOL()
 * @method static Cactus CACTUS()
 * @method static Cake CAKE()
 * @method static Carrot CARROTS()
 * @method static CarvedPumpkin CARVED_PUMPKIN()
 * @method static Chest CHEST()
 * @method static Opaque CHISELED_QUARTZ()
 * @method static Opaque CHISELED_RED_SANDSTONE()
 * @method static Opaque CHISELED_SANDSTONE()
 * @method static Opaque CHISELED_STONE_BRICKS()
 * @method static Clay CLAY()
 * @method static Coal COAL()
 * @method static CoalOre COAL_ORE()
 * @method static CoarseDirt COARSE_DIRT()
 * @method static Opaque COBBLESTONE()
 * @method static Slab COBBLESTONE_SLAB()
 * @method static Stair COBBLESTONE_STAIRS()
 * @method static Wall COBBLESTONE_WALL()
 * @method static Cobweb COBWEB()
 * @method static CocoaBlock COCOA_POD()
 * @method static Flower CORNFLOWER()
 * @method static Opaque CRACKED_STONE_BRICKS()
 * @method static CraftingTable CRAFTING_TABLE()
 * @method static Opaque CUT_RED_SANDSTONE()
 * @method static Slab CUT_RED_SANDSTONE_SLAB()
 * @method static Opaque CUT_SANDSTONE()
 * @method static Slab CUT_SANDSTONE_SLAB()
 * @method static Carpet CYAN_CARPET()
 * @method static Concrete CYAN_CONCRETE()
 * @method static ConcretePowder CYAN_CONCRETE_POWDER()
 * @method static GlazedTerracotta CYAN_GLAZED_TERRACOTTA()
 * @method static HardenedClay CYAN_STAINED_CLAY()
 * @method static Glass CYAN_STAINED_GLASS()
 * @method static GlassPane CYAN_STAINED_GLASS_PANE()
 * @method static Wool CYAN_WOOL()
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
 * @method static Sign DARK_OAK_SIGN()
 * @method static WoodenSlab DARK_OAK_SLAB()
 * @method static WoodenStairs DARK_OAK_STAIRS()
 * @method static WoodenTrapdoor DARK_OAK_TRAPDOOR()
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
 * @method static EndPortalFrame END_PORTAL_FRAME()
 * @method static EndRod END_ROD()
 * @method static Opaque END_STONE()
 * @method static Slab END_STONE_BRICK_SLAB()
 * @method static Stair END_STONE_BRICK_STAIRS()
 * @method static Wall END_STONE_BRICK_WALL()
 * @method static Opaque END_STONE_BRICKS()
 * @method static EnderChest ENDER_CHEST()
 * @method static Slab FAKE_WOODEN_SLAB()
 * @method static Farmland FARMLAND()
 * @method static TallGrass FERN()
 * @method static Fire FIRE()
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
 * @method static Carpet GRAY_CARPET()
 * @method static Concrete GRAY_CONCRETE()
 * @method static ConcretePowder GRAY_CONCRETE_POWDER()
 * @method static GlazedTerracotta GRAY_GLAZED_TERRACOTTA()
 * @method static HardenedClay GRAY_STAINED_CLAY()
 * @method static Glass GRAY_STAINED_GLASS()
 * @method static GlassPane GRAY_STAINED_GLASS_PANE()
 * @method static Wool GRAY_WOOL()
 * @method static Carpet GREEN_CARPET()
 * @method static Concrete GREEN_CONCRETE()
 * @method static ConcretePowder GREEN_CONCRETE_POWDER()
 * @method static GlazedTerracotta GREEN_GLAZED_TERRACOTTA()
 * @method static HardenedClay GREEN_STAINED_CLAY()
 * @method static Glass GREEN_STAINED_GLASS()
 * @method static GlassPane GREEN_STAINED_GLASS_PANE()
 * @method static Torch GREEN_TORCH()
 * @method static Wool GREEN_WOOL()
 * @method static HardenedGlass HARDENED_BLACK_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_BLACK_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_BLUE_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_BLUE_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_BROWN_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_BROWN_STAINED_GLASS_PANE()
 * @method static HardenedClay HARDENED_CLAY()
 * @method static HardenedGlass HARDENED_CYAN_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_CYAN_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_GLASS()
 * @method static HardenedGlassPane HARDENED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_GRAY_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_GRAY_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_GREEN_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_GREEN_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_LIGHT_BLUE_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_LIGHT_BLUE_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_LIGHT_GRAY_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_LIGHT_GRAY_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_LIME_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_LIME_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_MAGENTA_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_MAGENTA_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_ORANGE_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_ORANGE_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_PINK_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_PINK_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_PURPLE_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_PURPLE_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_RED_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_RED_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_WHITE_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_WHITE_STAINED_GLASS_PANE()
 * @method static HardenedGlass HARDENED_YELLOW_STAINED_GLASS()
 * @method static HardenedGlassPane HARDENED_YELLOW_STAINED_GLASS_PANE()
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
 * @method static WoodenButton JUNGLE_BUTTON()
 * @method static WoodenDoor JUNGLE_DOOR()
 * @method static WoodenFence JUNGLE_FENCE()
 * @method static FenceGate JUNGLE_FENCE_GATE()
 * @method static Leaves JUNGLE_LEAVES()
 * @method static Log JUNGLE_LOG()
 * @method static Planks JUNGLE_PLANKS()
 * @method static WoodenPressurePlate JUNGLE_PRESSURE_PLATE()
 * @method static Sapling JUNGLE_SAPLING()
 * @method static Sign JUNGLE_SIGN()
 * @method static WoodenSlab JUNGLE_SLAB()
 * @method static WoodenStairs JUNGLE_STAIRS()
 * @method static WoodenTrapdoor JUNGLE_TRAPDOOR()
 * @method static Wood JUNGLE_WOOD()
 * @method static Ladder LADDER()
 * @method static Lantern LANTERN()
 * @method static Opaque LAPIS_LAZULI()
 * @method static LapisOre LAPIS_LAZULI_ORE()
 * @method static DoubleTallGrass LARGE_FERN()
 * @method static Lava LAVA()
 * @method static Opaque LEGACY_STONECUTTER()
 * @method static Lever LEVER()
 * @method static Carpet LIGHT_BLUE_CARPET()
 * @method static Concrete LIGHT_BLUE_CONCRETE()
 * @method static ConcretePowder LIGHT_BLUE_CONCRETE_POWDER()
 * @method static GlazedTerracotta LIGHT_BLUE_GLAZED_TERRACOTTA()
 * @method static HardenedClay LIGHT_BLUE_STAINED_CLAY()
 * @method static Glass LIGHT_BLUE_STAINED_GLASS()
 * @method static GlassPane LIGHT_BLUE_STAINED_GLASS_PANE()
 * @method static Wool LIGHT_BLUE_WOOL()
 * @method static Carpet LIGHT_GRAY_CARPET()
 * @method static Concrete LIGHT_GRAY_CONCRETE()
 * @method static ConcretePowder LIGHT_GRAY_CONCRETE_POWDER()
 * @method static GlazedTerracotta LIGHT_GRAY_GLAZED_TERRACOTTA()
 * @method static HardenedClay LIGHT_GRAY_STAINED_CLAY()
 * @method static Glass LIGHT_GRAY_STAINED_GLASS()
 * @method static GlassPane LIGHT_GRAY_STAINED_GLASS_PANE()
 * @method static Wool LIGHT_GRAY_WOOL()
 * @method static DoublePlant LILAC()
 * @method static Flower LILY_OF_THE_VALLEY()
 * @method static WaterLily LILY_PAD()
 * @method static Carpet LIME_CARPET()
 * @method static Concrete LIME_CONCRETE()
 * @method static ConcretePowder LIME_CONCRETE_POWDER()
 * @method static GlazedTerracotta LIME_GLAZED_TERRACOTTA()
 * @method static HardenedClay LIME_STAINED_CLAY()
 * @method static Glass LIME_STAINED_GLASS()
 * @method static GlassPane LIME_STAINED_GLASS_PANE()
 * @method static Wool LIME_WOOL()
 * @method static LitPumpkin LIT_PUMPKIN()
 * @method static Carpet MAGENTA_CARPET()
 * @method static Concrete MAGENTA_CONCRETE()
 * @method static ConcretePowder MAGENTA_CONCRETE_POWDER()
 * @method static GlazedTerracotta MAGENTA_GLAZED_TERRACOTTA()
 * @method static HardenedClay MAGENTA_STAINED_CLAY()
 * @method static Glass MAGENTA_STAINED_GLASS()
 * @method static GlassPane MAGENTA_STAINED_GLASS_PANE()
 * @method static Wool MAGENTA_WOOL()
 * @method static Magma MAGMA()
 * @method static Melon MELON()
 * @method static MelonStem MELON_STEM()
 * @method static Skull MOB_HEAD()
 * @method static MonsterSpawner MONSTER_SPAWNER()
 * @method static Opaque MOSSY_COBBLESTONE()
 * @method static Slab MOSSY_COBBLESTONE_SLAB()
 * @method static Stair MOSSY_COBBLESTONE_STAIRS()
 * @method static Wall MOSSY_COBBLESTONE_WALL()
 * @method static Slab MOSSY_STONE_BRICK_SLAB()
 * @method static Stair MOSSY_STONE_BRICK_STAIRS()
 * @method static Wall MOSSY_STONE_BRICK_WALL()
 * @method static Opaque MOSSY_STONE_BRICKS()
 * @method static Mycelium MYCELIUM()
 * @method static Fence NETHER_BRICK_FENCE()
 * @method static Slab NETHER_BRICK_SLAB()
 * @method static Stair NETHER_BRICK_STAIRS()
 * @method static Wall NETHER_BRICK_WALL()
 * @method static Opaque NETHER_BRICKS()
 * @method static NetherPortal NETHER_PORTAL()
 * @method static NetherQuartzOre NETHER_QUARTZ_ORE()
 * @method static NetherReactor NETHER_REACTOR_CORE()
 * @method static NetherWartPlant NETHER_WART()
 * @method static Opaque NETHER_WART_BLOCK()
 * @method static Netherrack NETHERRACK()
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
 * @method static Sign OAK_SIGN()
 * @method static WoodenSlab OAK_SLAB()
 * @method static WoodenStairs OAK_STAIRS()
 * @method static WoodenTrapdoor OAK_TRAPDOOR()
 * @method static Wood OAK_WOOD()
 * @method static Opaque OBSIDIAN()
 * @method static Carpet ORANGE_CARPET()
 * @method static Concrete ORANGE_CONCRETE()
 * @method static ConcretePowder ORANGE_CONCRETE_POWDER()
 * @method static GlazedTerracotta ORANGE_GLAZED_TERRACOTTA()
 * @method static HardenedClay ORANGE_STAINED_CLAY()
 * @method static Glass ORANGE_STAINED_GLASS()
 * @method static GlassPane ORANGE_STAINED_GLASS_PANE()
 * @method static Flower ORANGE_TULIP()
 * @method static Wool ORANGE_WOOL()
 * @method static Flower OXEYE_DAISY()
 * @method static PackedIce PACKED_ICE()
 * @method static DoublePlant PEONY()
 * @method static Carpet PINK_CARPET()
 * @method static Concrete PINK_CONCRETE()
 * @method static ConcretePowder PINK_CONCRETE_POWDER()
 * @method static GlazedTerracotta PINK_GLAZED_TERRACOTTA()
 * @method static HardenedClay PINK_STAINED_CLAY()
 * @method static Glass PINK_STAINED_GLASS()
 * @method static GlassPane PINK_STAINED_GLASS_PANE()
 * @method static Flower PINK_TULIP()
 * @method static Wool PINK_WOOL()
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
 * @method static Opaque PUMPKIN()
 * @method static PumpkinStem PUMPKIN_STEM()
 * @method static Carpet PURPLE_CARPET()
 * @method static Concrete PURPLE_CONCRETE()
 * @method static ConcretePowder PURPLE_CONCRETE_POWDER()
 * @method static GlazedTerracotta PURPLE_GLAZED_TERRACOTTA()
 * @method static HardenedClay PURPLE_STAINED_CLAY()
 * @method static Glass PURPLE_STAINED_GLASS()
 * @method static GlassPane PURPLE_STAINED_GLASS_PANE()
 * @method static Torch PURPLE_TORCH()
 * @method static Wool PURPLE_WOOL()
 * @method static Opaque PURPUR()
 * @method static Opaque PURPUR_PILLAR()
 * @method static Slab PURPUR_SLAB()
 * @method static Stair PURPUR_STAIRS()
 * @method static Opaque QUARTZ()
 * @method static Opaque QUARTZ_PILLAR()
 * @method static Slab QUARTZ_SLAB()
 * @method static Stair QUARTZ_STAIRS()
 * @method static Rail RAIL()
 * @method static Carpet RED_CARPET()
 * @method static Concrete RED_CONCRETE()
 * @method static ConcretePowder RED_CONCRETE_POWDER()
 * @method static GlazedTerracotta RED_GLAZED_TERRACOTTA()
 * @method static RedMushroom RED_MUSHROOM()
 * @method static RedMushroomBlock RED_MUSHROOM_BLOCK()
 * @method static Slab RED_NETHER_BRICK_SLAB()
 * @method static Stair RED_NETHER_BRICK_STAIRS()
 * @method static Wall RED_NETHER_BRICK_WALL()
 * @method static Opaque RED_NETHER_BRICKS()
 * @method static Sand RED_SAND()
 * @method static Opaque RED_SANDSTONE()
 * @method static Slab RED_SANDSTONE_SLAB()
 * @method static Stair RED_SANDSTONE_STAIRS()
 * @method static Wall RED_SANDSTONE_WALL()
 * @method static HardenedClay RED_STAINED_CLAY()
 * @method static Glass RED_STAINED_GLASS()
 * @method static GlassPane RED_STAINED_GLASS_PANE()
 * @method static Torch RED_TORCH()
 * @method static Flower RED_TULIP()
 * @method static Wool RED_WOOL()
 * @method static Redstone REDSTONE()
 * @method static RedstoneComparator REDSTONE_COMPARATOR()
 * @method static RedstoneLamp REDSTONE_LAMP()
 * @method static RedstoneOre REDSTONE_ORE()
 * @method static RedstoneRepeater REDSTONE_REPEATER()
 * @method static RedstoneTorch REDSTONE_TORCH()
 * @method static RedstoneWire REDSTONE_WIRE()
 * @method static Reserved6 RESERVED6()
 * @method static DoublePlant ROSE_BUSH()
 * @method static Sand SAND()
 * @method static Opaque SANDSTONE()
 * @method static Slab SANDSTONE_SLAB()
 * @method static Stair SANDSTONE_STAIRS()
 * @method static Wall SANDSTONE_WALL()
 * @method static SeaLantern SEA_LANTERN()
 * @method static SeaPickle SEA_PICKLE()
 * @method static Anvil SLIGHTLY_DAMAGED_ANVIL()
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
 * @method static Sign SPRUCE_SIGN()
 * @method static WoodenSlab SPRUCE_SLAB()
 * @method static WoodenStairs SPRUCE_STAIRS()
 * @method static WoodenTrapdoor SPRUCE_TRAPDOOR()
 * @method static Wood SPRUCE_WOOD()
 * @method static Opaque STONE()
 * @method static Slab STONE_BRICK_SLAB()
 * @method static Stair STONE_BRICK_STAIRS()
 * @method static Wall STONE_BRICK_WALL()
 * @method static Opaque STONE_BRICKS()
 * @method static StoneButton STONE_BUTTON()
 * @method static StonePressurePlate STONE_PRESSURE_PLATE()
 * @method static Slab STONE_SLAB()
 * @method static Stair STONE_STAIRS()
 * @method static Sugarcane SUGARCANE()
 * @method static DoublePlant SUNFLOWER()
 * @method static TallGrass TALL_GRASS()
 * @method static TNT TNT()
 * @method static Torch TORCH()
 * @method static TrappedChest TRAPPED_CHEST()
 * @method static Tripwire TRIPWIRE()
 * @method static TripwireHook TRIPWIRE_HOOK()
 * @method static UnderwaterTorch UNDERWATER_TORCH()
 * @method static Anvil VERY_DAMAGED_ANVIL()
 * @method static Vine VINES()
 * @method static Water WATER()
 * @method static WeightedPressurePlateHeavy WEIGHTED_PRESSURE_PLATE_HEAVY()
 * @method static WeightedPressurePlateLight WEIGHTED_PRESSURE_PLATE_LIGHT()
 * @method static Wheat WHEAT()
 * @method static Carpet WHITE_CARPET()
 * @method static Concrete WHITE_CONCRETE()
 * @method static ConcretePowder WHITE_CONCRETE_POWDER()
 * @method static GlazedTerracotta WHITE_GLAZED_TERRACOTTA()
 * @method static HardenedClay WHITE_STAINED_CLAY()
 * @method static Glass WHITE_STAINED_GLASS()
 * @method static GlassPane WHITE_STAINED_GLASS_PANE()
 * @method static Flower WHITE_TULIP()
 * @method static Wool WHITE_WOOL()
 * @method static Carpet YELLOW_CARPET()
 * @method static Concrete YELLOW_CONCRETE()
 * @method static ConcretePowder YELLOW_CONCRETE_POWDER()
 * @method static GlazedTerracotta YELLOW_GLAZED_TERRACOTTA()
 * @method static HardenedClay YELLOW_STAINED_CLAY()
 * @method static Glass YELLOW_STAINED_GLASS()
 * @method static GlassPane YELLOW_STAINED_GLASS_PANE()
 * @method static Wool YELLOW_WOOL()
 */
final class VanillaBlocks{
	use RegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Block $block) : void{
		self::_registryRegister($name, $block);
	}

	/**
	 * @param string $name
	 *
	 * @return Block
	 */
	public static function fromString(string $name) : Block{
		$result = self::_registryFromString($name);
		assert($result instanceof Block);
		return clone $result;
	}

	/**
	 * @return Block[]
	 */
	public static function getAll() : array{
		return Utils::cloneObjectArray(self::_registryGetAll());
	}

	protected static function setup() : void{
		self::register("acacia_button", BlockFactory::get(395));
		self::register("acacia_door", BlockFactory::get(196));
		self::register("acacia_fence", BlockFactory::get(85, 4));
		self::register("acacia_fence_gate", BlockFactory::get(187));
		self::register("acacia_leaves", BlockFactory::get(161));
		self::register("acacia_log", BlockFactory::get(162));
		self::register("acacia_planks", BlockFactory::get(5, 4));
		self::register("acacia_pressure_plate", BlockFactory::get(405));
		self::register("acacia_sapling", BlockFactory::get(6, 4));
		self::register("acacia_sign", BlockFactory::get(445));
		self::register("acacia_slab", BlockFactory::get(158, 4));
		self::register("acacia_stairs", BlockFactory::get(163));
		self::register("acacia_trapdoor", BlockFactory::get(400));
		self::register("acacia_wood", BlockFactory::get(467, 4));
		self::register("activator_rail", BlockFactory::get(126));
		self::register("air", BlockFactory::get(0));
		self::register("allium", BlockFactory::get(38, 2));
		self::register("andesite", BlockFactory::get(1, 5));
		self::register("andesite_slab", BlockFactory::get(417, 3));
		self::register("andesite_stairs", BlockFactory::get(426));
		self::register("andesite_wall", BlockFactory::get(139, 4));
		self::register("anvil", BlockFactory::get(145));
		self::register("azure_bluet", BlockFactory::get(38, 3));
		self::register("banner", BlockFactory::get(176));
		self::register("barrier", BlockFactory::get(416));
		self::register("bed", BlockFactory::get(26));
		self::register("bedrock", BlockFactory::get(7));
		self::register("beetroots", BlockFactory::get(244));
		self::register("birch_button", BlockFactory::get(396));
		self::register("birch_door", BlockFactory::get(194));
		self::register("birch_fence", BlockFactory::get(85, 2));
		self::register("birch_fence_gate", BlockFactory::get(184));
		self::register("birch_leaves", BlockFactory::get(18, 2));
		self::register("birch_log", BlockFactory::get(17, 2));
		self::register("birch_planks", BlockFactory::get(5, 2));
		self::register("birch_pressure_plate", BlockFactory::get(406));
		self::register("birch_sapling", BlockFactory::get(6, 2));
		self::register("birch_sign", BlockFactory::get(441));
		self::register("birch_slab", BlockFactory::get(158, 2));
		self::register("birch_stairs", BlockFactory::get(135));
		self::register("birch_trapdoor", BlockFactory::get(401));
		self::register("birch_wood", BlockFactory::get(467, 2));
		self::register("black_carpet", BlockFactory::get(171, 15));
		self::register("black_concrete", BlockFactory::get(236, 15));
		self::register("black_concrete_powder", BlockFactory::get(237, 15));
		self::register("black_glazed_terracotta", BlockFactory::get(235, 2));
		self::register("black_stained_clay", BlockFactory::get(159, 15));
		self::register("black_stained_glass", BlockFactory::get(241, 15));
		self::register("black_stained_glass_pane", BlockFactory::get(160, 15));
		self::register("black_wool", BlockFactory::get(35, 15));
		self::register("blue_carpet", BlockFactory::get(171, 11));
		self::register("blue_concrete", BlockFactory::get(236, 11));
		self::register("blue_concrete_powder", BlockFactory::get(237, 11));
		self::register("blue_glazed_terracotta", BlockFactory::get(231, 2));
		self::register("blue_ice", BlockFactory::get(266));
		self::register("blue_orchid", BlockFactory::get(38, 1));
		self::register("blue_stained_clay", BlockFactory::get(159, 11));
		self::register("blue_stained_glass", BlockFactory::get(241, 11));
		self::register("blue_stained_glass_pane", BlockFactory::get(160, 11));
		self::register("blue_torch", BlockFactory::get(204, 5));
		self::register("blue_wool", BlockFactory::get(35, 11));
		self::register("bone_block", BlockFactory::get(216));
		self::register("bookshelf", BlockFactory::get(47));
		self::register("brewing_stand", BlockFactory::get(117));
		self::register("brick_slab", BlockFactory::get(44, 4));
		self::register("brick_stairs", BlockFactory::get(108));
		self::register("brick_wall", BlockFactory::get(139, 6));
		self::register("bricks", BlockFactory::get(45));
		self::register("brown_carpet", BlockFactory::get(171, 12));
		self::register("brown_concrete", BlockFactory::get(236, 12));
		self::register("brown_concrete_powder", BlockFactory::get(237, 12));
		self::register("brown_glazed_terracotta", BlockFactory::get(232, 2));
		self::register("brown_mushroom", BlockFactory::get(39));
		self::register("brown_mushroom_block", BlockFactory::get(99));
		self::register("brown_stained_clay", BlockFactory::get(159, 12));
		self::register("brown_stained_glass", BlockFactory::get(241, 12));
		self::register("brown_stained_glass_pane", BlockFactory::get(160, 12));
		self::register("brown_wool", BlockFactory::get(35, 12));
		self::register("cactus", BlockFactory::get(81));
		self::register("cake", BlockFactory::get(92));
		self::register("carrots", BlockFactory::get(141));
		self::register("carved_pumpkin", BlockFactory::get(410));
		self::register("chest", BlockFactory::get(54, 2));
		self::register("chiseled_quartz", BlockFactory::get(155, 1));
		self::register("chiseled_red_sandstone", BlockFactory::get(179, 1));
		self::register("chiseled_sandstone", BlockFactory::get(24, 1));
		self::register("chiseled_stone_bricks", BlockFactory::get(98, 3));
		self::register("clay", BlockFactory::get(82));
		self::register("coal", BlockFactory::get(173));
		self::register("coal_ore", BlockFactory::get(16));
		self::register("coarse_dirt", BlockFactory::get(3, 1));
		self::register("cobblestone", BlockFactory::get(4));
		self::register("cobblestone_slab", BlockFactory::get(44, 3));
		self::register("cobblestone_stairs", BlockFactory::get(67));
		self::register("cobblestone_wall", BlockFactory::get(139));
		self::register("cobweb", BlockFactory::get(30));
		self::register("cocoa_pod", BlockFactory::get(127));
		self::register("cornflower", BlockFactory::get(38, 9));
		self::register("cracked_stone_bricks", BlockFactory::get(98, 2));
		self::register("crafting_table", BlockFactory::get(58));
		self::register("cut_red_sandstone", BlockFactory::get(179, 2));
		self::register("cut_red_sandstone_slab", BlockFactory::get(421, 4));
		self::register("cut_sandstone", BlockFactory::get(24, 2));
		self::register("cut_sandstone_slab", BlockFactory::get(421, 3));
		self::register("cyan_carpet", BlockFactory::get(171, 9));
		self::register("cyan_concrete", BlockFactory::get(236, 9));
		self::register("cyan_concrete_powder", BlockFactory::get(237, 9));
		self::register("cyan_glazed_terracotta", BlockFactory::get(229, 2));
		self::register("cyan_stained_clay", BlockFactory::get(159, 9));
		self::register("cyan_stained_glass", BlockFactory::get(241, 9));
		self::register("cyan_stained_glass_pane", BlockFactory::get(160, 9));
		self::register("cyan_wool", BlockFactory::get(35, 9));
		self::register("dandelion", BlockFactory::get(37));
		self::register("dark_oak_button", BlockFactory::get(397));
		self::register("dark_oak_door", BlockFactory::get(197));
		self::register("dark_oak_fence", BlockFactory::get(85, 5));
		self::register("dark_oak_fence_gate", BlockFactory::get(186));
		self::register("dark_oak_leaves", BlockFactory::get(161, 1));
		self::register("dark_oak_log", BlockFactory::get(162, 1));
		self::register("dark_oak_planks", BlockFactory::get(5, 5));
		self::register("dark_oak_pressure_plate", BlockFactory::get(407));
		self::register("dark_oak_sapling", BlockFactory::get(6, 5));
		self::register("dark_oak_sign", BlockFactory::get(447));
		self::register("dark_oak_slab", BlockFactory::get(158, 5));
		self::register("dark_oak_stairs", BlockFactory::get(164));
		self::register("dark_oak_trapdoor", BlockFactory::get(402));
		self::register("dark_oak_wood", BlockFactory::get(467, 5));
		self::register("dark_prismarine", BlockFactory::get(168, 1));
		self::register("dark_prismarine_slab", BlockFactory::get(182, 3));
		self::register("dark_prismarine_stairs", BlockFactory::get(258));
		self::register("daylight_sensor", BlockFactory::get(151));
		self::register("dead_bush", BlockFactory::get(32));
		self::register("detector_rail", BlockFactory::get(28));
		self::register("diamond", BlockFactory::get(57));
		self::register("diamond_ore", BlockFactory::get(56));
		self::register("diorite", BlockFactory::get(1, 3));
		self::register("diorite_slab", BlockFactory::get(417, 4));
		self::register("diorite_stairs", BlockFactory::get(425));
		self::register("diorite_wall", BlockFactory::get(139, 3));
		self::register("dirt", BlockFactory::get(3));
		self::register("double_tallgrass", BlockFactory::get(175, 2));
		self::register("dragon_egg", BlockFactory::get(122));
		self::register("dried_kelp", BlockFactory::get(394));
		self::register("element_actinium", BlockFactory::get(355));
		self::register("element_aluminum", BlockFactory::get(279));
		self::register("element_americium", BlockFactory::get(361));
		self::register("element_antimony", BlockFactory::get(317));
		self::register("element_argon", BlockFactory::get(284));
		self::register("element_arsenic", BlockFactory::get(299));
		self::register("element_astatine", BlockFactory::get(351));
		self::register("element_barium", BlockFactory::get(322));
		self::register("element_berkelium", BlockFactory::get(363));
		self::register("element_beryllium", BlockFactory::get(270));
		self::register("element_bismuth", BlockFactory::get(349));
		self::register("element_bohrium", BlockFactory::get(373));
		self::register("element_boron", BlockFactory::get(271));
		self::register("element_bromine", BlockFactory::get(301));
		self::register("element_cadmium", BlockFactory::get(314));
		self::register("element_calcium", BlockFactory::get(286));
		self::register("element_californium", BlockFactory::get(364));
		self::register("element_carbon", BlockFactory::get(272));
		self::register("element_cerium", BlockFactory::get(324));
		self::register("element_cesium", BlockFactory::get(321));
		self::register("element_chlorine", BlockFactory::get(283));
		self::register("element_chromium", BlockFactory::get(290));
		self::register("element_cobalt", BlockFactory::get(293));
		self::register("element_copernicium", BlockFactory::get(378));
		self::register("element_copper", BlockFactory::get(295));
		self::register("element_curium", BlockFactory::get(362));
		self::register("element_darmstadtium", BlockFactory::get(376));
		self::register("element_dubnium", BlockFactory::get(371));
		self::register("element_dysprosium", BlockFactory::get(332));
		self::register("element_einsteinium", BlockFactory::get(365));
		self::register("element_erbium", BlockFactory::get(334));
		self::register("element_europium", BlockFactory::get(329));
		self::register("element_fermium", BlockFactory::get(366));
		self::register("element_flerovium", BlockFactory::get(380));
		self::register("element_fluorine", BlockFactory::get(275));
		self::register("element_francium", BlockFactory::get(353));
		self::register("element_gadolinium", BlockFactory::get(330));
		self::register("element_gallium", BlockFactory::get(297));
		self::register("element_germanium", BlockFactory::get(298));
		self::register("element_gold", BlockFactory::get(345));
		self::register("element_hafnium", BlockFactory::get(338));
		self::register("element_hassium", BlockFactory::get(374));
		self::register("element_helium", BlockFactory::get(268));
		self::register("element_holmium", BlockFactory::get(333));
		self::register("element_hydrogen", BlockFactory::get(267));
		self::register("element_indium", BlockFactory::get(315));
		self::register("element_iodine", BlockFactory::get(319));
		self::register("element_iridium", BlockFactory::get(343));
		self::register("element_iron", BlockFactory::get(292));
		self::register("element_krypton", BlockFactory::get(302));
		self::register("element_lanthanum", BlockFactory::get(323));
		self::register("element_lawrencium", BlockFactory::get(369));
		self::register("element_lead", BlockFactory::get(348));
		self::register("element_lithium", BlockFactory::get(269));
		self::register("element_livermorium", BlockFactory::get(382));
		self::register("element_lutetium", BlockFactory::get(337));
		self::register("element_magnesium", BlockFactory::get(278));
		self::register("element_manganese", BlockFactory::get(291));
		self::register("element_meitnerium", BlockFactory::get(375));
		self::register("element_mendelevium", BlockFactory::get(367));
		self::register("element_mercury", BlockFactory::get(346));
		self::register("element_molybdenum", BlockFactory::get(308));
		self::register("element_moscovium", BlockFactory::get(381));
		self::register("element_neodymium", BlockFactory::get(326));
		self::register("element_neon", BlockFactory::get(276));
		self::register("element_neptunium", BlockFactory::get(359));
		self::register("element_nickel", BlockFactory::get(294));
		self::register("element_nihonium", BlockFactory::get(379));
		self::register("element_niobium", BlockFactory::get(307));
		self::register("element_nitrogen", BlockFactory::get(273));
		self::register("element_nobelium", BlockFactory::get(368));
		self::register("element_oganesson", BlockFactory::get(384));
		self::register("element_osmium", BlockFactory::get(342));
		self::register("element_oxygen", BlockFactory::get(274));
		self::register("element_palladium", BlockFactory::get(312));
		self::register("element_phosphorus", BlockFactory::get(281));
		self::register("element_platinum", BlockFactory::get(344));
		self::register("element_plutonium", BlockFactory::get(360));
		self::register("element_polonium", BlockFactory::get(350));
		self::register("element_potassium", BlockFactory::get(285));
		self::register("element_praseodymium", BlockFactory::get(325));
		self::register("element_promethium", BlockFactory::get(327));
		self::register("element_protactinium", BlockFactory::get(357));
		self::register("element_radium", BlockFactory::get(354));
		self::register("element_radon", BlockFactory::get(352));
		self::register("element_rhenium", BlockFactory::get(341));
		self::register("element_rhodium", BlockFactory::get(311));
		self::register("element_roentgenium", BlockFactory::get(377));
		self::register("element_rubidium", BlockFactory::get(303));
		self::register("element_ruthenium", BlockFactory::get(310));
		self::register("element_rutherfordium", BlockFactory::get(370));
		self::register("element_samarium", BlockFactory::get(328));
		self::register("element_scandium", BlockFactory::get(287));
		self::register("element_seaborgium", BlockFactory::get(372));
		self::register("element_selenium", BlockFactory::get(300));
		self::register("element_silicon", BlockFactory::get(280));
		self::register("element_silver", BlockFactory::get(313));
		self::register("element_sodium", BlockFactory::get(277));
		self::register("element_strontium", BlockFactory::get(304));
		self::register("element_sulfur", BlockFactory::get(282));
		self::register("element_tantalum", BlockFactory::get(339));
		self::register("element_technetium", BlockFactory::get(309));
		self::register("element_tellurium", BlockFactory::get(318));
		self::register("element_tennessine", BlockFactory::get(383));
		self::register("element_terbium", BlockFactory::get(331));
		self::register("element_thallium", BlockFactory::get(347));
		self::register("element_thorium", BlockFactory::get(356));
		self::register("element_thulium", BlockFactory::get(335));
		self::register("element_tin", BlockFactory::get(316));
		self::register("element_titanium", BlockFactory::get(288));
		self::register("element_tungsten", BlockFactory::get(340));
		self::register("element_uranium", BlockFactory::get(358));
		self::register("element_vanadium", BlockFactory::get(289));
		self::register("element_xenon", BlockFactory::get(320));
		self::register("element_ytterbium", BlockFactory::get(336));
		self::register("element_yttrium", BlockFactory::get(305));
		self::register("element_zero", BlockFactory::get(36));
		self::register("element_zinc", BlockFactory::get(296));
		self::register("element_zirconium", BlockFactory::get(306));
		self::register("emerald", BlockFactory::get(133));
		self::register("emerald_ore", BlockFactory::get(129));
		self::register("enchanting_table", BlockFactory::get(116));
		self::register("end_portal_frame", BlockFactory::get(120));
		self::register("end_rod", BlockFactory::get(208));
		self::register("end_stone", BlockFactory::get(121));
		self::register("end_stone_brick_slab", BlockFactory::get(417));
		self::register("end_stone_brick_stairs", BlockFactory::get(433));
		self::register("end_stone_brick_wall", BlockFactory::get(139, 10));
		self::register("end_stone_bricks", BlockFactory::get(206));
		self::register("ender_chest", BlockFactory::get(130, 2));
		self::register("fake_wooden_slab", BlockFactory::get(44, 2));
		self::register("farmland", BlockFactory::get(60));
		self::register("fern", BlockFactory::get(31, 2));
		self::register("fire", BlockFactory::get(51));
		self::register("flower_pot", BlockFactory::get(140));
		self::register("frosted_ice", BlockFactory::get(207));
		self::register("furnace", BlockFactory::get(61, 2));
		self::register("glass", BlockFactory::get(20));
		self::register("glass_pane", BlockFactory::get(102));
		self::register("glowing_obsidian", BlockFactory::get(246));
		self::register("glowstone", BlockFactory::get(89));
		self::register("gold", BlockFactory::get(41));
		self::register("gold_ore", BlockFactory::get(14));
		self::register("granite", BlockFactory::get(1, 1));
		self::register("granite_slab", BlockFactory::get(417, 6));
		self::register("granite_stairs", BlockFactory::get(424));
		self::register("granite_wall", BlockFactory::get(139, 2));
		self::register("grass", BlockFactory::get(2));
		self::register("grass_path", BlockFactory::get(198));
		self::register("gravel", BlockFactory::get(13));
		self::register("gray_carpet", BlockFactory::get(171, 7));
		self::register("gray_concrete", BlockFactory::get(236, 7));
		self::register("gray_concrete_powder", BlockFactory::get(237, 7));
		self::register("gray_glazed_terracotta", BlockFactory::get(227, 2));
		self::register("gray_stained_clay", BlockFactory::get(159, 7));
		self::register("gray_stained_glass", BlockFactory::get(241, 7));
		self::register("gray_stained_glass_pane", BlockFactory::get(160, 7));
		self::register("gray_wool", BlockFactory::get(35, 7));
		self::register("green_carpet", BlockFactory::get(171, 13));
		self::register("green_concrete", BlockFactory::get(236, 13));
		self::register("green_concrete_powder", BlockFactory::get(237, 13));
		self::register("green_glazed_terracotta", BlockFactory::get(233, 2));
		self::register("green_stained_clay", BlockFactory::get(159, 13));
		self::register("green_stained_glass", BlockFactory::get(241, 13));
		self::register("green_stained_glass_pane", BlockFactory::get(160, 13));
		self::register("green_torch", BlockFactory::get(202, 13));
		self::register("green_wool", BlockFactory::get(35, 13));
		self::register("hardened_black_stained_glass", BlockFactory::get(254, 15));
		self::register("hardened_black_stained_glass_pane", BlockFactory::get(191, 15));
		self::register("hardened_blue_stained_glass", BlockFactory::get(254, 11));
		self::register("hardened_blue_stained_glass_pane", BlockFactory::get(191, 11));
		self::register("hardened_brown_stained_glass", BlockFactory::get(254, 12));
		self::register("hardened_brown_stained_glass_pane", BlockFactory::get(191, 12));
		self::register("hardened_clay", BlockFactory::get(172));
		self::register("hardened_cyan_stained_glass", BlockFactory::get(254, 9));
		self::register("hardened_cyan_stained_glass_pane", BlockFactory::get(191, 9));
		self::register("hardened_glass", BlockFactory::get(253));
		self::register("hardened_glass_pane", BlockFactory::get(190));
		self::register("hardened_gray_stained_glass", BlockFactory::get(254, 7));
		self::register("hardened_gray_stained_glass_pane", BlockFactory::get(191, 7));
		self::register("hardened_green_stained_glass", BlockFactory::get(254, 13));
		self::register("hardened_green_stained_glass_pane", BlockFactory::get(191, 13));
		self::register("hardened_light_blue_stained_glass", BlockFactory::get(254, 3));
		self::register("hardened_light_blue_stained_glass_pane", BlockFactory::get(191, 3));
		self::register("hardened_light_gray_stained_glass", BlockFactory::get(254, 8));
		self::register("hardened_light_gray_stained_glass_pane", BlockFactory::get(191, 8));
		self::register("hardened_lime_stained_glass", BlockFactory::get(254, 5));
		self::register("hardened_lime_stained_glass_pane", BlockFactory::get(191, 5));
		self::register("hardened_magenta_stained_glass", BlockFactory::get(254, 2));
		self::register("hardened_magenta_stained_glass_pane", BlockFactory::get(191, 2));
		self::register("hardened_orange_stained_glass", BlockFactory::get(254, 1));
		self::register("hardened_orange_stained_glass_pane", BlockFactory::get(191, 1));
		self::register("hardened_pink_stained_glass", BlockFactory::get(254, 6));
		self::register("hardened_pink_stained_glass_pane", BlockFactory::get(191, 6));
		self::register("hardened_purple_stained_glass", BlockFactory::get(254, 10));
		self::register("hardened_purple_stained_glass_pane", BlockFactory::get(191, 10));
		self::register("hardened_red_stained_glass", BlockFactory::get(254, 14));
		self::register("hardened_red_stained_glass_pane", BlockFactory::get(191, 14));
		self::register("hardened_white_stained_glass", BlockFactory::get(254));
		self::register("hardened_white_stained_glass_pane", BlockFactory::get(191));
		self::register("hardened_yellow_stained_glass", BlockFactory::get(254, 4));
		self::register("hardened_yellow_stained_glass_pane", BlockFactory::get(191, 4));
		self::register("hay_bale", BlockFactory::get(170));
		self::register("hopper", BlockFactory::get(154));
		self::register("ice", BlockFactory::get(79));
		self::register("infested_chiseled_stone_brick", BlockFactory::get(97, 5));
		self::register("infested_cobblestone", BlockFactory::get(97, 1));
		self::register("infested_cracked_stone_brick", BlockFactory::get(97, 4));
		self::register("infested_mossy_stone_brick", BlockFactory::get(97, 3));
		self::register("infested_stone", BlockFactory::get(97));
		self::register("infested_stone_brick", BlockFactory::get(97, 2));
		self::register("info_update", BlockFactory::get(248));
		self::register("info_update2", BlockFactory::get(249));
		self::register("invisible_bedrock", BlockFactory::get(95));
		self::register("iron", BlockFactory::get(42));
		self::register("iron_bars", BlockFactory::get(101));
		self::register("iron_door", BlockFactory::get(71));
		self::register("iron_ore", BlockFactory::get(15));
		self::register("iron_trapdoor", BlockFactory::get(167));
		self::register("item_frame", BlockFactory::get(199));
		self::register("jungle_button", BlockFactory::get(398));
		self::register("jungle_door", BlockFactory::get(195));
		self::register("jungle_fence", BlockFactory::get(85, 3));
		self::register("jungle_fence_gate", BlockFactory::get(185));
		self::register("jungle_leaves", BlockFactory::get(18, 3));
		self::register("jungle_log", BlockFactory::get(17, 3));
		self::register("jungle_planks", BlockFactory::get(5, 3));
		self::register("jungle_pressure_plate", BlockFactory::get(408));
		self::register("jungle_sapling", BlockFactory::get(6, 3));
		self::register("jungle_sign", BlockFactory::get(443));
		self::register("jungle_slab", BlockFactory::get(158, 3));
		self::register("jungle_stairs", BlockFactory::get(136));
		self::register("jungle_trapdoor", BlockFactory::get(403));
		self::register("jungle_wood", BlockFactory::get(467, 3));
		self::register("ladder", BlockFactory::get(65, 2));
		self::register("lantern", BlockFactory::get(463));
		self::register("lapis_lazuli", BlockFactory::get(22));
		self::register("lapis_lazuli_ore", BlockFactory::get(21));
		self::register("large_fern", BlockFactory::get(175, 3));
		self::register("lava", BlockFactory::get(10));
		self::register("legacy_stonecutter", BlockFactory::get(245));
		self::register("lever", BlockFactory::get(69));
		self::register("light_blue_carpet", BlockFactory::get(171, 3));
		self::register("light_blue_concrete", BlockFactory::get(236, 3));
		self::register("light_blue_concrete_powder", BlockFactory::get(237, 3));
		self::register("light_blue_glazed_terracotta", BlockFactory::get(223, 2));
		self::register("light_blue_stained_clay", BlockFactory::get(159, 3));
		self::register("light_blue_stained_glass", BlockFactory::get(241, 3));
		self::register("light_blue_stained_glass_pane", BlockFactory::get(160, 3));
		self::register("light_blue_wool", BlockFactory::get(35, 3));
		self::register("light_gray_carpet", BlockFactory::get(171, 8));
		self::register("light_gray_concrete", BlockFactory::get(236, 8));
		self::register("light_gray_concrete_powder", BlockFactory::get(237, 8));
		self::register("light_gray_glazed_terracotta", BlockFactory::get(228, 2));
		self::register("light_gray_stained_clay", BlockFactory::get(159, 8));
		self::register("light_gray_stained_glass", BlockFactory::get(241, 8));
		self::register("light_gray_stained_glass_pane", BlockFactory::get(160, 8));
		self::register("light_gray_wool", BlockFactory::get(35, 8));
		self::register("lilac", BlockFactory::get(175, 1));
		self::register("lily_of_the_valley", BlockFactory::get(38, 10));
		self::register("lily_pad", BlockFactory::get(111));
		self::register("lime_carpet", BlockFactory::get(171, 5));
		self::register("lime_concrete", BlockFactory::get(236, 5));
		self::register("lime_concrete_powder", BlockFactory::get(237, 5));
		self::register("lime_glazed_terracotta", BlockFactory::get(225, 2));
		self::register("lime_stained_clay", BlockFactory::get(159, 5));
		self::register("lime_stained_glass", BlockFactory::get(241, 5));
		self::register("lime_stained_glass_pane", BlockFactory::get(160, 5));
		self::register("lime_wool", BlockFactory::get(35, 5));
		self::register("lit_pumpkin", BlockFactory::get(91));
		self::register("magenta_carpet", BlockFactory::get(171, 2));
		self::register("magenta_concrete", BlockFactory::get(236, 2));
		self::register("magenta_concrete_powder", BlockFactory::get(237, 2));
		self::register("magenta_glazed_terracotta", BlockFactory::get(222, 2));
		self::register("magenta_stained_clay", BlockFactory::get(159, 2));
		self::register("magenta_stained_glass", BlockFactory::get(241, 2));
		self::register("magenta_stained_glass_pane", BlockFactory::get(160, 2));
		self::register("magenta_wool", BlockFactory::get(35, 2));
		self::register("magma", BlockFactory::get(213));
		self::register("melon", BlockFactory::get(103));
		self::register("melon_stem", BlockFactory::get(105));
		self::register("mob_head", BlockFactory::get(144, 2));
		self::register("monster_spawner", BlockFactory::get(52));
		self::register("mossy_cobblestone", BlockFactory::get(48));
		self::register("mossy_cobblestone_slab", BlockFactory::get(182, 5));
		self::register("mossy_cobblestone_stairs", BlockFactory::get(434));
		self::register("mossy_cobblestone_wall", BlockFactory::get(139, 1));
		self::register("mossy_stone_brick_slab", BlockFactory::get(421));
		self::register("mossy_stone_brick_stairs", BlockFactory::get(430));
		self::register("mossy_stone_brick_wall", BlockFactory::get(139, 8));
		self::register("mossy_stone_bricks", BlockFactory::get(98, 1));
		self::register("mycelium", BlockFactory::get(110));
		self::register("nether_brick_fence", BlockFactory::get(113));
		self::register("nether_brick_slab", BlockFactory::get(44, 7));
		self::register("nether_brick_stairs", BlockFactory::get(114));
		self::register("nether_brick_wall", BlockFactory::get(139, 9));
		self::register("nether_bricks", BlockFactory::get(112));
		self::register("nether_portal", BlockFactory::get(90, 1));
		self::register("nether_quartz_ore", BlockFactory::get(153));
		self::register("nether_reactor_core", BlockFactory::get(247));
		self::register("nether_wart", BlockFactory::get(115));
		self::register("nether_wart_block", BlockFactory::get(214));
		self::register("netherrack", BlockFactory::get(87));
		self::register("note_block", BlockFactory::get(25));
		self::register("oak_button", BlockFactory::get(143));
		self::register("oak_door", BlockFactory::get(64));
		self::register("oak_fence", BlockFactory::get(85));
		self::register("oak_fence_gate", BlockFactory::get(107));
		self::register("oak_leaves", BlockFactory::get(18));
		self::register("oak_log", BlockFactory::get(17));
		self::register("oak_planks", BlockFactory::get(5));
		self::register("oak_pressure_plate", BlockFactory::get(72));
		self::register("oak_sapling", BlockFactory::get(6));
		self::register("oak_sign", BlockFactory::get(63));
		self::register("oak_slab", BlockFactory::get(158));
		self::register("oak_stairs", BlockFactory::get(53));
		self::register("oak_trapdoor", BlockFactory::get(96));
		self::register("oak_wood", BlockFactory::get(467));
		self::register("obsidian", BlockFactory::get(49));
		self::register("orange_carpet", BlockFactory::get(171, 1));
		self::register("orange_concrete", BlockFactory::get(236, 1));
		self::register("orange_concrete_powder", BlockFactory::get(237, 1));
		self::register("orange_glazed_terracotta", BlockFactory::get(221, 2));
		self::register("orange_stained_clay", BlockFactory::get(159, 1));
		self::register("orange_stained_glass", BlockFactory::get(241, 1));
		self::register("orange_stained_glass_pane", BlockFactory::get(160, 1));
		self::register("orange_tulip", BlockFactory::get(38, 5));
		self::register("orange_wool", BlockFactory::get(35, 1));
		self::register("oxeye_daisy", BlockFactory::get(38, 8));
		self::register("packed_ice", BlockFactory::get(174));
		self::register("peony", BlockFactory::get(175, 5));
		self::register("pink_carpet", BlockFactory::get(171, 6));
		self::register("pink_concrete", BlockFactory::get(236, 6));
		self::register("pink_concrete_powder", BlockFactory::get(237, 6));
		self::register("pink_glazed_terracotta", BlockFactory::get(226, 2));
		self::register("pink_stained_clay", BlockFactory::get(159, 6));
		self::register("pink_stained_glass", BlockFactory::get(241, 6));
		self::register("pink_stained_glass_pane", BlockFactory::get(160, 6));
		self::register("pink_tulip", BlockFactory::get(38, 7));
		self::register("pink_wool", BlockFactory::get(35, 6));
		self::register("podzol", BlockFactory::get(243));
		self::register("polished_andesite", BlockFactory::get(1, 6));
		self::register("polished_andesite_slab", BlockFactory::get(417, 2));
		self::register("polished_andesite_stairs", BlockFactory::get(429));
		self::register("polished_diorite", BlockFactory::get(1, 4));
		self::register("polished_diorite_slab", BlockFactory::get(417, 5));
		self::register("polished_diorite_stairs", BlockFactory::get(428));
		self::register("polished_granite", BlockFactory::get(1, 2));
		self::register("polished_granite_slab", BlockFactory::get(417, 7));
		self::register("polished_granite_stairs", BlockFactory::get(427));
		self::register("poppy", BlockFactory::get(38));
		self::register("potatoes", BlockFactory::get(142));
		self::register("powered_rail", BlockFactory::get(27));
		self::register("prismarine", BlockFactory::get(168));
		self::register("prismarine_bricks", BlockFactory::get(168, 2));
		self::register("prismarine_bricks_slab", BlockFactory::get(182, 4));
		self::register("prismarine_bricks_stairs", BlockFactory::get(259));
		self::register("prismarine_slab", BlockFactory::get(182, 2));
		self::register("prismarine_stairs", BlockFactory::get(257));
		self::register("prismarine_wall", BlockFactory::get(139, 11));
		self::register("pumpkin", BlockFactory::get(86));
		self::register("pumpkin_stem", BlockFactory::get(104));
		self::register("purple_carpet", BlockFactory::get(171, 10));
		self::register("purple_concrete", BlockFactory::get(236, 10));
		self::register("purple_concrete_powder", BlockFactory::get(237, 10));
		self::register("purple_glazed_terracotta", BlockFactory::get(219, 2));
		self::register("purple_stained_clay", BlockFactory::get(159, 10));
		self::register("purple_stained_glass", BlockFactory::get(241, 10));
		self::register("purple_stained_glass_pane", BlockFactory::get(160, 10));
		self::register("purple_torch", BlockFactory::get(204, 13));
		self::register("purple_wool", BlockFactory::get(35, 10));
		self::register("purpur", BlockFactory::get(201));
		self::register("purpur_pillar", BlockFactory::get(201, 2));
		self::register("purpur_slab", BlockFactory::get(182, 1));
		self::register("purpur_stairs", BlockFactory::get(203));
		self::register("quartz", BlockFactory::get(155));
		self::register("quartz_pillar", BlockFactory::get(155, 2));
		self::register("quartz_slab", BlockFactory::get(44, 6));
		self::register("quartz_stairs", BlockFactory::get(156));
		self::register("rail", BlockFactory::get(66));
		self::register("red_carpet", BlockFactory::get(171, 14));
		self::register("red_concrete", BlockFactory::get(236, 14));
		self::register("red_concrete_powder", BlockFactory::get(237, 14));
		self::register("red_glazed_terracotta", BlockFactory::get(234, 2));
		self::register("red_mushroom", BlockFactory::get(40));
		self::register("red_mushroom_block", BlockFactory::get(100));
		self::register("red_nether_brick_slab", BlockFactory::get(182, 7));
		self::register("red_nether_brick_stairs", BlockFactory::get(439));
		self::register("red_nether_brick_wall", BlockFactory::get(139, 13));
		self::register("red_nether_bricks", BlockFactory::get(215));
		self::register("red_sand", BlockFactory::get(12, 1));
		self::register("red_sandstone", BlockFactory::get(179));
		self::register("red_sandstone_slab", BlockFactory::get(182));
		self::register("red_sandstone_stairs", BlockFactory::get(180));
		self::register("red_sandstone_wall", BlockFactory::get(139, 12));
		self::register("red_stained_clay", BlockFactory::get(159, 14));
		self::register("red_stained_glass", BlockFactory::get(241, 14));
		self::register("red_stained_glass_pane", BlockFactory::get(160, 14));
		self::register("red_torch", BlockFactory::get(202, 5));
		self::register("red_tulip", BlockFactory::get(38, 4));
		self::register("red_wool", BlockFactory::get(35, 14));
		self::register("redstone", BlockFactory::get(152));
		self::register("redstone_comparator", BlockFactory::get(149));
		self::register("redstone_lamp", BlockFactory::get(123));
		self::register("redstone_ore", BlockFactory::get(73));
		self::register("redstone_repeater", BlockFactory::get(93));
		self::register("redstone_torch", BlockFactory::get(76, 5));
		self::register("redstone_wire", BlockFactory::get(55));
		self::register("reserved6", BlockFactory::get(255));
		self::register("rose_bush", BlockFactory::get(175, 4));
		self::register("sand", BlockFactory::get(12));
		self::register("sandstone", BlockFactory::get(24));
		self::register("sandstone_slab", BlockFactory::get(44, 1));
		self::register("sandstone_stairs", BlockFactory::get(128));
		self::register("sandstone_wall", BlockFactory::get(139, 5));
		self::register("sea_lantern", BlockFactory::get(169));
		self::register("sea_pickle", BlockFactory::get(411));
		self::register("slightly_damaged_anvil", BlockFactory::get(145, 4));
		self::register("smooth_quartz", BlockFactory::get(155, 3));
		self::register("smooth_quartz_slab", BlockFactory::get(421, 1));
		self::register("smooth_quartz_stairs", BlockFactory::get(440));
		self::register("smooth_red_sandstone", BlockFactory::get(179, 3));
		self::register("smooth_red_sandstone_slab", BlockFactory::get(417, 1));
		self::register("smooth_red_sandstone_stairs", BlockFactory::get(431));
		self::register("smooth_sandstone", BlockFactory::get(24, 3));
		self::register("smooth_sandstone_slab", BlockFactory::get(182, 6));
		self::register("smooth_sandstone_stairs", BlockFactory::get(432));
		self::register("smooth_stone", BlockFactory::get(438));
		self::register("smooth_stone_slab", BlockFactory::get(44));
		self::register("snow", BlockFactory::get(80));
		self::register("snow_layer", BlockFactory::get(78));
		self::register("soul_sand", BlockFactory::get(88));
		self::register("sponge", BlockFactory::get(19));
		self::register("spruce_button", BlockFactory::get(399));
		self::register("spruce_door", BlockFactory::get(193));
		self::register("spruce_fence", BlockFactory::get(85, 1));
		self::register("spruce_fence_gate", BlockFactory::get(183));
		self::register("spruce_leaves", BlockFactory::get(18, 1));
		self::register("spruce_log", BlockFactory::get(17, 1));
		self::register("spruce_planks", BlockFactory::get(5, 1));
		self::register("spruce_pressure_plate", BlockFactory::get(409));
		self::register("spruce_sapling", BlockFactory::get(6, 1));
		self::register("spruce_sign", BlockFactory::get(436));
		self::register("spruce_slab", BlockFactory::get(158, 1));
		self::register("spruce_stairs", BlockFactory::get(134));
		self::register("spruce_trapdoor", BlockFactory::get(404));
		self::register("spruce_wood", BlockFactory::get(467, 1));
		self::register("stone", BlockFactory::get(1));
		self::register("stone_brick_slab", BlockFactory::get(44, 5));
		self::register("stone_brick_stairs", BlockFactory::get(109));
		self::register("stone_brick_wall", BlockFactory::get(139, 7));
		self::register("stone_bricks", BlockFactory::get(98));
		self::register("stone_button", BlockFactory::get(77));
		self::register("stone_pressure_plate", BlockFactory::get(70));
		self::register("stone_slab", BlockFactory::get(421, 2));
		self::register("stone_stairs", BlockFactory::get(435));
		self::register("sugarcane", BlockFactory::get(83));
		self::register("sunflower", BlockFactory::get(175));
		self::register("tall_grass", BlockFactory::get(31, 1));
		self::register("tnt", BlockFactory::get(46));
		self::register("torch", BlockFactory::get(50, 5));
		self::register("trapped_chest", BlockFactory::get(146, 2));
		self::register("tripwire", BlockFactory::get(132));
		self::register("tripwire_hook", BlockFactory::get(131));
		self::register("underwater_torch", BlockFactory::get(239, 5));
		self::register("very_damaged_anvil", BlockFactory::get(145, 8));
		self::register("vines", BlockFactory::get(106));
		self::register("water", BlockFactory::get(8));
		self::register("weighted_pressure_plate_heavy", BlockFactory::get(148));
		self::register("weighted_pressure_plate_light", BlockFactory::get(147));
		self::register("wheat", BlockFactory::get(59));
		self::register("white_carpet", BlockFactory::get(171));
		self::register("white_concrete", BlockFactory::get(236));
		self::register("white_concrete_powder", BlockFactory::get(237));
		self::register("white_glazed_terracotta", BlockFactory::get(220, 2));
		self::register("white_stained_clay", BlockFactory::get(159));
		self::register("white_stained_glass", BlockFactory::get(241));
		self::register("white_stained_glass_pane", BlockFactory::get(160));
		self::register("white_tulip", BlockFactory::get(38, 6));
		self::register("white_wool", BlockFactory::get(35));
		self::register("yellow_carpet", BlockFactory::get(171, 4));
		self::register("yellow_concrete", BlockFactory::get(236, 4));
		self::register("yellow_concrete_powder", BlockFactory::get(237, 4));
		self::register("yellow_glazed_terracotta", BlockFactory::get(224, 2));
		self::register("yellow_stained_clay", BlockFactory::get(159, 4));
		self::register("yellow_stained_glass", BlockFactory::get(241, 4));
		self::register("yellow_stained_glass_pane", BlockFactory::get(160, 4));
		self::register("yellow_wool", BlockFactory::get(35, 4));
	}
}
