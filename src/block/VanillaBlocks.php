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
use pocketmine\block\tile\Campfire as TileCampfire;
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
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\tile\Smoker as TileSmoker;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\AmethystTrait;
use pocketmine\block\utils\LeavesType;
use pocketmine\block\utils\SaplingType;
use pocketmine\block\utils\WoodType;
use pocketmine\crafting\FurnaceType;
use pocketmine\data\bedrock\block\BlockBlastResistanceValues as BlastResistance;
use pocketmine\data\bedrock\block\BlockHardnessValues as Hardness;
use pocketmine\item\enchantment\ItemEnchantmentTags as EnchantmentTags;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\utils\CloningRegistryTrait;
use function is_int;
use function mb_strtolower;
use function mb_strtoupper;
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
 * @method static Campfire CAMPFIRE()
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
 * @method static Copper CHISELED_COPPER()
 * @method static Opaque CHISELED_DEEPSLATE()
 * @method static Opaque CHISELED_NETHER_BRICKS()
 * @method static Opaque CHISELED_POLISHED_BLACKSTONE()
 * @method static SimplePillar CHISELED_QUARTZ()
 * @method static Opaque CHISELED_RED_SANDSTONE()
 * @method static Opaque CHISELED_RESIN_BRICKS()
 * @method static Opaque CHISELED_SANDSTONE()
 * @method static Opaque CHISELED_STONE_BRICKS()
 * @method static Opaque CHISELED_TUFF()
 * @method static Opaque CHISELED_TUFF_BRICKS()
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
 * @method static CopperBulb COPPER_BULB()
 * @method static CopperDoor COPPER_DOOR()
 * @method static CopperGrate COPPER_GRATE()
 * @method static CopperOre COPPER_ORE()
 * @method static CopperTrapdoor COPPER_TRAPDOOR()
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
 * @method static WoodenButton PALE_OAK_BUTTON()
 * @method static WoodenDoor PALE_OAK_DOOR()
 * @method static WoodenFence PALE_OAK_FENCE()
 * @method static FenceGate PALE_OAK_FENCE_GATE()
 * @method static Leaves PALE_OAK_LEAVES()
 * @method static Wood PALE_OAK_LOG()
 * @method static Planks PALE_OAK_PLANKS()
 * @method static WoodenPressurePlate PALE_OAK_PRESSURE_PLATE()
 * @method static FloorSign PALE_OAK_SIGN()
 * @method static WoodenSlab PALE_OAK_SLAB()
 * @method static WoodenStairs PALE_OAK_STAIRS()
 * @method static WoodenTrapdoor PALE_OAK_TRAPDOOR()
 * @method static WallSign PALE_OAK_WALL_SIGN()
 * @method static Wood PALE_OAK_WOOD()
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
 * @method static Opaque POLISHED_TUFF()
 * @method static Slab POLISHED_TUFF_SLAB()
 * @method static Stair POLISHED_TUFF_STAIRS()
 * @method static Wall POLISHED_TUFF_WALL()
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
 * @method static Opaque RESIN()
 * @method static Opaque RESIN_BRICKS()
 * @method static Slab RESIN_BRICK_SLAB()
 * @method static Stair RESIN_BRICK_STAIRS()
 * @method static Wall RESIN_BRICK_WALL()
 * @method static ResinClump RESIN_CLUMP()
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
 * @method static SoulCampfire SOUL_CAMPFIRE()
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
 * @method static Opaque TUFF_BRICKS()
 * @method static Slab TUFF_BRICK_SLAB()
 * @method static Stair TUFF_BRICK_STAIRS()
 * @method static Wall TUFF_BRICK_WALL()
 * @method static Slab TUFF_SLAB()
 * @method static Stair TUFF_STAIRS()
 * @method static Wall TUFF_WALL()
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

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param \Closure(BID) : TBlock $createBlock
	 * @phpstan-param class-string<covariant Tile> $tileClass
	 * @phpstan-return TBlock
	 */
	protected static function register(string $name, \Closure $createBlock, ?string $tileClass = null) : Block{
		//this sketchy hack allows us to avoid manually writing the constants inline
		//since type IDs are generated from this class anyway, I'm OK with this hack
		//nonetheless, we should try to get rid of it in a future major version (e.g by using string type IDs)
		$reflect = new \ReflectionClass(BlockTypeIds::class);
		$typeId = $reflect->getConstant(mb_strtoupper($name));
		if(!is_int($typeId)){
			//this allows registering new stuff without adding new type ID constants
			//this reduces the number of mandatory steps to test new features in local development
			\GlobalLogger::get()->error(self::class . ": No constant type ID found for $name, generating a new one");
			$typeId = BlockTypeIds::newId();
		}
		$block = $createBlock(new BID($typeId, $tileClass));
		self::_registryRegister($name, $block);

		return $block;
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
		self::register("air", fn(BID $id) => new Air($id, "Air", new Info(BreakInfo::indestructible(-1.0))));

		self::register("activator_rail", fn(BID $id) => new ActivatorRail($id, "Activator Rail", new Info(new BreakInfo(Hardness::ACTIVATOR_RAIL, blastResistance: BlastResistance::ACTIVATOR_RAIL))));
		self::register("anvil", fn(BID $id) => new Anvil($id, "Anvil", new Info(BreakInfo::pickaxe(Hardness::ANVIL, ToolTier::WOOD, BlastResistance::ANVIL))));
		self::register("bamboo", fn(BID $id) => new Bamboo($id, "Bamboo", new Info(new class(Hardness::BAMBOO, ToolType::AXE, blastResistance: BlastResistance::BAMBOO) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}, [Tags::POTTABLE_PLANTS])));
		self::register("bamboo_sapling", fn(BID $id) => new BambooSapling($id, "Bamboo Sapling", new Info(new BreakInfo(Hardness::BAMBOO_SAPLING, blastResistance: BlastResistance::BAMBOO_SAPLING))));

		self::register("banner", fn(BID $id) => new FloorBanner($id, "Banner", new Info(BreakInfo::axe(Hardness::STANDING_BANNER, blastResistance: BlastResistance::STANDING_BANNER))), TileBanner::class);
		self::register("wall_banner", fn(BID $id) => new WallBanner($id, "Wall Banner", new Info(BreakInfo::axe(Hardness::WALL_BANNER, blastResistance: BlastResistance::WALL_BANNER))), TileBanner::class);
		self::register("barrel", fn(BID $id) => new Barrel($id, "Barrel", new Info(BreakInfo::axe(Hardness::BARREL, blastResistance: BlastResistance::BARREL))), TileBarrel::class);
		self::register("barrier", fn(BID $id) => new Transparent($id, "Barrier", new Info(BreakInfo::indestructible(BlastResistance::BARRIER))));
		self::register("beacon", fn(BID $id) => new Beacon($id, "Beacon", new Info(new BreakInfo(Hardness::BEACON, blastResistance: BlastResistance::BEACON))), TileBeacon::class);
		self::register("bed", fn(BID $id) => new Bed($id, "Bed Block", new Info(new BreakInfo(Hardness::BED, blastResistance: BlastResistance::BED))), TileBed::class);
		self::register("bedrock", fn(BID $id) => new Bedrock($id, "Bedrock", new Info(BreakInfo::indestructible(BlastResistance::BEDROCK))));

		self::register("beetroots", fn(BID $id) => new Beetroot($id, "Beetroot Block", new Info(new BreakInfo(Hardness::BEETROOT, blastResistance: BlastResistance::BEETROOT))));
		self::register("bell", fn(BID $id) => new Bell($id, "Bell", new Info(BreakInfo::pickaxe(Hardness::BELL, blastResistance: BlastResistance::BELL))), TileBell::class);
		self::register("blue_ice", fn(BID $id) => new BlueIce($id, "Blue Ice", new Info(BreakInfo::pickaxe(Hardness::BLUE_ICE, blastResistance: BlastResistance::BLUE_ICE))));
		self::register("bone_block", fn(BID $id) => new BoneBlock($id, "Bone Block", new Info(BreakInfo::pickaxe(Hardness::BONE_BLOCK, ToolTier::WOOD, BlastResistance::BONE_BLOCK))));
		self::register("bookshelf", fn(BID $id) => new Bookshelf($id, "Bookshelf", new Info(BreakInfo::axe(Hardness::BOOKSHELF, blastResistance: BlastResistance::BOOKSHELF))));
		self::register("chiseled_bookshelf", fn(BID $id) => new ChiseledBookshelf($id, "Chiseled Bookshelf", new Info(BreakInfo::axe(Hardness::CHISELED_BOOKSHELF, blastResistance: BlastResistance::CHISELED_BOOKSHELF))), TileChiseledBookshelf::class);
		self::register("brewing_stand", fn(BID $id) => new BrewingStand($id, "Brewing Stand", new Info(BreakInfo::pickaxe(Hardness::BREWING_STAND, blastResistance: BlastResistance::BREWING_STAND))), TileBrewingStand::class);

		self::register("brick_stairs", fn(BID $id) => new Stair($id, "Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::BRICK_STAIRS, ToolTier::WOOD, BlastResistance::BRICK_STAIRS))));
		self::register("bricks", fn(BID $id) => new Opaque($id, "Bricks", new Info(BreakInfo::pickaxe(Hardness::BRICK_BLOCK, ToolTier::WOOD, BlastResistance::BRICK_BLOCK))));

		self::register("brown_mushroom", fn(BID $id) => new BrownMushroom($id, "Brown Mushroom", new Info(new BreakInfo(Hardness::BROWN_MUSHROOM, blastResistance: BlastResistance::BROWN_MUSHROOM), [Tags::POTTABLE_PLANTS])));
		self::register("cactus", fn(BID $id) => new Cactus($id, "Cactus", new Info(new BreakInfo(Hardness::CACTUS, blastResistance: BlastResistance::CACTUS), [Tags::POTTABLE_PLANTS])));
		self::register("cake", fn(BID $id) => new Cake($id, "Cake", new Info(new BreakInfo(Hardness::CAKE, blastResistance: BlastResistance::CAKE))));

		self::register("campfire", fn(BID $id) => new Campfire($id, "Campfire", new Info(BreakInfo::axe(Hardness::CAMPFIRE, blastResistance: BlastResistance::CAMPFIRE))), TileCampfire::class);
		self::register("soul_campfire", fn(BID $id) => new SoulCampfire($id, "Soul Campfire", new Info(BreakInfo::axe(Hardness::SOUL_CAMPFIRE, blastResistance: BlastResistance::SOUL_CAMPFIRE))), TileCampfire::class);

		self::register("carrots", fn(BID $id) => new Carrot($id, "Carrot Block", new Info(new BreakInfo(Hardness::CARROTS, blastResistance: BlastResistance::CARROTS))));

		self::register("chest", fn(BID $id) => new Chest($id, "Chest", new Info(BreakInfo::axe(Hardness::CHEST, blastResistance: BlastResistance::CHEST))), TileChest::class);
		self::register("clay", fn(BID $id) => new Clay($id, "Clay Block", new Info(BreakInfo::shovel(Hardness::CLAY, blastResistance: BlastResistance::CLAY))));
		self::register("coal", fn(BID $id) => new Coal($id, "Coal Block", new Info(BreakInfo::pickaxe(Hardness::COAL_BLOCK, ToolTier::WOOD, BlastResistance::COAL_BLOCK))));

		$cobblestone = self::register("cobblestone", fn(BID $id) => new Opaque($id, "Cobblestone", new Info(BreakInfo::pickaxe(Hardness::COBBLESTONE, ToolTier::WOOD, BlastResistance::COBBLESTONE))));
		self::register("mossy_cobblestone", fn(BID $id) => new Opaque($id, "Mossy Cobblestone", new Info(BreakInfo::pickaxe(Hardness::MOSSY_COBBLESTONE, ToolTier::WOOD, BlastResistance::MOSSY_COBBLESTONE))));
		self::register("cobblestone_stairs", fn(BID $id) => new Stair($id, "Cobblestone Stairs", new Info(BreakInfo::pickaxe(Hardness::STONE_STAIRS, ToolTier::WOOD, BlastResistance::STONE_STAIRS))));
		self::register("mossy_cobblestone_stairs", fn(BID $id) => new Stair($id, "Mossy Cobblestone Stairs", new Info(BreakInfo::pickaxe(Hardness::MOSSY_COBBLESTONE_STAIRS, ToolTier::WOOD, BlastResistance::MOSSY_COBBLESTONE_STAIRS))));

		self::register("cobweb", fn(BID $id) => new Cobweb($id, "Cobweb", new Info(new BreakInfo(Hardness::WEB, ToolType::SWORD | ToolType::SHEARS, 1, BlastResistance::WEB))));
		self::register("cocoa_pod", fn(BID $id) => new CocoaBlock($id, "Cocoa Block", new Info(BreakInfo::axe(Hardness::COCOA, null, BlastResistance::COCOA))));
		self::register("coral_block", fn(BID $id) => new CoralBlock($id, "Coral Block", new Info(BreakInfo::pickaxe(Hardness::TUBE_CORAL_BLOCK, ToolTier::WOOD, BlastResistance::TUBE_CORAL_BLOCK))));
		self::register("daylight_sensor", fn(BID $id) => new DaylightSensor($id, "Daylight Sensor", new Info(BreakInfo::axe(Hardness::DAYLIGHT_DETECTOR, blastResistance: BlastResistance::DAYLIGHT_DETECTOR))), TileDaylightSensor::class);
		self::register("dead_bush", fn(BID $id) => new DeadBush($id, "Dead Bush", new Info(new BreakInfo(Hardness::DEADBUSH, ToolType::SHEARS, 1, BlastResistance::DEADBUSH), [Tags::POTTABLE_PLANTS])));
		self::register("detector_rail", fn(BID $id) => new DetectorRail($id, "Detector Rail", new Info(new BreakInfo(Hardness::DETECTOR_RAIL, blastResistance: BlastResistance::DETECTOR_RAIL))));

		self::register("diamond", fn(BID $id) => new Opaque($id, "Diamond Block", new Info(BreakInfo::pickaxe(Hardness::DIAMOND_BLOCK, ToolTier::IRON, blastResistance: BlastResistance::DIAMOND_BLOCK))));
		self::register("dirt", fn(BID $id) => new Dirt($id, "Dirt", new Info(BreakInfo::shovel(Hardness::DIRT, blastResistance: BlastResistance::DIRT), [Tags::DIRT])));
		self::register("sunflower", fn(BID $id) => new DoublePlant($id, "Sunflower", new Info(new BreakInfo(Hardness::SUNFLOWER, blastResistance: BlastResistance::SUNFLOWER))));
		self::register("lilac", fn(BID $id) => new DoublePlant($id, "Lilac", new Info(new BreakInfo(Hardness::LILAC, blastResistance: BlastResistance::LILAC))));
		self::register("rose_bush", fn(BID $id) => new DoublePlant($id, "Rose Bush", new Info(new BreakInfo(Hardness::ROSE_BUSH, blastResistance: BlastResistance::ROSE_BUSH))));
		self::register("peony", fn(BID $id) => new DoublePlant($id, "Peony", new Info(new BreakInfo(Hardness::PEONY, blastResistance: BlastResistance::PEONY))));
		self::register("pink_petals", fn(BID $id) => new PinkPetals($id, "Pink Petals", new Info(new BreakInfo(Hardness::PINK_PETALS, blastResistance: BlastResistance::PINK_PETALS))));
		self::register("double_tallgrass", fn(BID $id) => new DoubleTallGrass($id, "Double Tallgrass", new Info(new BreakInfo(Hardness::TALL_GRASS, ToolType::SHEARS, 1, BlastResistance::TALL_GRASS))));
		self::register("large_fern", fn(BID $id) => new DoubleTallGrass($id, "Large Fern", new Info(new BreakInfo(Hardness::LARGE_FERN, ToolType::SHEARS, 1, BlastResistance::LARGE_FERN))));
		self::register("pitcher_plant", fn(BID $id) => new DoublePlant($id, "Pitcher Plant", new Info(new BreakInfo(Hardness::PITCHER_PLANT, blastResistance: BlastResistance::PITCHER_PLANT))));
		self::register("pitcher_crop", fn(BID $id) => new PitcherCrop($id, "Pitcher Crop", new Info(new BreakInfo(Hardness::PITCHER_CROP, blastResistance: BlastResistance::PITCHER_CROP))));
		self::register("double_pitcher_crop", fn(BID $id) => new DoublePitcherCrop($id, "Double Pitcher Crop", new Info(new BreakInfo(Hardness::PITCHER_CROP, blastResistance: BlastResistance::PITCHER_CROP))));
		self::register("dragon_egg", fn(BID $id) => new DragonEgg($id, "Dragon Egg", new Info(BreakInfo::pickaxe(Hardness::DRAGON_EGG, ToolTier::WOOD, BlastResistance::DRAGON_EGG))));
		self::register("dried_kelp", fn(BID $id) => new DriedKelp($id, "Dried Kelp Block", new Info(new BreakInfo(Hardness::DRIED_KELP_BLOCK, ToolType::NONE, 0, BlastResistance::DRIED_KELP_BLOCK))));
		self::register("emerald", fn(BID $id) => new Opaque($id, "Emerald Block", new Info(BreakInfo::pickaxe(Hardness::EMERALD_BLOCK, ToolTier::IRON, BlastResistance::EMERALD_BLOCK))));
		self::register("enchanting_table", fn(BID $id) => new EnchantingTable($id, "Enchanting Table", new Info(BreakInfo::pickaxe(Hardness::ENCHANTING_TABLE, ToolTier::WOOD, BlastResistance::ENCHANTING_TABLE))), TileEnchantingTable::class);
		self::register("end_portal_frame", fn(BID $id) => new EndPortalFrame($id, "End Portal Frame", new Info(BreakInfo::indestructible(BlastResistance::END_PORTAL_FRAME))));
		self::register("end_rod", fn(BID $id) => new EndRod($id, "End Rod", new Info(new BreakInfo(Hardness::END_ROD, blastResistance: BlastResistance::END_ROD))));
		self::register("end_stone", fn(BID $id) => new Opaque($id, "End Stone", new Info(BreakInfo::pickaxe(Hardness::END_STONE, ToolTier::WOOD, BlastResistance::END_STONE))));

		self::register("end_stone_bricks", fn(BID $id) => new Opaque($id, "End Stone Bricks", new Info(BreakInfo::pickaxe(Hardness::END_BRICKS, ToolTier::WOOD, Hardness::END_BRICKS))));
		self::register("end_stone_brick_stairs", fn(BID $id) => new Stair($id, "End Stone Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::END_BRICK_STAIRS, ToolTier::WOOD, Hardness::END_BRICK_STAIRS))));

		self::register("ender_chest", fn(BID $id) => new EnderChest($id, "Ender Chest", new Info(BreakInfo::pickaxe(Hardness::ENDER_CHEST, blastResistance: BlastResistance::ENDER_CHEST))), TileEnderChest::class);
		self::register("farmland", fn(BID $id) => new Farmland($id, "Farmland", new Info(BreakInfo::shovel(Hardness::FARMLAND, blastResistance: BlastResistance::FARMLAND), [Tags::DIRT])));
		self::register("fire", fn(BID $id) => new Fire($id, "Fire Block", new Info(new BreakInfo(Hardness::FIRE, blastResistance: BlastResistance::FIRE), [Tags::FIRE])));

		self::register("dandelion", fn(BID $id) => new Flower($id, "Dandelion", new Info(new BreakInfo(Hardness::DANDELION, blastResistance: BlastResistance::DANDELION), [Tags::POTTABLE_PLANTS])));
		self::register("poppy", fn(BID $id) => new Flower($id, "Poppy", new Info(new BreakInfo(Hardness::POPPY, blastResistance: BlastResistance::POPPY), [Tags::POTTABLE_PLANTS])));
		self::register("allium", fn(BID $id) => new Flower($id, "Allium", new Info(new BreakInfo(Hardness::ALLIUM, blastResistance: BlastResistance::ALLIUM), [Tags::POTTABLE_PLANTS])));
		self::register("azure_bluet", fn(BID $id) => new Flower($id, "Azure Bluet", new Info(new BreakInfo(Hardness::AZURE_BLUET, blastResistance: BlastResistance::AZURE_BLUET), [Tags::POTTABLE_PLANTS])));
		self::register("blue_orchid", fn(BID $id) => new Flower($id, "Blue Orchid", new Info(new BreakInfo(Hardness::BLUE_ORCHID, blastResistance: BlastResistance::BLUE_ORCHID), [Tags::POTTABLE_PLANTS])));
		self::register("cornflower", fn(BID $id) => new Flower($id, "Cornflower", new Info(new BreakInfo(Hardness::CORNFLOWER, blastResistance: BlastResistance::CORNFLOWER), [Tags::POTTABLE_PLANTS])));
		self::register("lily_of_the_valley", fn(BID $id) => new Flower($id, "Lily of the Valley", new Info(new BreakInfo(Hardness::LILY_OF_THE_VALLEY, blastResistance: BlastResistance::LILY_OF_THE_VALLEY), [Tags::POTTABLE_PLANTS])));
		self::register("orange_tulip", fn(BID $id) => new Flower($id, "Orange Tulip", new Info(new BreakInfo(Hardness::ORANGE_TULIP, blastResistance: BlastResistance::ORANGE_TULIP), [Tags::POTTABLE_PLANTS])));
		self::register("oxeye_daisy", fn(BID $id) => new Flower($id, "Oxeye Daisy", new Info(new BreakInfo(Hardness::OXEYE_DAISY, blastResistance: BlastResistance::OXEYE_DAISY), [Tags::POTTABLE_PLANTS])));
		self::register("pink_tulip", fn(BID $id) => new Flower($id, "Pink Tulip", new Info(new BreakInfo(Hardness::PINK_TULIP, blastResistance: BlastResistance::PINK_TULIP), [Tags::POTTABLE_PLANTS])));
		self::register("red_tulip", fn(BID $id) => new Flower($id, "Red Tulip", new Info(new BreakInfo(Hardness::RED_TULIP, blastResistance: BlastResistance::RED_TULIP), [Tags::POTTABLE_PLANTS])));
		self::register("white_tulip", fn(BID $id) => new Flower($id, "White Tulip", new Info(new BreakInfo(Hardness::WHITE_TULIP, blastResistance: BlastResistance::WHITE_TULIP), [Tags::POTTABLE_PLANTS])));
		self::register("torchflower", fn(BID $id) => new Flower($id, "Torchflower", new Info(new BreakInfo(Hardness::TORCHFLOWER, blastResistance: BlastResistance::TORCHFLOWER), [Tags::POTTABLE_PLANTS])));
		self::register("torchflower_crop", fn(BID $id) => new TorchflowerCrop($id, "Torchflower Crop", new Info(new BreakInfo(Hardness::TORCHFLOWER_CROP, blastResistance: BlastResistance::TORCHFLOWER_CROP))));
		self::register("flower_pot", fn(BID $id) => new FlowerPot($id, "Flower Pot", new Info(new BreakInfo(Hardness::FLOWER_POT, blastResistance: BlastResistance::FLOWER_POT))), TileFlowerPot::class);
		self::register("frosted_ice", fn(BID $id) => new FrostedIce($id, "Frosted Ice", new Info(BreakInfo::pickaxe(Hardness::FROSTED_ICE, blastResistance: BlastResistance::FROSTED_ICE))));
		self::register("furnace", fn(BID $id) => new Furnace($id, "Furnace", new Info(BreakInfo::pickaxe(Hardness::FURNACE, ToolTier::WOOD, BlastResistance::FURNACE)), FurnaceType::FURNACE), TileNormalFurnace::class);
		self::register("blast_furnace", fn(BID $id) => new Furnace($id, "Blast Furnace", new Info(BreakInfo::pickaxe(Hardness::BLAST_FURNACE, ToolTier::WOOD, BlastResistance::BLAST_FURNACE)), FurnaceType::BLAST_FURNACE), TileBlastFurnace::class);
		self::register("smoker", fn(BID $id) => new Furnace($id, "Smoker", new Info(BreakInfo::pickaxe(Hardness::SMOKER, ToolTier::WOOD, BlastResistance::SMOKER)), FurnaceType::SMOKER), TileSmoker::class);

		self::register("glass", fn(BID $id) => new Glass($id, "Glass", new Info(new BreakInfo(Hardness::GLASS, blastResistance: BlastResistance::GLASS))));
		self::register("glass_pane", fn(BID $id) => new GlassPane($id, "Glass Pane", new Info(new BreakInfo(Hardness::GLASS_PANE, blastResistance: BlastResistance::GLASS_PANE))));
		self::register("glowing_obsidian", fn(BID $id) => new GlowingObsidian($id, "Glowing Obsidian", new Info(BreakInfo::pickaxe(Hardness::GLOWINGOBSIDIAN, ToolTier::DIAMOND, BlastResistance::GLOWINGOBSIDIAN))));
		self::register("glowstone", fn(BID $id) => new Glowstone($id, "Glowstone", new Info(BreakInfo::pickaxe(Hardness::GLOWSTONE, blastResistance: BlastResistance::GLOWSTONE))));
		self::register("glow_lichen", fn(BID $id) => new GlowLichen($id, "Glow Lichen", new Info(BreakInfo::axe(Hardness::GLOW_LICHEN, null, BlastResistance::GLOW_LICHEN))));
		self::register("gold", fn(BID $id) => new Opaque($id, "Gold Block", new Info(BreakInfo::pickaxe(Hardness::GOLD_BLOCK, ToolTier::IRON, BlastResistance::GOLD_BLOCK))));

		self::register("grass", fn(BID $id) => new Grass($id, "Grass", new Info(BreakInfo::shovel(Hardness::GRASS_BLOCK, blastResistance: BlastResistance::GRASS_BLOCK), [Tags::DIRT])));
		self::register("grass_path", fn(BID $id) => new GrassPath($id, "Grass Path", new Info(BreakInfo::shovel(Hardness::GRASS_PATH, blastResistance: BlastResistance::GRASS_PATH))));
		self::register("gravel", fn(BID $id) => new Gravel($id, "Gravel", new Info(BreakInfo::shovel(Hardness::GRAVEL, blastResistance: BlastResistance::GRAVEL))));

		self::register("hardened_clay", fn(BID $id) => new HardenedClay($id, "Hardened Clay", new Info(BreakInfo::pickaxe(Hardness::HARDENED_CLAY, ToolTier::WOOD, BlastResistance::HARDENED_CLAY))));

		self::register("hardened_glass", fn(BID $id) => new HardenedGlass($id, "Hardened Glass", new Info(new BreakInfo(Hardness::HARD_GLASS, blastResistance: BlastResistance::HARD_GLASS))));
		self::register("hardened_glass_pane", fn(BID $id) => new HardenedGlassPane($id, "Hardened Glass Pane", new Info(new BreakInfo(Hardness::HARD_GLASS_PANE, blastResistance: BlastResistance::HARD_GLASS_PANE))));
		self::register("hay_bale", fn(BID $id) => new HayBale($id, "Hay Bale", new Info(new BreakInfo(Hardness::HAY_BLOCK, blastResistance: BlastResistance::HAY_BLOCK))));
		self::register("hopper", fn(BID $id) => new Hopper($id, "Hopper", new Info(BreakInfo::pickaxe(Hardness::HOPPER, ToolTier::WOOD, BlastResistance::HOPPER))), TileHopper::class);
		self::register("ice", fn(BID $id) => new Ice($id, "Ice", new Info(BreakInfo::pickaxe(Hardness::ICE, blastResistance: BlastResistance::ICE))));

		self::register("info_update", fn(BID $id) => new Opaque($id, "update!", new Info(new BreakInfo(Hardness::INFO_UPDATE, blastResistance: BlastResistance::INFO_UPDATE))));
		self::register("info_update2", fn(BID $id) => new Opaque($id, "ate!upd", new Info(new BreakInfo(Hardness::INFO_UPDATE2, blastResistance: BlastResistance::INFO_UPDATE2))));
		self::register("invisible_bedrock", fn(BID $id) => new Transparent($id, "Invisible Bedrock", new Info(BreakInfo::indestructible(BlastResistance::INVISIBLE_BEDROCK))));

		self::register("iron", fn(BID $id) => new Opaque($id, "Iron Block", new Info(BreakInfo::pickaxe(Hardness::IRON_BLOCK, ToolTier::STONE, BlastResistance::IRON_BLOCK))));
		self::register("iron_bars", fn(BID $id) => new Thin($id, "Iron Bars", new Info(BreakInfo::pickaxe(Hardness::IRON_BARS, ToolTier::STONE, BlastResistance::IRON_BARS))));

		self::register("iron_door", fn(BID $id) => new Door($id, "Iron Door", new Info(BreakInfo::pickaxe(Hardness::IRON_DOOR, blastResistance: BlastResistance::IRON_DOOR))));
		self::register("iron_trapdoor", fn(BID $id) => new Trapdoor($id, "Iron Trapdoor", new Info(BreakInfo::pickaxe(Hardness::IRON_TRAPDOOR, ToolTier::WOOD, BlastResistance::IRON_TRAPDOOR))));

		self::register("item_frame", fn(BID $id) => new ItemFrame($id, "Item Frame", new Info(new BreakInfo(Hardness::FRAME, blastResistance: BlastResistance::FRAME))), TileItemFrame::class);
		self::register("glowing_item_frame", fn(BID $id) => new ItemFrame($id, "Glow Item Frame", new Info(new BreakInfo(Hardness::GLOW_FRAME, blastResistance: BlastResistance::GLOW_FRAME))), TileGlowingItemFrame::class);

		self::register("jukebox", fn(BID $id) => new Jukebox($id, "Jukebox", new Info(BreakInfo::axe(Hardness::JUKEBOX, blastResistance: BlastResistance::JUKEBOX))), TileJukebox::class);
		self::register("ladder", fn(BID $id) => new Ladder($id, "Ladder", new Info(BreakInfo::axe(Hardness::LADDER, blastResistance: BlastResistance::LADDER))));

		self::register("lantern", fn(BID $id) => new Lantern($id, "Lantern", new Info(BreakInfo::pickaxe(Hardness::LANTERN, blastResistance: BlastResistance::LANTERN)), 15));
		self::register("soul_lantern", fn(BID $id) => new Lantern($id, "Soul Lantern", new Info(BreakInfo::pickaxe(Hardness::SOUL_LANTERN, blastResistance: BlastResistance::SOUL_LANTERN)), 10));

		self::register("lapis_lazuli", fn(BID $id) => new Opaque($id, "Lapis Lazuli Block", new Info(BreakInfo::pickaxe(Hardness::LAPIS_BLOCK, ToolTier::STONE, BlastResistance::LAPIS_BLOCK))));
		self::register("lava", fn(BID $id) => new Lava($id, "Lava", new Info(BreakInfo::indestructible(BlastResistance::LAVA))));
		self::register("lectern", fn(BID $id) => new Lectern($id, "Lectern", new Info(BreakInfo::axe(Hardness::LECTERN, blastResistance: BlastResistance::LECTERN))), TileLectern::class);
		self::register("lever", fn(BID $id) => new Lever($id, "Lever", new Info(new BreakInfo(Hardness::LEVER, blastResistance: BlastResistance::LEVER))));
		self::register("magma", fn(BID $id) => new Magma($id, "Magma Block", new Info(BreakInfo::pickaxe(Hardness::MAGMA, ToolTier::WOOD, BlastResistance::MAGMA))));
		self::register("melon", fn(BID $id) => new Melon($id, "Melon Block", new Info(BreakInfo::axe(Hardness::MELON_BLOCK, blastResistance: BlastResistance::MELON_BLOCK))));
		self::register("melon_stem", fn(BID $id) => new MelonStem($id, "Melon Stem", new Info(new BreakInfo(Hardness::MELON_STEM, blastResistance: BlastResistance::MELON_STEM))));
		self::register("monster_spawner", fn(BID $id) => new MonsterSpawner($id, "Monster Spawner", new Info(BreakInfo::pickaxe(Hardness::MOB_SPAWNER, ToolTier::WOOD, BlastResistance::MOB_SPAWNER))), TileMonsterSpawner::class);
		self::register("mycelium", fn(BID $id) => new Mycelium($id, "Mycelium", new Info(BreakInfo::shovel(Hardness::MYCELIUM, blastResistance: BlastResistance::MYCELIUM), [Tags::DIRT])));

		self::register("nether_bricks", fn(BID $id) => new Opaque($id, "Nether Bricks", new Info(BreakInfo::pickaxe(Hardness::NETHER_BRICK, ToolTier::WOOD, BlastResistance::NETHER_BRICK))));
		self::register("red_nether_bricks", fn(BID $id) => new Opaque($id, "Red Nether Bricks", new Info(BreakInfo::pickaxe(Hardness::RED_NETHER_BRICK, ToolTier::WOOD, BlastResistance::RED_NETHER_BRICK))));
		self::register("nether_brick_fence", fn(BID $id) => new Fence($id, "Nether Brick Fence", new Info(BreakInfo::pickaxe(Hardness::NETHER_BRICK_FENCE, ToolTier::WOOD, BlastResistance::NETHER_BRICK_FENCE))));
		self::register("nether_brick_stairs", fn(BID $id) => new Stair($id, "Nether Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::NETHER_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::NETHER_BRICK_STAIRS))));
		self::register("red_nether_brick_stairs", fn(BID $id) => new Stair($id, "Red Nether Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::RED_NETHER_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::RED_NETHER_BRICK_STAIRS))));
		self::register("chiseled_nether_bricks", fn(BID $id) => new Opaque($id, "Chiseled Nether Bricks", new Info(BreakInfo::pickaxe(Hardness::CHISELED_NETHER_BRICKS, ToolTier::WOOD, BlastResistance::CHISELED_NETHER_BRICKS))));
		self::register("cracked_nether_bricks", fn(BID $id) => new Opaque($id, "Cracked Nether Bricks", new Info(BreakInfo::pickaxe(Hardness::CRACKED_NETHER_BRICKS, ToolTier::WOOD, BlastResistance::CRACKED_NETHER_BRICKS))));

		self::register("nether_portal", fn(BID $id) => new NetherPortal($id, "Nether Portal", new Info(BreakInfo::indestructible(BlastResistance::PORTAL))));
		self::register("nether_reactor_core", fn(BID $id) => new NetherReactor($id, "Nether Reactor Core", new Info(BreakInfo::pickaxe(Hardness::NETHERREACTOR, ToolTier::WOOD, BlastResistance::NETHERREACTOR))));
		self::register("nether_wart_block", fn(BID $id) => new Opaque($id, "Nether Wart Block", new Info(new BreakInfo(Hardness::NETHER_WART_BLOCK, ToolType::HOE, BlastResistance::NETHER_WART_BLOCK))));
		self::register("nether_wart", fn(BID $id) => new NetherWartPlant($id, "Nether Wart", new Info(new BreakInfo(Hardness::NETHER_WART, blastResistance: BlastResistance::NETHER_WART))));
		self::register("netherrack", fn(BID $id) => new Netherrack($id, "Netherrack", new Info(BreakInfo::pickaxe(Hardness::NETHERRACK, ToolTier::WOOD, BlastResistance::NETHERRACK))));
		self::register("note_block", fn(BID $id) => new Note($id, "Note Block", new Info(BreakInfo::axe(Hardness::NOTEBLOCK, blastResistance: BlastResistance::NOTEBLOCK))), TileNote::class);
		self::register("obsidian", fn(BID $id) => new Opaque($id, "Obsidian", new Info(BreakInfo::pickaxe(Hardness::OBSIDIAN, ToolTier::DIAMOND, BlastResistance::OBSIDIAN))));
		self::register("packed_ice", fn(BID $id) => new PackedIce($id, "Packed Ice", new Info(BreakInfo::pickaxe(Hardness::PACKED_ICE, blastResistance: BlastResistance::PACKED_ICE))));
		self::register("podzol", fn(BID $id) => new Podzol($id, "Podzol", new Info(BreakInfo::shovel(Hardness::PODZOL, blastResistance: BlastResistance::PODZOL), [Tags::DIRT])));
		self::register("potatoes", fn(BID $id) => new Potato($id, "Potato Block", new Info(new BreakInfo(Hardness::POTATOES, blastResistance: BlastResistance::POTATOES))));
		self::register("powered_rail", fn(BID $id) => new PoweredRail($id, "Powered Rail", new Info(new BreakInfo(Hardness::GOLDEN_RAIL, blastResistance: BlastResistance::GOLDEN_RAIL))));

		self::register("prismarine", fn(BID $id) => new Opaque($id, "Prismarine", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE, ToolTier::WOOD, BlastResistance::PRISMARINE))));
		self::register("dark_prismarine", fn(BID $id) => new Opaque($id, "Dark Prismarine", new Info(BreakInfo::pickaxe(Hardness::DARK_PRISMARINE, ToolTier::WOOD, BlastResistance::DARK_PRISMARINE))));
		self::register("prismarine_bricks", fn(BID $id) => new Opaque($id, "Prismarine Bricks", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_BRICKS, ToolTier::WOOD, BlastResistance::PRISMARINE_BRICKS))));
		self::register("prismarine_bricks_stairs", fn(BID $id) => new Stair($id, "Prismarine Bricks Stairs", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_BRICKS_STAIRS, ToolTier::WOOD, BlastResistance::PRISMARINE_BRICKS_STAIRS))));
		self::register("dark_prismarine_stairs", fn(BID $id) => new Stair($id, "Dark Prismarine Stairs", new Info(BreakInfo::pickaxe(Hardness::DARK_PRISMARINE_STAIRS, ToolTier::WOOD, BlastResistance::DARK_PRISMARINE_STAIRS))));
		self::register("prismarine_stairs", fn(BID $id) => new Stair($id, "Prismarine Stairs", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_STAIRS, ToolTier::WOOD, BlastResistance::PRISMARINE_STAIRS))));

		self::register("pumpkin", fn(BID $id) => new Pumpkin($id, "Pumpkin", new Info(BreakInfo::axe(Hardness::PUMPKIN, blastResistance: BlastResistance::PUMPKIN))));
		self::register("carved_pumpkin", fn(BID $id) => new CarvedPumpkin($id, "Carved Pumpkin", new Info(BreakInfo::axe(Hardness::CARVED_PUMPKIN, blastResistance: BlastResistance::CARVED_PUMPKIN), enchantmentTags: [EnchantmentTags::MASK])));
		self::register("lit_pumpkin", fn(BID $id) => new LitPumpkin($id, "Jack o'Lantern", new Info(BreakInfo::axe(Hardness::LIT_PUMPKIN, blastResistance: BlastResistance::LIT_PUMPKIN))));

		self::register("pumpkin_stem", fn(BID $id) => new PumpkinStem($id, "Pumpkin Stem", new Info(new BreakInfo(Hardness::PUMPKIN_STEM, blastResistance: BlastResistance::PUMPKIN_STEM))));

		self::register("purpur", fn(BID $id) => new Opaque($id, "Purpur Block", new Info(BreakInfo::pickaxe(Hardness::PURPUR_BLOCK, ToolTier::WOOD, BlastResistance::PURPUR_BLOCK))));
		self::register("purpur_pillar", fn(BID $id) => new SimplePillar($id, "Purpur Pillar", new Info(BreakInfo::pickaxe(Hardness::PURPUR_PILLAR, ToolTier::WOOD, BlastResistance::PURPUR_PILLAR))));
		self::register("purpur_stairs", fn(BID $id) => new Stair($id, "Purpur Stairs", new Info(BreakInfo::pickaxe(Hardness::PURPUR_STAIRS, ToolTier::WOOD, BlastResistance::PURPUR_STAIRS))));

		self::register("quartz", fn(BID $id) => new Opaque($id, "Quartz Block", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_BLOCK, ToolTier::WOOD, BlastResistance::QUARTZ_BLOCK))));
		self::register("chiseled_quartz", fn(BID $id) => new SimplePillar($id, "Chiseled Quartz Block", new Info(BreakInfo::pickaxe(Hardness::CHISELED_QUARTZ_BLOCK, ToolTier::WOOD, BlastResistance::CHISELED_QUARTZ_BLOCK))));
		self::register("quartz_pillar", fn(BID $id) => new SimplePillar($id, "Quartz Pillar", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_PILLAR, ToolTier::WOOD, BlastResistance::QUARTZ_PILLAR))));
		self::register("smooth_quartz", fn(BID $id) => new Opaque($id, "Smooth Quartz Block", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_QUARTZ, ToolTier::WOOD, BlastResistance::SMOOTH_QUARTZ))));
		self::register("quartz_bricks", fn(BID $id) => new Opaque($id, "Quartz Bricks", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_BRICKS, ToolTier::WOOD, BlastResistance::QUARTZ_BRICKS))));

		self::register("quartz_stairs", fn(BID $id) => new Stair($id, "Quartz Stairs", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_STAIRS, ToolTier::WOOD, BlastResistance::QUARTZ_STAIRS))));
		self::register("smooth_quartz_stairs", fn(BID $id) => new Stair($id, "Smooth Quartz Stairs", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_QUARTZ_STAIRS, ToolTier::WOOD, BlastResistance::SMOOTH_QUARTZ_STAIRS))));

		self::register("rail", fn(BID $id) => new Rail($id, "Rail", new Info(new BreakInfo(Hardness::RAIL, blastResistance: BlastResistance::RAIL))));
		self::register("red_mushroom", fn(BID $id) => new RedMushroom($id, "Red Mushroom", new Info(new BreakInfo(Hardness::RED_MUSHROOM, blastResistance: BlastResistance::RED_MUSHROOM), [Tags::POTTABLE_PLANTS])));
		self::register("redstone", fn(BID $id) => new Redstone($id, "Redstone Block", new Info(BreakInfo::pickaxe(Hardness::REDSTONE_BLOCK, ToolTier::WOOD, BlastResistance::REDSTONE_BLOCK))));
		self::register("redstone_comparator", fn(BID $id) => new RedstoneComparator($id, "Redstone Comparator", new Info(new BreakInfo(Hardness::UNPOWERED_COMPARATOR, blastResistance: BlastResistance::UNPOWERED_COMPARATOR))), TileComparator::class);
		self::register("redstone_lamp", fn(BID $id) => new RedstoneLamp($id, "Redstone Lamp", new Info(new BreakInfo(Hardness::REDSTONE_LAMP, blastResistance: BlastResistance::REDSTONE_LAMP))));
		self::register("redstone_repeater", fn(BID $id) => new RedstoneRepeater($id, "Redstone Repeater", new Info(new BreakInfo(Hardness::UNPOWERED_REPEATER, blastResistance: BlastResistance::UNPOWERED_REPEATER))));
		self::register("redstone_torch", fn(BID $id) => new RedstoneTorch($id, "Redstone Torch", new Info(new BreakInfo(Hardness::REDSTONE_TORCH, blastResistance: BlastResistance::REDSTONE_TORCH))));
		self::register("redstone_wire", fn(BID $id) => new RedstoneWire($id, "Redstone", new Info(new BreakInfo(Hardness::REDSTONE_WIRE, blastResistance: BlastResistance::REDSTONE_WIRE))));
		self::register("reserved6", fn(BID $id) => new Reserved6($id, "reserved6", new Info(new BreakInfo(Hardness::RESERVED6, blastResistance: BlastResistance::RESERVED6))));

		self::register("sand", fn(BID $id) => new Sand($id, "Sand", new Info(BreakInfo::shovel(Hardness::SAND, blastResistance: BlastResistance::SAND), [Tags::SAND])));
		self::register("red_sand", fn(BID $id) => new Sand($id, "Red Sand", new Info(BreakInfo::shovel(Hardness::SAND, blastResistance: BlastResistance::SAND), [Tags::SAND])));

		self::register("sea_lantern", fn(BID $id) => new SeaLantern($id, "Sea Lantern", new Info(new BreakInfo(Hardness::SEA_LANTERN, blastResistance: BlastResistance::SEA_LANTERN))));
		self::register("sea_pickle", fn(BID $id) => new SeaPickle($id, "Sea Pickle", new Info(new BreakInfo(Hardness::SEA_PICKLE, blastResistance: BlastResistance::SEA_PICKLE))));
		self::register("mob_head", fn(BID $id) => new MobHead($id, "Mob Head", new Info(new BreakInfo(Hardness::PLAYER_HEAD, blastResistance: BlastResistance::PLAYER_HEAD), enchantmentTags: [EnchantmentTags::MASK])), TileMobHead::class);
		self::register("slime", fn(BID $id) => new Slime($id, "Slime Block", new Info(new BreakInfo(Hardness::SLIME, blastResistance: BlastResistance::SLIME))));
		self::register("snow", fn(BID $id) => new Snow($id, "Snow Block", new Info(BreakInfo::shovel(Hardness::SNOW, ToolTier::WOOD, BlastResistance::SNOW))));
		self::register("snow_layer", fn(BID $id) => new SnowLayer($id, "Snow Layer", new Info(BreakInfo::shovel(Hardness::SNOW_LAYER, ToolTier::WOOD, BlastResistance::SNOW_LAYER))));
		self::register("soul_sand", fn(BID $id) => new SoulSand($id, "Soul Sand", new Info(BreakInfo::shovel(Hardness::SOUL_SAND, blastResistance: BlastResistance::SOUL_SAND))));
		self::register("sponge", fn(BID $id) => new Sponge($id, "Sponge", new Info(new BreakInfo(Hardness::SPONGE, ToolType::HOE, BlastResistance::SPONGE))));
		self::register("shulker_box", fn(BID $id) => new ShulkerBox($id, "Shulker Box", new Info(BreakInfo::pickaxe(Hardness::UNDYED_SHULKER_BOX, blastResistance: BlastResistance::UNDYED_SHULKER_BOX))), TileShulkerBox::class);

		$stone = self::register(
			"stone",
			fn(BID $id) => new class($id, "Stone", new Info(BreakInfo::pickaxe(Hardness::STONE, ToolTier::WOOD, BlastResistance::STONE))) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			}
		);
		self::register("andesite", fn(BID $id) => new Opaque($id, "Andesite", new Info(BreakInfo::pickaxe(Hardness::ANDESITE, ToolTier::WOOD, BlastResistance::ANDESITE))));
		self::register("diorite", fn(BID $id) => new Opaque($id, "Diorite", new Info(BreakInfo::pickaxe(Hardness::DIORITE, ToolTier::WOOD, BlastResistance::DIORITE))));
		self::register("granite", fn(BID $id) => new Opaque($id, "Granite", new Info(BreakInfo::pickaxe(Hardness::GRANITE, ToolTier::WOOD, BlastResistance::GRANITE))));
		self::register("polished_andesite", fn(BID $id) => new Opaque($id, "Polished Andesite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_ANDESITE, ToolTier::WOOD, BlastResistance::POLISHED_ANDESITE))));
		self::register("polished_diorite", fn(BID $id) => new Opaque($id, "Polished Diorite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DIORITE, ToolTier::WOOD, BlastResistance::POLISHED_DIORITE))));
		self::register("polished_granite", fn(BID $id) => new Opaque($id, "Polished Granite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_GRANITE, ToolTier::WOOD, BlastResistance::POLISHED_GRANITE))));

		$stoneBrick = self::register("stone_bricks", fn(BID $id) => new Opaque($id, "Stone Bricks", new Info(BreakInfo::pickaxe(Hardness::STONE_BRICKS, ToolTier::WOOD, BlastResistance::STONE_BRICKS))));
		$mossyStoneBrick = self::register("mossy_stone_bricks", fn(BID $id) => new Opaque($id, "Mossy Stone Bricks", new Info(BreakInfo::pickaxe(Hardness::MOSSY_STONE_BRICKS, ToolTier::WOOD, BlastResistance::MOSSY_STONE_BRICKS))));
		$crackedStoneBrick = self::register("cracked_stone_bricks", fn(BID $id) => new Opaque($id, "Cracked Stone Bricks", new Info(BreakInfo::pickaxe(Hardness::CRACKED_STONE_BRICKS, ToolTier::WOOD, BlastResistance::CRACKED_STONE_BRICKS))));
		$chiseledStoneBrick = self::register("chiseled_stone_bricks", fn(BID $id) => new Opaque($id, "Chiseled Stone Bricks", new Info(BreakInfo::pickaxe(Hardness::CHISELED_STONE_BRICKS, ToolTier::WOOD, BlastResistance::CHISELED_STONE_BRICKS))));

		self::register("infested_stone", fn(BID $id) => new InfestedStone($id, "Infested Stone", new Info(BreakInfo::pickaxe(Hardness::INFESTED_STONE, blastResistance: BlastResistance::INFESTED_STONE)), $stone));
		self::register("infested_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Stone Brick", new Info(BreakInfo::pickaxe(Hardness::INFESTED_STONE_BRICKS, blastResistance: BlastResistance::INFESTED_STONE_BRICKS)), $stoneBrick));
		self::register("infested_cobblestone", fn(BID $id) => new InfestedStone($id, "Infested Cobblestone", new Info(BreakInfo::pickaxe(Hardness::INFESTED_COBBLESTONE, blastResistance: BlastResistance::INFESTED_COBBLESTONE)), $cobblestone));
		self::register("infested_mossy_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Mossy Stone Brick", new Info(BreakInfo::pickaxe(Hardness::INFESTED_MOSSY_STONE_BRICKS, blastResistance: BlastResistance::INFESTED_MOSSY_STONE_BRICKS)), $mossyStoneBrick));
		self::register("infested_cracked_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Cracked Stone Brick", new Info(BreakInfo::pickaxe(Hardness::INFESTED_CRACKED_STONE_BRICKS, blastResistance: BlastResistance::INFESTED_CRACKED_STONE_BRICKS)), $crackedStoneBrick));
		self::register("infested_chiseled_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Chiseled Stone Brick", new Info(BreakInfo::pickaxe(Hardness::INFESTED_CHISELED_STONE_BRICKS, blastResistance: BlastResistance::INFESTED_CHISELED_STONE_BRICKS)), $chiseledStoneBrick));

		self::register("stone_stairs", fn(BID $id) => new Stair($id, "Stone Stairs", new Info(BreakInfo::pickaxe(Hardness::STONE_STAIRS, ToolTier::WOOD, BlastResistance::STONE_STAIRS))));
		self::register("smooth_stone", fn(BID $id) => new Opaque($id, "Smooth Stone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_STONE, ToolTier::WOOD, BlastResistance::SMOOTH_STONE))));
		self::register("andesite_stairs", fn(BID $id) => new Stair($id, "Andesite Stairs", new Info(BreakInfo::pickaxe(Hardness::ANDESITE_STAIRS, ToolTier::WOOD, BlastResistance::ANDESITE_STAIRS))));
		self::register("diorite_stairs", fn(BID $id) => new Stair($id, "Diorite Stairs", new Info(BreakInfo::pickaxe(Hardness::DIORITE_STAIRS, ToolTier::WOOD, BlastResistance::DIORITE_STAIRS))));
		self::register("granite_stairs", fn(BID $id) => new Stair($id, "Granite Stairs", new Info(BreakInfo::pickaxe(Hardness::GRANITE_STAIRS, ToolTier::WOOD, BlastResistance::GRANITE_STAIRS))));
		self::register("polished_andesite_stairs", fn(BID $id) => new Stair($id, "Polished Andesite Stairs", new Info(BreakInfo::pickaxe(Hardness::POLISHED_ANDESITE_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_ANDESITE_STAIRS))));
		self::register("polished_diorite_stairs", fn(BID $id) => new Stair($id, "Polished Diorite Stairs", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DIORITE_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_DIORITE_STAIRS))));
		self::register("polished_granite_stairs", fn(BID $id) => new Stair($id, "Polished Granite Stairs", new Info(BreakInfo::pickaxe(Hardness::POLISHED_GRANITE_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_GRANITE_STAIRS))));
		self::register("stone_brick_stairs", fn(BID $id) => new Stair($id, "Stone Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::STONE_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::STONE_BRICK_STAIRS))));
		self::register("mossy_stone_brick_stairs", fn(BID $id) => new Stair($id, "Mossy Stone Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::MOSSY_STONE_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::MOSSY_STONE_BRICK_STAIRS))));
		self::register("stone_button", fn(BID $id) => new StoneButton($id, "Stone Button", new Info(BreakInfo::pickaxe(Hardness::STONE_BUTTON, blastResistance: BlastResistance::STONE_BUTTON))));
		self::register("stonecutter", fn(BID $id) => new Stonecutter($id, "Stonecutter", new Info(BreakInfo::pickaxe(Hardness::STONECUTTER_BLOCK, blastResistance: BlastResistance::STONECUTTER_BLOCK))));
		self::register("stone_pressure_plate", fn(BID $id) => new StonePressurePlate($id, "Stone Pressure Plate", new Info(BreakInfo::pickaxe(Hardness::STONE_PRESSURE_PLATE, blastResistance: BlastResistance::STONE_PRESSURE_PLATE))));

		self::register("brick_slab", fn(BID $id) => new Slab($id, "Brick", new Info(BreakInfo::pickaxe(Hardness::BRICK_SLAB, ToolTier::WOOD, BlastResistance::BRICK_SLAB))));
		self::register("cobblestone_slab", fn(BID $id) => new Slab($id, "Cobblestone", new Info(BreakInfo::pickaxe(Hardness::COBBLESTONE_SLAB, ToolTier::WOOD, BlastResistance::COBBLESTONE_SLAB))));
		self::register("fake_wooden_slab", fn(BID $id) => new Slab($id, "Fake Wooden", new Info(BreakInfo::pickaxe(Hardness::PETRIFIED_OAK_SLAB, ToolTier::WOOD, BlastResistance::PETRIFIED_OAK_SLAB))));
		self::register("nether_brick_slab", fn(BID $id) => new Slab($id, "Nether Brick", new Info(BreakInfo::pickaxe(Hardness::NETHER_BRICK_SLAB, ToolTier::WOOD, BlastResistance::NETHER_BRICK_SLAB))));
		self::register("quartz_slab", fn(BID $id) => new Slab($id, "Quartz", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_SLAB, ToolTier::WOOD, BlastResistance::QUARTZ_SLAB))));
		self::register("sandstone_slab", fn(BID $id) => new Slab($id, "Sandstone", new Info(BreakInfo::pickaxe(Hardness::SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::SANDSTONE_SLAB))));
		self::register("smooth_stone_slab", fn(BID $id) => new Slab($id, "Smooth Stone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_STONE_SLAB, ToolTier::WOOD, BlastResistance::SMOOTH_STONE_SLAB))));
		self::register("stone_brick_slab", fn(BID $id) => new Slab($id, "Stone Brick", new Info(BreakInfo::pickaxe(Hardness::STONE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::STONE_BRICK_SLAB))));
		self::register("dark_prismarine_slab", fn(BID $id) => new Slab($id, "Dark Prismarine", new Info(BreakInfo::pickaxe(Hardness::DARK_PRISMARINE_SLAB, ToolTier::WOOD, BlastResistance::DARK_PRISMARINE_SLAB))));
		self::register("mossy_cobblestone_slab", fn(BID $id) => new Slab($id, "Mossy Cobblestone", new Info(BreakInfo::pickaxe(Hardness::MOSSY_COBBLESTONE_SLAB, ToolTier::WOOD, BlastResistance::MOSSY_COBBLESTONE_SLAB))));
		self::register("prismarine_slab", fn(BID $id) => new Slab($id, "Prismarine", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_SLAB, ToolTier::WOOD, BlastResistance::PRISMARINE_SLAB))));
		self::register("prismarine_bricks_slab", fn(BID $id) => new Slab($id, "Prismarine Bricks", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::PRISMARINE_BRICK_SLAB))));
		self::register("purpur_slab", fn(BID $id) => new Slab($id, "Purpur", new Info(BreakInfo::pickaxe(Hardness::PURPUR_SLAB, ToolTier::WOOD, BlastResistance::PURPUR_SLAB))));
		self::register("red_nether_brick_slab", fn(BID $id) => new Slab($id, "Red Nether Brick", new Info(BreakInfo::pickaxe(Hardness::RED_NETHER_BRICK_SLAB, ToolTier::WOOD, BlastResistance::RED_NETHER_BRICK_SLAB))));
		self::register("red_sandstone_slab", fn(BID $id) => new Slab($id, "Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::RED_SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::RED_SANDSTONE_SLAB))));
		self::register("smooth_sandstone_slab", fn(BID $id) => new Slab($id, "Smooth Sandstone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::SMOOTH_SANDSTONE_SLAB))));
		self::register("andesite_slab", fn(BID $id) => new Slab($id, "Andesite", new Info(BreakInfo::pickaxe(Hardness::ANDESITE_SLAB, ToolTier::WOOD, BlastResistance::ANDESITE_SLAB))));
		self::register("diorite_slab", fn(BID $id) => new Slab($id, "Diorite", new Info(BreakInfo::pickaxe(Hardness::DIORITE_SLAB, ToolTier::WOOD, BlastResistance::DIORITE_SLAB))));
		self::register("end_stone_brick_slab", fn(BID $id) => new Slab($id, "End Stone Brick", new Info(BreakInfo::pickaxe(Hardness::END_STONE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::END_STONE_BRICK_SLAB))));
		self::register("granite_slab", fn(BID $id) => new Slab($id, "Granite", new Info(BreakInfo::pickaxe(Hardness::GRANITE_SLAB, ToolTier::WOOD, BlastResistance::GRANITE_SLAB))));
		self::register("polished_andesite_slab", fn(BID $id) => new Slab($id, "Polished Andesite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_ANDESITE_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_ANDESITE_SLAB))));
		self::register("polished_diorite_slab", fn(BID $id) => new Slab($id, "Polished Diorite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DIORITE_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_DIORITE_SLAB))));
		self::register("polished_granite_slab", fn(BID $id) => new Slab($id, "Polished Granite", new Info(BreakInfo::pickaxe(Hardness::POLISHED_GRANITE_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_GRANITE_SLAB))));
		self::register("smooth_red_sandstone_slab", fn(BID $id) => new Slab($id, "Smooth Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_RED_SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::SMOOTH_RED_SANDSTONE_SLAB))));
		self::register("cut_red_sandstone_slab", fn(BID $id) => new Slab($id, "Cut Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::CUT_RED_SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::CUT_RED_SANDSTONE_SLAB))));
		self::register("cut_sandstone_slab", fn(BID $id) => new Slab($id, "Cut Sandstone", new Info(BreakInfo::pickaxe(Hardness::CUT_SANDSTONE_SLAB, ToolTier::WOOD, BlastResistance::CUT_SANDSTONE_SLAB))));
		self::register("mossy_stone_brick_slab", fn(BID $id) => new Slab($id, "Mossy Stone Brick", new Info(BreakInfo::pickaxe(Hardness::MOSSY_STONE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::MOSSY_STONE_BRICK_SLAB))));
		self::register("smooth_quartz_slab", fn(BID $id) => new Slab($id, "Smooth Quartz", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_QUARTZ_SLAB, ToolTier::WOOD, BlastResistance::SMOOTH_QUARTZ_SLAB))));
		self::register("stone_slab", fn(BID $id) => new Slab($id, "Stone", new Info(BreakInfo::pickaxe(Hardness::NORMAL_STONE_SLAB, ToolTier::WOOD, BlastResistance::NORMAL_STONE_SLAB))));

		self::register("legacy_stonecutter", fn(BID $id) => new Opaque($id, "Legacy Stonecutter", new Info(BreakInfo::pickaxe(Hardness::STONECUTTER, ToolTier::WOOD, BlastResistance::STONECUTTER))));
		self::register("sugarcane", fn(BID $id) => new Sugarcane($id, "Sugarcane", new Info(new BreakInfo(Hardness::REEDS, blastResistance: BlastResistance::REEDS))));
		self::register("sweet_berry_bush", fn(BID $id) => new SweetBerryBush($id, "Sweet Berry Bush", new Info(new BreakInfo(Hardness::SWEET_BERRY_BUSH, blastResistance: BlastResistance::SWEET_BERRY_BUSH))));
		self::register("tnt", fn(BID $id) => new TNT($id, "TNT", new Info(new BreakInfo(Hardness::TNT, blastResistance: BlastResistance::TNT))));
		self::register("fern", fn(BID $id) => new TallGrass($id, "Fern", new Info(new BreakInfo(Hardness::FERN, ToolType::SHEARS, 1, BlastResistance::FERN), [Tags::POTTABLE_PLANTS])));
		self::register("tall_grass", fn(BID $id) => new TallGrass($id, "Tall Grass", new Info(new BreakInfo(Hardness::TALL_GRASS, ToolType::SHEARS, 1, BlastResistance::TALL_GRASS))));

		self::register("blue_torch", fn(BID $id) => new Torch($id, "Blue Torch", new Info(new BreakInfo(Hardness::COLORED_TORCH_BLUE, blastResistance: BlastResistance::COLORED_TORCH_BLUE))));
		self::register("purple_torch", fn(BID $id) => new Torch($id, "Purple Torch", new Info(new BreakInfo(Hardness::COLORED_TORCH_PURPLE, blastResistance: BlastResistance::COLORED_TORCH_PURPLE))));
		self::register("red_torch", fn(BID $id) => new Torch($id, "Red Torch", new Info(new BreakInfo(Hardness::COLORED_TORCH_RED, blastResistance: BlastResistance::COLORED_TORCH_RED))));
		self::register("green_torch", fn(BID $id) => new Torch($id, "Green Torch", new Info(new BreakInfo(Hardness::COLORED_TORCH_GREEN, blastResistance: BlastResistance::COLORED_TORCH_GREEN))));
		self::register("torch", fn(BID $id) => new Torch($id, "Torch", new Info(new BreakInfo(Hardness::TORCH, blastResistance: BlastResistance::TORCH))));

		self::register("trapped_chest", fn(BID $id) => new TrappedChest($id, "Trapped Chest", new Info(BreakInfo::axe(Hardness::TRAPPED_CHEST, blastResistance: BlastResistance::TRAPPED_CHEST))), TileChest::class);
		self::register("tripwire", fn(BID $id) => new Tripwire($id, "Tripwire", new Info(new BreakInfo(Hardness::TRIP_WIRE, blastResistance: BlastResistance::TRIP_WIRE))));
		self::register("tripwire_hook", fn(BID $id) => new TripwireHook($id, "Tripwire Hook", new Info(new BreakInfo(Hardness::TRIPWIRE_HOOK, blastResistance: BlastResistance::TRIPWIRE_HOOK))));
		self::register("underwater_torch", fn(BID $id) => new UnderwaterTorch($id, "Underwater Torch", new Info(new BreakInfo(Hardness::UNDERWATER_TORCH, blastResistance: BlastResistance::UNDERWATER_TORCH))));
		self::register("vines", fn(BID $id) => new Vine($id, "Vines", new Info(BreakInfo::axe(Hardness::VINE, blastResistance: BlastResistance::VINE))));
		self::register("water", fn(BID $id) => new Water($id, "Water", new Info(BreakInfo::indestructible(BlastResistance::WATER))));
		self::register("lily_pad", fn(BID $id) => new WaterLily($id, "Lily Pad", new Info(new BreakInfo(Hardness::WATERLILY, blastResistance: BlastResistance::WATERLILY))));

		self::register("weighted_pressure_plate_heavy", fn(BID $id) => new WeightedPressurePlateHeavy(
			$id,
			"Weighted Pressure Plate Heavy",
			new Info(BreakInfo::pickaxe(Hardness::HEAVY_WEIGHTED_PRESSURE_PLATE, blastResistance: BlastResistance::HEAVY_WEIGHTED_PRESSURE_PLATE)),
			deactivationDelayTicks: 10,
			signalStrengthFactor: 0.1
		));
		self::register("weighted_pressure_plate_light", fn(BID $id) => new WeightedPressurePlateLight(
			$id,
			"Weighted Pressure Plate Light",
			new Info(BreakInfo::pickaxe(Hardness::LIGHT_WEIGHTED_PRESSURE_PLATE, blastResistance: BlastResistance::LIGHT_WEIGHTED_PRESSURE_PLATE)),
			deactivationDelayTicks: 10,
			signalStrengthFactor: 1.0
		));
		self::register("wheat", fn(BID $id) => new Wheat($id, "Wheat Block", new Info(new BreakInfo(Hardness::WHEAT, blastResistance: BlastResistance::WHEAT))));

		foreach(SaplingType::cases() as $saplingType){
			$name = $saplingType->getDisplayName();
			[$hardness, $blastResistance] = match($saplingType){
				SaplingType::OAK => [Hardness::OAK_SAPLING, BlastResistance::OAK_SAPLING],
				SaplingType::SPRUCE => [Hardness::SPRUCE_SAPLING, BlastResistance::BIRCH_SAPLING],
				SaplingType::BIRCH => [Hardness::BIRCH_SAPLING, BlastResistance::BIRCH_SAPLING],
				SaplingType::JUNGLE => [Hardness::JUNGLE_SAPLING, BlastResistance::JUNGLE_SAPLING],
				SaplingType::ACACIA => [Hardness::ACACIA_SAPLING, BlastResistance::ACACIA_SAPLING],
				SaplingType::DARK_OAK => [Hardness::DARK_OAK_SAPLING, BlastResistance::DARK_OAK_SAPLING],
			};
			self::register(strtolower($saplingType->name) . "_sapling", fn(BID $id) => new Sapling($id, $name . " Sapling", new Info(new BreakInfo($hardness, blastResistance: $blastResistance), [Tags::POTTABLE_PLANTS]), $saplingType));
		}
		foreach(LeavesType::cases() as $leavesType){
			$name = $leavesType->getDisplayName();
			[$hardness, $blastResistance] = match($leavesType){
				LeavesType::OAK => [Hardness::OAK_LEAVES, BlastResistance::OAK_LEAVES],
				LeavesType::SPRUCE => [Hardness::SPRUCE_LEAVES, BlastResistance::SPRUCE_LEAVES],
				LeavesType::BIRCH => [Hardness::BIRCH_LEAVES, BlastResistance::BIRCH_LEAVES],
				LeavesType::JUNGLE => [Hardness::JUNGLE_LEAVES, BlastResistance::JUNGLE_LEAVES],
				LeavesType::ACACIA => [Hardness::ACACIA_LEAVES, BlastResistance::ACACIA_LEAVES],
				LeavesType::DARK_OAK => [Hardness::DARK_OAK_LEAVES, BlastResistance::DARK_OAK_LEAVES],
				LeavesType::MANGROVE => [Hardness::MANGROVE_LEAVES, BlastResistance::MANGROVE_LEAVES],
				LeavesType::AZALEA => [Hardness::AZALEA_LEAVES, BlastResistance::AZALEA_LEAVES],
				LeavesType::FLOWERING_AZALEA => [Hardness::AZALEA_LEAVES_FLOWERED, BlastResistance::AZALEA_LEAVES_FLOWERED],
				LeavesType::CHERRY => [Hardness::CHERRY_LEAVES, BlastResistance::CHERRY_LEAVES],
				LeavesType::PALE_OAK => [Hardness::PALE_OAK_LEAVES, BlastResistance::PALE_OAK_LEAVES],
			};
			self::register(strtolower($leavesType->name) . "_leaves", fn(BID $id) => new Leaves($id, $name . " Leaves", new Info(new class($hardness, ToolType::HOE, blastResistance: $blastResistance) extends BreakInfo{
				public function getBreakTime(Item $item) : float{
					if($item->getBlockToolType() === ToolType::SHEARS){
						return 0.0;
					}
					return parent::getBreakTime($item);
				}
			}), $leavesType));
		}

		self::register("red_sandstone_stairs", fn(BID $id) => new Stair($id, "Red Sandstone Stairs", new Info(BreakInfo::pickaxe(Hardness::RED_SANDSTONE_STAIRS, ToolTier::WOOD, BlastResistance::RED_SANDSTONE_STAIRS))));
		self::register("smooth_red_sandstone_stairs", fn(BID $id) => new Stair($id, "Smooth Red Sandstone Stairs", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_RED_SANDSTONE_STAIRS, ToolTier::WOOD, BlastResistance::SMOOTH_RED_SANDSTONE_STAIRS))));
		self::register("red_sandstone", fn(BID $id) => new Opaque($id, "Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::RED_SANDSTONE, ToolTier::WOOD, BlastResistance::RED_SANDSTONE))));
		self::register("chiseled_red_sandstone", fn(BID $id) => new Opaque($id, "Chiseled Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::CHISELED_RED_SANDSTONE, ToolTier::WOOD, BlastResistance::CHISELED_RED_SANDSTONE))));
		self::register("cut_red_sandstone", fn(BID $id) => new Opaque($id, "Cut Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::CUT_RED_SANDSTONE, ToolTier::WOOD, BlastResistance::CUT_RED_SANDSTONE))));
		self::register("smooth_red_sandstone", fn(BID $id) => new Opaque($id, "Smooth Red Sandstone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_RED_SANDSTONE, ToolTier::WOOD, BlastResistance::SMOOTH_RED_SANDSTONE))));

		self::register("sandstone_stairs", fn(BID $id) => new Stair($id, "Sandstone Stairs", new Info(BreakInfo::pickaxe(Hardness::SANDSTONE_STAIRS, ToolTier::WOOD, BlastResistance::SANDSTONE_STAIRS))));
		self::register("smooth_sandstone_stairs", fn(BID $id) => new Stair($id, "Smooth Sandstone Stairs", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_SANDSTONE_STAIRS, ToolTier::WOOD, BlastResistance::SMOOTH_SANDSTONE_STAIRS))));
		self::register("sandstone", fn(BID $id) => new Opaque($id, "Sandstone", new Info(BreakInfo::pickaxe(Hardness::SANDSTONE, ToolTier::WOOD, BlastResistance::SANDSTONE))));
		self::register("chiseled_sandstone", fn(BID $id) => new Opaque($id, "Chiseled Sandstone", new Info(BreakInfo::pickaxe(Hardness::CHISELED_SANDSTONE, ToolTier::WOOD, BlastResistance::CHISELED_SANDSTONE))));
		self::register("cut_sandstone", fn(BID $id) => new Opaque($id, "Cut Sandstone", new Info(BreakInfo::pickaxe(Hardness::CUT_SANDSTONE, ToolTier::WOOD, BlastResistance::CUT_SANDSTONE))));
		self::register("smooth_sandstone", fn(BID $id) => new Opaque($id, "Smooth Sandstone", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_SANDSTONE, ToolTier::WOOD, BlastResistance::SMOOTH_SANDSTONE))));

		self::register("glazed_terracotta", fn(BID $id) => new GlazedTerracotta($id, "Glazed Terracotta", new Info(BreakInfo::pickaxe(Hardness::WHITE_GLAZED_TERRACOTTA, ToolTier::WOOD, BlastResistance::WHITE_GLAZED_TERRACOTTA))));
		self::register("dyed_shulker_box", fn(BID $id) => new DyedShulkerBox($id, "Dyed Shulker Box", new Info(BreakInfo::pickaxe(Hardness::WHITE_SHULKER_BOX, blastResistance: BlastResistance::WHITE_SHULKER_BOX))), TileShulkerBox::class);
		self::register("stained_glass", fn(BID $id) => new StainedGlass($id, "Stained Glass", new Info(new BreakInfo(Hardness::WHITE_STAINED_GLASS, blastResistance: BlastResistance::WHITE_STAINED_GLASS))));
		self::register("stained_glass_pane", fn(BID $id) => new StainedGlassPane($id, "Stained Glass Pane", new Info(new BreakInfo(Hardness::WHITE_STAINED_GLASS_PANE, blastResistance: BlastResistance::WHITE_STAINED_GLASS_PANE))));
		self::register("stained_clay", fn(BID $id) => new StainedHardenedClay($id, "Stained Clay", new Info(BreakInfo::pickaxe(Hardness::WHITE_TERRACOTTA, ToolTier::WOOD, BlastResistance::WHITE_TERRACOTTA))));
		self::register("stained_hardened_glass", fn(BID $id) => new StainedHardenedGlass($id, "Stained Hardened Glass", new Info(new BreakInfo(Hardness::HARD_WHITE_STAINED_GLASS, blastResistance: BlastResistance::HARD_WHITE_STAINED_GLASS))));
		self::register("stained_hardened_glass_pane", fn(BID $id) => new StainedHardenedGlassPane($id, "Stained Hardened Glass Pane", new Info(new BreakInfo(Hardness::HARD_WHITE_STAINED_GLASS_PANE, blastResistance: BlastResistance::HARD_WHITE_STAINED_GLASS_PANE))));
		self::register("carpet", fn(BID $id) => new Carpet($id, "Carpet", new Info(new BreakInfo(Hardness::WHITE_CARPET, blastResistance: BlastResistance::WHITE_CARPET))));
		self::register("concrete", fn(BID $id) => new Concrete($id, "Concrete", new Info(BreakInfo::pickaxe(Hardness::WHITE_CONCRETE, ToolTier::WOOD, BlastResistance::WHITE_CONCRETE))));
		self::register("concrete_powder", fn(BID $id) => new ConcretePowder($id, "Concrete Powder", new Info(BreakInfo::shovel(Hardness::WHITE_CONCRETE_POWDER, blastResistance: BlastResistance::WHITE_CONCRETE_POWDER))));
		self::register("wool", fn(BID $id) => new Wool($id, "Wool", new Info(new class(Hardness::WHITE_WOOL, ToolType::SHEARS, BlastResistance::WHITE_WOOL) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				$time = parent::getBreakTime($item);
				if($item->getBlockToolType() === ToolType::SHEARS){
					$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
				}

				return $time;
			}
		})));

		self::register("cobblestone_wall", fn(BID $id) => new Wall($id, "Cobblestone Wall", new Info(BreakInfo::pickaxe(Hardness::COBBLESTONE_WALL, ToolTier::WOOD, BlastResistance::COBBLESTONE_WALL))));
		self::register("andesite_wall", fn(BID $id) => new Wall($id, "Andesite Wall", new Info(BreakInfo::pickaxe(Hardness::ANDESITE_WALL, ToolTier::WOOD, BlastResistance::ANDESITE_WALL))));
		self::register("brick_wall", fn(BID $id) => new Wall($id, "Brick Wall", new Info(BreakInfo::pickaxe(Hardness::BRICK_WALL, ToolTier::WOOD, BlastResistance::BRICK_WALL))));
		self::register("diorite_wall", fn(BID $id) => new Wall($id, "Diorite Wall", new Info(BreakInfo::pickaxe(Hardness::DIORITE_WALL, ToolTier::WOOD, BlastResistance::DIORITE_WALL))));
		self::register("end_stone_brick_wall", fn(BID $id) => new Wall($id, "End Stone Brick Wall", new Info(BreakInfo::pickaxe(Hardness::END_STONE_BRICK_WALL, ToolTier::WOOD, BlastResistance::END_STONE_BRICK_WALL))));
		self::register("granite_wall", fn(BID $id) => new Wall($id, "Granite Wall", new Info(BreakInfo::pickaxe(Hardness::GRANITE_WALL, ToolTier::WOOD, BlastResistance::GRANITE_WALL))));
		self::register("mossy_stone_brick_wall", fn(BID $id) => new Wall($id, "Mossy Stone Brick Wall", new Info(BreakInfo::pickaxe(Hardness::MOSSY_STONE_BRICK_WALL, ToolTier::WOOD, BlastResistance::MOSSY_STONE_BRICK_WALL))));
		self::register("mossy_cobblestone_wall", fn(BID $id) => new Wall($id, "Mossy Cobblestone Wall", new Info(BreakInfo::pickaxe(Hardness::MOSSY_COBBLESTONE_WALL, ToolTier::WOOD, BlastResistance::MOSSY_COBBLESTONE_WALL))));
		self::register("nether_brick_wall", fn(BID $id) => new Wall($id, "Nether Brick Wall", new Info(BreakInfo::pickaxe(Hardness::NETHER_BRICK_WALL, ToolTier::WOOD, BlastResistance::NETHER_BRICK_WALL))));
		self::register("prismarine_wall", fn(BID $id) => new Wall($id, "Prismarine Wall", new Info(BreakInfo::pickaxe(Hardness::PRISMARINE_WALL, ToolTier::WOOD, BlastResistance::PRISMARINE_WALL))));
		self::register("red_nether_brick_wall", fn(BID $id) => new Wall($id, "Red Nether Brick Wall", new Info(BreakInfo::pickaxe(Hardness::RED_NETHER_BRICK_WALL, ToolTier::WOOD, BlastResistance::RED_NETHER_BRICK_WALL))));
		self::register("red_sandstone_wall", fn(BID $id) => new Wall($id, "Red Sandstone Wall", new Info(BreakInfo::pickaxe(Hardness::RED_SANDSTONE_WALL, ToolTier::WOOD, BlastResistance::RED_SANDSTONE_WALL))));
		self::register("sandstone_wall", fn(BID $id) => new Wall($id, "Sandstone Wall", new Info(BreakInfo::pickaxe(Hardness::SANDSTONE_WALL, ToolTier::WOOD, BlastResistance::SANDSTONE_WALL))));
		self::register("stone_brick_wall", fn(BID $id) => new Wall($id, "Stone Brick Wall", new Info(BreakInfo::pickaxe(Hardness::STONE_BRICK_WALL, ToolTier::WOOD, BlastResistance::STONE_BRICK_WALL))));

		self::registerElements();

		self::register("compound_creator", fn(BID $id) => new ChemistryTable($id, "Compound Creator", new Info(BreakInfo::pickaxe(Hardness::COMPOUND_CREATOR, ToolTier::WOOD, BlastResistance::COMPOUND_CREATOR))));
		self::register("element_constructor", fn(BID $id) => new ChemistryTable($id, "Element Constructor", new Info(BreakInfo::pickaxe(Hardness::ELEMENT_CONSTRUCTOR, ToolTier::WOOD, BlastResistance::ELEMENT_CONSTRUCTOR))));
		self::register("lab_table", fn(BID $id) => new ChemistryTable($id, "Lab Table", new Info(BreakInfo::pickaxe(Hardness::LAB_TABLE, ToolTier::WOOD, BlastResistance::LAB_TABLE))));
		self::register("material_reducer", fn(BID $id) => new ChemistryTable($id, "Material Reducer", new Info(BreakInfo::pickaxe(Hardness::MATERIAL_REDUCER, ToolTier::WOOD, BlastResistance::MATERIAL_REDUCER))));

		self::register("chemical_heat", fn(BID $id) => new ChemicalHeat($id, "Heat Block", new Info(BreakInfo::pickaxe(Hardness::CHEMICAL_HEAT, ToolTier::WOOD, BlastResistance::CHEMICAL_HEAT))));

		self::registerMushroomBlocks();

		self::register("coral", fn(BID $id) => new Coral(
			$id,
			"Coral",
			new Info(new BreakInfo(Hardness::TUBE_CORAL, blastResistance: BlastResistance::TUBE_CORAL)),
		));
		self::register("coral_fan", fn(BID $id) => new FloorCoralFan(
			$id,
			"Coral Fan",
			new Info(new BreakInfo(Hardness::TUBE_CORAL_FAN, blastResistance: BlastResistance::TUBE_CORAL_FAN)),
		));
		self::register("wall_coral_fan", fn(BID $id) => new WallCoralFan(
			$id,
			"Wall Coral Fan",
			new Info(new BreakInfo(Hardness::TUBE_CORAL_WALL_FAN, blastResistance: BlastResistance::TUBE_CORAL_WALL_FAN)),
		));

		self::register("mangrove_roots", fn(BID $id) => new MangroveRoots($id, "Mangrove Roots", new Info(BreakInfo::axe(Hardness::MANGROVE_ROOTS, blastResistance: BlastResistance::MANGROVE_ROOTS))));
		self::register("muddy_mangrove_roots", fn(BID $id) => new SimplePillar($id, "Muddy Mangrove Roots", new Info(BreakInfo::shovel(Hardness::MUDDY_MANGROVE_ROOTS, blastResistance: BlastResistance::MUDDY_MANGROVE_ROOTS), [Tags::MUD])));
		self::register("froglight", fn(BID $id) => new Froglight($id, "Froglight", new Info(new BreakInfo(Hardness::OCHRE_FROGLIGHT, blastResistance: BlastResistance::OCHRE_FROGLIGHT))));
		self::register("sculk", fn(BID $id) => new Sculk($id, "Sculk", new Info(new BreakInfo(Hardness::SCULK, ToolType::HOE, BlastResistance::SCULK))));
		self::register("reinforced_deepslate", fn(BID $id) => new class($id, "Reinforced Deepslate", new Info(new BreakInfo(Hardness::REINFORCED_DEEPSLATE, ToolType::NONE, 0, BlastResistance::REINFORCED_DEEPSLATE))) extends Opaque{
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
		self::registerResinBlocks();
		self::registerTuffBlocks();

		self::registerCraftingTables();
		self::registerChorusBlocks();
		self::registerOres();
		self::registerWoodenBlocks();
		self::registerCauldronBlocks();
	}

	private static function registerWoodenBlocks() : void{
		$planksBreakInfo = new Info(BreakInfo::axe(Hardness::OAK_PLANKS, null, BlastResistance::OAK_PLANKS));
		$signBreakInfo = new Info(BreakInfo::axe(Hardness::STANDING_SIGN, blastResistance: BlastResistance::STANDING_SIGN));
		$logBreakInfo = new Info(BreakInfo::axe(Hardness::OAK_LOG, blastResistance: BlastResistance::OAK_LOG));
		$woodenDoorBreakInfo = new Info(BreakInfo::axe(Hardness::WOODEN_DOOR, null, blastResistance: BlastResistance::WOODEN_DOOR));
		$woodenButtonBreakInfo = new Info(BreakInfo::axe(Hardness::WOODEN_BUTTON, blastResistance: BlastResistance::WOODEN_BUTTON));
		$woodenPressurePlateBreakInfo = new Info(BreakInfo::axe(Hardness::WOODEN_PRESSURE_PLATE, blastResistance: BlastResistance::WOODEN_PRESSURE_PLATE));

		foreach(WoodType::cases() as $woodType){
			$name = $woodType->getDisplayName();
			$idName = fn(string $suffix) => strtolower($woodType->name) . "_" . $suffix;

			self::register($idName(mb_strtolower($woodType->getStandardLogSuffix() ?? "log", 'US-ASCII')), fn(BID $id) => new Wood($id, $name . " " . ($woodType->getStandardLogSuffix() ?? "Log"), $logBreakInfo, $woodType));
			self::register($idName(mb_strtolower($woodType->getAllSidedLogSuffix() ?? "wood", 'US-ASCII')), fn(BID $id) => new Wood($id, $name . " " . ($woodType->getAllSidedLogSuffix() ?? "Wood"), $logBreakInfo, $woodType));

			self::register($idName("planks"), fn(BID $id) => new Planks($id, $name . " Planks", $planksBreakInfo, $woodType));
			self::register($idName("fence"), fn(BID $id) => new WoodenFence($id, $name . " Fence", $planksBreakInfo, $woodType));
			self::register($idName("slab"), fn(BID $id) => new WoodenSlab($id, $name, $planksBreakInfo, $woodType));

			self::register($idName("fence_gate"), fn(BID $id) => new FenceGate($id, $name . " Fence Gate", $planksBreakInfo, $woodType));
			self::register($idName("stairs"), fn(BID $id) => new WoodenStairs($id, $name . " Stairs", $planksBreakInfo, $woodType));
			self::register($idName("door"), fn(BID $id) => new WoodenDoor($id, $name . " Door", $woodenDoorBreakInfo, $woodType));

			self::register($idName("button"), fn(BID $id) => new WoodenButton($id, $name . " Button", $woodenButtonBreakInfo, $woodType));
			self::register($idName("pressure_plate"), fn(BID $id) => new WoodenPressurePlate($id, $name . " Pressure Plate", $woodenPressurePlateBreakInfo, $woodType, 20));
			self::register($idName("trapdoor"), fn(BID $id) => new WoodenTrapdoor($id, $name . " Trapdoor", $woodenDoorBreakInfo, $woodType));

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
				WoodType::PALE_OAK => VanillaItems::PALE_OAK_SIGN(...),
			};
			self::register($idName("sign"), fn(BID $id) => new FloorSign($id, $name . " Sign", $signBreakInfo, $woodType, $signAsItem), TileSign::class);
			self::register($idName("wall_sign"), fn(BID $id) => new WallSign($id, $name . " Wall Sign", $signBreakInfo, $woodType, $signAsItem), TileSign::class);
		}
	}

	private static function registerMushroomBlocks() : void{
		self::register("brown_mushroom_block", fn(BID $id) => new BrownMushroomBlock($id, "Brown Mushroom Block", new Info(BreakInfo::axe(Hardness::BROWN_MUSHROOM_BLOCK, blastResistance: BlastResistance::BROWN_MUSHROOM_BLOCK))));
		self::register("red_mushroom_block", fn(BID $id) => new RedMushroomBlock($id, "Red Mushroom Block", new Info(BreakInfo::axe(Hardness::RED_MUSHROOM_BLOCK, blastResistance: BlastResistance::RED_MUSHROOM_BLOCK))));

		//finally, the stems
		self::register("mushroom_stem", fn(BID $id) => new MushroomStem($id, "Mushroom Stem", new Info(BreakInfo::axe(Hardness::MUSHROOM_STEM, blastResistance: BlastResistance::MUSHROOM_STEM))));
		self::register("all_sided_mushroom_stem", fn(BID $id) => new MushroomStem($id, "All Sided Mushroom Stem", new Info(BreakInfo::axe(Hardness::MUSHROOM_STEM, blastResistance: BlastResistance::MUSHROOM_STEM))));
	}

	private static function registerElements() : void{
		$instaBreak = new Info(BreakInfo::instant());
		self::register("element_zero", fn(BID $id) => new Opaque($id, "???", new Info(new BreakInfo(Hardness::ELEMENT_0, blastResistance: BlastResistance::ELEMENT_0))));

		$register = fn(string $name, string $displayName, string $symbol, int $atomicWeight, int $group, float $hardness, float $blastResistance) =>
			self::register("element_$name", fn(BID $id) => new Element($id, $displayName, $instaBreak, $symbol, $atomicWeight, $group));

		$register("hydrogen", "Hydrogen", "h", 1, 5, Hardness::ELEMENT_1, BlastResistance::ELEMENT_1);
		$register("helium", "Helium", "he", 2, 7, Hardness::ELEMENT_2, BlastResistance::ELEMENT_2);
		$register("lithium", "Lithium", "li", 3, 0, Hardness::ELEMENT_3, BlastResistance::ELEMENT_3);
		$register("beryllium", "Beryllium", "be", 4, 1, Hardness::ELEMENT_4, BlastResistance::ELEMENT_4);
		$register("boron", "Boron", "b", 5, 4, Hardness::ELEMENT_5, BlastResistance::ELEMENT_5);
		$register("carbon", "Carbon", "c", 6, 5, Hardness::ELEMENT_6, BlastResistance::ELEMENT_6);
		$register("nitrogen", "Nitrogen", "n", 7, 5, Hardness::ELEMENT_7, BlastResistance::ELEMENT_7);
		$register("oxygen", "Oxygen", "o", 8, 5, Hardness::ELEMENT_8, BlastResistance::ELEMENT_8);
		$register("fluorine", "Fluorine", "f", 9, 6, Hardness::ELEMENT_9, BlastResistance::ELEMENT_9);
		$register("neon", "Neon", "ne", 10, 7, Hardness::ELEMENT_10, BlastResistance::ELEMENT_10);
		$register("sodium", "Sodium", "na", 11, 0, Hardness::ELEMENT_11, BlastResistance::ELEMENT_11);
		$register("magnesium", "Magnesium", "mg", 12, 1, Hardness::ELEMENT_12, BlastResistance::ELEMENT_12);
		$register("aluminum", "Aluminum", "al", 13, 3, Hardness::ELEMENT_13, BlastResistance::ELEMENT_13);
		$register("silicon", "Silicon", "si", 14, 4, Hardness::ELEMENT_14, BlastResistance::ELEMENT_14);
		$register("phosphorus", "Phosphorus", "p", 15, 5, Hardness::ELEMENT_15, BlastResistance::ELEMENT_15);
		$register("sulfur", "Sulfur", "s", 16, 5, Hardness::ELEMENT_16, BlastResistance::ELEMENT_16);
		$register("chlorine", "Chlorine", "cl", 17, 6, Hardness::ELEMENT_17, BlastResistance::ELEMENT_17);
		$register("argon", "Argon", "ar", 18, 7, Hardness::ELEMENT_18, BlastResistance::ELEMENT_18);
		$register("potassium", "Potassium", "k", 19, 0, Hardness::ELEMENT_19, BlastResistance::ELEMENT_19);
		$register("calcium", "Calcium", "ca", 20, 1, Hardness::ELEMENT_20, BlastResistance::ELEMENT_20);
		$register("scandium", "Scandium", "sc", 21, 2, Hardness::ELEMENT_21, BlastResistance::ELEMENT_21);
		$register("titanium", "Titanium", "ti", 22, 2, Hardness::ELEMENT_22, BlastResistance::ELEMENT_22);
		$register("vanadium", "Vanadium", "v", 23, 2, Hardness::ELEMENT_23, BlastResistance::ELEMENT_23);
		$register("chromium", "Chromium", "cr", 24, 2, Hardness::ELEMENT_24, BlastResistance::ELEMENT_24);
		$register("manganese", "Manganese", "mn", 25, 2, Hardness::ELEMENT_25, BlastResistance::ELEMENT_25);
		$register("iron", "Iron", "fe", 26, 2, Hardness::ELEMENT_26, BlastResistance::ELEMENT_26);
		$register("cobalt", "Cobalt", "co", 27, 2, Hardness::ELEMENT_27, BlastResistance::ELEMENT_27);
		$register("nickel", "Nickel", "ni", 28, 2, Hardness::ELEMENT_28, BlastResistance::ELEMENT_28);
		$register("copper", "Copper", "cu", 29, 2, Hardness::ELEMENT_29, BlastResistance::ELEMENT_29);
		$register("zinc", "Zinc", "zn", 30, 2, Hardness::ELEMENT_30, BlastResistance::ELEMENT_30);
		$register("gallium", "Gallium", "ga", 31, 3, Hardness::ELEMENT_31, BlastResistance::ELEMENT_31);
		$register("germanium", "Germanium", "ge", 32, 4, Hardness::ELEMENT_32, BlastResistance::ELEMENT_32);
		$register("arsenic", "Arsenic", "as", 33, 4, Hardness::ELEMENT_33, BlastResistance::ELEMENT_33);
		$register("selenium", "Selenium", "se", 34, 5, Hardness::ELEMENT_34, BlastResistance::ELEMENT_34);
		$register("bromine", "Bromine", "br", 35, 6, Hardness::ELEMENT_35, BlastResistance::ELEMENT_35);
		$register("krypton", "Krypton", "kr", 36, 7, Hardness::ELEMENT_36, BlastResistance::ELEMENT_36);
		$register("rubidium", "Rubidium", "rb", 37, 0, Hardness::ELEMENT_37, BlastResistance::ELEMENT_37);
		$register("strontium", "Strontium", "sr", 38, 1, Hardness::ELEMENT_38, BlastResistance::ELEMENT_38);
		$register("yttrium", "Yttrium", "y", 39, 2, Hardness::ELEMENT_39, BlastResistance::ELEMENT_39);
		$register("zirconium", "Zirconium", "zr", 40, 2, Hardness::ELEMENT_40, BlastResistance::ELEMENT_40);
		$register("niobium", "Niobium", "nb", 41, 2, Hardness::ELEMENT_41, BlastResistance::ELEMENT_41);
		$register("molybdenum", "Molybdenum", "mo", 42, 2, Hardness::ELEMENT_42, BlastResistance::ELEMENT_42);
		$register("technetium", "Technetium", "tc", 43, 2, Hardness::ELEMENT_43, BlastResistance::ELEMENT_43);
		$register("ruthenium", "Ruthenium", "ru", 44, 2, Hardness::ELEMENT_44, BlastResistance::ELEMENT_44);
		$register("rhodium", "Rhodium", "rh", 45, 2, Hardness::ELEMENT_45, BlastResistance::ELEMENT_45);
		$register("palladium", "Palladium", "pd", 46, 2, Hardness::ELEMENT_46, BlastResistance::ELEMENT_46);
		$register("silver", "Silver", "ag", 47, 2, Hardness::ELEMENT_47, BlastResistance::ELEMENT_47);
		$register("cadmium", "Cadmium", "cd", 48, 2, Hardness::ELEMENT_48, BlastResistance::ELEMENT_48);
		$register("indium", "Indium", "in", 49, 3, Hardness::ELEMENT_49, BlastResistance::ELEMENT_49);
		$register("tin", "Tin", "sn", 50, 3, Hardness::ELEMENT_50, BlastResistance::ELEMENT_50);
		$register("antimony", "Antimony", "sb", 51, 4, Hardness::ELEMENT_51, BlastResistance::ELEMENT_51);
		$register("tellurium", "Tellurium", "te", 52, 4, Hardness::ELEMENT_52, BlastResistance::ELEMENT_52);
		$register("iodine", "Iodine", "i", 53, 6, Hardness::ELEMENT_53, BlastResistance::ELEMENT_53);
		$register("xenon", "Xenon", "xe", 54, 7, Hardness::ELEMENT_54, BlastResistance::ELEMENT_54);
		$register("cesium", "Cesium", "cs", 55, 0, Hardness::ELEMENT_55, BlastResistance::ELEMENT_55);
		$register("barium", "Barium", "ba", 56, 1, Hardness::ELEMENT_56, BlastResistance::ELEMENT_56);
		$register("lanthanum", "Lanthanum", "la", 57, 8, Hardness::ELEMENT_57, BlastResistance::ELEMENT_57);
		$register("cerium", "Cerium", "ce", 58, 8, Hardness::ELEMENT_58, BlastResistance::ELEMENT_58);
		$register("praseodymium", "Praseodymium", "pr", 59, 8, Hardness::ELEMENT_59, BlastResistance::ELEMENT_59);
		$register("neodymium", "Neodymium", "nd", 60, 8, Hardness::ELEMENT_60, BlastResistance::ELEMENT_60);
		$register("promethium", "Promethium", "pm", 61, 8, Hardness::ELEMENT_61, BlastResistance::ELEMENT_61);
		$register("samarium", "Samarium", "sm", 62, 8, Hardness::ELEMENT_62, BlastResistance::ELEMENT_62);
		$register("europium", "Europium", "eu", 63, 8, Hardness::ELEMENT_63, BlastResistance::ELEMENT_63);
		$register("gadolinium", "Gadolinium", "gd", 64, 8, Hardness::ELEMENT_64, BlastResistance::ELEMENT_64);
		$register("terbium", "Terbium", "tb", 65, 8, Hardness::ELEMENT_65, BlastResistance::ELEMENT_65);
		$register("dysprosium", "Dysprosium", "dy", 66, 8, Hardness::ELEMENT_66, BlastResistance::ELEMENT_66);
		$register("holmium", "Holmium", "ho", 67, 8, Hardness::ELEMENT_67, BlastResistance::ELEMENT_67);
		$register("erbium", "Erbium", "er", 68, 8, Hardness::ELEMENT_68, BlastResistance::ELEMENT_68);
		$register("thulium", "Thulium", "tm", 69, 8, Hardness::ELEMENT_69, BlastResistance::ELEMENT_69);
		$register("ytterbium", "Ytterbium", "yb", 70, 8, Hardness::ELEMENT_70, BlastResistance::ELEMENT_70);
		$register("lutetium", "Lutetium", "lu", 71, 8, Hardness::ELEMENT_71, BlastResistance::ELEMENT_71);
		$register("hafnium", "Hafnium", "hf", 72, 2, Hardness::ELEMENT_72, BlastResistance::ELEMENT_72);
		$register("tantalum", "Tantalum", "ta", 73, 2, Hardness::ELEMENT_73, BlastResistance::ELEMENT_73);
		$register("tungsten", "Tungsten", "w", 74, 2, Hardness::ELEMENT_74, BlastResistance::ELEMENT_74);
		$register("rhenium", "Rhenium", "re", 75, 2, Hardness::ELEMENT_75, BlastResistance::ELEMENT_75);
		$register("osmium", "Osmium", "os", 76, 2, Hardness::ELEMENT_76, BlastResistance::ELEMENT_76);
		$register("iridium", "Iridium", "ir", 77, 2, Hardness::ELEMENT_77, BlastResistance::ELEMENT_77);
		$register("platinum", "Platinum", "pt", 78, 2, Hardness::ELEMENT_78, BlastResistance::ELEMENT_78);
		$register("gold", "Gold", "au", 79, 2, Hardness::ELEMENT_79, BlastResistance::ELEMENT_79);
		$register("mercury", "Mercury", "hg", 80, 2, Hardness::ELEMENT_80, BlastResistance::ELEMENT_80);
		$register("thallium", "Thallium", "tl", 81, 3, Hardness::ELEMENT_81, BlastResistance::ELEMENT_81);
		$register("lead", "Lead", "pb", 82, 3, Hardness::ELEMENT_82, BlastResistance::ELEMENT_82);
		$register("bismuth", "Bismuth", "bi", 83, 3, Hardness::ELEMENT_83, BlastResistance::ELEMENT_83);
		$register("polonium", "Polonium", "po", 84, 4, Hardness::ELEMENT_84, BlastResistance::ELEMENT_84);
		$register("astatine", "Astatine", "at", 85, 6, Hardness::ELEMENT_85, BlastResistance::ELEMENT_85);
		$register("radon", "Radon", "rn", 86, 7, Hardness::ELEMENT_86, BlastResistance::ELEMENT_86);
		$register("francium", "Francium", "fr", 87, 0, Hardness::ELEMENT_87, BlastResistance::ELEMENT_87);
		$register("radium", "Radium", "ra", 88, 1, Hardness::ELEMENT_88, BlastResistance::ELEMENT_88);
		$register("actinium", "Actinium", "ac", 89, 9, Hardness::ELEMENT_89, BlastResistance::ELEMENT_89);
		$register("thorium", "Thorium", "th", 90, 9, Hardness::ELEMENT_90, BlastResistance::ELEMENT_90);
		$register("protactinium", "Protactinium", "pa", 91, 9, Hardness::ELEMENT_91, BlastResistance::ELEMENT_91);
		$register("uranium", "Uranium", "u", 92, 9, Hardness::ELEMENT_92, BlastResistance::ELEMENT_92);
		$register("neptunium", "Neptunium", "np", 93, 9, Hardness::ELEMENT_93, BlastResistance::ELEMENT_93);
		$register("plutonium", "Plutonium", "pu", 94, 9, Hardness::ELEMENT_94, BlastResistance::ELEMENT_94);
		$register("americium", "Americium", "am", 95, 9, Hardness::ELEMENT_95, BlastResistance::ELEMENT_95);
		$register("curium", "Curium", "cm", 96, 9, Hardness::ELEMENT_96, BlastResistance::ELEMENT_96);
		$register("berkelium", "Berkelium", "bk", 97, 9, Hardness::ELEMENT_97, BlastResistance::ELEMENT_97);
		$register("californium", "Californium", "cf", 98, 9, Hardness::ELEMENT_98, BlastResistance::ELEMENT_98);
		$register("einsteinium", "Einsteinium", "es", 99, 9, Hardness::ELEMENT_99, BlastResistance::ELEMENT_99);
		$register("fermium", "Fermium", "fm", 100, 9, Hardness::ELEMENT_100, BlastResistance::ELEMENT_100);
		$register("mendelevium", "Mendelevium", "md", 101, 9, Hardness::ELEMENT_101, BlastResistance::ELEMENT_101);
		$register("nobelium", "Nobelium", "no", 102, 9, Hardness::ELEMENT_102, BlastResistance::ELEMENT_102);
		$register("lawrencium", "Lawrencium", "lr", 103, 9, Hardness::ELEMENT_103, BlastResistance::ELEMENT_103);
		$register("rutherfordium", "Rutherfordium", "rf", 104, 2, Hardness::ELEMENT_104, BlastResistance::ELEMENT_104);
		$register("dubnium", "Dubnium", "db", 105, 2, Hardness::ELEMENT_105, BlastResistance::ELEMENT_105);
		$register("seaborgium", "Seaborgium", "sg", 106, 2, Hardness::ELEMENT_106, BlastResistance::ELEMENT_106);
		$register("bohrium", "Bohrium", "bh", 107, 2, Hardness::ELEMENT_107, BlastResistance::ELEMENT_107);
		$register("hassium", "Hassium", "hs", 108, 2, Hardness::ELEMENT_108, BlastResistance::ELEMENT_108);
		$register("meitnerium", "Meitnerium", "mt", 109, 2, Hardness::ELEMENT_109, BlastResistance::ELEMENT_109);
		$register("darmstadtium", "Darmstadtium", "ds", 110, 2, Hardness::ELEMENT_110, BlastResistance::ELEMENT_110);
		$register("roentgenium", "Roentgenium", "rg", 111, 2, Hardness::ELEMENT_111, BlastResistance::ELEMENT_111);
		$register("copernicium", "Copernicium", "cn", 112, 2, Hardness::ELEMENT_112, BlastResistance::ELEMENT_112);
		$register("nihonium", "Nihonium", "nh", 113, 3, Hardness::ELEMENT_113, BlastResistance::ELEMENT_113);
		$register("flerovium", "Flerovium", "fl", 114, 3, Hardness::ELEMENT_114, BlastResistance::ELEMENT_114);
		$register("moscovium", "Moscovium", "mc", 115, 3, Hardness::ELEMENT_115, BlastResistance::ELEMENT_115);
		$register("livermorium", "Livermorium", "lv", 116, 3, Hardness::ELEMENT_116, BlastResistance::ELEMENT_116);
		$register("tennessine", "Tennessine", "ts", 117, 6, Hardness::ELEMENT_117, BlastResistance::ELEMENT_117);
		$register("oganesson", "Oganesson", "og", 118, 7, Hardness::ELEMENT_118, BlastResistance::ELEMENT_118);
	}

	private static function registerOres() : void{
		self::register("coal_ore", fn(BID $id) => new CoalOre($id, "Coal Ore", new Info(BreakInfo::pickaxe(Hardness::COAL_ORE, ToolTier::WOOD, BlastResistance::COAL_ORE))));
		self::register("copper_ore", fn(BID $id) => new CopperOre($id, "Copper Ore", new Info(BreakInfo::pickaxe(Hardness::COPPER_ORE, ToolTier::STONE, BlastResistance::COPPER_ORE))));
		self::register("diamond_ore", fn(BID $id) => new DiamondOre($id, "Diamond Ore", new Info(BreakInfo::pickaxe(Hardness::DIAMOND_ORE, ToolTier::IRON, BlastResistance::DIAMOND_ORE))));
		self::register("emerald_ore", fn(BID $id) => new EmeraldOre($id, "Emerald Ore", new Info(BreakInfo::pickaxe(Hardness::EMERALD_ORE, ToolTier::IRON, BlastResistance::EMERALD_ORE))));
		self::register("gold_ore", fn(BID $id) => new GoldOre($id, "Gold Ore", new Info(BreakInfo::pickaxe(Hardness::GOLD_ORE, ToolTier::IRON, BlastResistance::GOLD_ORE))));
		self::register("iron_ore", fn(BID $id) => new IronOre($id, "Iron Ore", new Info(BreakInfo::pickaxe(Hardness::IRON_ORE, ToolTier::STONE, BlastResistance::IRON_ORE))));
		self::register("lapis_lazuli_ore", fn(BID $id) => new LapisOre($id, "Lapis Lazuli Ore", new Info(BreakInfo::pickaxe(Hardness::LAPIS_ORE, ToolTier::STONE, BlastResistance::LAPIS_ORE))));
		self::register("redstone_ore", fn(BID $id) => new RedstoneOre($id, "Redstone Ore", new Info(BreakInfo::pickaxe(Hardness::REDSTONE_ORE, ToolTier::IRON, BlastResistance::REDSTONE_ORE))));

		self::register("deepslate_coal_ore", fn(BID $id) => new CoalOre($id, "Deepslate Coal Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_COAL_ORE, ToolTier::WOOD, BlastResistance::DEEPSLATE_COAL_ORE))));
		self::register("deepslate_copper_ore", fn(BID $id) => new CopperOre($id, "Deepslate Copper Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_COPPER_ORE, ToolTier::STONE, BlastResistance::DEEPSLATE_COPPER_ORE))));
		self::register("deepslate_diamond_ore", fn(BID $id) => new DiamondOre($id, "Deepslate Diamond Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_DIAMOND_ORE, ToolTier::IRON, BlastResistance::DEEPSLATE_DIAMOND_ORE))));
		self::register("deepslate_emerald_ore", fn(BID $id) => new EmeraldOre($id, "Deepslate Emerald Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_EMERALD_ORE, ToolTier::IRON, BlastResistance::DEEPSLATE_EMERALD_ORE))));
		self::register("deepslate_gold_ore", fn(BID $id) => new GoldOre($id, "Deepslate Gold Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_GOLD_ORE, ToolTier::IRON, BlastResistance::DEEPSLATE_GOLD_ORE))));
		self::register("deepslate_iron_ore", fn(BID $id) => new IronOre($id, "Deepslate Iron Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_IRON_ORE, ToolTier::STONE, BlastResistance::DEEPSLATE_IRON_ORE))));
		self::register("deepslate_lapis_lazuli_ore", fn(BID $id) => new LapisOre($id, "Deepslate Lapis Lazuli Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_LAPIS_ORE, ToolTier::STONE, BlastResistance::DEEPSLATE_LAPIS_ORE))));
		self::register("deepslate_redstone_ore", fn(BID $id) => new RedstoneOre($id, "Deepslate Redstone Ore", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_REDSTONE_ORE, ToolTier::IRON, BlastResistance::DEEPSLATE_REDSTONE_ORE))));

		self::register("nether_quartz_ore", fn(BID $id) => new NetherQuartzOre($id, "Nether Quartz Ore", new Info(BreakInfo::pickaxe(Hardness::QUARTZ_ORE, ToolTier::WOOD, BlastResistance::QUARTZ_ORE))));
		self::register("nether_gold_ore", fn(BID $id) => new NetherGoldOre($id, "Nether Gold Ore", new Info(BreakInfo::pickaxe(Hardness::NETHER_GOLD_ORE, ToolTier::WOOD, BlastResistance::NETHER_GOLD_ORE))));
	}

	private static function registerCraftingTables() : void{
		self::register("cartography_table", fn(BID $id) => new CartographyTable($id, "Cartography Table", new Info(BreakInfo::axe(Hardness::CARTOGRAPHY_TABLE, blastResistance: BlastResistance::CARTOGRAPHY_TABLE))));
		self::register("crafting_table", fn(BID $id) => new CraftingTable($id, "Crafting Table", new Info(BreakInfo::axe(Hardness::CRAFTING_TABLE, blastResistance: BlastResistance::CRAFTING_TABLE))));
		self::register("fletching_table", fn(BID $id) => new FletchingTable($id, "Fletching Table", new Info(BreakInfo::axe(Hardness::FLETCHING_TABLE, blastResistance: BlastResistance::FLETCHING_TABLE))));
		self::register("loom", fn(BID $id) => new Loom($id, "Loom", new Info(BreakInfo::axe(Hardness::LOOM, blastResistance: BlastResistance::LOOM))));
		self::register("smithing_table", fn(BID $id) => new SmithingTable($id, "Smithing Table", new Info(BreakInfo::axe(Hardness::SMITHING_TABLE, blastResistance: BlastResistance::SMITHING_TABLE))));
	}

	private static function registerChorusBlocks() : void{
		self::register("chorus_plant", fn(BID $id) => new ChorusPlant($id, "Chorus Plant", new Info(BreakInfo::axe(Hardness::CHORUS_PLANT, blastResistance: BlastResistance::CHORUS_PLANT))));
		self::register("chorus_flower", fn(BID $id) => new ChorusFlower($id, "Chorus Flower", new Info(BreakInfo::axe(Hardness::CHORUS_FLOWER, blastResistance: BlastResistance::CHORUS_FLOWER))));
	}

	private static function registerBlocksR13() : void{
		self::register("light", fn(BID $id) => new Light($id, "Light Block", new Info(BreakInfo::indestructible(BlastResistance::LIGHT_BLOCK_0))));
		self::register("wither_rose", fn(BID $id) => new WitherRose($id, "Wither Rose", new Info(new BreakInfo(Hardness::WITHER_ROSE, blastResistance: BlastResistance::WITHER_ROSE), [Tags::POTTABLE_PLANTS])));
	}

	private static function registerBlocksR14() : void{
		self::register("honeycomb", fn(BID $id) => new Opaque($id, "Honeycomb Block", new Info(new BreakInfo(Hardness::HONEYCOMB_BLOCK, blastResistance: BlastResistance::HONEYCOMB_BLOCK))));
	}

	private static function registerBlocksR16() : void{
		self::register("ancient_debris", fn(BID $id) => new class($id, "Ancient Debris", new Info(BreakInfo::pickaxe(Hardness::ANCIENT_DEBRIS, ToolTier::DIAMOND, BlastResistance::ANCIENT_DEBRIS))) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});
		$netheriteBreakInfo = new Info(BreakInfo::pickaxe(Hardness::NETHERITE_BLOCK, ToolTier::DIAMOND, BlastResistance::NETHERITE_BLOCK));
		self::register("netherite", fn(BID $id) => new class($id, "Netherite Block", $netheriteBreakInfo) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});

		self::register("basalt", fn(BID $id) => new SimplePillar($id, "Basalt", new Info(BreakInfo::pickaxe(Hardness::BASALT, ToolTier::WOOD, BlastResistance::BASALT))));
		self::register("polished_basalt", fn(BID $id) => new SimplePillar($id, "Polished Basalt", new Info(BreakInfo::pickaxe(Hardness::POLISHED_BASALT, ToolTier::WOOD, BlastResistance::POLISHED_BASALT))));
		self::register("smooth_basalt", fn(BID $id) => new Opaque($id, "Smooth Basalt", new Info(BreakInfo::pickaxe(Hardness::SMOOTH_BASALT, ToolTier::WOOD, BlastResistance::SMOOTH_BASALT))));

		self::register("blackstone", fn(BID $id) => new Opaque($id, "Blackstone", new Info(BreakInfo::pickaxe(Hardness::BLACKSTONE, ToolTier::WOOD, BlastResistance::BLACKSTONE))));
		self::register("blackstone_slab", fn(BID $id) => new Slab($id, "Blackstone", new Info(BreakInfo::pickaxe(Hardness::BLACKSTONE_SLAB, ToolTier::WOOD, BlastResistance::BLACKSTONE_SLAB))));
		self::register("blackstone_stairs", fn(BID $id) => new Stair($id, "Blackstone Stairs", new Info(BreakInfo::pickaxe(Hardness::BLACKSTONE_STAIRS, ToolTier::WOOD, BlastResistance::BLACKSTONE_STAIRS))));
		self::register("blackstone_wall", fn(BID $id) => new Wall($id, "Blackstone Wall", new Info(BreakInfo::pickaxe(Hardness::BLACKSTONE_WALL, ToolTier::WOOD, BlastResistance::BLACKSTONE_WALL))));

		self::register("gilded_blackstone", fn(BID $id) => new GildedBlackstone($id, "Gilded Blackstone", new Info(BreakInfo::pickaxe(Hardness::GILDED_BLACKSTONE, ToolTier::WOOD, BlastResistance::GILDED_BLACKSTONE))));

		$prefix = fn(string $thing) => "Polished Blackstone" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone", fn(BID $id) => new Opaque($id, $prefix(""), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE))));
		self::register("polished_blackstone_button", fn(BID $id) => new StoneButton($id, $prefix("Button"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_BUTTON, blastResistance: BlastResistance::POLISHED_BLACKSTONE_BUTTON))));
		self::register("polished_blackstone_pressure_plate", fn(BID $id) => new StonePressurePlate($id, $prefix("Pressure Plate"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_PRESSURE_PLATE, blastResistance: BlastResistance::POLISHED_BLACKSTONE_PRESSURE_PLATE)), 20));
		self::register("polished_blackstone_slab", fn(BID $id) => new Slab($id, $prefix(""), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_SLAB))));
		self::register("polished_blackstone_stairs", fn(BID $id) => new Stair($id, $prefix("Stairs"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_STAIRS))));
		self::register("polished_blackstone_wall", fn(BID $id) => new Wall($id, $prefix("Wall"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_WALL, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_WALL))));
		self::register("chiseled_polished_blackstone", fn(BID $id) => new Opaque($id, "Chiseled Polished Blackstone", new Info(BreakInfo::pickaxe(Hardness::CHISELED_POLISHED_BLACKSTONE, ToolTier::WOOD, BlastResistance::CHISELED_POLISHED_BLACKSTONE))));

		$prefix = fn(string $thing) => "Polished Blackstone Brick" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone_bricks", fn(BID $id) => new Opaque($id, "Polished Blackstone Bricks", new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_BRICKS, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_BRICKS))));
		self::register("polished_blackstone_brick_slab", fn(BID $id) => new Slab($id, "Polished Blackstone Brick", new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_BRICK_SLAB))));
		self::register("polished_blackstone_brick_stairs", fn(BID $id) => new Stair($id, $prefix("Stairs"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_BRICK_STAIRS))));
		self::register("polished_blackstone_brick_wall", fn(BID $id) => new Wall($id, $prefix("Wall"), new Info(BreakInfo::pickaxe(Hardness::POLISHED_BLACKSTONE_BRICK_WALL, ToolTier::WOOD, BlastResistance::POLISHED_BLACKSTONE_BRICK_WALL))));
		self::register("cracked_polished_blackstone_bricks", fn(BID $id) => new Opaque($id, "Cracked Polished Blackstone Bricks", new Info(BreakInfo::pickaxe(Hardness::CRACKED_POLISHED_BLACKSTONE_BRICKS, ToolTier::WOOD, BlastResistance::CRACKED_POLISHED_BLACKSTONE_BRICKS))));

		self::register("soul_torch", fn(BID $id) => new Torch($id, "Soul Torch", new Info(new BreakInfo(Hardness::SOUL_TORCH, blastResistance: BlastResistance::SOUL_TORCH))));
		self::register("soul_fire", fn(BID $id) => new SoulFire($id, "Soul Fire", new Info(new BreakInfo(Hardness::SOUL_FIRE, blastResistance: BlastResistance::SOUL_FIRE), [Tags::FIRE])));

		self::register("soul_soil", fn(BID $id) => new Opaque($id, "Soul Soil", new Info(BreakInfo::shovel(Hardness::SOUL_SOIL, blastResistance: BlastResistance::SOUL_SOIL))));

		self::register("shroomlight", fn(BID $id) => new class($id, "Shroomlight", new Info(new BreakInfo(Hardness::SHROOMLIGHT, ToolType::HOE, BlastResistance::SHROOMLIGHT))) extends Opaque{
			public function getLightLevel() : int{ return 15; }
		});

		self::register("warped_wart_block", fn(BID $id) => new Opaque($id, "Warped Wart Block", new Info(new BreakInfo(Hardness::WARPED_WART_BLOCK, ToolType::HOE, BlastResistance::WARPED_WART_BLOCK))));
		self::register("crying_obsidian", fn(BID $id) => new class($id, "Crying Obsidian", new Info(BreakInfo::pickaxe(Hardness::CRYING_OBSIDIAN, ToolTier::DIAMOND, BlastResistance::CRYING_OBSIDIAN))) extends Opaque{
			public function getLightLevel() : int{ return 10;}
		});

		self::register("twisting_vines", fn(BID $id) => new NetherVines($id, "Twisting Vines", new Info(new BreakInfo(Hardness::TWISTING_VINES, blastResistance: BlastResistance::TWISTING_VINES)), Facing::UP));
		self::register("weeping_vines", fn(BID $id) => new NetherVines($id, "Weeping Vines", new Info(new BreakInfo(Hardness::WEEPING_VINES, blastResistance: BlastResistance::WEEPING_VINES)), Facing::DOWN));

		self::register("crimson_roots", fn(BID $id) => new NetherRoots($id, "Crimson Roots", new Info(new BreakInfo(Hardness::CRIMSON_ROOTS, blastResistance: BlastResistance::CRIMSON_ROOTS), [Tags::POTTABLE_PLANTS])));
		self::register("warped_roots", fn(BID $id) => new NetherRoots($id, "Warped Roots", new Info(new BreakInfo(Hardness::CRIMSON_ROOTS, blastResistance: BlastResistance::CRIMSON_ROOTS), [Tags::POTTABLE_PLANTS])));

		self::register("chain", fn(BID $id) => new Chain($id, "Chain", new Info(BreakInfo::pickaxe(Hardness::CHAIN, ToolTier::WOOD, BlastResistance::CHAIN))));
	}

	private static function registerBlocksR17() : void{
		//in java this can be acquired using any tool - seems to be a parity issue in bedrock
		self::register("amethyst", fn(BID $id) => new class($id, "Amethyst", new Info(BreakInfo::pickaxe(Hardness::AMETHYST_BLOCK, ToolTier::WOOD, BlastResistance::AMETHYST_BLOCK))) extends Opaque{
			use AmethystTrait;
		});
		self::register("budding_amethyst", fn(BID $id) => new BuddingAmethyst($id, "Budding Amethyst", new Info(BreakInfo::pickaxe(Hardness::BUDDING_AMETHYST, ToolTier::WOOD, BlastResistance::BUDDING_AMETHYST))));
		self::register("amethyst_cluster", fn(BID $id) => new AmethystCluster($id, "Amethyst Cluster", new Info(BreakInfo::pickaxe(Hardness::AMETHYST_CLUSTER, ToolTier::WOOD, BlastResistance::AMETHYST_CLUSTER))));

		self::register("calcite", fn(BID $id) => new Opaque($id, "Calcite", new Info(BreakInfo::pickaxe(Hardness::CALCITE, ToolTier::WOOD, BlastResistance::CALCITE))));

		self::register("raw_copper", fn(BID $id) => new Opaque($id, "Raw Copper Block", new Info(BreakInfo::pickaxe(Hardness::RAW_COPPER_BLOCK, ToolTier::STONE, BlastResistance::RAW_COPPER_BLOCK))));
		self::register("raw_gold", fn(BID $id) => new Opaque($id, "Raw Gold Block", new Info(BreakInfo::pickaxe(Hardness::RAW_GOLD_BLOCK, ToolTier::IRON, BlastResistance::RAW_GOLD_BLOCK))));
		self::register("raw_iron", fn(BID $id) => new Opaque($id, "Raw Iron Block", new Info(BreakInfo::pickaxe(Hardness::RAW_IRON_BLOCK, ToolTier::STONE, BlastResistance::RAW_IRON_BLOCK))));

		self::register("deepslate", fn(BID $id) => new class($id, "Deepslate", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE, ToolTier::WOOD, BlastResistance::DEEPSLATE))) extends SimplePillar{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [VanillaBlocks::COBBLED_DEEPSLATE()->asItem()];
			}

			public function isAffectedBySilkTouch() : bool{
				return true;
			}
		});

		self::register("chiseled_deepslate", fn(BID $id) => new Opaque($id, "Chiseled Deepslate", new Info(BreakInfo::pickaxe(Hardness::CHISELED_DEEPSLATE, ToolTier::WOOD, BlastResistance::CHISELED_DEEPSLATE))));

		self::register("deepslate_bricks", fn(BID $id) => new Opaque($id, "Deepslate Bricks", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_BRICKS, ToolTier::WOOD, BlastResistance::DEEPSLATE_BRICKS))));
		self::register("deepslate_brick_slab", fn(BID $id) => new Slab($id, "Deepslate Brick", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_BRICK_SLAB, ToolTier::WOOD, BlastResistance::DEEPSLATE_BRICK_SLAB))));
		self::register("deepslate_brick_stairs", fn(BID $id) => new Stair($id, "Deepslate Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::DEEPSLATE_BRICK_STAIRS))));
		self::register("deepslate_brick_wall", fn(BID $id) => new Wall($id, "Deepslate Brick Wall", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_BRICK_WALL, ToolTier::WOOD, BlastResistance::DEEPSLATE_BRICK_WALL))));
		self::register("cracked_deepslate_bricks", fn(BID $id) => new Opaque($id, "Cracked Deepslate Bricks", new Info(BreakInfo::pickaxe(Hardness::CRACKED_DEEPSLATE_BRICKS, ToolTier::WOOD, BlastResistance::CRACKED_DEEPSLATE_BRICKS))));

		self::register("deepslate_tiles", fn(BID $id) => new Opaque($id, "Deepslate Tiles", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_TILES, ToolTier::WOOD, BlastResistance::DEEPSLATE_TILES))));
		self::register("deepslate_tile_slab", fn(BID $id) => new Slab($id, "Deepslate Tile", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_TILE_SLAB, ToolTier::WOOD, BlastResistance::DEEPSLATE_TILE_SLAB))));
		self::register("deepslate_tile_stairs", fn(BID $id) => new Stair($id, "Deepslate Tile Stairs", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_TILE_STAIRS, ToolTier::WOOD, BlastResistance::DEEPSLATE_TILE_STAIRS))));
		self::register("deepslate_tile_wall", fn(BID $id) => new Wall($id, "Deepslate Tile Wall", new Info(BreakInfo::pickaxe(Hardness::DEEPSLATE_TILE_WALL, ToolTier::WOOD, BlastResistance::DEEPSLATE_TILE_WALL))));
		self::register("cracked_deepslate_tiles", fn(BID $id) => new Opaque($id, "Cracked Deepslate Tiles", new Info(BreakInfo::pickaxe(Hardness::CRACKED_DEEPSLATE_TILES, ToolTier::WOOD, BlastResistance::CRACKED_DEEPSLATE_TILES))));

		self::register("cobbled_deepslate", fn(BID $id) => new Opaque($id, "Cobbled Deepslate", new Info(BreakInfo::pickaxe(Hardness::COBBLED_DEEPSLATE, ToolTier::WOOD, BlastResistance::COBBLED_DEEPSLATE))));
		self::register("cobbled_deepslate_slab", fn(BID $id) => new Slab($id, "Cobbled Deepslate", new Info(BreakInfo::pickaxe(Hardness::COBBLED_DEEPSLATE_SLAB, ToolTier::WOOD, BlastResistance::COBBLED_DEEPSLATE_SLAB))));
		self::register("cobbled_deepslate_stairs", fn(BID $id) => new Stair($id, "Cobbled Deepslate Stairs", new Info(BreakInfo::pickaxe(Hardness::COBBLED_DEEPSLATE_STAIRS, ToolTier::WOOD, BlastResistance::COBBLED_DEEPSLATE_STAIRS))));
		self::register("cobbled_deepslate_wall", fn(BID $id) => new Wall($id, "Cobbled Deepslate Wall", new Info(BreakInfo::pickaxe(Hardness::COBBLED_DEEPSLATE_WALL, ToolTier::WOOD, BlastResistance::COBBLED_DEEPSLATE_WALL))));

		self::register("polished_deepslate", fn(BID $id) => new Opaque($id, "Polished Deepslate", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DEEPSLATE, ToolTier::WOOD, BlastResistance::POLISHED_DEEPSLATE))));
		self::register("polished_deepslate_slab", fn(BID $id) => new Slab($id, "Polished Deepslate", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DEEPSLATE_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_DEEPSLATE_SLAB))));
		self::register("polished_deepslate_stairs", fn(BID $id) => new Stair($id, "Polished Deepslate Stairs", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DEEPSLATE_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_DEEPSLATE_STAIRS))));
		self::register("polished_deepslate_wall", fn(BID $id) => new Wall($id, "Polished Deepslate Wall", new Info(BreakInfo::pickaxe(Hardness::POLISHED_DEEPSLATE_WALL, ToolTier::WOOD, BlastResistance::POLISHED_DEEPSLATE_WALL))));

		self::register("tinted_glass", fn(BID $id) => new TintedGlass($id, "Tinted Glass", new Info(new BreakInfo(Hardness::TINTED_GLASS, blastResistance: BlastResistance::TINTED_GLASS))));

		self::register("lightning_rod", fn(BID $id) => new LightningRod($id, "Lightning Rod", new Info(BreakInfo::pickaxe(Hardness::LIGHTNING_ROD, ToolTier::STONE, BlastResistance::LIGHTNING_ROD))));

		self::register("copper", fn(BID $id) => new Copper($id, "Copper Block", new Info(BreakInfo::pickaxe(Hardness::COPPER_BLOCK, ToolTier::STONE, BlastResistance::COPPER_BLOCK))));
		self::register("chiseled_copper", fn(BID $id) => new Copper($id, "Chiseled Copper", new Info(BreakInfo::pickaxe(Hardness::CHISELED_COPPER, ToolTier::STONE, BlastResistance::CHISELED_COPPER))));
		self::register("copper_grate", fn(BID $id) => new CopperGrate($id, "Copper Grate", new Info(BreakInfo::pickaxe(Hardness::COPPER_GRATE, ToolTier::STONE, BlastResistance::COPPER_GRATE))));
		self::register("cut_copper", fn(BID $id) => new Copper($id, "Cut Copper Block", new Info(BreakInfo::pickaxe(Hardness::CUT_COPPER, ToolTier::STONE, BlastResistance::CUT_COPPER))));
		self::register("cut_copper_slab", fn(BID $id) => new CopperSlab($id, "Cut Copper Slab", new Info(BreakInfo::pickaxe(Hardness::CUT_COPPER_SLAB, ToolTier::STONE, BlastResistance::CUT_COPPER_SLAB))));
		self::register("cut_copper_stairs", fn(BID $id) => new CopperStairs($id, "Cut Copper Stairs", new Info(BreakInfo::pickaxe(Hardness::CUT_COPPER_STAIRS, ToolTier::STONE, BlastResistance::CUT_COPPER_STAIRS))));
		self::register("copper_bulb", fn(BID $id) => new CopperBulb($id, "Copper Bulb", new Info(BreakInfo::pickaxe(Hardness::COPPER_BULB, ToolTier::STONE, BlastResistance::COPPER_BULB))));

		self::register("copper_door", fn(BID $id) => new CopperDoor($id, "Copper Door", new Info(BreakInfo::pickaxe(Hardness::COPPER_DOOR, blastResistance: BlastResistance::COPPER_DOOR))));
		self::register("copper_trapdoor", fn(BID $id) => new CopperTrapdoor($id, "Copper Trapdoor", new Info(BreakInfo::pickaxe(Hardness::COPPER_TRAPDOOR, ToolTier::STONE, BlastResistance::COPPER_TRAPDOOR))));

		self::register("candle", fn(BID $id) => new Candle($id, "Candle", new Info(new BreakInfo(Hardness::CANDLE, blastResistance: BlastResistance::CANDLE))));
		self::register("dyed_candle", fn(BID $id) => new DyedCandle($id, "Dyed Candle", new Info(new BreakInfo(Hardness::WHITE_CANDLE, blastResistance: BlastResistance::WHITE_CANDLE))));

		self::register("cake_with_candle", fn(BID $id) => new CakeWithCandle($id, "Cake With Candle", new Info(new BreakInfo(Hardness::CANDLE_CAKE, blastResistance: BlastResistance::CANDLE_CAKE))));
		self::register("cake_with_dyed_candle", fn(BID $id) => new CakeWithDyedCandle($id, "Cake With Dyed Candle", new Info(new BreakInfo(Hardness::WHITE_CANDLE_CAKE, blastResistance: BlastResistance::WHITE_CANDLE_CAKE))));

		self::register("hanging_roots", fn(BID $id) => new HangingRoots($id, "Hanging Roots", new Info(new BreakInfo(Hardness::HANGING_ROOTS, ToolType::SHEARS, 1, BlastResistance::HANGING_ROOTS))));

		self::register("cave_vines", fn(BID $id) => new CaveVines($id, "Cave Vines", new Info(new BreakInfo(Hardness::CAVE_VINES, blastResistance: BlastResistance::CAVE_VINES))));

		self::register("small_dripleaf", fn(BID $id) => new SmallDripleaf($id, "Small Dripleaf", new Info(new BreakInfo(Hardness::SMALL_DRIPLEAF_BLOCK, ToolType::SHEARS, 1, BlastResistance::SMALL_DRIPLEAF_BLOCK))));
		self::register("big_dripleaf_head", fn(BID $id) => new BigDripleafHead($id, "Big Dripleaf", new Info(new BreakInfo(Hardness::BIG_DRIPLEAF, blastResistance: BlastResistance::BIG_DRIPLEAF))));
		self::register("big_dripleaf_stem", fn(BID $id) => new BigDripleafStem($id, "Big Dripleaf Stem", new Info(new BreakInfo(Hardness::BIG_DRIPLEAF, blastResistance: BlastResistance::BIG_DRIPLEAF))));
	}

	private static function registerBlocksR18() : void{
		self::register("spore_blossom", fn(BID $id) => new SporeBlossom($id, "Spore Blossom", new Info(new BreakInfo(Hardness::SPORE_BLOSSOM, blastResistance: BlastResistance::SPORE_BLOSSOM))));
	}

	private static function registerMudBlocks() : void{
		self::register("mud", fn(BID $id) => new Opaque($id, "Mud", new Info(BreakInfo::shovel(Hardness::MUD, blastResistance: BlastResistance::MUD), [Tags::MUD])));
		self::register("packed_mud", fn(BID $id) => new Opaque($id, "Packed Mud", new Info(BreakInfo::pickaxe(Hardness::PACKED_MUD, null, BlastResistance::PACKED_MUD))));

		self::register("mud_bricks", fn(BID $id) => new Opaque($id, "Mud Bricks", new Info(BreakInfo::pickaxe(Hardness::MUD_BRICKS, ToolTier::WOOD, BlastResistance::MUD_BRICKS))));
		self::register("mud_brick_slab", fn(BID $id) => new Slab($id, "Mud Brick", new Info(BreakInfo::pickaxe(Hardness::MUD_BRICK_SLAB, ToolTier::WOOD, BlastResistance::MUD_BRICK_SLAB))));
		self::register("mud_brick_stairs", fn(BID $id) => new Stair($id, "Mud Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::MUD_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::MUD_BRICK_STAIRS))));
		self::register("mud_brick_wall", fn(BID $id) => new Wall($id, "Mud Brick Wall", new Info(BreakInfo::pickaxe(Hardness::MUD_BRICK_WALL, ToolTier::WOOD, BlastResistance::MUD_BRICK_WALL))));
	}

	private static function registerResinBlocks() : void{
		self::register("resin", fn(BID $id) => new Opaque($id, "Block of Resin", new Info(new BreakInfo(Hardness::RESIN_BLOCK, blastResistance: BlastResistance::RESIN_BLOCK))));
		self::register("resin_clump", fn(BID $id) => new ResinClump($id, "Resin Clump", new Info(new BreakInfo(Hardness::RESIN_CLUMP, blastResistance: BlastResistance::RESIN_CLUMP))));

		self::register("resin_brick_slab", fn(BID $id) => new Slab($id, "Resin Brick", new Info(BreakInfo::pickaxe(Hardness::RESIN_BRICK_SLAB, ToolTier::WOOD, BlastResistance::RESIN_BRICK_SLAB))));
		self::register("resin_brick_stairs", fn(BID $id) => new Stair($id, "Resin Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::RESIN_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::RESIN_BRICK_STAIRS))));
		self::register("resin_brick_wall", fn(BID $id) => new Wall($id, "Resin Brick Wall", new Info(BreakInfo::pickaxe(Hardness::RESIN_BRICK_WALL, ToolTier::WOOD, BlastResistance::RESIN_BRICK_WALL))));
		self::register("resin_bricks", fn(BID $id) => new Opaque($id, "Resin Bricks", new Info(BreakInfo::pickaxe(Hardness::RESIN_BRICKS, ToolTier::WOOD, BlastResistance::RESIN_BRICKS))));
		self::register("chiseled_resin_bricks", fn(BID $id) => new Opaque($id, "Chiseled Resin Bricks", new Info(BreakInfo::pickaxe(Hardness::CHISELED_RESIN_BRICKS, ToolTier::WOOD, BlastResistance::CHISELED_RESIN_BRICKS))));
	}

	private static function registerTuffBlocks() : void{
		self::register("tuff", fn(BID $id) => new Opaque($id, "Tuff", new Info(BreakInfo::pickaxe(Hardness::TUFF, ToolTier::WOOD, BlastResistance::TUFF))));
		self::register("tuff_slab", fn(BID $id) => new Slab($id, "Tuff", new Info(BreakInfo::pickaxe(Hardness::TUFF_SLAB, ToolTier::WOOD, BlastResistance::TUFF_SLAB))));
		self::register("tuff_stairs", fn(BID $id) => new Stair($id, "Tuff Stairs", new Info(BreakInfo::pickaxe(Hardness::TUFF_STAIRS, ToolTier::WOOD, BlastResistance::TUFF_STAIRS))));
		self::register("tuff_wall", fn(BID $id) => new Wall($id, "Tuff Wall", new Info(BreakInfo::pickaxe(Hardness::TUFF_WALL, ToolTier::WOOD, BlastResistance::TUFF_WALL))));
		self::register("chiseled_tuff", fn(BID $id) => new Opaque($id, "Chiseled Tuff", new Info(BreakInfo::pickaxe(Hardness::CHISELED_TUFF, ToolTier::WOOD, BlastResistance::CHISELED_TUFF))));

		self::register("tuff_bricks", fn(BID $id) => new Opaque($id, "Tuff Bricks", new Info(BreakInfo::pickaxe(Hardness::TUFF_BRICKS, ToolTier::WOOD, BlastResistance::TUFF_BRICKS))));
		self::register("tuff_brick_slab", fn(BID $id) => new Slab($id, "Tuff Brick", new Info(BreakInfo::pickaxe(Hardness::TUFF_BRICK_SLAB, ToolTier::WOOD, BlastResistance::TUFF_BRICK_SLAB))));
		self::register("tuff_brick_stairs", fn(BID $id) => new Stair($id, "Tuff Brick Stairs", new Info(BreakInfo::pickaxe(Hardness::TUFF_BRICK_STAIRS, ToolTier::WOOD, BlastResistance::TUFF_BRICK_STAIRS))));
		self::register("tuff_brick_wall", fn(BID $id) => new Wall($id, "Tuff Brick Wall", new Info(BreakInfo::pickaxe(Hardness::TUFF_BRICK_WALL, ToolTier::WOOD, BlastResistance::TUFF_BRICK_WALL))));
		self::register("chiseled_tuff_bricks", fn(BID $id) => new Opaque($id, "Chiseled Tuff Bricks", new Info(BreakInfo::pickaxe(Hardness::CHISELED_TUFF_BRICKS, ToolTier::WOOD, BlastResistance::CHISELED_TUFF_BRICKS))));

		self::register("polished_tuff", fn(BID $id) => new Opaque($id, "Polished Tuff", new Info(BreakInfo::pickaxe(Hardness::POLISHED_TUFF, ToolTier::WOOD, BlastResistance::POLISHED_TUFF))));
		self::register("polished_tuff_slab", fn(BID $id) => new Slab($id, "Polished Tuff", new Info(BreakInfo::pickaxe(Hardness::POLISHED_TUFF_SLAB, ToolTier::WOOD, BlastResistance::POLISHED_TUFF_SLAB))));
		self::register("polished_tuff_stairs", fn(BID $id) => new Stair($id, "Polished Tuff Stairs", new Info(BreakInfo::pickaxe(Hardness::POLISHED_TUFF_STAIRS, ToolTier::WOOD, BlastResistance::POLISHED_TUFF_STAIRS))));
		self::register("polished_tuff_wall", fn(BID $id) => new Wall($id, "Polished Tuff Wall", new Info(BreakInfo::pickaxe(Hardness::POLISHED_TUFF_WALL, ToolTier::WOOD, BlastResistance::POLISHED_TUFF_WALL))));
	}

	private static function registerCauldronBlocks() : void{
		$cauldronBreakInfo = new Info(BreakInfo::pickaxe(Hardness::CAULDRON, ToolTier::WOOD, BlastResistance::CAULDRON));

		self::register("cauldron", fn(BID $id) => new Cauldron($id, "Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("water_cauldron", fn(BID $id) => new WaterCauldron($id, "Water Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("lava_cauldron", fn(BID $id) => new LavaCauldron($id, "Lava Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("potion_cauldron", fn(BID $id) => new PotionCauldron($id, "Potion Cauldron", $cauldronBreakInfo), TileCauldron::class);
	}
}
