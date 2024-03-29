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

use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\BlockTypeInfo as Info;
use pocketmine\block\BlockTypeTags as Tags;
use pocketmine\block\tile\Banner as TileBanner;
use pocketmine\block\tile\Barrel as TileBarrel;
use pocketmine\block\tile\Beacon as TileBeacon;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\block\tile\Bell as TileBell;
use pocketmine\block\tile\BlastFurnace as TileBlastFurnace;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\tile\Cauldron as TileCauldron;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\ChiseledBookshelf as TileChiseledBookshelf;
use pocketmine\block\tile\Comparator as TileComparator;
use pocketmine\block\tile\DaylightSensor as TileDaylightSensor;
use pocketmine\block\tile\EnchantTable as TileEnchantingTable;
use pocketmine\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\tile\FlowerPot as TileFlowerPot;
use pocketmine\block\tile\GlowingItemFrame as TileGlowingItemFrame;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\tile\ItemFrame as TileItemFrame;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\tile\MobHead as TileMobHead;
use pocketmine\block\tile\MonsterSpawner as TileMonsterSpawner;
use pocketmine\block\tile\NormalFurnace as TileNormalFurnace;
use pocketmine\block\tile\Note as TileNote;
use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\tile\Smoker as TileSmoker;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\AmethystTrait;
use pocketmine\block\utils\LeavesType;
use pocketmine\block\utils\SaplingType;
use pocketmine\block\utils\WoodType;
use pocketmine\crafting\FurnaceType;
use pocketmine\item\enchantment\ItemEnchantmentTags as EnchantmentTags;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\utils\CloningRegistryTrait;
use function mb_strtolower;
use function strtolower;

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
 * @method static AmethystCluster AMETHYST_CLUSTER()
 * @method static Opaque ANCIENT_DEBRIS()
 * @method static Opaque ANDESITE()
 * @method static Slab ANDESITE_SLAB()
 * @method static Stair ANDESITE_STAIRS()
 * @method static Wall ANDESITE_WALL()
 * @method static Anvil ANVIL()
 * @method static Leaves AZALEA_LEAVES()
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
 * @method static BigDripleafHead BIG_DRIPLEAF_HEAD()
 * @method static BigDripleafStem BIG_DRIPLEAF_STEM()
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
 * @method static BuddingAmethyst BUDDING_AMETHYST()
 * @method static Cactus CACTUS()
 * @method static Cake CAKE()
 * @method static CakeWithCandle CAKE_WITH_CANDLE()
 * @method static CakeWithDyedCandle CAKE_WITH_DYED_CANDLE()
 * @method static Opaque CALCITE()
 * @method static Candle CANDLE()
 * @method static Carpet CARPET()
 * @method static Carrot CARROTS()
 * @method static CartographyTable CARTOGRAPHY_TABLE()
 * @method static CarvedPumpkin CARVED_PUMPKIN()
 * @method static Cauldron CAULDRON()
 * @method static CaveVines CAVE_VINES()
 * @method static Chain CHAIN()
 * @method static ChemicalHeat CHEMICAL_HEAT()
 * @method static WoodenButton CHERRY_BUTTON()
 * @method static WoodenDoor CHERRY_DOOR()
 * @method static WoodenFence CHERRY_FENCE()
 * @method static FenceGate CHERRY_FENCE_GATE()
 * @method static Leaves CHERRY_LEAVES()
 * @method static Wood CHERRY_LOG()
 * @method static Planks CHERRY_PLANKS()
 * @method static WoodenPressurePlate CHERRY_PRESSURE_PLATE()
 * @method static FloorSign CHERRY_SIGN()
 * @method static WoodenSlab CHERRY_SLAB()
 * @method static WoodenStairs CHERRY_STAIRS()
 * @method static WoodenTrapdoor CHERRY_TRAPDOOR()
 * @method static WallSign CHERRY_WALL_SIGN()
 * @method static Wood CHERRY_WOOD()
 * @method static Chest CHEST()
 * @method static ChiseledBookshelf CHISELED_BOOKSHELF()
 * @method static Opaque CHISELED_DEEPSLATE()
 * @method static Opaque CHISELED_NETHER_BRICKS()
 * @method static Opaque CHISELED_POLISHED_BLACKSTONE()
 * @method static SimplePillar CHISELED_QUARTZ()
 * @method static Opaque CHISELED_RED_SANDSTONE()
 * @method static Opaque CHISELED_SANDSTONE()
 * @method static Opaque CHISELED_STONE_BRICKS()
 * @method static ChorusFlower CHORUS_FLOWER()
 * @method static ChorusPlant CHORUS_PLANT()
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
 * @method static Copper COPPER()
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
 * @method static NetherRoots CRIMSON_ROOTS()
 * @method static FloorSign CRIMSON_SIGN()
 * @method static WoodenSlab CRIMSON_SLAB()
 * @method static WoodenStairs CRIMSON_STAIRS()
 * @method static Wood CRIMSON_STEM()
 * @method static WoodenTrapdoor CRIMSON_TRAPDOOR()
 * @method static WallSign CRIMSON_WALL_SIGN()
 * @method static Opaque CRYING_OBSIDIAN()
 * @method static Copper CUT_COPPER()
 * @method static CopperSlab CUT_COPPER_SLAB()
 * @method static CopperStairs CUT_COPPER_STAIRS()
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
 * @method static DoublePitcherCrop DOUBLE_PITCHER_CROP()
 * @method static DoubleTallGrass DOUBLE_TALLGRASS()
 * @method static DragonEgg DRAGON_EGG()
 * @method static DriedKelp DRIED_KELP()
 * @method static DyedCandle DYED_CANDLE()
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
 * @method static Leaves FLOWERING_AZALEA_LEAVES()
 * @method static FlowerPot FLOWER_POT()
 * @method static Froglight FROGLIGHT()
 * @method static FrostedIce FROSTED_ICE()
 * @method static Furnace FURNACE()
 * @method static GildedBlackstone GILDED_BLACKSTONE()
 * @method static Glass GLASS()
 * @method static GlassPane GLASS_PANE()
 * @method static GlazedTerracotta GLAZED_TERRACOTTA()
 * @method static ItemFrame GLOWING_ITEM_FRAME()
 * @method static GlowingObsidian GLOWING_OBSIDIAN()
 * @method static Glowstone GLOWSTONE()
 * @method static GlowLichen GLOW_LICHEN()
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
 * @method static HangingRoots HANGING_ROOTS()
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
 * @method static LavaCauldron LAVA_CAULDRON()
 * @method static Lectern LECTERN()
 * @method static Opaque LEGACY_STONECUTTER()
 * @method static Lever LEVER()
 * @method static Light LIGHT()
 * @method static LightningRod LIGHTNING_ROD()
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
 * @method static Leaves MANGROVE_LEAVES()
 * @method static Wood MANGROVE_LOG()
 * @method static Planks MANGROVE_PLANKS()
 * @method static WoodenPressurePlate MANGROVE_PRESSURE_PLATE()
 * @method static MangroveRoots MANGROVE_ROOTS()
 * @method static FloorSign MANGROVE_SIGN()
 * @method static WoodenSlab MANGROVE_SLAB()
 * @method static WoodenStairs MANGROVE_STAIRS()
 * @method static WoodenTrapdoor MANGROVE_TRAPDOOR()
 * @method static WallSign MANGROVE_WALL_SIGN()
 * @method static Wood MANGROVE_WOOD()
 * @method static ChemistryTable MATERIAL_REDUCER()
 * @method static Melon MELON()
 * @method static MelonStem MELON_STEM()
 * @method static MobHead MOB_HEAD()
 * @method static MonsterSpawner MONSTER_SPAWNER()
 * @method static Opaque MOSSY_COBBLESTONE()
 * @method static Slab MOSSY_COBBLESTONE_SLAB()
 * @method static Stair MOSSY_COBBLESTONE_STAIRS()
 * @method static Wall MOSSY_COBBLESTONE_WALL()
 * @method static Opaque MOSSY_STONE_BRICKS()
 * @method static Slab MOSSY_STONE_BRICK_SLAB()
 * @method static Stair MOSSY_STONE_BRICK_STAIRS()
 * @method static Wall MOSSY_STONE_BRICK_WALL()
 * @method static Opaque MUD()
 * @method static SimplePillar MUDDY_MANGROVE_ROOTS()
 * @method static Opaque MUD_BRICKS()
 * @method static Slab MUD_BRICK_SLAB()
 * @method static Stair MUD_BRICK_STAIRS()
 * @method static Wall MUD_BRICK_WALL()
 * @method static MushroomStem MUSHROOM_STEM()
 * @method static Mycelium MYCELIUM()
 * @method static Opaque NETHERITE()
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
 * @method static Opaque PACKED_MUD()
 * @method static DoublePlant PEONY()
 * @method static PinkPetals PINK_PETALS()
 * @method static Flower PINK_TULIP()
 * @method static PitcherCrop PITCHER_CROP()
 * @method static DoublePlant PITCHER_PLANT()
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
 * @method static PotionCauldron POTION_CAULDRON()
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
 * @method static Opaque REINFORCED_DEEPSLATE()
 * @method static Reserved6 RESERVED6()
 * @method static DoublePlant ROSE_BUSH()
 * @method static Sand SAND()
 * @method static Opaque SANDSTONE()
 * @method static Slab SANDSTONE_SLAB()
 * @method static Stair SANDSTONE_STAIRS()
 * @method static Wall SANDSTONE_WALL()
 * @method static Sculk SCULK()
 * @method static SeaLantern SEA_LANTERN()
 * @method static SeaPickle SEA_PICKLE()
 * @method static Opaque SHROOMLIGHT()
 * @method static ShulkerBox SHULKER_BOX()
 * @method static Slime SLIME()
 * @method static SmallDripleaf SMALL_DRIPLEAF()
 * @method static SmithingTable SMITHING_TABLE()
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
 * @method static SporeBlossom SPORE_BLOSSOM()
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
 * @method static TintedGlass TINTED_GLASS()
 * @method static TNT TNT()
 * @method static Torch TORCH()
 * @method static Flower TORCHFLOWER()
 * @method static TorchflowerCrop TORCHFLOWER_CROP()
 * @method static TrappedChest TRAPPED_CHEST()
 * @method static Tripwire TRIPWIRE()
 * @method static TripwireHook TRIPWIRE_HOOK()
 * @method static Opaque TUFF()
 * @method static NetherVines TWISTING_VINES()
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
 * @method static NetherRoots WARPED_ROOTS()
 * @method static FloorSign WARPED_SIGN()
 * @method static WoodenSlab WARPED_SLAB()
 * @method static WoodenStairs WARPED_STAIRS()
 * @method static Wood WARPED_STEM()
 * @method static WoodenTrapdoor WARPED_TRAPDOOR()
 * @method static WallSign WARPED_WALL_SIGN()
 * @method static Opaque WARPED_WART_BLOCK()
 * @method static Water WATER()
 * @method static WaterCauldron WATER_CAULDRON()
 * @method static NetherVines WEEPING_VINES()
 * @method static WeightedPressurePlateHeavy WEIGHTED_PRESSURE_PLATE_HEAVY()
 * @method static WeightedPressurePlateLight WEIGHTED_PRESSURE_PLATE_LIGHT()
 * @method static Wheat WHEAT()
 * @method static Flower WHITE_TULIP()
 * @method static WitherRose WITHER_ROSE()
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

	private static int $nextTypeId = 10_000;

	/**
	 * @phpstan-param class-string<covariant Tile>|null $tileClass
	 */
	private static function newBID(?string $tileClass = null) : BID{
		return new BID(self::$nextTypeId++, $tileClass);
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
		self::register("air", new Air(self::newBID(), "Air", new Info(BreakInfo::indestructible(-1.0))));

		$railBreakInfo = new Info(new BreakInfo(0.7));
		self::register("activator_rail", new ActivatorRail(self::newBID(), "Activator Rail", $railBreakInfo));
		self::register("anvil", new Anvil(self::newBID(), "Anvil", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 6000.0))));
		self::register("bamboo", new Bamboo(self::newBID(), "Bamboo", new Info(new class(2.0 /* 1.0 in PC */, ToolType::AXE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}, [Tags::POTTABLE_PLANTS])));
		self::register("bamboo_sapling", new BambooSapling(self::newBID(), "Bamboo Sapling", new Info(BreakInfo::instant())));

		$bannerBreakInfo = new Info(BreakInfo::axe(1.0));
		self::register("banner", new FloorBanner(self::newBID(TileBanner::class), "Banner", $bannerBreakInfo));
		self::register("wall_banner", new WallBanner(self::newBID(TileBanner::class), "Wall Banner", $bannerBreakInfo));
		self::register("barrel", new Barrel(self::newBID(TileBarrel::class), "Barrel", new Info(BreakInfo::axe(2.5))));
		self::register("barrier", new Transparent(self::newBID(), "Barrier", new Info(BreakInfo::indestructible())));
		self::register("beacon", new Beacon(self::newBID(TileBeacon::class), "Beacon", new Info(new BreakInfo(3.0))));
		self::register("bed", new Bed(self::newBID(TileBed::class), "Bed Block", new Info(new BreakInfo(0.2))));
		self::register("bedrock", new Bedrock(self::newBID(), "Bedrock", new Info(BreakInfo::indestructible())));

		self::register("beetroots", new Beetroot(self::newBID(), "Beetroot Block", new Info(BreakInfo::instant())));
		self::register("bell", new Bell(self::newBID(TileBell::class), "Bell", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD))));
		self::register("blue_ice", new BlueIce(self::newBID(), "Blue Ice", new Info(BreakInfo::pickaxe(2.8))));
		self::register("bone_block", new BoneBlock(self::newBID(), "Bone Block", new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD))));
		self::register("bookshelf", new Bookshelf(self::newBID(), "Bookshelf", new Info(BreakInfo::axe(1.5))));
		self::register("chiseled_bookshelf", new ChiseledBookshelf(self::newBID(TileChiseledBookshelf::class), "Chiseled Bookshelf", new Info(BreakInfo::axe(1.5))));
		self::register("brewing_stand", new BrewingStand(self::newBID(TileBrewingStand::class), "Brewing Stand", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD))));

		$bricksBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("brick_stairs", new Stair(self::newBID(), "Brick Stairs", $bricksBreakInfo));
		self::register("bricks", new Opaque(self::newBID(), "Bricks", $bricksBreakInfo));

		self::register("brown_mushroom", new BrownMushroom(self::newBID(), "Brown Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("cactus", new Cactus(self::newBID(), "Cactus", new Info(new BreakInfo(0.4), [Tags::POTTABLE_PLANTS])));
		self::register("cake", new Cake(self::newBID(), "Cake", new Info(new BreakInfo(0.5))));
		self::register("carrots", new Carrot(self::newBID(), "Carrot Block", new Info(BreakInfo::instant())));

		$chestBreakInfo = new Info(BreakInfo::axe(2.5));
		self::register("chest", new Chest(self::newBID(TileChest::class), "Chest", $chestBreakInfo));
		self::register("clay", new Clay(self::newBID(), "Clay Block", new Info(BreakInfo::shovel(0.6))));
		self::register("coal", new Coal(self::newBID(), "Coal Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));

		$cobblestoneBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("cobblestone", $cobblestone = new Opaque(self::newBID(), "Cobblestone", $cobblestoneBreakInfo));
		self::register("mossy_cobblestone", new Opaque(self::newBID(), "Mossy Cobblestone", $cobblestoneBreakInfo));
		self::register("cobblestone_stairs", new Stair(self::newBID(), "Cobblestone Stairs", $cobblestoneBreakInfo));
		self::register("mossy_cobblestone_stairs", new Stair(self::newBID(), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		self::register("cobweb", new Cobweb(self::newBID(), "Cobweb", new Info(new BreakInfo(4.0, ToolType::SWORD | ToolType::SHEARS, 1))));
		self::register("cocoa_pod", new CocoaBlock(self::newBID(), "Cocoa Block", new Info(BreakInfo::axe(0.2, null, 15.0))));
		self::register("coral_block", new CoralBlock(self::newBID(), "Coral Block", new Info(BreakInfo::pickaxe(7.0, ToolTier::WOOD))));
		self::register("daylight_sensor", new DaylightSensor(self::newBID(TileDaylightSensor::class), "Daylight Sensor", new Info(BreakInfo::axe(0.2))));
		self::register("dead_bush", new DeadBush(self::newBID(), "Dead Bush", new Info(BreakInfo::instant(ToolType::SHEARS, 1), [Tags::POTTABLE_PLANTS])));
		self::register("detector_rail", new DetectorRail(self::newBID(), "Detector Rail", $railBreakInfo));

		self::register("diamond", new Opaque(self::newBID(), "Diamond Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::IRON, 30.0))));
		self::register("dirt", new Dirt(self::newBID(), "Dirt", new Info(BreakInfo::shovel(0.5), [Tags::DIRT])));
		self::register("sunflower", new DoublePlant(self::newBID(), "Sunflower", new Info(BreakInfo::instant())));
		self::register("lilac", new DoublePlant(self::newBID(), "Lilac", new Info(BreakInfo::instant())));
		self::register("rose_bush", new DoublePlant(self::newBID(), "Rose Bush", new Info(BreakInfo::instant())));
		self::register("peony", new DoublePlant(self::newBID(), "Peony", new Info(BreakInfo::instant())));
		self::register("pink_petals", new PinkPetals(self::newBID(), "Pink Petals", new Info(BreakInfo::instant())));
		self::register("double_tallgrass", new DoubleTallGrass(self::newBID(), "Double Tallgrass", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));
		self::register("large_fern", new DoubleTallGrass(self::newBID(), "Large Fern", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));
		self::register("pitcher_plant", new DoublePlant(self::newBID(), "Pitcher Plant", new Info(BreakInfo::instant())));
		self::register("pitcher_crop", new PitcherCrop(self::newBID(), "Pitcher Crop", new Info(BreakInfo::instant())));
		self::register("double_pitcher_crop", new DoublePitcherCrop(self::newBID(), "Double Pitcher Crop", new Info(BreakInfo::instant())));
		self::register("dragon_egg", new DragonEgg(self::newBID(), "Dragon Egg", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD))));
		self::register("dried_kelp", new DriedKelp(self::newBID(), "Dried Kelp Block", new Info(new BreakInfo(0.5, ToolType::NONE, 0, 12.5))));
		self::register("emerald", new Opaque(self::newBID(), "Emerald Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::IRON, 30.0))));
		self::register("enchanting_table", new EnchantingTable(self::newBID(TileEnchantingTable::class), "Enchanting Table", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 6000.0))));
		self::register("end_portal_frame", new EndPortalFrame(self::newBID(), "End Portal Frame", new Info(BreakInfo::indestructible())));
		self::register("end_rod", new EndRod(self::newBID(), "End Rod", new Info(BreakInfo::instant())));
		self::register("end_stone", new Opaque(self::newBID(), "End Stone", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 45.0))));

		$endBrickBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD, 4.0));
		self::register("end_stone_bricks", new Opaque(self::newBID(), "End Stone Bricks", $endBrickBreakInfo));
		self::register("end_stone_brick_stairs", new Stair(self::newBID(), "End Stone Brick Stairs", $endBrickBreakInfo));

		self::register("ender_chest", new EnderChest(self::newBID(TileEnderChest::class), "Ender Chest", new Info(BreakInfo::pickaxe(22.5, ToolTier::WOOD, 3000.0))));
		self::register("farmland", new Farmland(self::newBID(), "Farmland", new Info(BreakInfo::shovel(0.6), [Tags::DIRT])));
		self::register("fire", new Fire(self::newBID(), "Fire Block", new Info(BreakInfo::instant(), [Tags::FIRE])));

		$flowerTypeInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);
		self::register("dandelion", new Flower(self::newBID(), "Dandelion", $flowerTypeInfo));
		self::register("poppy", new Flower(self::newBID(), "Poppy", $flowerTypeInfo));
		self::register("allium", new Flower(self::newBID(), "Allium", $flowerTypeInfo));
		self::register("azure_bluet", new Flower(self::newBID(), "Azure Bluet", $flowerTypeInfo));
		self::register("blue_orchid", new Flower(self::newBID(), "Blue Orchid", $flowerTypeInfo));
		self::register("cornflower", new Flower(self::newBID(), "Cornflower", $flowerTypeInfo));
		self::register("lily_of_the_valley", new Flower(self::newBID(), "Lily of the Valley", $flowerTypeInfo));
		self::register("orange_tulip", new Flower(self::newBID(), "Orange Tulip", $flowerTypeInfo));
		self::register("oxeye_daisy", new Flower(self::newBID(), "Oxeye Daisy", $flowerTypeInfo));
		self::register("pink_tulip", new Flower(self::newBID(), "Pink Tulip", $flowerTypeInfo));
		self::register("red_tulip", new Flower(self::newBID(), "Red Tulip", $flowerTypeInfo));
		self::register("white_tulip", new Flower(self::newBID(), "White Tulip", $flowerTypeInfo));
		self::register("torchflower", new Flower(self::newBID(), "Torchflower", $flowerTypeInfo));
		self::register("torchflower_crop", new TorchflowerCrop(self::newBID(), "Torchflower Crop", new Info(BreakInfo::instant())));
		self::register("flower_pot", new FlowerPot(self::newBID(TileFlowerPot::class), "Flower Pot", new Info(BreakInfo::instant())));
		self::register("frosted_ice", new FrostedIce(self::newBID(), "Frosted Ice", new Info(BreakInfo::pickaxe(2.5))));
		self::register("furnace", new Furnace(self::newBID(TileNormalFurnace::class), "Furnace", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::FURNACE));
		self::register("blast_furnace", new Furnace(self::newBID(TileBlastFurnace::class), "Blast Furnace", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::BLAST_FURNACE));
		self::register("smoker", new Furnace(self::newBID(TileSmoker::class), "Smoker", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::SMOKER));

		$glassBreakInfo = new Info(new BreakInfo(0.3));
		self::register("glass", new Glass(self::newBID(), "Glass", $glassBreakInfo));
		self::register("glass_pane", new GlassPane(self::newBID(), "Glass Pane", $glassBreakInfo));
		self::register("glowing_obsidian", new GlowingObsidian(self::newBID(), "Glowing Obsidian", new Info(BreakInfo::pickaxe(10.0, ToolTier::DIAMOND, 50.0))));
		self::register("glowstone", new Glowstone(self::newBID(), "Glowstone", new Info(BreakInfo::pickaxe(0.3))));
		self::register("glow_lichen", new GlowLichen(self::newBID(), "Glow Lichen", new Info(BreakInfo::axe(0.2, null, 0.2))));
		self::register("gold", new Opaque(self::newBID(), "Gold Block", new Info(BreakInfo::pickaxe(3.0, ToolTier::IRON, 30.0))));

		$grassBreakInfo = BreakInfo::shovel(0.6);
		self::register("grass", new Grass(self::newBID(), "Grass", new Info($grassBreakInfo, [Tags::DIRT])));
		self::register("grass_path", new GrassPath(self::newBID(), "Grass Path", new Info($grassBreakInfo)));
		self::register("gravel", new Gravel(self::newBID(), "Gravel", new Info(BreakInfo::shovel(0.6))));

		$hardenedClayBreakInfo = new Info(BreakInfo::pickaxe(1.25, ToolTier::WOOD, 21.0));
		self::register("hardened_clay", new HardenedClay(self::newBID(), "Hardened Clay", $hardenedClayBreakInfo));

		$hardenedGlassBreakInfo = new Info(new BreakInfo(10.0));
		self::register("hardened_glass", new HardenedGlass(self::newBID(), "Hardened Glass", $hardenedGlassBreakInfo));
		self::register("hardened_glass_pane", new HardenedGlassPane(self::newBID(), "Hardened Glass Pane", $hardenedGlassBreakInfo));
		self::register("hay_bale", new HayBale(self::newBID(), "Hay Bale", new Info(new BreakInfo(0.5))));
		self::register("hopper", new Hopper(self::newBID(TileHopper::class), "Hopper", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 15.0))));
		self::register("ice", new Ice(self::newBID(), "Ice", new Info(BreakInfo::pickaxe(0.5))));

		$updateBlockBreakInfo = new Info(new BreakInfo(1.0));
		self::register("info_update", new Opaque(self::newBID(), "update!", $updateBlockBreakInfo));
		self::register("info_update2", new Opaque(self::newBID(), "ate!upd", $updateBlockBreakInfo));
		self::register("invisible_bedrock", new Transparent(self::newBID(), "Invisible Bedrock", new Info(BreakInfo::indestructible())));

		$ironBreakInfo = new Info(BreakInfo::pickaxe(5.0, ToolTier::STONE, 30.0));
		self::register("iron", new Opaque(self::newBID(), "Iron Block", $ironBreakInfo));
		self::register("iron_bars", new Thin(self::newBID(), "Iron Bars", $ironBreakInfo));
		$ironDoorBreakInfo = new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 25.0));
		self::register("iron_door", new Door(self::newBID(), "Iron Door", $ironDoorBreakInfo));
		self::register("iron_trapdoor", new Trapdoor(self::newBID(), "Iron Trapdoor", $ironDoorBreakInfo));

		$itemFrameInfo = new Info(new BreakInfo(0.25));
		self::register("item_frame", new ItemFrame(self::newBID(TileItemFrame::class), "Item Frame", $itemFrameInfo));
		self::register("glowing_item_frame", new ItemFrame(self::newBID(TileGlowingItemFrame::class), "Glow Item Frame", $itemFrameInfo));

		self::register("jukebox", new Jukebox(self::newBID(TileJukebox::class), "Jukebox", new Info(BreakInfo::axe(0.8)))); //TODO: in PC the hardness is 2.0, not 0.8, unsure if this is a MCPE bug or not
		self::register("ladder", new Ladder(self::newBID(), "Ladder", new Info(BreakInfo::axe(0.4))));

		$lanternBreakInfo = new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD));
		self::register("lantern", new Lantern(self::newBID(), "Lantern", $lanternBreakInfo, 15));
		self::register("soul_lantern", new Lantern(self::newBID(), "Soul Lantern", $lanternBreakInfo, 10));

		self::register("lapis_lazuli", new Opaque(self::newBID(), "Lapis Lazuli Block", new Info(BreakInfo::pickaxe(3.0, ToolTier::STONE))));
		self::register("lava", new Lava(self::newBID(), "Lava", new Info(BreakInfo::indestructible(500.0))));
		self::register("lectern", new Lectern(self::newBID(TileLectern::class), "Lectern", new Info(BreakInfo::axe(2.0))));
		self::register("lever", new Lever(self::newBID(), "Lever", new Info(new BreakInfo(0.5))));
		self::register("magma", new Magma(self::newBID(), "Magma Block", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD))));
		self::register("melon", new Melon(self::newBID(), "Melon Block", new Info(BreakInfo::axe(1.0))));
		self::register("melon_stem", new MelonStem(self::newBID(), "Melon Stem", new Info(BreakInfo::instant())));
		self::register("monster_spawner", new MonsterSpawner(self::newBID(TileMonsterSpawner::class), "Monster Spawner", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD))));
		self::register("mycelium", new Mycelium(self::newBID(), "Mycelium", new Info(BreakInfo::shovel(0.6), [Tags::DIRT])));

		$netherBrickBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("nether_bricks", new Opaque(self::newBID(), "Nether Bricks", $netherBrickBreakInfo));
		self::register("red_nether_bricks", new Opaque(self::newBID(), "Red Nether Bricks", $netherBrickBreakInfo));
		self::register("nether_brick_fence", new Fence(self::newBID(), "Nether Brick Fence", $netherBrickBreakInfo));
		self::register("nether_brick_stairs", new Stair(self::newBID(), "Nether Brick Stairs", $netherBrickBreakInfo));
		self::register("red_nether_brick_stairs", new Stair(self::newBID(), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		self::register("chiseled_nether_bricks", new Opaque(self::newBID(), "Chiseled Nether Bricks", $netherBrickBreakInfo));
		self::register("cracked_nether_bricks", new Opaque(self::newBID(), "Cracked Nether Bricks", $netherBrickBreakInfo));

		self::register("nether_portal", new NetherPortal(self::newBID(), "Nether Portal", new Info(BreakInfo::indestructible(0.0))));
		self::register("nether_reactor_core", new NetherReactor(self::newBID(), "Nether Reactor Core", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD))));
		self::register("nether_wart_block", new Opaque(self::newBID(), "Nether Wart Block", new Info(new BreakInfo(1.0, ToolType::HOE))));
		self::register("nether_wart", new NetherWartPlant(self::newBID(), "Nether Wart", new Info(BreakInfo::instant())));
		self::register("netherrack", new Netherrack(self::newBID(), "Netherrack", new Info(BreakInfo::pickaxe(0.4, ToolTier::WOOD))));
		self::register("note_block", new Note(self::newBID(TileNote::class), "Note Block", new Info(BreakInfo::axe(0.8))));
		self::register("obsidian", new Opaque(self::newBID(), "Obsidian", new Info(BreakInfo::pickaxe(35.0 /* 50 in PC */,  ToolTier::DIAMOND, 6000.0))));
		self::register("packed_ice", new PackedIce(self::newBID(), "Packed Ice", new Info(BreakInfo::pickaxe(0.5))));
		self::register("podzol", new Podzol(self::newBID(), "Podzol", new Info(BreakInfo::shovel(0.5), [Tags::DIRT])));
		self::register("potatoes", new Potato(self::newBID(), "Potato Block", new Info(BreakInfo::instant())));
		self::register("powered_rail", new PoweredRail(self::newBID(), "Powered Rail", $railBreakInfo));

		$prismarineBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("prismarine", new Opaque(self::newBID(), "Prismarine", $prismarineBreakInfo));
		self::register("dark_prismarine", new Opaque(self::newBID(), "Dark Prismarine", $prismarineBreakInfo));
		self::register("prismarine_bricks", new Opaque(self::newBID(), "Prismarine Bricks", $prismarineBreakInfo));
		self::register("prismarine_bricks_stairs", new Stair(self::newBID(), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		self::register("dark_prismarine_stairs", new Stair(self::newBID(), "Dark Prismarine Stairs", $prismarineBreakInfo));
		self::register("prismarine_stairs", new Stair(self::newBID(), "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new Info(BreakInfo::axe(1.0));
		self::register("pumpkin", new Pumpkin(self::newBID(), "Pumpkin", $pumpkinBreakInfo));
		self::register("carved_pumpkin", new CarvedPumpkin(self::newBID(), "Carved Pumpkin", new Info(BreakInfo::axe(1.0), enchantmentTags: [EnchantmentTags::MASK])));
		self::register("lit_pumpkin", new LitPumpkin(self::newBID(), "Jack o'Lantern", $pumpkinBreakInfo));

		self::register("pumpkin_stem", new PumpkinStem(self::newBID(), "Pumpkin Stem", new Info(BreakInfo::instant())));

		$purpurBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("purpur", new Opaque(self::newBID(), "Purpur Block", $purpurBreakInfo));
		self::register("purpur_pillar", new SimplePillar(self::newBID(), "Purpur Pillar", $purpurBreakInfo));
		self::register("purpur_stairs", new Stair(self::newBID(), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD));
		self::register("quartz", new Opaque(self::newBID(), "Quartz Block", $quartzBreakInfo));
		self::register("chiseled_quartz", new SimplePillar(self::newBID(), "Chiseled Quartz Block", $quartzBreakInfo));
		self::register("quartz_pillar", new SimplePillar(self::newBID(), "Quartz Pillar", $quartzBreakInfo));
		self::register("smooth_quartz", new Opaque(self::newBID(), "Smooth Quartz Block", $quartzBreakInfo));
		self::register("quartz_bricks", new Opaque(self::newBID(), "Quartz Bricks", $quartzBreakInfo));

		self::register("quartz_stairs", new Stair(self::newBID(), "Quartz Stairs", $quartzBreakInfo));
		self::register("smooth_quartz_stairs", new Stair(self::newBID(), "Smooth Quartz Stairs", $quartzBreakInfo));

		self::register("rail", new Rail(self::newBID(), "Rail", $railBreakInfo));
		self::register("red_mushroom", new RedMushroom(self::newBID(), "Red Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("redstone", new Redstone(self::newBID(), "Redstone Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));
		self::register("redstone_comparator", new RedstoneComparator(self::newBID(TileComparator::class), "Redstone Comparator", new Info(BreakInfo::instant())));
		self::register("redstone_lamp", new RedstoneLamp(self::newBID(), "Redstone Lamp", new Info(new BreakInfo(0.3))));
		self::register("redstone_repeater", new RedstoneRepeater(self::newBID(), "Redstone Repeater", new Info(BreakInfo::instant())));
		self::register("redstone_torch", new RedstoneTorch(self::newBID(), "Redstone Torch", new Info(BreakInfo::instant())));
		self::register("redstone_wire", new RedstoneWire(self::newBID(), "Redstone", new Info(BreakInfo::instant())));
		self::register("reserved6", new Reserved6(self::newBID(), "reserved6", new Info(BreakInfo::instant())));

		$sandTypeInfo = new Info(BreakInfo::shovel(0.5), [Tags::SAND]);
		self::register("sand", new Sand(self::newBID(), "Sand", $sandTypeInfo));
		self::register("red_sand", new Sand(self::newBID(), "Red Sand", $sandTypeInfo));

		self::register("sea_lantern", new SeaLantern(self::newBID(), "Sea Lantern", new Info(new BreakInfo(0.3))));
		self::register("sea_pickle", new SeaPickle(self::newBID(), "Sea Pickle", new Info(BreakInfo::instant())));
		self::register("mob_head", new MobHead(self::newBID(TileMobHead::class), "Mob Head", new Info(new BreakInfo(1.0), enchantmentTags: [EnchantmentTags::MASK])));
		self::register("slime", new Slime(self::newBID(), "Slime Block", new Info(BreakInfo::instant())));
		self::register("snow", new Snow(self::newBID(), "Snow Block", new Info(BreakInfo::shovel(0.2, ToolTier::WOOD))));
		self::register("snow_layer", new SnowLayer(self::newBID(), "Snow Layer", new Info(BreakInfo::shovel(0.1, ToolTier::WOOD))));
		self::register("soul_sand", new SoulSand(self::newBID(), "Soul Sand", new Info(BreakInfo::shovel(0.5))));
		self::register("sponge", new Sponge(self::newBID(), "Sponge", new Info(new BreakInfo(0.6, ToolType::HOE))));
		$shulkerBoxBreakInfo = new Info(BreakInfo::pickaxe(2));
		self::register("shulker_box", new ShulkerBox(self::newBID(TileShulkerBox::class), "Shulker Box", $shulkerBoxBreakInfo));

		$stoneBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register(
			"stone",
			$stone = new class(self::newBID(), "Stone", $stoneBreakInfo) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			}
		);
		self::register("andesite", new Opaque(self::newBID(), "Andesite", $stoneBreakInfo));
		self::register("diorite", new Opaque(self::newBID(), "Diorite", $stoneBreakInfo));
		self::register("granite", new Opaque(self::newBID(), "Granite", $stoneBreakInfo));
		self::register("polished_andesite", new Opaque(self::newBID(), "Polished Andesite", $stoneBreakInfo));
		self::register("polished_diorite", new Opaque(self::newBID(), "Polished Diorite", $stoneBreakInfo));
		self::register("polished_granite", new Opaque(self::newBID(), "Polished Granite", $stoneBreakInfo));

		self::register("stone_bricks", $stoneBrick = new Opaque(self::newBID(), "Stone Bricks", $stoneBreakInfo));
		self::register("mossy_stone_bricks", $mossyStoneBrick = new Opaque(self::newBID(), "Mossy Stone Bricks", $stoneBreakInfo));
		self::register("cracked_stone_bricks", $crackedStoneBrick = new Opaque(self::newBID(), "Cracked Stone Bricks", $stoneBreakInfo));
		self::register("chiseled_stone_bricks", $chiseledStoneBrick = new Opaque(self::newBID(), "Chiseled Stone Bricks", $stoneBreakInfo));

		$infestedStoneBreakInfo = new Info(BreakInfo::pickaxe(0.75));
		self::register("infested_stone", new InfestedStone(self::newBID(), "Infested Stone", $infestedStoneBreakInfo, $stone));
		self::register("infested_stone_brick", new InfestedStone(self::newBID(), "Infested Stone Brick", $infestedStoneBreakInfo, $stoneBrick));
		self::register("infested_cobblestone", new InfestedStone(self::newBID(), "Infested Cobblestone", $infestedStoneBreakInfo, $cobblestone));
		self::register("infested_mossy_stone_brick", new InfestedStone(self::newBID(), "Infested Mossy Stone Brick", $infestedStoneBreakInfo, $mossyStoneBrick));
		self::register("infested_cracked_stone_brick", new InfestedStone(self::newBID(), "Infested Cracked Stone Brick", $infestedStoneBreakInfo, $crackedStoneBrick));
		self::register("infested_chiseled_stone_brick", new InfestedStone(self::newBID(), "Infested Chiseled Stone Brick", $infestedStoneBreakInfo, $chiseledStoneBrick));

		self::register("stone_stairs", new Stair(self::newBID(), "Stone Stairs", $stoneBreakInfo));
		self::register("smooth_stone", new Opaque(self::newBID(), "Smooth Stone", $stoneBreakInfo));
		self::register("andesite_stairs", new Stair(self::newBID(), "Andesite Stairs", $stoneBreakInfo));
		self::register("diorite_stairs", new Stair(self::newBID(), "Diorite Stairs", $stoneBreakInfo));
		self::register("granite_stairs", new Stair(self::newBID(), "Granite Stairs", $stoneBreakInfo));
		self::register("polished_andesite_stairs", new Stair(self::newBID(), "Polished Andesite Stairs", $stoneBreakInfo));
		self::register("polished_diorite_stairs", new Stair(self::newBID(), "Polished Diorite Stairs", $stoneBreakInfo));
		self::register("polished_granite_stairs", new Stair(self::newBID(), "Polished Granite Stairs", $stoneBreakInfo));
		self::register("stone_brick_stairs", new Stair(self::newBID(), "Stone Brick Stairs", $stoneBreakInfo));
		self::register("mossy_stone_brick_stairs", new Stair(self::newBID(), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		self::register("stone_button", new StoneButton(self::newBID(), "Stone Button", new Info(BreakInfo::pickaxe(0.5))));
		self::register("stonecutter", new Stonecutter(self::newBID(), "Stonecutter", new Info(BreakInfo::pickaxe(3.5))));
		self::register("stone_pressure_plate", new StonePressurePlate(self::newBID(), "Stone Pressure Plate", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD))));

		//TODO: in the future this won't be the same for all the types
		$stoneSlabBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));

		self::register("brick_slab", new Slab(self::newBID(), "Brick", $stoneSlabBreakInfo));
		self::register("cobblestone_slab", new Slab(self::newBID(), "Cobblestone", $stoneSlabBreakInfo));
		self::register("fake_wooden_slab", new Slab(self::newBID(), "Fake Wooden", $stoneSlabBreakInfo));
		self::register("nether_brick_slab", new Slab(self::newBID(), "Nether Brick", $stoneSlabBreakInfo));
		self::register("quartz_slab", new Slab(self::newBID(), "Quartz", $stoneSlabBreakInfo));
		self::register("sandstone_slab", new Slab(self::newBID(), "Sandstone", $stoneSlabBreakInfo));
		self::register("smooth_stone_slab", new Slab(self::newBID(), "Smooth Stone", $stoneSlabBreakInfo));
		self::register("stone_brick_slab", new Slab(self::newBID(), "Stone Brick", $stoneSlabBreakInfo));
		self::register("dark_prismarine_slab", new Slab(self::newBID(), "Dark Prismarine", $stoneSlabBreakInfo));
		self::register("mossy_cobblestone_slab", new Slab(self::newBID(), "Mossy Cobblestone", $stoneSlabBreakInfo));
		self::register("prismarine_slab", new Slab(self::newBID(), "Prismarine", $stoneSlabBreakInfo));
		self::register("prismarine_bricks_slab", new Slab(self::newBID(), "Prismarine Bricks", $stoneSlabBreakInfo));
		self::register("purpur_slab", new Slab(self::newBID(), "Purpur", $stoneSlabBreakInfo));
		self::register("red_nether_brick_slab", new Slab(self::newBID(), "Red Nether Brick", $stoneSlabBreakInfo));
		self::register("red_sandstone_slab", new Slab(self::newBID(), "Red Sandstone", $stoneSlabBreakInfo));
		self::register("smooth_sandstone_slab", new Slab(self::newBID(), "Smooth Sandstone", $stoneSlabBreakInfo));
		self::register("andesite_slab", new Slab(self::newBID(), "Andesite", $stoneSlabBreakInfo));
		self::register("diorite_slab", new Slab(self::newBID(), "Diorite", $stoneSlabBreakInfo));
		self::register("end_stone_brick_slab", new Slab(self::newBID(), "End Stone Brick", $stoneSlabBreakInfo));
		self::register("granite_slab", new Slab(self::newBID(), "Granite", $stoneSlabBreakInfo));
		self::register("polished_andesite_slab", new Slab(self::newBID(), "Polished Andesite", $stoneSlabBreakInfo));
		self::register("polished_diorite_slab", new Slab(self::newBID(), "Polished Diorite", $stoneSlabBreakInfo));
		self::register("polished_granite_slab", new Slab(self::newBID(), "Polished Granite", $stoneSlabBreakInfo));
		self::register("smooth_red_sandstone_slab", new Slab(self::newBID(), "Smooth Red Sandstone", $stoneSlabBreakInfo));
		self::register("cut_red_sandstone_slab", new Slab(self::newBID(), "Cut Red Sandstone", $stoneSlabBreakInfo));
		self::register("cut_sandstone_slab", new Slab(self::newBID(), "Cut Sandstone", $stoneSlabBreakInfo));
		self::register("mossy_stone_brick_slab", new Slab(self::newBID(), "Mossy Stone Brick", $stoneSlabBreakInfo));
		self::register("smooth_quartz_slab", new Slab(self::newBID(), "Smooth Quartz", $stoneSlabBreakInfo));
		self::register("stone_slab", new Slab(self::newBID(), "Stone", $stoneSlabBreakInfo));

		self::register("legacy_stonecutter", new Opaque(self::newBID(), "Legacy Stonecutter", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD))));
		self::register("sugarcane", new Sugarcane(self::newBID(), "Sugarcane", new Info(BreakInfo::instant())));
		self::register("sweet_berry_bush", new SweetBerryBush(self::newBID(), "Sweet Berry Bush", new Info(BreakInfo::instant())));
		self::register("tnt", new TNT(self::newBID(), "TNT", new Info(BreakInfo::instant())));
		self::register("fern", new TallGrass(self::newBID(), "Fern", new Info(BreakInfo::instant(ToolType::SHEARS, 1), [Tags::POTTABLE_PLANTS])));
		self::register("tall_grass", new TallGrass(self::newBID(), "Tall Grass", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));

		self::register("blue_torch", new Torch(self::newBID(), "Blue Torch", new Info(BreakInfo::instant())));
		self::register("purple_torch", new Torch(self::newBID(), "Purple Torch", new Info(BreakInfo::instant())));
		self::register("red_torch", new Torch(self::newBID(), "Red Torch", new Info(BreakInfo::instant())));
		self::register("green_torch", new Torch(self::newBID(), "Green Torch", new Info(BreakInfo::instant())));
		self::register("torch", new Torch(self::newBID(), "Torch", new Info(BreakInfo::instant())));

		self::register("trapped_chest", new TrappedChest(self::newBID(TileChest::class), "Trapped Chest", $chestBreakInfo));
		self::register("tripwire", new Tripwire(self::newBID(), "Tripwire", new Info(BreakInfo::instant())));
		self::register("tripwire_hook", new TripwireHook(self::newBID(), "Tripwire Hook", new Info(BreakInfo::instant())));
		self::register("underwater_torch", new UnderwaterTorch(self::newBID(), "Underwater Torch", new Info(BreakInfo::instant())));
		self::register("vines", new Vine(self::newBID(), "Vines", new Info(BreakInfo::axe(0.2))));
		self::register("water", new Water(self::newBID(), "Water", new Info(BreakInfo::indestructible(500.0))));
		self::register("lily_pad", new WaterLily(self::newBID(), "Lily Pad", new Info(BreakInfo::instant())));

		$weightedPressurePlateBreakInfo = new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD));
		self::register("weighted_pressure_plate_heavy", new WeightedPressurePlateHeavy(
			self::newBID(),
			"Weighted Pressure Plate Heavy",
			$weightedPressurePlateBreakInfo,
			deactivationDelayTicks: 10,
			signalStrengthFactor: 0.1
		));
		self::register("weighted_pressure_plate_light", new WeightedPressurePlateLight(
			self::newBID(),
			"Weighted Pressure Plate Light",
			$weightedPressurePlateBreakInfo,
			deactivationDelayTicks: 10,
			signalStrengthFactor: 1.0
		));
		self::register("wheat", new Wheat(self::newBID(), "Wheat Block", new Info(BreakInfo::instant())));

		$leavesBreakInfo = new Info(new class(0.2, ToolType::HOE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SHEARS){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		});
		$saplingTypeInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);

		foreach(SaplingType::cases() as $saplingType){
			$name = $saplingType->getDisplayName();
			self::register(strtolower($saplingType->name) . "_sapling", new Sapling(self::newBID(), $name . " Sapling", $saplingTypeInfo, $saplingType));
		}
		foreach(LeavesType::cases() as $leavesType){
			$name = $leavesType->getDisplayName();
			self::register(strtolower($leavesType->name) . "_leaves", new Leaves(self::newBID(), $name . " Leaves", $leavesBreakInfo, $leavesType));
		}

		$sandstoneBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD));
		self::register("red_sandstone_stairs", new Stair(self::newBID(), "Red Sandstone Stairs", $sandstoneBreakInfo));
		self::register("smooth_red_sandstone_stairs", new Stair(self::newBID(), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		self::register("red_sandstone", new Opaque(self::newBID(), "Red Sandstone", $sandstoneBreakInfo));
		self::register("chiseled_red_sandstone", new Opaque(self::newBID(), "Chiseled Red Sandstone", $sandstoneBreakInfo));
		self::register("cut_red_sandstone", new Opaque(self::newBID(), "Cut Red Sandstone", $sandstoneBreakInfo));
		self::register("smooth_red_sandstone", new Opaque(self::newBID(), "Smooth Red Sandstone", $sandstoneBreakInfo));

		self::register("sandstone_stairs", new Stair(self::newBID(), "Sandstone Stairs", $sandstoneBreakInfo));
		self::register("smooth_sandstone_stairs", new Stair(self::newBID(), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		self::register("sandstone", new Opaque(self::newBID(), "Sandstone", $sandstoneBreakInfo));
		self::register("chiseled_sandstone", new Opaque(self::newBID(), "Chiseled Sandstone", $sandstoneBreakInfo));
		self::register("cut_sandstone", new Opaque(self::newBID(), "Cut Sandstone", $sandstoneBreakInfo));
		self::register("smooth_sandstone", new Opaque(self::newBID(), "Smooth Sandstone", $sandstoneBreakInfo));

		self::register("glazed_terracotta", new GlazedTerracotta(self::newBID(), "Glazed Terracotta", new Info(BreakInfo::pickaxe(1.4, ToolTier::WOOD))));
		self::register("dyed_shulker_box", new DyedShulkerBox(self::newBID(TileShulkerBox::class), "Dyed Shulker Box", $shulkerBoxBreakInfo));
		self::register("stained_glass", new StainedGlass(self::newBID(), "Stained Glass", $glassBreakInfo));
		self::register("stained_glass_pane", new StainedGlassPane(self::newBID(), "Stained Glass Pane", $glassBreakInfo));
		self::register("stained_clay", new StainedHardenedClay(self::newBID(), "Stained Clay", $hardenedClayBreakInfo));
		self::register("stained_hardened_glass", new StainedHardenedGlass(self::newBID(), "Stained Hardened Glass", $hardenedGlassBreakInfo));
		self::register("stained_hardened_glass_pane", new StainedHardenedGlassPane(self::newBID(), "Stained Hardened Glass Pane", $hardenedGlassBreakInfo));
		self::register("carpet", new Carpet(self::newBID(), "Carpet", new Info(new BreakInfo(0.1))));
		self::register("concrete", new Concrete(self::newBID(), "Concrete", new Info(BreakInfo::pickaxe(1.8, ToolTier::WOOD))));
		self::register("concrete_powder", new ConcretePowder(self::newBID(), "Concrete Powder", new Info(BreakInfo::shovel(0.5))));
		self::register("wool", new Wool(self::newBID(), "Wool", new Info(new class(0.8, ToolType::SHEARS) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				$time = parent::getBreakTime($item);
				if($item->getBlockToolType() === ToolType::SHEARS){
					$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
				}

				return $time;
			}
		})));

		//TODO: in the future these won't all have the same hardness; they only do now because of the old metadata crap
		$wallBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("cobblestone_wall", new Wall(self::newBID(), "Cobblestone Wall", $wallBreakInfo));
		self::register("andesite_wall", new Wall(self::newBID(), "Andesite Wall", $wallBreakInfo));
		self::register("brick_wall", new Wall(self::newBID(), "Brick Wall", $wallBreakInfo));
		self::register("diorite_wall", new Wall(self::newBID(), "Diorite Wall", $wallBreakInfo));
		self::register("end_stone_brick_wall", new Wall(self::newBID(), "End Stone Brick Wall", $wallBreakInfo));
		self::register("granite_wall", new Wall(self::newBID(), "Granite Wall", $wallBreakInfo));
		self::register("mossy_stone_brick_wall", new Wall(self::newBID(), "Mossy Stone Brick Wall", $wallBreakInfo));
		self::register("mossy_cobblestone_wall", new Wall(self::newBID(), "Mossy Cobblestone Wall", $wallBreakInfo));
		self::register("nether_brick_wall", new Wall(self::newBID(), "Nether Brick Wall", $wallBreakInfo));
		self::register("prismarine_wall", new Wall(self::newBID(), "Prismarine Wall", $wallBreakInfo));
		self::register("red_nether_brick_wall", new Wall(self::newBID(), "Red Nether Brick Wall", $wallBreakInfo));
		self::register("red_sandstone_wall", new Wall(self::newBID(), "Red Sandstone Wall", $wallBreakInfo));
		self::register("sandstone_wall", new Wall(self::newBID(), "Sandstone Wall", $wallBreakInfo));
		self::register("stone_brick_wall", new Wall(self::newBID(), "Stone Brick Wall", $wallBreakInfo));

		self::registerElements();

		$chemistryTableBreakInfo = new Info(BreakInfo::pickaxe(2.5, ToolTier::WOOD));
		self::register("compound_creator", new ChemistryTable(self::newBID(), "Compound Creator", $chemistryTableBreakInfo));
		self::register("element_constructor", new ChemistryTable(self::newBID(), "Element Constructor", $chemistryTableBreakInfo));
		self::register("lab_table", new ChemistryTable(self::newBID(), "Lab Table", $chemistryTableBreakInfo));
		self::register("material_reducer", new ChemistryTable(self::newBID(), "Material Reducer", $chemistryTableBreakInfo));

		self::register("chemical_heat", new ChemicalHeat(self::newBID(), "Heat Block", $chemistryTableBreakInfo));

		self::registerMushroomBlocks();

		self::register("coral", new Coral(
			self::newBID(),
			"Coral",
			new Info(BreakInfo::instant()),
		));
		self::register("coral_fan", new FloorCoralFan(
			self::newBID(),
			"Coral Fan",
			new Info(BreakInfo::instant()),
		));
		self::register("wall_coral_fan", new WallCoralFan(
			self::newBID(),
			"Wall Coral Fan",
			new Info(BreakInfo::instant()),
		));

		self::register("mangrove_roots", new MangroveRoots(self::newBID(), "Mangrove Roots", new Info(BreakInfo::axe(0.7))));
		self::register("muddy_mangrove_roots", new SimplePillar(self::newBID(), "Muddy Mangrove Roots", new Info(BreakInfo::shovel(0.7), [Tags::MUD])));
		self::register("froglight", new Froglight(self::newBID(), "Froglight", new Info(new BreakInfo(0.3))));
		self::register("sculk", new Sculk(self::newBID(), "Sculk", new Info(new BreakInfo(0.6, ToolType::HOE))));
		self::register("reinforced_deepslate", new class(self::newBID(), "Reinforced Deepslate", new Info(new BreakInfo(55.0, ToolType::NONE, 0, 3600.0))) extends Opaque{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [];
			}
		});

		self::registerBlocksR13();
		self::registerBlocksR14();
		self::registerBlocksR16();
		self::registerBlocksR17();
		self::registerBlocksR18();
		self::registerMudBlocks();

		self::registerCraftingTables();
		self::registerChorusBlocks();
		self::registerOres();
		self::registerWoodenBlocks();
		self::registerCauldronBlocks();
	}

	private static function registerWoodenBlocks() : void{
		$planksBreakInfo = new Info(BreakInfo::axe(2.0, null, 15.0));
		$signBreakInfo = new Info(BreakInfo::axe(1.0));
		$logBreakInfo = new Info(BreakInfo::axe(2.0));
		$woodenDoorBreakInfo = new Info(BreakInfo::axe(3.0, null, 15.0));
		$woodenButtonBreakInfo = new Info(BreakInfo::axe(0.5));
		$woodenPressurePlateBreakInfo = new Info(BreakInfo::axe(0.5));

		foreach(WoodType::cases() as $woodType){
			$name = $woodType->getDisplayName();
			$idName = fn(string $suffix) => strtolower($woodType->name) . "_" . $suffix;

			self::register($idName(mb_strtolower($woodType->getStandardLogSuffix() ?? "log", 'US-ASCII')), new Wood(self::newBID(), $name . " " . ($woodType->getStandardLogSuffix() ?? "Log"), $logBreakInfo, $woodType));
			self::register($idName(mb_strtolower($woodType->getAllSidedLogSuffix() ?? "wood", 'US-ASCII')), new Wood(self::newBID(), $name . " " . ($woodType->getAllSidedLogSuffix() ?? "Wood"), $logBreakInfo, $woodType));

			self::register($idName("planks"), new Planks(self::newBID(), $name . " Planks", $planksBreakInfo, $woodType));
			self::register($idName("fence"), new WoodenFence(self::newBID(), $name . " Fence", $planksBreakInfo, $woodType));
			self::register($idName("slab"), new WoodenSlab(self::newBID(), $name, $planksBreakInfo, $woodType));

			self::register($idName("fence_gate"), new FenceGate(self::newBID(), $name . " Fence Gate", $planksBreakInfo, $woodType));
			self::register($idName("stairs"), new WoodenStairs(self::newBID(), $name . " Stairs", $planksBreakInfo, $woodType));
			self::register($idName("door"), new WoodenDoor(self::newBID(), $name . " Door", $woodenDoorBreakInfo, $woodType));

			self::register($idName("button"), new WoodenButton(self::newBID(), $name . " Button", $woodenButtonBreakInfo, $woodType));
			self::register($idName("pressure_plate"), new WoodenPressurePlate(self::newBID(), $name . " Pressure Plate", $woodenPressurePlateBreakInfo, $woodType, 20));
			self::register($idName("trapdoor"), new WoodenTrapdoor(self::newBID(), $name . " Trapdoor", $woodenDoorBreakInfo, $woodType));

			$signAsItem = match($woodType){
				WoodType::OAK => VanillaItems::OAK_SIGN(...),
				WoodType::SPRUCE => VanillaItems::SPRUCE_SIGN(...),
				WoodType::BIRCH => VanillaItems::BIRCH_SIGN(...),
				WoodType::JUNGLE => VanillaItems::JUNGLE_SIGN(...),
				WoodType::ACACIA => VanillaItems::ACACIA_SIGN(...),
				WoodType::DARK_OAK => VanillaItems::DARK_OAK_SIGN(...),
				WoodType::MANGROVE => VanillaItems::MANGROVE_SIGN(...),
				WoodType::CRIMSON => VanillaItems::CRIMSON_SIGN(...),
				WoodType::WARPED => VanillaItems::WARPED_SIGN(...),
				WoodType::CHERRY => VanillaItems::CHERRY_SIGN(...),
			};
			self::register($idName("sign"), new FloorSign(self::newBID(), $name . " Sign", $signBreakInfo, $woodType, $signAsItem));
			self::register($idName("wall_sign"), new WallSign(self::newBID(), $name . " Wall Sign", $signBreakInfo, $woodType, $signAsItem));
		}
	}

	private static function registerMushroomBlocks() : void{
		$mushroomBlockBreakInfo = new Info(BreakInfo::axe(0.2));

		self::register("brown_mushroom_block", new BrownMushroomBlock(self::newBID(), "Brown Mushroom Block", $mushroomBlockBreakInfo));
		self::register("red_mushroom_block", new RedMushroomBlock(self::newBID(), "Red Mushroom Block", $mushroomBlockBreakInfo));

		//finally, the stems
		self::register("mushroom_stem", new MushroomStem(self::newBID(), "Mushroom Stem", $mushroomBlockBreakInfo));
		self::register("all_sided_mushroom_stem", new MushroomStem(self::newBID(), "All Sided Mushroom Stem", $mushroomBlockBreakInfo));
	}

	private static function registerElements() : void{
		$instaBreak = new Info(BreakInfo::instant());
		self::register("element_zero", new Opaque(self::newBID(), "???", $instaBreak));

		$register = fn(string $name, string $displayName, string $symbol, int $atomicWeight, int $group) =>
			self::register("element_$name", new Element(self::newBID(), $displayName, $instaBreak, $symbol, $atomicWeight, $group));

		$register("hydrogen", "Hydrogen", "h", 1, 5);
		$register("helium", "Helium", "he", 2, 7);
		$register("lithium", "Lithium", "li", 3, 0);
		$register("beryllium", "Beryllium", "be", 4, 1);
		$register("boron", "Boron", "b", 5, 4);
		$register("carbon", "Carbon", "c", 6, 5);
		$register("nitrogen", "Nitrogen", "n", 7, 5);
		$register("oxygen", "Oxygen", "o", 8, 5);
		$register("fluorine", "Fluorine", "f", 9, 6);
		$register("neon", "Neon", "ne", 10, 7);
		$register("sodium", "Sodium", "na", 11, 0);
		$register("magnesium", "Magnesium", "mg", 12, 1);
		$register("aluminum", "Aluminum", "al", 13, 3);
		$register("silicon", "Silicon", "si", 14, 4);
		$register("phosphorus", "Phosphorus", "p", 15, 5);
		$register("sulfur", "Sulfur", "s", 16, 5);
		$register("chlorine", "Chlorine", "cl", 17, 6);
		$register("argon", "Argon", "ar", 18, 7);
		$register("potassium", "Potassium", "k", 19, 0);
		$register("calcium", "Calcium", "ca", 20, 1);
		$register("scandium", "Scandium", "sc", 21, 2);
		$register("titanium", "Titanium", "ti", 22, 2);
		$register("vanadium", "Vanadium", "v", 23, 2);
		$register("chromium", "Chromium", "cr", 24, 2);
		$register("manganese", "Manganese", "mn", 25, 2);
		$register("iron", "Iron", "fe", 26, 2);
		$register("cobalt", "Cobalt", "co", 27, 2);
		$register("nickel", "Nickel", "ni", 28, 2);
		$register("copper", "Copper", "cu", 29, 2);
		$register("zinc", "Zinc", "zn", 30, 2);
		$register("gallium", "Gallium", "ga", 31, 3);
		$register("germanium", "Germanium", "ge", 32, 4);
		$register("arsenic", "Arsenic", "as", 33, 4);
		$register("selenium", "Selenium", "se", 34, 5);
		$register("bromine", "Bromine", "br", 35, 6);
		$register("krypton", "Krypton", "kr", 36, 7);
		$register("rubidium", "Rubidium", "rb", 37, 0);
		$register("strontium", "Strontium", "sr", 38, 1);
		$register("yttrium", "Yttrium", "y", 39, 2);
		$register("zirconium", "Zirconium", "zr", 40, 2);
		$register("niobium", "Niobium", "nb", 41, 2);
		$register("molybdenum", "Molybdenum", "mo", 42, 2);
		$register("technetium", "Technetium", "tc", 43, 2);
		$register("ruthenium", "Ruthenium", "ru", 44, 2);
		$register("rhodium", "Rhodium", "rh", 45, 2);
		$register("palladium", "Palladium", "pd", 46, 2);
		$register("silver", "Silver", "ag", 47, 2);
		$register("cadmium", "Cadmium", "cd", 48, 2);
		$register("indium", "Indium", "in", 49, 3);
		$register("tin", "Tin", "sn", 50, 3);
		$register("antimony", "Antimony", "sb", 51, 4);
		$register("tellurium", "Tellurium", "te", 52, 4);
		$register("iodine", "Iodine", "i", 53, 6);
		$register("xenon", "Xenon", "xe", 54, 7);
		$register("cesium", "Cesium", "cs", 55, 0);
		$register("barium", "Barium", "ba", 56, 1);
		$register("lanthanum", "Lanthanum", "la", 57, 8);
		$register("cerium", "Cerium", "ce", 58, 8);
		$register("praseodymium", "Praseodymium", "pr", 59, 8);
		$register("neodymium", "Neodymium", "nd", 60, 8);
		$register("promethium", "Promethium", "pm", 61, 8);
		$register("samarium", "Samarium", "sm", 62, 8);
		$register("europium", "Europium", "eu", 63, 8);
		$register("gadolinium", "Gadolinium", "gd", 64, 8);
		$register("terbium", "Terbium", "tb", 65, 8);
		$register("dysprosium", "Dysprosium", "dy", 66, 8);
		$register("holmium", "Holmium", "ho", 67, 8);
		$register("erbium", "Erbium", "er", 68, 8);
		$register("thulium", "Thulium", "tm", 69, 8);
		$register("ytterbium", "Ytterbium", "yb", 70, 8);
		$register("lutetium", "Lutetium", "lu", 71, 8);
		$register("hafnium", "Hafnium", "hf", 72, 2);
		$register("tantalum", "Tantalum", "ta", 73, 2);
		$register("tungsten", "Tungsten", "w", 74, 2);
		$register("rhenium", "Rhenium", "re", 75, 2);
		$register("osmium", "Osmium", "os", 76, 2);
		$register("iridium", "Iridium", "ir", 77, 2);
		$register("platinum", "Platinum", "pt", 78, 2);
		$register("gold", "Gold", "au", 79, 2);
		$register("mercury", "Mercury", "hg", 80, 2);
		$register("thallium", "Thallium", "tl", 81, 3);
		$register("lead", "Lead", "pb", 82, 3);
		$register("bismuth", "Bismuth", "bi", 83, 3);
		$register("polonium", "Polonium", "po", 84, 4);
		$register("astatine", "Astatine", "at", 85, 6);
		$register("radon", "Radon", "rn", 86, 7);
		$register("francium", "Francium", "fr", 87, 0);
		$register("radium", "Radium", "ra", 88, 1);
		$register("actinium", "Actinium", "ac", 89, 9);
		$register("thorium", "Thorium", "th", 90, 9);
		$register("protactinium", "Protactinium", "pa", 91, 9);
		$register("uranium", "Uranium", "u", 92, 9);
		$register("neptunium", "Neptunium", "np", 93, 9);
		$register("plutonium", "Plutonium", "pu", 94, 9);
		$register("americium", "Americium", "am", 95, 9);
		$register("curium", "Curium", "cm", 96, 9);
		$register("berkelium", "Berkelium", "bk", 97, 9);
		$register("californium", "Californium", "cf", 98, 9);
		$register("einsteinium", "Einsteinium", "es", 99, 9);
		$register("fermium", "Fermium", "fm", 100, 9);
		$register("mendelevium", "Mendelevium", "md", 101, 9);
		$register("nobelium", "Nobelium", "no", 102, 9);
		$register("lawrencium", "Lawrencium", "lr", 103, 9);
		$register("rutherfordium", "Rutherfordium", "rf", 104, 2);
		$register("dubnium", "Dubnium", "db", 105, 2);
		$register("seaborgium", "Seaborgium", "sg", 106, 2);
		$register("bohrium", "Bohrium", "bh", 107, 2);
		$register("hassium", "Hassium", "hs", 108, 2);
		$register("meitnerium", "Meitnerium", "mt", 109, 2);
		$register("darmstadtium", "Darmstadtium", "ds", 110, 2);
		$register("roentgenium", "Roentgenium", "rg", 111, 2);
		$register("copernicium", "Copernicium", "cn", 112, 2);
		$register("nihonium", "Nihonium", "nh", 113, 3);
		$register("flerovium", "Flerovium", "fl", 114, 3);
		$register("moscovium", "Moscovium", "mc", 115, 3);
		$register("livermorium", "Livermorium", "lv", 116, 3);
		$register("tennessine", "Tennessine", "ts", 117, 6);
		$register("oganesson", "Oganesson", "og", 118, 7);
	}

	private static function registerOres() : void{
		$stoneOreBreakInfo = fn(ToolTier $toolTier) => new Info(BreakInfo::pickaxe(3.0, $toolTier));
		self::register("coal_ore", new CoalOre(self::newBID(), "Coal Ore", $stoneOreBreakInfo(ToolTier::WOOD)));
		self::register("copper_ore", new CopperOre(self::newBID(), "Copper Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("diamond_ore", new DiamondOre(self::newBID(), "Diamond Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("emerald_ore", new EmeraldOre(self::newBID(), "Emerald Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("gold_ore", new GoldOre(self::newBID(), "Gold Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("iron_ore", new IronOre(self::newBID(), "Iron Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("lapis_lazuli_ore", new LapisOre(self::newBID(), "Lapis Lazuli Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("redstone_ore", new RedstoneOre(self::newBID(), "Redstone Ore", $stoneOreBreakInfo(ToolTier::IRON)));

		$deepslateOreBreakInfo = fn(ToolTier $toolTier) => new Info(BreakInfo::pickaxe(4.5, $toolTier));
		self::register("deepslate_coal_ore", new CoalOre(self::newBID(), "Deepslate Coal Ore", $deepslateOreBreakInfo(ToolTier::WOOD)));
		self::register("deepslate_copper_ore", new CopperOre(self::newBID(), "Deepslate Copper Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_diamond_ore", new DiamondOre(self::newBID(), "Deepslate Diamond Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_emerald_ore", new EmeraldOre(self::newBID(), "Deepslate Emerald Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_gold_ore", new GoldOre(self::newBID(), "Deepslate Gold Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_iron_ore", new IronOre(self::newBID(), "Deepslate Iron Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_lapis_lazuli_ore", new LapisOre(self::newBID(), "Deepslate Lapis Lazuli Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_redstone_ore", new RedstoneOre(self::newBID(), "Deepslate Redstone Ore", $deepslateOreBreakInfo(ToolTier::IRON)));

		$netherrackOreBreakInfo = new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD));
		self::register("nether_quartz_ore", new NetherQuartzOre(self::newBID(), "Nether Quartz Ore", $netherrackOreBreakInfo));
		self::register("nether_gold_ore", new NetherGoldOre(self::newBID(), "Nether Gold Ore", $netherrackOreBreakInfo));
	}

	private static function registerCraftingTables() : void{
		//TODO: this is the same for all wooden crafting blocks
		$craftingBlockBreakInfo = new Info(BreakInfo::axe(2.5));
		self::register("cartography_table", new CartographyTable(self::newBID(), "Cartography Table", $craftingBlockBreakInfo));
		self::register("crafting_table", new CraftingTable(self::newBID(), "Crafting Table", $craftingBlockBreakInfo));
		self::register("fletching_table", new FletchingTable(self::newBID(), "Fletching Table", $craftingBlockBreakInfo));
		self::register("loom", new Loom(self::newBID(), "Loom", $craftingBlockBreakInfo));
		self::register("smithing_table", new SmithingTable(self::newBID(), "Smithing Table", $craftingBlockBreakInfo));
	}

	private static function registerChorusBlocks() : void{
		$chorusBlockBreakInfo = new Info(BreakInfo::axe(0.4));
		self::register("chorus_plant", new ChorusPlant(self::newBID(), "Chorus Plant", $chorusBlockBreakInfo));
		self::register("chorus_flower", new ChorusFlower(self::newBID(), "Chorus Flower", $chorusBlockBreakInfo));
	}

	private static function registerBlocksR13() : void{
		self::register("light", new Light(self::newBID(), "Light Block", new Info(BreakInfo::indestructible())));
		self::register("wither_rose", new WitherRose(self::newBID(), "Wither Rose", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
	}

	private static function registerBlocksR14() : void{
		self::register("honeycomb", new Opaque(self::newBID(), "Honeycomb Block", new Info(new BreakInfo(0.6))));
	}

	private static function registerBlocksR16() : void{
		//for some reason, slabs have weird hardness like the legacy ones
		$slabBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));

		self::register("ancient_debris", new class(self::newBID(), "Ancient Debris", new Info(BreakInfo::pickaxe(30, ToolTier::DIAMOND, 3600.0))) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});
		$netheriteBreakInfo = new Info(BreakInfo::pickaxe(50, ToolTier::DIAMOND, 3600.0));
		self::register("netherite", new class(self::newBID(), "Netherite Block", $netheriteBreakInfo) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});

		$basaltBreakInfo = new Info(BreakInfo::pickaxe(1.25, ToolTier::WOOD, 21.0));
		self::register("basalt", new SimplePillar(self::newBID(), "Basalt", $basaltBreakInfo));
		self::register("polished_basalt", new SimplePillar(self::newBID(), "Polished Basalt", $basaltBreakInfo));
		self::register("smooth_basalt", new Opaque(self::newBID(), "Smooth Basalt", $basaltBreakInfo));

		$blackstoneBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("blackstone", new Opaque(self::newBID(), "Blackstone", $blackstoneBreakInfo));
		self::register("blackstone_slab", new Slab(self::newBID(), "Blackstone", $slabBreakInfo));
		self::register("blackstone_stairs", new Stair(self::newBID(), "Blackstone Stairs", $blackstoneBreakInfo));
		self::register("blackstone_wall", new Wall(self::newBID(), "Blackstone Wall", $blackstoneBreakInfo));

		self::register("gilded_blackstone", new GildedBlackstone(self::newBID(), "Gilded Blackstone", $blackstoneBreakInfo));

		//TODO: polished blackstone ought to have 2.0 hardness (as per java) but it's 1.5 in Bedrock (probably parity bug)
		$prefix = fn(string $thing) => "Polished Blackstone" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone", new Opaque(self::newBID(), $prefix(""), $blackstoneBreakInfo));
		self::register("polished_blackstone_button", new StoneButton(self::newBID(), $prefix("Button"), new Info(BreakInfo::pickaxe(0.5))));
		self::register("polished_blackstone_pressure_plate", new StonePressurePlate(self::newBID(), $prefix("Pressure Plate"), new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD)), 20));
		self::register("polished_blackstone_slab", new Slab(self::newBID(), $prefix(""), $slabBreakInfo));
		self::register("polished_blackstone_stairs", new Stair(self::newBID(), $prefix("Stairs"), $blackstoneBreakInfo));
		self::register("polished_blackstone_wall", new Wall(self::newBID(), $prefix("Wall"), $blackstoneBreakInfo));
		self::register("chiseled_polished_blackstone", new Opaque(self::newBID(), "Chiseled Polished Blackstone", $blackstoneBreakInfo));

		$prefix = fn(string $thing) => "Polished Blackstone Brick" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone_bricks", new Opaque(self::newBID(), "Polished Blackstone Bricks", $blackstoneBreakInfo));
		self::register("polished_blackstone_brick_slab", new Slab(self::newBID(), "Polished Blackstone Brick", $slabBreakInfo));
		self::register("polished_blackstone_brick_stairs", new Stair(self::newBID(), $prefix("Stairs"), $blackstoneBreakInfo));
		self::register("polished_blackstone_brick_wall", new Wall(self::newBID(), $prefix("Wall"), $blackstoneBreakInfo));
		self::register("cracked_polished_blackstone_bricks", new Opaque(self::newBID(), "Cracked Polished Blackstone Bricks", $blackstoneBreakInfo));

		self::register("soul_torch", new Torch(self::newBID(), "Soul Torch", new Info(BreakInfo::instant())));
		self::register("soul_fire", new SoulFire(self::newBID(), "Soul Fire", new Info(BreakInfo::instant(), [Tags::FIRE])));

		//TODO: soul soul ought to have 0.5 hardness (as per java) but it's 1.0 in Bedrock (probably parity bug)
		self::register("soul_soil", new Opaque(self::newBID(), "Soul Soil", new Info(BreakInfo::shovel(1.0))));

		self::register("shroomlight", new class(self::newBID(), "Shroomlight", new Info(new BreakInfo(1.0, ToolType::HOE))) extends Opaque{
			public function getLightLevel() : int{ return 15; }
		});

		self::register("warped_wart_block", new Opaque(self::newBID(), "Warped Wart Block", new Info(new BreakInfo(1.0, ToolType::HOE))));
		self::register("crying_obsidian", new class(self::newBID(), "Crying Obsidian", new Info(BreakInfo::pickaxe(35.0 /* 50 in Java */, ToolTier::DIAMOND, 6000.0))) extends Opaque{
			public function getLightLevel() : int{ return 10;}
		});

		self::register("twisting_vines", new NetherVines(self::newBID(), "Twisting Vines", new Info(BreakInfo::instant()), Facing::UP));
		self::register("weeping_vines", new NetherVines(self::newBID(), "Weeping Vines", new Info(BreakInfo::instant()), Facing::DOWN));

		$netherRootsInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);
		self::register("crimson_roots", new NetherRoots(self::newBID(), "Crimson Roots", $netherRootsInfo));
		self::register("warped_roots", new NetherRoots(self::newBID(), "Warped Roots", $netherRootsInfo));

		self::register("chain", new Chain(self::newBID(), "Chain", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD))));
	}

	private static function registerBlocksR17() : void{
		//in java this can be acquired using any tool - seems to be a parity issue in bedrock
		$amethystInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD));
		self::register("amethyst", new class(self::newBID(), "Amethyst", $amethystInfo) extends Opaque{
			use AmethystTrait;
		});
		self::register("budding_amethyst", new BuddingAmethyst(self::newBID(), "Budding Amethyst", $amethystInfo));
		self::register("amethyst_cluster", new AmethystCluster(self::newBID(), "Amethyst Cluster", $amethystInfo));

		self::register("calcite", new Opaque(self::newBID(), "Calcite", new Info(BreakInfo::pickaxe(0.75, ToolTier::WOOD))));
		self::register("tuff", new Opaque(self::newBID(), "Tuff", new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0))));

		self::register("raw_copper", new Opaque(self::newBID(), "Raw Copper Block", new Info(BreakInfo::pickaxe(5, ToolTier::STONE, 30.0))));
		self::register("raw_gold", new Opaque(self::newBID(), "Raw Gold Block", new Info(BreakInfo::pickaxe(5, ToolTier::IRON, 30.0))));
		self::register("raw_iron", new Opaque(self::newBID(), "Raw Iron Block", new Info(BreakInfo::pickaxe(5, ToolTier::STONE, 30.0))));

		$deepslateBreakInfo = new Info(BreakInfo::pickaxe(3, ToolTier::WOOD, 18.0));
		self::register("deepslate", new class(self::newBID(), "Deepslate", $deepslateBreakInfo) extends SimplePillar{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [VanillaBlocks::COBBLED_DEEPSLATE()->asItem()];
			}

			public function isAffectedBySilkTouch() : bool{
				return true;
			}
		});

		//TODO: parity issue here - in Java this has a hardness of 3.0, but in bedrock it's 3.5
		self::register("chiseled_deepslate", new Opaque(self::newBID(), "Chiseled Deepslate", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 18.0))));

		$deepslateBrickBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 18.0));
		self::register("deepslate_bricks", new Opaque(self::newBID(), "Deepslate Bricks", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_slab", new Slab(self::newBID(), "Deepslate Brick", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_stairs", new Stair(self::newBID(), "Deepslate Brick Stairs", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_wall", new Wall(self::newBID(), "Deepslate Brick Wall", $deepslateBrickBreakInfo));
		self::register("cracked_deepslate_bricks", new Opaque(self::newBID(), "Cracked Deepslate Bricks", $deepslateBrickBreakInfo));

		$deepslateTilesBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 18.0));
		self::register("deepslate_tiles", new Opaque(self::newBID(), "Deepslate Tiles", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_slab", new Slab(self::newBID(), "Deepslate Tile", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_stairs", new Stair(self::newBID(), "Deepslate Tile Stairs", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_wall", new Wall(self::newBID(), "Deepslate Tile Wall", $deepslateTilesBreakInfo));
		self::register("cracked_deepslate_tiles", new Opaque(self::newBID(), "Cracked Deepslate Tiles", $deepslateTilesBreakInfo));

		$cobbledDeepslateBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 18.0));
		self::register("cobbled_deepslate", new Opaque(self::newBID(), "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_slab", new Slab(self::newBID(), "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_stairs", new Stair(self::newBID(), "Cobbled Deepslate Stairs", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_wall", new Wall(self::newBID(), "Cobbled Deepslate Wall", $cobbledDeepslateBreakInfo));

		$polishedDeepslateBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 18.0));
		self::register("polished_deepslate", new Opaque(self::newBID(), "Polished Deepslate", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_slab", new Slab(self::newBID(), "Polished Deepslate", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_stairs", new Stair(self::newBID(), "Polished Deepslate Stairs", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_wall", new Wall(self::newBID(), "Polished Deepslate Wall", $polishedDeepslateBreakInfo));

		self::register("tinted_glass", new TintedGlass(self::newBID(), "Tinted Glass", new Info(new BreakInfo(0.3))));

		//blast resistance should be 30 if we were matched with java :(
		$copperBreakInfo = new Info(BreakInfo::pickaxe(3.0, ToolTier::STONE, 18.0));
		self::register("lightning_rod", new LightningRod(self::newBID(), "Lightning Rod", $copperBreakInfo));

		self::register("copper", new Copper(self::newBID(), "Copper Block", $copperBreakInfo));
		self::register("cut_copper", new Copper(self::newBID(), "Cut Copper Block", $copperBreakInfo));
		self::register("cut_copper_slab", new CopperSlab(self::newBID(), "Cut Copper Slab", $copperBreakInfo));
		self::register("cut_copper_stairs", new CopperStairs(self::newBID(), "Cut Copper Stairs", $copperBreakInfo));

		$candleBreakInfo = new Info(new BreakInfo(0.1));
		self::register("candle", new Candle(self::newBID(), "Candle", $candleBreakInfo));
		self::register("dyed_candle", new DyedCandle(self::newBID(), "Dyed Candle", $candleBreakInfo));

		//TODO: duplicated break info :(
		$cakeBreakInfo = new Info(new BreakInfo(0.5));
		self::register("cake_with_candle", new CakeWithCandle(self::newBID(), "Cake With Candle", $cakeBreakInfo));
		self::register("cake_with_dyed_candle", new CakeWithDyedCandle(self::newBID(), "Cake With Dyed Candle", $cakeBreakInfo));

		self::register("hanging_roots", new HangingRoots(self::newBID(), "Hanging Roots", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));

		self::register("cave_vines", new CaveVines(self::newBID(), "Cave Vines", new Info(BreakInfo::instant())));

		self::register("small_dripleaf", new SmallDripleaf(self::newBID(), "Small Dripleaf", new Info(BreakInfo::instant(ToolType::SHEARS, toolHarvestLevel: 1))));
		self::register("big_dripleaf_head", new BigDripleafHead(self::newBID(), "Big Dripleaf", new Info(BreakInfo::instant())));
		self::register("big_dripleaf_stem", new BigDripleafStem(self::newBID(), "Big Dripleaf Stem", new Info(BreakInfo::instant())));
	}

	private static function registerBlocksR18() : void{
		self::register("spore_blossom", new SporeBlossom(self::newBID(), "Spore Blossom", new Info(BreakInfo::instant())));
	}

	private static function registerMudBlocks() : void{
		self::register("mud", new Opaque(self::newBID(), "Mud", new Info(BreakInfo::shovel(0.5), [Tags::MUD])));
		self::register("packed_mud", new Opaque(self::newBID(), "Packed Mud", new Info(BreakInfo::pickaxe(1.0, null, 15.0))));

		$mudBricksBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));

		self::register("mud_bricks", new Opaque(self::newBID(), "Mud Bricks", $mudBricksBreakInfo));
		self::register("mud_brick_slab", new Slab(self::newBID(), "Mud Brick", $mudBricksBreakInfo));
		self::register("mud_brick_stairs", new Stair(self::newBID(), "Mud Brick Stairs", $mudBricksBreakInfo));
		self::register("mud_brick_wall", new Wall(self::newBID(), "Mud Brick Wall", $mudBricksBreakInfo));
	}

	private static function registerCauldronBlocks() : void{
		$cauldronBreakInfo = new Info(BreakInfo::pickaxe(2, ToolTier::WOOD));

		self::register("cauldron", new Cauldron(self::newBID(TileCauldron::class), "Cauldron", $cauldronBreakInfo));
		self::register("water_cauldron", new WaterCauldron(self::newBID(TileCauldron::class), "Water Cauldron", $cauldronBreakInfo));
		self::register("lava_cauldron", new LavaCauldron(self::newBID(TileCauldron::class), "Lava Cauldron", $cauldronBreakInfo));
		self::register("potion_cauldron", new PotionCauldron(self::newBID(TileCauldron::class), "Potion Cauldron", $cauldronBreakInfo));
	}
}
