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
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as LegacyIds;
use pocketmine\block\BlockLegacyMetadata as Meta;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\BlockTypeIds as Ids;
use pocketmine\block\tile\Banner as TileBanner;
use pocketmine\block\tile\Barrel as TileBarrel;
use pocketmine\block\tile\Beacon as TileBeacon;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\block\tile\Bell as TileBell;
use pocketmine\block\tile\BlastFurnace as TileBlastFurnace;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\Comparator as TileComparator;
use pocketmine\block\tile\DaylightSensor as TileDaylightSensor;
use pocketmine\block\tile\EnchantTable as TileEnchantingTable;
use pocketmine\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\tile\FlowerPot as TileFlowerPot;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\tile\ItemFrame as TileItemFrame;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\tile\MonsterSpawner as TileMonsterSpawner;
use pocketmine\block\tile\NormalFurnace as TileNormalFurnace;
use pocketmine\block\tile\Note as TileNote;
use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\tile\Skull as TileSkull;
use pocketmine\block\tile\Smoker as TileSmoker;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\light\LightUpdate;
use function get_class;
use function min;

/**
 * Manages deserializing block types from their legacy blockIDs and metadata.
 * This is primarily needed for loading chunks from disk.
 */
class BlockFactory{
	use SingletonTrait;

	/**
	 * @var Block[]
	 * @phpstan-var array<int, Block>
	 */
	private array $fullList = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $defaultStateIndexes = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $mappedStateIndexes = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	public array $light = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	public array $lightFilter = [];
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	public array $blocksDirectSkyLight = [];
	/**
	 * @var float[]
	 * @phpstan-var array<int, float>
	 */
	public array $blastResistance = [];

	public function __construct(){
		$railBreakInfo = new BlockBreakInfo(0.7);
		$this->registerAllMeta(new ActivatorRail(new BID(Ids::ACTIVATOR_RAIL, LegacyIds::ACTIVATOR_RAIL, 0), "Activator Rail", $railBreakInfo));
		$this->registerAllMeta(new Air(new BID(Ids::AIR, LegacyIds::AIR, 0), "Air", BreakInfo::indestructible(-1.0)));
		$this->registerAllMeta(new Anvil(new BID(Ids::ANVIL, LegacyIds::ANVIL, 0), "Anvil", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->registerAllMeta(new Bamboo(new BID(Ids::BAMBOO, LegacyIds::BAMBOO, 0), "Bamboo", new class(2.0 /* 1.0 in PC */, ToolType::AXE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}));
		$this->registerAllMeta(new BambooSapling(new BID(Ids::BAMBOO_SAPLING, LegacyIds::BAMBOO_SAPLING, 0), "Bamboo Sapling", BreakInfo::instant()));

		$bannerBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->registerAllMeta(new FloorBanner(new BID(Ids::BANNER, LegacyIds::STANDING_BANNER, 0, ItemIds::BANNER, TileBanner::class), "Banner", $bannerBreakInfo));
		$this->registerAllMeta(new WallBanner(new BID(Ids::WALL_BANNER, LegacyIds::WALL_BANNER, 0, ItemIds::BANNER, TileBanner::class), "Wall Banner", $bannerBreakInfo));
		$this->registerAllMeta(new Barrel(new BID(Ids::BARREL, LegacyIds::BARREL, 0, null, TileBarrel::class), "Barrel", new BreakInfo(2.5, ToolType::AXE)));
		$this->registerAllMeta(new Transparent(new BID(Ids::BARRIER, LegacyIds::BARRIER, 0), "Barrier", BreakInfo::indestructible()));
		$this->registerAllMeta(new Beacon(new BID(Ids::BEACON, LegacyIds::BEACON, 0, null, TileBeacon::class), "Beacon", new BreakInfo(3.0)));
		$this->registerAllMeta(new Bed(new BID(Ids::BED, LegacyIds::BED_BLOCK, 0, ItemIds::BED, TileBed::class), "Bed Block", new BreakInfo(0.2)));
		$this->registerAllMeta(new Bedrock(new BID(Ids::BEDROCK, LegacyIds::BEDROCK, 0), "Bedrock", BreakInfo::indestructible()));

		$this->registerAllMeta(new Beetroot(new BID(Ids::BEETROOTS, LegacyIds::BEETROOT_BLOCK, 0), "Beetroot Block", BreakInfo::instant()));
		$this->registerAllMeta(new Bell(new BID(Ids::BELL, LegacyIds::BELL, 0, null, TileBell::class), "Bell", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new BlueIce(new BID(Ids::BLUE_ICE, LegacyIds::BLUE_ICE, 0), "Blue Ice", new BreakInfo(2.8, ToolType::PICKAXE)));
		$this->registerAllMeta(new BoneBlock(new BID(Ids::BONE_BLOCK, LegacyIds::BONE_BLOCK, 0), "Bone Block", new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Bookshelf(new BID(Ids::BOOKSHELF, LegacyIds::BOOKSHELF, 0), "Bookshelf", new BreakInfo(1.5, ToolType::AXE)));
		$this->registerAllMeta(new BrewingStand(new BID(Ids::BREWING_STAND, LegacyIds::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND, TileBrewingStand::class), "Brewing Stand", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$bricksBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(new Stair(new BID(Ids::BRICK_STAIRS, LegacyIds::BRICK_STAIRS, 0), "Brick Stairs", $bricksBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::BRICKS, LegacyIds::BRICK_BLOCK, 0), "Bricks", $bricksBreakInfo));

		$this->registerAllMeta(new BrownMushroom(new BID(Ids::BROWN_MUSHROOM, LegacyIds::BROWN_MUSHROOM, 0), "Brown Mushroom", BreakInfo::instant()));
		$this->registerAllMeta(new Cactus(new BID(Ids::CACTUS, LegacyIds::CACTUS, 0), "Cactus", new BreakInfo(0.4)));
		$this->registerAllMeta(new Cake(new BID(Ids::CAKE, LegacyIds::CAKE_BLOCK, 0, ItemIds::CAKE), "Cake", new BreakInfo(0.5)));
		$this->registerAllMeta(new Carrot(new BID(Ids::CARROTS, LegacyIds::CARROTS, 0), "Carrot Block", BreakInfo::instant()));

		$chestBreakInfo = new BreakInfo(2.5, ToolType::AXE);
		$this->registerAllMeta(new Chest(new BID(Ids::CHEST, LegacyIds::CHEST, 0, null, TileChest::class), "Chest", $chestBreakInfo));
		$this->registerAllMeta(new Clay(new BID(Ids::CLAY, LegacyIds::CLAY_BLOCK, 0), "Clay Block", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->registerAllMeta(new Coal(new BID(Ids::COAL, LegacyIds::COAL_BLOCK, 0), "Coal Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->registerAllMeta(new CoalOre(new BID(Ids::COAL_ORE, LegacyIds::COAL_ORE, 0), "Coal Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$cobblestoneBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta($cobblestone = new Opaque(new BID(Ids::COBBLESTONE, LegacyIds::COBBLESTONE, 0), "Cobblestone", $cobblestoneBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::MOSSY_COBBLESTONE, LegacyIds::MOSSY_COBBLESTONE, 0), "Mossy Cobblestone", $cobblestoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::COBBLESTONE_STAIRS, LegacyIds::COBBLESTONE_STAIRS, 0), "Cobblestone Stairs", $cobblestoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::MOSSY_COBBLESTONE_STAIRS, LegacyIds::MOSSY_COBBLESTONE_STAIRS, 0), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		$this->registerAllMeta(new Cobweb(new BID(Ids::COBWEB, LegacyIds::COBWEB, 0), "Cobweb", new BreakInfo(4.0, ToolType::SWORD | ToolType::SHEARS, 1)));
		$this->registerAllMeta(new CocoaBlock(new BID(Ids::COCOA_POD, LegacyIds::COCOA, 0), "Cocoa Block", new BreakInfo(0.2, ToolType::AXE, 0, 15.0)));
		$this->registerAllMeta(new CoralBlock(new BID(Ids::CORAL_BLOCK, LegacyIds::CORAL_BLOCK, 0), "Coral Block", new BreakInfo(7.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new CraftingTable(new BID(Ids::CRAFTING_TABLE, LegacyIds::CRAFTING_TABLE, 0), "Crafting Table", new BreakInfo(2.5, ToolType::AXE)));
		$this->registerAllMeta(new DaylightSensor(new BIDFlattened(Ids::DAYLIGHT_SENSOR, LegacyIds::DAYLIGHT_DETECTOR, [LegacyIds::DAYLIGHT_DETECTOR_INVERTED], 0, null, TileDaylightSensor::class), "Daylight Sensor", new BreakInfo(0.2, ToolType::AXE)));
		$this->registerAllMeta(new DeadBush(new BID(Ids::DEAD_BUSH, LegacyIds::DEADBUSH, 0), "Dead Bush", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->registerAllMeta(new DetectorRail(new BID(Ids::DETECTOR_RAIL, LegacyIds::DETECTOR_RAIL, 0), "Detector Rail", $railBreakInfo));

		$this->registerAllMeta(new Opaque(new BID(Ids::DIAMOND, LegacyIds::DIAMOND_BLOCK, 0), "Diamond Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->registerAllMeta(new DiamondOre(new BID(Ids::DIAMOND_ORE, LegacyIds::DIAMOND_ORE, 0), "Diamond Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->registerAllMeta(new Dirt(new BID(Ids::DIRT, LegacyIds::DIRT, 0), "Dirt", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->registerAllMeta(
			new DoublePlant(new BID(Ids::SUNFLOWER, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_SUNFLOWER), "Sunflower", BreakInfo::instant()),
			new DoublePlant(new BID(Ids::LILAC, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LILAC), "Lilac", BreakInfo::instant()),
			new DoublePlant(new BID(Ids::ROSE_BUSH, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_ROSE_BUSH), "Rose Bush", BreakInfo::instant()),
			new DoublePlant(new BID(Ids::PEONY, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_PEONY), "Peony", BreakInfo::instant()),
			new DoubleTallGrass(new BID(Ids::DOUBLE_TALLGRASS, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_TALLGRASS), "Double Tallgrass", BreakInfo::instant(ToolType::SHEARS, 1)),
			new DoubleTallGrass(new BID(Ids::LARGE_FERN, LegacyIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LARGE_FERN), "Large Fern", BreakInfo::instant(ToolType::SHEARS, 1)),
		);
		$this->registerAllMeta(new DragonEgg(new BID(Ids::DRAGON_EGG, LegacyIds::DRAGON_EGG, 0), "Dragon Egg", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new DriedKelp(new BID(Ids::DRIED_KELP, LegacyIds::DRIED_KELP_BLOCK, 0), "Dried Kelp Block", new BreakInfo(0.5, ToolType::NONE, 0, 12.5)));
		$this->registerAllMeta(new Opaque(new BID(Ids::EMERALD, LegacyIds::EMERALD_BLOCK, 0), "Emerald Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->registerAllMeta(new EmeraldOre(new BID(Ids::EMERALD_ORE, LegacyIds::EMERALD_ORE, 0), "Emerald Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->registerAllMeta(new EnchantingTable(new BID(Ids::ENCHANTING_TABLE, LegacyIds::ENCHANTING_TABLE, 0, null, TileEnchantingTable::class), "Enchanting Table", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->registerAllMeta(new EndPortalFrame(new BID(Ids::END_PORTAL_FRAME, LegacyIds::END_PORTAL_FRAME, 0), "End Portal Frame", BreakInfo::indestructible()));
		$this->registerAllMeta(new EndRod(new BID(Ids::END_ROD, LegacyIds::END_ROD, 0), "End Rod", BreakInfo::instant()));
		$this->registerAllMeta(new Opaque(new BID(Ids::END_STONE, LegacyIds::END_STONE, 0), "End Stone", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 45.0)));

		$endBrickBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.0);
		$this->registerAllMeta(new Opaque(new BID(Ids::END_STONE_BRICKS, LegacyIds::END_BRICKS, 0), "End Stone Bricks", $endBrickBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::END_STONE_BRICK_STAIRS, LegacyIds::END_BRICK_STAIRS, 0), "End Stone Brick Stairs", $endBrickBreakInfo));

		$this->registerAllMeta(new EnderChest(new BID(Ids::ENDER_CHEST, LegacyIds::ENDER_CHEST, 0, null, TileEnderChest::class), "Ender Chest", new BreakInfo(22.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3000.0)));
		$this->registerAllMeta(new Farmland(new BID(Ids::FARMLAND, LegacyIds::FARMLAND, 0), "Farmland", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->registerAllMeta(new Fire(new BID(Ids::FIRE, LegacyIds::FIRE, 0), "Fire Block", BreakInfo::instant()));
		$this->registerAllMeta(new FletchingTable(new BID(Ids::FLETCHING_TABLE, LegacyIds::FLETCHING_TABLE, 0), "Fletching Table", new BreakInfo(2.5, ToolType::AXE, 0, 2.5)));
		$this->registerAllMeta(new Flower(new BID(Ids::DANDELION, LegacyIds::DANDELION, 0), "Dandelion", BreakInfo::instant()));
		$this->registerAllMeta(
			new Flower(new BID(Ids::POPPY, LegacyIds::RED_FLOWER, Meta::FLOWER_POPPY), "Poppy", BreakInfo::instant()),
			new Flower(new BID(Ids::ALLIUM, LegacyIds::RED_FLOWER, Meta::FLOWER_ALLIUM), "Allium", BreakInfo::instant()),
			new Flower(new BID(Ids::AZURE_BLUET, LegacyIds::RED_FLOWER, Meta::FLOWER_AZURE_BLUET), "Azure Bluet", BreakInfo::instant()),
			new Flower(new BID(Ids::BLUE_ORCHID, LegacyIds::RED_FLOWER, Meta::FLOWER_BLUE_ORCHID), "Blue Orchid", BreakInfo::instant()),
			new Flower(new BID(Ids::CORNFLOWER, LegacyIds::RED_FLOWER, Meta::FLOWER_CORNFLOWER), "Cornflower", BreakInfo::instant()),
			new Flower(new BID(Ids::LILY_OF_THE_VALLEY, LegacyIds::RED_FLOWER, Meta::FLOWER_LILY_OF_THE_VALLEY), "Lily of the Valley", BreakInfo::instant()),
			new Flower(new BID(Ids::ORANGE_TULIP, LegacyIds::RED_FLOWER, Meta::FLOWER_ORANGE_TULIP), "Orange Tulip", BreakInfo::instant()),
			new Flower(new BID(Ids::OXEYE_DAISY, LegacyIds::RED_FLOWER, Meta::FLOWER_OXEYE_DAISY), "Oxeye Daisy", BreakInfo::instant()),
			new Flower(new BID(Ids::PINK_TULIP, LegacyIds::RED_FLOWER, Meta::FLOWER_PINK_TULIP), "Pink Tulip", BreakInfo::instant()),
			new Flower(new BID(Ids::RED_TULIP, LegacyIds::RED_FLOWER, Meta::FLOWER_RED_TULIP), "Red Tulip", BreakInfo::instant()),
			new Flower(new BID(Ids::WHITE_TULIP, LegacyIds::RED_FLOWER, Meta::FLOWER_WHITE_TULIP), "White Tulip", BreakInfo::instant()),
		);
		$this->registerAllMeta(new FlowerPot(new BID(Ids::FLOWER_POT, LegacyIds::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT, TileFlowerPot::class), "Flower Pot", BreakInfo::instant()));
		$this->registerAllMeta(new FrostedIce(new BID(Ids::FROSTED_ICE, LegacyIds::FROSTED_ICE, 0), "Frosted Ice", new BreakInfo(2.5, ToolType::PICKAXE)));
		$this->registerAllMeta(new Furnace(new BIDFlattened(Ids::FURNACE, LegacyIds::FURNACE, [LegacyIds::LIT_FURNACE], 0, null, TileNormalFurnace::class), "Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Furnace(new BIDFlattened(Ids::BLAST_FURNACE, LegacyIds::BLAST_FURNACE, [LegacyIds::LIT_BLAST_FURNACE], 0, null, TileBlastFurnace::class), "Blast Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Furnace(new BIDFlattened(Ids::SMOKER, LegacyIds::SMOKER, [LegacyIds::LIT_SMOKER], 0, null, TileSmoker::class), "Smoker", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$glassBreakInfo = new BreakInfo(0.3);
		$this->registerAllMeta(new Glass(new BID(Ids::GLASS, LegacyIds::GLASS, 0), "Glass", $glassBreakInfo));
		$this->registerAllMeta(new GlassPane(new BID(Ids::GLASS_PANE, LegacyIds::GLASS_PANE, 0), "Glass Pane", $glassBreakInfo));
		$this->registerAllMeta(new GlowingObsidian(new BID(Ids::GLOWING_OBSIDIAN, LegacyIds::GLOWINGOBSIDIAN, 0), "Glowing Obsidian", new BreakInfo(10.0, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 50.0)));
		$this->registerAllMeta(new Glowstone(new BID(Ids::GLOWSTONE, LegacyIds::GLOWSTONE, 0), "Glowstone", new BreakInfo(0.3, ToolType::PICKAXE)));
		$this->registerAllMeta(new Opaque(new BID(Ids::GOLD, LegacyIds::GOLD_BLOCK, 0), "Gold Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->registerAllMeta(new Opaque(new BID(Ids::GOLD_ORE, LegacyIds::GOLD_ORE, 0), "Gold Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));

		$grassBreakInfo = new BreakInfo(0.6, ToolType::SHOVEL);
		$this->registerAllMeta(new Grass(new BID(Ids::GRASS, LegacyIds::GRASS, 0), "Grass", $grassBreakInfo));
		$this->registerAllMeta(new GrassPath(new BID(Ids::GRASS_PATH, LegacyIds::GRASS_PATH, 0), "Grass Path", $grassBreakInfo));
		$this->registerAllMeta(new Gravel(new BID(Ids::GRAVEL, LegacyIds::GRAVEL, 0), "Gravel", new BreakInfo(0.6, ToolType::SHOVEL)));

		$hardenedClayBreakInfo = new BreakInfo(1.25, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 21.0);
		$this->registerAllMeta(new HardenedClay(new BID(Ids::HARDENED_CLAY, LegacyIds::HARDENED_CLAY, 0), "Hardened Clay", $hardenedClayBreakInfo));

		$hardenedGlassBreakInfo = new BreakInfo(10.0);
		$this->registerAllMeta(new HardenedGlass(new BID(Ids::HARDENED_GLASS, LegacyIds::HARD_GLASS, 0), "Hardened Glass", $hardenedGlassBreakInfo));
		$this->registerAllMeta(new HardenedGlassPane(new BID(Ids::HARDENED_GLASS_PANE, LegacyIds::HARD_GLASS_PANE, 0), "Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->registerAllMeta(new HayBale(new BID(Ids::HAY_BALE, LegacyIds::HAY_BALE, 0), "Hay Bale", new BreakInfo(0.5)));
		$this->registerAllMeta(new Hopper(new BID(Ids::HOPPER, LegacyIds::HOPPER_BLOCK, 0, ItemIds::HOPPER, TileHopper::class), "Hopper", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0)));
		$this->registerAllMeta(new Ice(new BID(Ids::ICE, LegacyIds::ICE, 0), "Ice", new BreakInfo(0.5, ToolType::PICKAXE)));

		$updateBlockBreakInfo = new BreakInfo(1.0);
		$this->registerAllMeta(new Opaque(new BID(Ids::INFO_UPDATE, LegacyIds::INFO_UPDATE, 0), "update!", $updateBlockBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::INFO_UPDATE2, LegacyIds::INFO_UPDATE2, 0), "ate!upd", $updateBlockBreakInfo));
		$this->registerAllMeta(new Transparent(new BID(Ids::INVISIBLE_BEDROCK, LegacyIds::INVISIBLEBEDROCK, 0), "Invisible Bedrock", BreakInfo::indestructible()));

		$ironBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(new Opaque(new BID(Ids::IRON, LegacyIds::IRON_BLOCK, 0), "Iron Block", $ironBreakInfo));
		$this->registerAllMeta(new Thin(new BID(Ids::IRON_BARS, LegacyIds::IRON_BARS, 0), "Iron Bars", $ironBreakInfo));
		$ironDoorBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 25.0);
		$this->registerAllMeta(new Door(new BID(Ids::IRON_DOOR, LegacyIds::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door", $ironDoorBreakInfo));
		$this->registerAllMeta(new Trapdoor(new BID(Ids::IRON_TRAPDOOR, LegacyIds::IRON_TRAPDOOR, 0), "Iron Trapdoor", $ironDoorBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::IRON_ORE, LegacyIds::IRON_ORE, 0), "Iron Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->registerAllMeta(new ItemFrame(new BID(Ids::ITEM_FRAME, LegacyIds::FRAME_BLOCK, 0, ItemIds::FRAME, TileItemFrame::class), "Item Frame", new BreakInfo(0.25)));
		$this->registerAllMeta(new Jukebox(new BID(Ids::JUKEBOX, LegacyIds::JUKEBOX, 0, ItemIds::JUKEBOX, TileJukebox::class), "Jukebox", new BreakInfo(0.8, ToolType::AXE))); //TODO: in PC the hardness is 2.0, not 0.8, unsure if this is a MCPE bug or not
		$this->registerAllMeta(new Ladder(new BID(Ids::LADDER, LegacyIds::LADDER, 0), "Ladder", new BreakInfo(0.4, ToolType::AXE)));
		$this->registerAllMeta(new Lantern(new BID(Ids::LANTERN, LegacyIds::LANTERN, 0), "Lantern", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Opaque(new BID(Ids::LAPIS_LAZULI, LegacyIds::LAPIS_BLOCK, 0), "Lapis Lazuli Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->registerAllMeta(new LapisOre(new BID(Ids::LAPIS_LAZULI_ORE, LegacyIds::LAPIS_ORE, 0), "Lapis Lazuli Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->registerAllMeta(new Lava(new BIDFlattened(Ids::LAVA, LegacyIds::FLOWING_LAVA, [LegacyIds::STILL_LAVA], 0), "Lava", BreakInfo::indestructible(500.0)));
		$this->registerAllMeta(new Lectern(new BID(Ids::LECTERN, LegacyIds::LECTERN, 0, ItemIds::LECTERN, TileLectern::class), "Lectern", new BreakInfo(2.0, ToolType::AXE)));
		$this->registerAllMeta(new Lever(new BID(Ids::LEVER, LegacyIds::LEVER, 0), "Lever", new BreakInfo(0.5)));
		$this->registerAllMeta(new Loom(new BID(Ids::LOOM, LegacyIds::LOOM, 0), "Loom", new BreakInfo(2.5, ToolType::AXE)));
		$this->registerAllMeta(new Magma(new BID(Ids::MAGMA, LegacyIds::MAGMA, 0), "Magma Block", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Melon(new BID(Ids::MELON, LegacyIds::MELON_BLOCK, 0), "Melon Block", new BreakInfo(1.0, ToolType::AXE)));
		$this->registerAllMeta(new MelonStem(new BID(Ids::MELON_STEM, LegacyIds::MELON_STEM, 0, ItemIds::MELON_SEEDS), "Melon Stem", BreakInfo::instant()));
		$this->registerAllMeta(new MonsterSpawner(new BID(Ids::MONSTER_SPAWNER, LegacyIds::MOB_SPAWNER, 0, null, TileMonsterSpawner::class), "Monster Spawner", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Mycelium(new BID(Ids::MYCELIUM, LegacyIds::MYCELIUM, 0), "Mycelium", new BreakInfo(0.6, ToolType::SHOVEL)));

		$netherBrickBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(new Opaque(new BID(Ids::NETHER_BRICKS, LegacyIds::NETHER_BRICK_BLOCK, 0), "Nether Bricks", $netherBrickBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::RED_NETHER_BRICKS, LegacyIds::RED_NETHER_BRICK, 0), "Red Nether Bricks", $netherBrickBreakInfo));
		$this->registerAllMeta(new Fence(new BID(Ids::NETHER_BRICK_FENCE, LegacyIds::NETHER_BRICK_FENCE, 0), "Nether Brick Fence", $netherBrickBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::NETHER_BRICK_STAIRS, LegacyIds::NETHER_BRICK_STAIRS, 0), "Nether Brick Stairs", $netherBrickBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::RED_NETHER_BRICK_STAIRS, LegacyIds::RED_NETHER_BRICK_STAIRS, 0), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		$this->registerAllMeta(new NetherPortal(new BID(Ids::NETHER_PORTAL, LegacyIds::PORTAL, 0), "Nether Portal", BreakInfo::indestructible(0.0)));
		$this->registerAllMeta(new NetherQuartzOre(new BID(Ids::NETHER_QUARTZ_ORE, LegacyIds::NETHER_QUARTZ_ORE, 0), "Nether Quartz Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new NetherReactor(new BID(Ids::NETHER_REACTOR_CORE, LegacyIds::NETHERREACTOR, 0), "Nether Reactor Core", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Opaque(new BID(Ids::NETHER_WART_BLOCK, LegacyIds::NETHER_WART_BLOCK, 0), "Nether Wart Block", new BreakInfo(1.0, ToolType::HOE)));
		$this->registerAllMeta(new NetherWartPlant(new BID(Ids::NETHER_WART, LegacyIds::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART), "Nether Wart", BreakInfo::instant()));
		$this->registerAllMeta(new Netherrack(new BID(Ids::NETHERRACK, LegacyIds::NETHERRACK, 0), "Netherrack", new BreakInfo(0.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Note(new BID(Ids::NOTE_BLOCK, LegacyIds::NOTEBLOCK, 0, null, TileNote::class), "Note Block", new BreakInfo(0.8, ToolType::AXE)));
		$this->registerAllMeta(new Opaque(new BID(Ids::OBSIDIAN, LegacyIds::OBSIDIAN, 0), "Obsidian", new BreakInfo(35.0 /* 50 in PC */, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000.0)));
		$this->registerAllMeta(new PackedIce(new BID(Ids::PACKED_ICE, LegacyIds::PACKED_ICE, 0), "Packed Ice", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->registerAllMeta(new Podzol(new BID(Ids::PODZOL, LegacyIds::PODZOL, 0), "Podzol", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->registerAllMeta(new Potato(new BID(Ids::POTATOES, LegacyIds::POTATOES, 0), "Potato Block", BreakInfo::instant()));
		$this->registerAllMeta(new PoweredRail(new BID(Ids::POWERED_RAIL, LegacyIds::GOLDEN_RAIL, 0), "Powered Rail", $railBreakInfo));

		$prismarineBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(
			new Opaque(new BID(Ids::PRISMARINE, LegacyIds::PRISMARINE, Meta::PRISMARINE_NORMAL), "Prismarine", $prismarineBreakInfo),
			new Opaque(new BID(Ids::DARK_PRISMARINE, LegacyIds::PRISMARINE, Meta::PRISMARINE_DARK), "Dark Prismarine", $prismarineBreakInfo),
			new Opaque(new BID(Ids::PRISMARINE_BRICKS, LegacyIds::PRISMARINE, Meta::PRISMARINE_BRICKS), "Prismarine Bricks", $prismarineBreakInfo)
		);
		$this->registerAllMeta(new Stair(new BID(Ids::PRISMARINE_BRICKS_STAIRS, LegacyIds::PRISMARINE_BRICKS_STAIRS, 0), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::DARK_PRISMARINE_STAIRS, LegacyIds::DARK_PRISMARINE_STAIRS, 0), "Dark Prismarine Stairs", $prismarineBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::PRISMARINE_STAIRS, LegacyIds::PRISMARINE_STAIRS, 0), "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->registerAllMeta(new Pumpkin(new BID(Ids::PUMPKIN, LegacyIds::PUMPKIN, 0), "Pumpkin", $pumpkinBreakInfo));
		$this->registerAllMeta(new CarvedPumpkin(new BID(Ids::CARVED_PUMPKIN, LegacyIds::CARVED_PUMPKIN, 0), "Carved Pumpkin", $pumpkinBreakInfo));
		$this->registerAllMeta(new LitPumpkin(new BID(Ids::LIT_PUMPKIN, LegacyIds::JACK_O_LANTERN, 0), "Jack o'Lantern", $pumpkinBreakInfo));

		$this->registerAllMeta(new PumpkinStem(new BID(Ids::PUMPKIN_STEM, LegacyIds::PUMPKIN_STEM, 0, ItemIds::PUMPKIN_SEEDS), "Pumpkin Stem", BreakInfo::instant()));

		$purpurBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(
			new Opaque(new BID(Ids::PURPUR, LegacyIds::PURPUR_BLOCK, Meta::PURPUR_NORMAL), "Purpur Block", $purpurBreakInfo),
			new SimplePillar(new BID(Ids::PURPUR_PILLAR, LegacyIds::PURPUR_BLOCK, Meta::PURPUR_PILLAR), "Purpur Pillar", $purpurBreakInfo)
		);
		$this->registerAllMeta(new Stair(new BID(Ids::PURPUR_STAIRS, LegacyIds::PURPUR_STAIRS, 0), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->registerAllMeta(
			new Opaque(new BID(Ids::QUARTZ, LegacyIds::QUARTZ_BLOCK, Meta::QUARTZ_NORMAL), "Quartz Block", $quartzBreakInfo),
			new SimplePillar(new BID(Ids::CHISELED_QUARTZ, LegacyIds::QUARTZ_BLOCK, Meta::QUARTZ_CHISELED), "Chiseled Quartz Block", $quartzBreakInfo),
			new SimplePillar(new BID(Ids::QUARTZ_PILLAR, LegacyIds::QUARTZ_BLOCK, Meta::QUARTZ_PILLAR), "Quartz Pillar", $quartzBreakInfo),
			new Opaque(new BID(Ids::SMOOTH_QUARTZ, LegacyIds::QUARTZ_BLOCK, Meta::QUARTZ_SMOOTH), "Smooth Quartz Block", $quartzBreakInfo) //TODO: we may need to account for the fact this previously incorrectly had axis
		);
		$this->registerAllMeta(new Stair(new BID(Ids::QUARTZ_STAIRS, LegacyIds::QUARTZ_STAIRS, 0), "Quartz Stairs", $quartzBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::SMOOTH_QUARTZ_STAIRS, LegacyIds::SMOOTH_QUARTZ_STAIRS, 0), "Smooth Quartz Stairs", $quartzBreakInfo));

		$this->registerAllMeta(new Rail(new BID(Ids::RAIL, LegacyIds::RAIL, 0), "Rail", $railBreakInfo));
		$this->registerAllMeta(new RedMushroom(new BID(Ids::RED_MUSHROOM, LegacyIds::RED_MUSHROOM, 0), "Red Mushroom", BreakInfo::instant()));
		$this->registerAllMeta(new Redstone(new BID(Ids::REDSTONE, LegacyIds::REDSTONE_BLOCK, 0), "Redstone Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->registerAllMeta(new RedstoneComparator(new BIDFlattened(Ids::REDSTONE_COMPARATOR, LegacyIds::UNPOWERED_COMPARATOR, [LegacyIds::POWERED_COMPARATOR], 0, ItemIds::COMPARATOR, TileComparator::class), "Redstone Comparator", BreakInfo::instant()));
		$this->registerAllMeta(new RedstoneLamp(new BIDFlattened(Ids::REDSTONE_LAMP, LegacyIds::REDSTONE_LAMP, [LegacyIds::LIT_REDSTONE_LAMP], 0), "Redstone Lamp", new BreakInfo(0.3)));
		$this->registerAllMeta(new RedstoneOre(new BIDFlattened(Ids::REDSTONE_ORE, LegacyIds::REDSTONE_ORE, [LegacyIds::LIT_REDSTONE_ORE], 0), "Redstone Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->registerAllMeta(new RedstoneRepeater(new BIDFlattened(Ids::REDSTONE_REPEATER, LegacyIds::UNPOWERED_REPEATER, [LegacyIds::POWERED_REPEATER], 0, ItemIds::REPEATER), "Redstone Repeater", BreakInfo::instant()));
		$this->registerAllMeta(new RedstoneTorch(new BIDFlattened(Ids::REDSTONE_TORCH, LegacyIds::REDSTONE_TORCH, [LegacyIds::UNLIT_REDSTONE_TORCH], 0), "Redstone Torch", BreakInfo::instant()));
		$this->registerAllMeta(new RedstoneWire(new BID(Ids::REDSTONE_WIRE, LegacyIds::REDSTONE_WIRE, 0, ItemIds::REDSTONE), "Redstone", BreakInfo::instant()));
		$this->registerAllMeta(new Reserved6(new BID(Ids::RESERVED6, LegacyIds::RESERVED6, 0), "reserved6", BreakInfo::instant()));

		$sandBreakInfo = new BreakInfo(0.5, ToolType::SHOVEL);
		$this->registerAllMeta(
			new Sand(new BID(Ids::SAND, LegacyIds::SAND, 0), "Sand", $sandBreakInfo),
			new Sand(new BID(Ids::RED_SAND, LegacyIds::SAND, 1), "Red Sand", $sandBreakInfo)
		);
		$this->registerAllMeta(new SeaLantern(new BID(Ids::SEA_LANTERN, LegacyIds::SEALANTERN, 0), "Sea Lantern", new BreakInfo(0.3)));
		$this->registerAllMeta(new SeaPickle(new BID(Ids::SEA_PICKLE, LegacyIds::SEA_PICKLE, 0), "Sea Pickle", BreakInfo::instant()));
		$this->registerAllMeta(new Skull(new BID(Ids::MOB_HEAD, LegacyIds::MOB_HEAD_BLOCK, 0, ItemIds::SKULL, TileSkull::class), "Mob Head", new BreakInfo(1.0)));
		$this->registerAllMeta(new Slime(new BID(Ids::SLIME, LegacyIds::SLIME, 0), "Slime Block", BreakInfo::instant()));
		$this->registerAllMeta(new Snow(new BID(Ids::SNOW, LegacyIds::SNOW, 0), "Snow Block", new BreakInfo(0.2, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new SnowLayer(new BID(Ids::SNOW_LAYER, LegacyIds::SNOW_LAYER, 0), "Snow Layer", new BreakInfo(0.1, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new SoulSand(new BID(Ids::SOUL_SAND, LegacyIds::SOUL_SAND, 0), "Soul Sand", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->registerAllMeta(new Sponge(new BID(Ids::SPONGE, LegacyIds::SPONGE, 0), "Sponge", new BreakInfo(0.6, ToolType::HOE)));
		$shulkerBoxBreakInfo = new BreakInfo(2, ToolType::PICKAXE);
		$this->registerAllMeta(new ShulkerBox(new BID(Ids::SHULKER_BOX, LegacyIds::UNDYED_SHULKER_BOX, 0, null, TileShulkerBox::class), "Shulker Box", $shulkerBoxBreakInfo));

		$stoneBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(
			$stone = new class(new BID(Ids::STONE, LegacyIds::STONE, Meta::STONE_NORMAL), "Stone", $stoneBreakInfo) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			},
			new Opaque(new BID(Ids::ANDESITE, LegacyIds::STONE, Meta::STONE_ANDESITE), "Andesite", $stoneBreakInfo),
			new Opaque(new BID(Ids::DIORITE, LegacyIds::STONE, Meta::STONE_DIORITE), "Diorite", $stoneBreakInfo),
			new Opaque(new BID(Ids::GRANITE, LegacyIds::STONE, Meta::STONE_GRANITE), "Granite", $stoneBreakInfo),
			new Opaque(new BID(Ids::POLISHED_ANDESITE, LegacyIds::STONE, Meta::STONE_POLISHED_ANDESITE), "Polished Andesite", $stoneBreakInfo),
			new Opaque(new BID(Ids::POLISHED_DIORITE, LegacyIds::STONE, Meta::STONE_POLISHED_DIORITE), "Polished Diorite", $stoneBreakInfo),
			new Opaque(new BID(Ids::POLISHED_GRANITE, LegacyIds::STONE, Meta::STONE_POLISHED_GRANITE), "Polished Granite", $stoneBreakInfo)
		);
		$this->registerAllMeta(
			$stoneBrick = new Opaque(new BID(Ids::STONE_BRICKS, LegacyIds::STONEBRICK, Meta::STONE_BRICK_NORMAL), "Stone Bricks", $stoneBreakInfo),
			$mossyStoneBrick = new Opaque(new BID(Ids::MOSSY_STONE_BRICKS, LegacyIds::STONEBRICK, Meta::STONE_BRICK_MOSSY), "Mossy Stone Bricks", $stoneBreakInfo),
			$crackedStoneBrick = new Opaque(new BID(Ids::CRACKED_STONE_BRICKS, LegacyIds::STONEBRICK, Meta::STONE_BRICK_CRACKED), "Cracked Stone Bricks", $stoneBreakInfo),
			$chiseledStoneBrick = new Opaque(new BID(Ids::CHISELED_STONE_BRICKS, LegacyIds::STONEBRICK, Meta::STONE_BRICK_CHISELED), "Chiseled Stone Bricks", $stoneBreakInfo)
		);
		$infestedStoneBreakInfo = new BreakInfo(0.75, ToolType::PICKAXE);
		$this->registerAllMeta(
			new InfestedStone(new BID(Ids::INFESTED_STONE, LegacyIds::MONSTER_EGG, Meta::INFESTED_STONE), "Infested Stone", $infestedStoneBreakInfo, $stone),
			new InfestedStone(new BID(Ids::INFESTED_STONE_BRICK, LegacyIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK), "Infested Stone Brick", $infestedStoneBreakInfo, $stoneBrick),
			new InfestedStone(new BID(Ids::INFESTED_COBBLESTONE, LegacyIds::MONSTER_EGG, Meta::INFESTED_COBBLESTONE), "Infested Cobblestone", $infestedStoneBreakInfo, $cobblestone),
			new InfestedStone(new BID(Ids::INFESTED_MOSSY_STONE_BRICK, LegacyIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_MOSSY), "Infested Mossy Stone Brick", $infestedStoneBreakInfo, $mossyStoneBrick),
			new InfestedStone(new BID(Ids::INFESTED_CRACKED_STONE_BRICK, LegacyIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CRACKED), "Infested Cracked Stone Brick", $infestedStoneBreakInfo, $crackedStoneBrick),
			new InfestedStone(new BID(Ids::INFESTED_CHISELED_STONE_BRICK, LegacyIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CHISELED), "Infested Chiseled Stone Brick", $infestedStoneBreakInfo, $chiseledStoneBrick)
		);
		$this->registerAllMeta(new Stair(new BID(Ids::STONE_STAIRS, LegacyIds::NORMAL_STONE_STAIRS, 0), "Stone Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Opaque(new BID(Ids::SMOOTH_STONE, LegacyIds::SMOOTH_STONE, 0), "Smooth Stone", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::ANDESITE_STAIRS, LegacyIds::ANDESITE_STAIRS, 0), "Andesite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::DIORITE_STAIRS, LegacyIds::DIORITE_STAIRS, 0), "Diorite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::GRANITE_STAIRS, LegacyIds::GRANITE_STAIRS, 0), "Granite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::POLISHED_ANDESITE_STAIRS, LegacyIds::POLISHED_ANDESITE_STAIRS, 0), "Polished Andesite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::POLISHED_DIORITE_STAIRS, LegacyIds::POLISHED_DIORITE_STAIRS, 0), "Polished Diorite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::POLISHED_GRANITE_STAIRS, LegacyIds::POLISHED_GRANITE_STAIRS, 0), "Polished Granite Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::STONE_BRICK_STAIRS, LegacyIds::STONE_BRICK_STAIRS, 0), "Stone Brick Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::MOSSY_STONE_BRICK_STAIRS, LegacyIds::MOSSY_STONE_BRICK_STAIRS, 0), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		$this->registerAllMeta(new StoneButton(new BID(Ids::STONE_BUTTON, LegacyIds::STONE_BUTTON, 0), "Stone Button", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->registerAllMeta(new Stonecutter(new BID(Ids::STONECUTTER, LegacyIds::STONECUTTER_BLOCK, 0, ItemIds::STONECUTTER_BLOCK), "Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE)));
		$this->registerAllMeta(new StonePressurePlate(new BID(Ids::STONE_PRESSURE_PLATE, LegacyIds::STONE_PRESSURE_PLATE, 0), "Stone Pressure Plate", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		//TODO: in the future this won't be the same for all the types
		$stoneSlabBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);

		$getStoneSlabId = static fn(int $blockTypeId, int $stoneSlabId, int $meta) => BlockLegacyIdHelper::getStoneSlabIdentifier($blockTypeId, $stoneSlabId, $meta);
		foreach([
			new Slab($getStoneSlabId(Ids::BRICK_SLAB, 1, Meta::STONE_SLAB_BRICK), "Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::COBBLESTONE_SLAB, 1, Meta::STONE_SLAB_COBBLESTONE), "Cobblestone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::FAKE_WOODEN_SLAB, 1, Meta::STONE_SLAB_FAKE_WOODEN), "Fake Wooden", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::NETHER_BRICK_SLAB, 1, Meta::STONE_SLAB_NETHER_BRICK), "Nether Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::QUARTZ_SLAB, 1, Meta::STONE_SLAB_QUARTZ), "Quartz", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::SANDSTONE_SLAB, 1, Meta::STONE_SLAB_SANDSTONE), "Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::SMOOTH_STONE_SLAB, 1, Meta::STONE_SLAB_SMOOTH_STONE), "Smooth Stone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::STONE_BRICK_SLAB, 1, Meta::STONE_SLAB_STONE_BRICK), "Stone Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::DARK_PRISMARINE_SLAB, 2, Meta::STONE_SLAB2_DARK_PRISMARINE), "Dark Prismarine", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::MOSSY_COBBLESTONE_SLAB, 2, Meta::STONE_SLAB2_MOSSY_COBBLESTONE), "Mossy Cobblestone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::PRISMARINE_SLAB, 2, Meta::STONE_SLAB2_PRISMARINE), "Prismarine", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::PRISMARINE_BRICKS_SLAB, 2, Meta::STONE_SLAB2_PRISMARINE_BRICKS), "Prismarine Bricks", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::PURPUR_SLAB, 2, Meta::STONE_SLAB2_PURPUR), "Purpur", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::RED_NETHER_BRICK_SLAB, 2, Meta::STONE_SLAB2_RED_NETHER_BRICK), "Red Nether Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::RED_SANDSTONE_SLAB, 2, Meta::STONE_SLAB2_RED_SANDSTONE), "Red Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::SMOOTH_SANDSTONE_SLAB, 2, Meta::STONE_SLAB2_SMOOTH_SANDSTONE), "Smooth Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::ANDESITE_SLAB, 3, Meta::STONE_SLAB3_ANDESITE), "Andesite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::DIORITE_SLAB, 3, Meta::STONE_SLAB3_DIORITE), "Diorite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::END_STONE_BRICK_SLAB, 3, Meta::STONE_SLAB3_END_STONE_BRICK), "End Stone Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::GRANITE_SLAB, 3, Meta::STONE_SLAB3_GRANITE), "Granite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::POLISHED_ANDESITE_SLAB, 3, Meta::STONE_SLAB3_POLISHED_ANDESITE), "Polished Andesite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::POLISHED_DIORITE_SLAB, 3, Meta::STONE_SLAB3_POLISHED_DIORITE), "Polished Diorite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::POLISHED_GRANITE_SLAB, 3, Meta::STONE_SLAB3_POLISHED_GRANITE), "Polished Granite", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::SMOOTH_RED_SANDSTONE_SLAB, 3, Meta::STONE_SLAB3_SMOOTH_RED_SANDSTONE), "Smooth Red Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::CUT_RED_SANDSTONE_SLAB, 4, Meta::STONE_SLAB4_CUT_RED_SANDSTONE), "Cut Red Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::CUT_SANDSTONE_SLAB, 4, Meta::STONE_SLAB4_CUT_SANDSTONE), "Cut Sandstone", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::MOSSY_STONE_BRICK_SLAB, 4, Meta::STONE_SLAB4_MOSSY_STONE_BRICK), "Mossy Stone Brick", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::SMOOTH_QUARTZ_SLAB, 4, Meta::STONE_SLAB4_SMOOTH_QUARTZ), "Smooth Quartz", $stoneSlabBreakInfo),
			new Slab($getStoneSlabId(Ids::STONE_SLAB, 4, Meta::STONE_SLAB4_STONE), "Stone", $stoneSlabBreakInfo),
		] as $slabType){
			$this->registerSlabWithDoubleHighBitsRemapping($slabType);
		}

		$this->registerAllMeta(new Opaque(new BID(Ids::LEGACY_STONECUTTER, LegacyIds::STONECUTTER, 0), "Legacy Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new Sugarcane(new BID(Ids::SUGARCANE, LegacyIds::REEDS_BLOCK, 0, ItemIds::REEDS), "Sugarcane", BreakInfo::instant()));
		$this->registerAllMeta(new SweetBerryBush(new BID(Ids::SWEET_BERRY_BUSH, LegacyIds::SWEET_BERRY_BUSH, 0, ItemIds::SWEET_BERRIES), "Sweet Berry Bush", BreakInfo::instant()));
		$this->registerAllMeta(new TNT(new BID(Ids::TNT, LegacyIds::TNT, 0), "TNT", BreakInfo::instant()));
		$this->registerAllMeta(
			new TallGrass(new BID(Ids::FERN, LegacyIds::TALLGRASS, Meta::TALLGRASS_FERN), "Fern", BreakInfo::instant(ToolType::SHEARS, 1)),
			new TallGrass(new BID(Ids::TALL_GRASS, LegacyIds::TALLGRASS, Meta::TALLGRASS_NORMAL), "Tall Grass", BreakInfo::instant(ToolType::SHEARS, 1))
		);
		$this->registerAllMeta(
			new Torch(new BID(Ids::BLUE_TORCH, LegacyIds::COLORED_TORCH_BP, 0), "Blue Torch", BreakInfo::instant()),
			new Torch(new BID(Ids::PURPLE_TORCH, LegacyIds::COLORED_TORCH_BP, 8), "Purple Torch", BreakInfo::instant())
		);
		$this->registerAllMeta(
			new Torch(new BID(Ids::RED_TORCH, LegacyIds::COLORED_TORCH_RG, 0), "Red Torch", BreakInfo::instant()),
			new Torch(new BID(Ids::GREEN_TORCH, LegacyIds::COLORED_TORCH_RG, 8), "Green Torch", BreakInfo::instant())
		);
		$this->registerAllMeta(new Torch(new BID(Ids::TORCH, LegacyIds::TORCH, 0), "Torch", BreakInfo::instant()));
		$this->registerAllMeta(new TrappedChest(new BID(Ids::TRAPPED_CHEST, LegacyIds::TRAPPED_CHEST, 0, null, TileChest::class), "Trapped Chest", $chestBreakInfo));
		$this->registerAllMeta(new Tripwire(new BID(Ids::TRIPWIRE, LegacyIds::TRIPWIRE, 0, ItemIds::STRING), "Tripwire", BreakInfo::instant()));
		$this->registerAllMeta(new TripwireHook(new BID(Ids::TRIPWIRE_HOOK, LegacyIds::TRIPWIRE_HOOK, 0), "Tripwire Hook", BreakInfo::instant()));
		$this->registerAllMeta(new UnderwaterTorch(new BID(Ids::UNDERWATER_TORCH, LegacyIds::UNDERWATER_TORCH, 0), "Underwater Torch", BreakInfo::instant()));
		$this->registerAllMeta(new Vine(new BID(Ids::VINES, LegacyIds::VINE, 0), "Vines", new BreakInfo(0.2, ToolType::AXE)));
		$this->registerAllMeta(new Water(new BIDFlattened(Ids::WATER, LegacyIds::FLOWING_WATER, [LegacyIds::STILL_WATER], 0), "Water", BreakInfo::indestructible(500.0)));
		$this->registerAllMeta(new WaterLily(new BID(Ids::LILY_PAD, LegacyIds::LILY_PAD, 0), "Lily Pad", BreakInfo::instant()));

		$weightedPressurePlateBreakInfo = new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->registerAllMeta(new WeightedPressurePlateHeavy(new BID(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY, LegacyIds::HEAVY_WEIGHTED_PRESSURE_PLATE, 0), "Weighted Pressure Plate Heavy", $weightedPressurePlateBreakInfo));
		$this->registerAllMeta(new WeightedPressurePlateLight(new BID(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT, LegacyIds::LIGHT_WEIGHTED_PRESSURE_PLATE, 0), "Weighted Pressure Plate Light", $weightedPressurePlateBreakInfo));
		$this->registerAllMeta(new Wheat(new BID(Ids::WHEAT, LegacyIds::WHEAT_BLOCK, 0), "Wheat Block", BreakInfo::instant()));

		$planksBreakInfo = new BreakInfo(2.0, ToolType::AXE, 0, 15.0);
		$leavesBreakInfo = new class(0.2, ToolType::HOE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SHEARS){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		};
		$signBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$logBreakInfo = new BreakInfo(2.0, ToolType::AXE);
		$woodenDoorBreakInfo = new BreakInfo(3.0, ToolType::AXE, 0, 15.0);
		$woodenButtonBreakInfo = new BreakInfo(0.5, ToolType::AXE);
		$woodenPressurePlateBreakInfo = new BreakInfo(0.5, ToolType::AXE);

		$planks = [];
		$saplings = [];
		$fences = [];
		$leaves = [];
		$allSidedLogs = [];
		foreach(TreeType::getAll() as $treeType){
			$magicNumber = $treeType->getMagicNumber();
			$name = $treeType->getDisplayName();
			$planks[] = new Planks(BlockLegacyIdHelper::getWoodenPlanksIdentifier($treeType), $name . " Planks", $planksBreakInfo);
			$saplings[] = new Sapling(BlockLegacyIdHelper::getSaplingIdentifier($treeType), $name . " Sapling", BreakInfo::instant(), $treeType);
			$fences[] = new WoodenFence(BlockLegacyIdHelper::getWoodenFenceIdentifier($treeType), $name . " Fence", $planksBreakInfo);
			$this->registerSlabWithDoubleHighBitsRemapping(new WoodenSlab(BlockLegacyIdHelper::getWoodenSlabIdentifier($treeType), $name, $planksBreakInfo));

			//TODO: find a better way to deal with this split
			$leaves[] = new Leaves(BlockLegacyIdHelper::getLeavesIdentifier($treeType), $name . " Leaves", $leavesBreakInfo, $treeType);

			$this->register(new Log(BlockLegacyIdHelper::getLogIdentifier($treeType), $name . " Log", $logBreakInfo, $treeType, false));
			$wood = new Wood(BlockLegacyIdHelper::getAllSidedLogIdentifier($treeType), $name . " Wood", $logBreakInfo, $treeType, false);
			$this->remap($magicNumber >= 4 ? LegacyIds::LOG2 : LegacyIds::LOG, ($magicNumber & 0x03) | 0b1100, $wood);

			$allSidedLogs[] = $wood;
			$allSidedLogs[] = new Wood(BlockLegacyIdHelper::getAllSidedStrippedLogIdentifier($treeType), "Stripped $name Wood", $logBreakInfo, $treeType, true);

			$this->registerAllMeta(new Log(BlockLegacyIdHelper::getStrippedLogIdentifier($treeType), "Stripped " . $name . " Log", $logBreakInfo, $treeType, true));
			$this->registerAllMeta(new FenceGate(BlockLegacyIdHelper::getWoodenFenceGateIdentifier($treeType), $name . " Fence Gate", $planksBreakInfo));
			$this->registerAllMeta(new WoodenStairs(BlockLegacyIdHelper::getWoodenStairsIdentifier($treeType), $name . " Stairs", $planksBreakInfo));
			$this->registerAllMeta(new WoodenDoor(BlockLegacyIdHelper::getWoodenDoorIdentifier($treeType), $name . " Door", $woodenDoorBreakInfo));

			$this->registerAllMeta(new WoodenButton(BlockLegacyIdHelper::getWoodenButtonIdentifier($treeType), $name . " Button", $woodenButtonBreakInfo));
			$this->registerAllMeta(new WoodenPressurePlate(BlockLegacyIdHelper::getWoodenPressurePlateIdentifier($treeType), $name . " Pressure Plate", $woodenPressurePlateBreakInfo));
			$this->registerAllMeta(new WoodenTrapdoor(BlockLegacyIdHelper::getWoodenTrapdoorIdentifier($treeType), $name . " Trapdoor", $woodenDoorBreakInfo));

			$this->registerAllMeta(new FloorSign(BlockLegacyIdHelper::getWoodenFloorSignIdentifier($treeType), $name . " Sign", $signBreakInfo));
			$this->registerAllMeta(new WallSign(BlockLegacyIdHelper::getWoodenWallSignIdentifier($treeType), $name . " Wall Sign", $signBreakInfo));
		}
		$this->registerAllMeta(...$planks);
		$this->registerAllMeta(...$saplings);
		$this->registerAllMeta(...$fences);
		$this->registerAllMeta(...$leaves);
		$this->registerAllMeta(...$allSidedLogs);

		$sandstoneBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->registerAllMeta(new Stair(new BID(Ids::RED_SANDSTONE_STAIRS, LegacyIds::RED_SANDSTONE_STAIRS, 0), "Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::SMOOTH_RED_SANDSTONE_STAIRS, LegacyIds::SMOOTH_RED_SANDSTONE_STAIRS, 0), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::SANDSTONE_STAIRS, LegacyIds::SANDSTONE_STAIRS, 0), "Sandstone Stairs", $sandstoneBreakInfo));
		$this->registerAllMeta(new Stair(new BID(Ids::SMOOTH_SANDSTONE_STAIRS, LegacyIds::SMOOTH_SANDSTONE_STAIRS, 0), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		$this->registerAllMeta(
			new Opaque(new BID(Ids::SANDSTONE, LegacyIds::SANDSTONE, Meta::SANDSTONE_NORMAL), "Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::CHISELED_SANDSTONE, LegacyIds::SANDSTONE, Meta::SANDSTONE_CHISELED), "Chiseled Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::CUT_SANDSTONE, LegacyIds::SANDSTONE, Meta::SANDSTONE_CUT), "Cut Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::SMOOTH_SANDSTONE, LegacyIds::SANDSTONE, Meta::SANDSTONE_SMOOTH), "Smooth Sandstone", $sandstoneBreakInfo),
		);
		$this->registerAllMeta(
			new Opaque(new BID(Ids::RED_SANDSTONE, LegacyIds::RED_SANDSTONE, Meta::SANDSTONE_NORMAL), "Red Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::CHISELED_RED_SANDSTONE, LegacyIds::RED_SANDSTONE, Meta::SANDSTONE_CHISELED), "Chiseled Red Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::CUT_RED_SANDSTONE, LegacyIds::RED_SANDSTONE, Meta::SANDSTONE_CUT), "Cut Red Sandstone", $sandstoneBreakInfo),
			new Opaque(new BID(Ids::SMOOTH_RED_SANDSTONE, LegacyIds::RED_SANDSTONE, Meta::SANDSTONE_SMOOTH), "Smooth Red Sandstone", $sandstoneBreakInfo),
		);

		$glazedTerracottaBreakInfo = new BreakInfo(1.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		foreach(DyeColor::getAll() as $color){
			$coloredName = function(string $name) use($color) : string{
				return $color->getDisplayName() . " " . $name;
			};
			$this->registerAllMeta(new GlazedTerracotta(BlockLegacyIdHelper::getGlazedTerracottaIdentifier($color), $coloredName("Glazed Terracotta"), $glazedTerracottaBreakInfo));
		}
		$this->registerAllMeta(new DyedShulkerBox(new BID(Ids::DYED_SHULKER_BOX, LegacyIds::SHULKER_BOX, 0, null, TileShulkerBox::class), "Dyed Shulker Box", $shulkerBoxBreakInfo));
		$this->registerAllMeta(new StainedGlass(new BID(Ids::STAINED_GLASS, LegacyIds::STAINED_GLASS, 0), "Stained Glass", $glassBreakInfo));
		$this->registerAllMeta(new StainedGlassPane(new BID(Ids::STAINED_GLASS_PANE, LegacyIds::STAINED_GLASS_PANE, 0), "Stained Glass Pane", $glassBreakInfo));
		$this->registerAllMeta(new StainedHardenedClay(new BID(Ids::STAINED_CLAY, LegacyIds::STAINED_CLAY, 0), "Stained Clay", $hardenedClayBreakInfo));
		$this->registerAllMeta(new StainedHardenedGlass(new BID(Ids::STAINED_HARDENED_GLASS, LegacyIds::HARD_STAINED_GLASS, 0), "Stained Hardened Glass", $hardenedGlassBreakInfo));
		$this->registerAllMeta(new StainedHardenedGlassPane(new BID(Ids::STAINED_HARDENED_GLASS_PANE, LegacyIds::HARD_STAINED_GLASS_PANE, 0), "Stained Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->registerAllMeta(new Carpet(new BID(Ids::CARPET, LegacyIds::CARPET, 0), "Carpet", new BreakInfo(0.1)));
		$this->registerAllMeta(new Concrete(new BID(Ids::CONCRETE, LegacyIds::CONCRETE, 0), "Concrete", new BreakInfo(1.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->registerAllMeta(new ConcretePowder(new BID(Ids::CONCRETE_POWDER, LegacyIds::CONCRETE_POWDER, 0), "Concrete Powder", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->registerAllMeta(new Wool(new BID(Ids::WOOL, LegacyIds::WOOL, 0), "Wool", new class(0.8, ToolType::SHEARS) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				$time = parent::getBreakTime($item);
				if($item->getBlockToolType() === ToolType::SHEARS){
					$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
				}

				return $time;
			}
		}));

		//TODO: in the future these won't all have the same hardness; they only do now because of the old metadata crap
		$wallBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->registerAllMeta(
			new Wall(new BID(Ids::COBBLESTONE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_COBBLESTONE), "Cobblestone Wall", $wallBreakInfo),
			new Wall(new BID(Ids::ANDESITE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_ANDESITE), "Andesite Wall", $wallBreakInfo),
			new Wall(new BID(Ids::BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_BRICK), "Brick Wall", $wallBreakInfo),
			new Wall(new BID(Ids::DIORITE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_DIORITE), "Diorite Wall", $wallBreakInfo),
			new Wall(new BID(Ids::END_STONE_BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_END_STONE_BRICK), "End Stone Brick Wall", $wallBreakInfo),
			new Wall(new BID(Ids::GRANITE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_GRANITE), "Granite Wall", $wallBreakInfo),
			new Wall(new BID(Ids::MOSSY_STONE_BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_MOSSY_STONE_BRICK), "Mossy Stone Brick Wall", $wallBreakInfo),
			new Wall(new BID(Ids::MOSSY_COBBLESTONE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_MOSSY_COBBLESTONE), "Mossy Cobblestone Wall", $wallBreakInfo),
			new Wall(new BID(Ids::NETHER_BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_NETHER_BRICK), "Nether Brick Wall", $wallBreakInfo),
			new Wall(new BID(Ids::PRISMARINE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_PRISMARINE), "Prismarine Wall", $wallBreakInfo),
			new Wall(new BID(Ids::RED_NETHER_BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_RED_NETHER_BRICK), "Red Nether Brick Wall", $wallBreakInfo),
			new Wall(new BID(Ids::RED_SANDSTONE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_RED_SANDSTONE), "Red Sandstone Wall", $wallBreakInfo),
			new Wall(new BID(Ids::SANDSTONE_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_SANDSTONE), "Sandstone Wall", $wallBreakInfo),
			new Wall(new BID(Ids::STONE_BRICK_WALL, LegacyIds::COBBLESTONE_WALL, Meta::WALL_STONE_BRICK), "Stone Brick Wall", $wallBreakInfo),
		);

		$this->registerElements();

		$chemistryTableBreakInfo = new BreakInfo(2.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->registerAllMeta(
			new ChemistryTable(new BID(Ids::COMPOUND_CREATOR, LegacyIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_COMPOUND_CREATOR), "Compound Creator", $chemistryTableBreakInfo),
			new ChemistryTable(new BID(Ids::ELEMENT_CONSTRUCTOR, LegacyIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_ELEMENT_CONSTRUCTOR), "Element Constructor", $chemistryTableBreakInfo),
			new ChemistryTable(new BID(Ids::LAB_TABLE, LegacyIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_LAB_TABLE), "Lab Table", $chemistryTableBreakInfo),
			new ChemistryTable(new BID(Ids::MATERIAL_REDUCER, LegacyIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_MATERIAL_REDUCER), "Material Reducer", $chemistryTableBreakInfo)
		);

		$this->registerAllMeta(new ChemicalHeat(new BID(Ids::CHEMICAL_HEAT, LegacyIds::CHEMICAL_HEAT, 0), "Heat Block", $chemistryTableBreakInfo));

		$this->registerMushroomBlocks();

		$this->registerAllMeta(new Coral(
			new BID(Ids::CORAL, LegacyIds::CORAL, 0),
			"Coral",
			BreakInfo::instant(),
		));
		$this->registerAllMeta(new FloorCoralFan(
			new BlockIdentifierFlattened(Ids::CORAL_FAN, LegacyIds::CORAL_FAN, [LegacyIds::CORAL_FAN_DEAD], 0, ItemIds::CORAL_FAN),
			"Coral Fan",
			BreakInfo::instant(),
		));
		$this->registerAllMeta(new WallCoralFan(
			new BlockIdentifierFlattened(Ids::WALL_CORAL_FAN, LegacyIds::CORAL_FAN_HANG, [LegacyIds::CORAL_FAN_HANG2, LegacyIds::CORAL_FAN_HANG3], 0, ItemIds::CORAL_FAN),
			"Wall Coral Fan",
			BreakInfo::instant(),
		));

		//region --- auto-generated TODOs for bedrock-1.11.0 ---
		//TODO: minecraft:bubble_column
		//TODO: minecraft:campfire
		//TODO: minecraft:cartography_table
		//TODO: minecraft:cauldron
		//TODO: minecraft:chain_command_block
		//TODO: minecraft:chorus_flower
		//TODO: minecraft:chorus_plant
		//TODO: minecraft:command_block
		//TODO: minecraft:composter
		//TODO: minecraft:conduit
		//TODO: minecraft:dispenser
		//TODO: minecraft:dropper
		//TODO: minecraft:end_gateway
		//TODO: minecraft:end_portal
		//TODO: minecraft:grindstone
		//TODO: minecraft:jigsaw
		//TODO: minecraft:kelp
		//TODO: minecraft:lava_cauldron
		//TODO: minecraft:movingBlock
		//TODO: minecraft:observer
		//TODO: minecraft:piston
		//TODO: minecraft:pistonArmCollision
		//TODO: minecraft:repeating_command_block
		//TODO: minecraft:scaffolding
		//TODO: minecraft:seagrass
		//TODO: minecraft:smithing_table
		//TODO: minecraft:sticky_piston
		//TODO: minecraft:structure_block
		//TODO: minecraft:turtle_egg
		//endregion

		//region --- auto-generated TODOs for bedrock-1.13.0 ---
		//TODO: minecraft:camera
		//TODO: minecraft:light_block
		//TODO: minecraft:stickyPistonArmCollision
		//TODO: minecraft:structure_void
		//TODO: minecraft:wither_rose
		//endregion

		//region --- auto-generated TODOs for bedrock-1.14.0 ---
		//TODO: minecraft:bee_nest
		//TODO: minecraft:beehive
		//TODO: minecraft:honey_block
		//TODO: minecraft:honeycomb_block
		//endregion

		//region --- auto-generated TODOs for bedrock-1.16.0 ---
		//TODO: minecraft:allow
		//TODO: minecraft:ancient_debris
		//TODO: minecraft:basalt
		//TODO: minecraft:blackstone
		//TODO: minecraft:blackstone_double_slab
		//TODO: minecraft:blackstone_slab
		//TODO: minecraft:blackstone_stairs
		//TODO: minecraft:blackstone_wall
		//TODO: minecraft:border_block
		//TODO: minecraft:chain
		//TODO: minecraft:chiseled_nether_bricks
		//TODO: minecraft:chiseled_polished_blackstone
		//TODO: minecraft:cracked_nether_bricks
		//TODO: minecraft:cracked_polished_blackstone_bricks
		//TODO: minecraft:crimson_button
		//TODO: minecraft:crimson_door
		//TODO: minecraft:crimson_double_slab
		//TODO: minecraft:crimson_fence
		//TODO: minecraft:crimson_fence_gate
		//TODO: minecraft:crimson_fungus
		//TODO: minecraft:crimson_hyphae
		//TODO: minecraft:crimson_nylium
		//TODO: minecraft:crimson_planks
		//TODO: minecraft:crimson_pressure_plate
		//TODO: minecraft:crimson_roots
		//TODO: minecraft:crimson_slab
		//TODO: minecraft:crimson_stairs
		//TODO: minecraft:crimson_standing_sign
		//TODO: minecraft:crimson_stem
		//TODO: minecraft:crimson_trapdoor
		//TODO: minecraft:crimson_wall_sign
		//TODO: minecraft:crying_obsidian
		//TODO: minecraft:deny
		//TODO: minecraft:gilded_blackstone
		//TODO: minecraft:lodestone
		//TODO: minecraft:nether_gold_ore
		//TODO: minecraft:nether_sprouts
		//TODO: minecraft:netherite_block
		//TODO: minecraft:polished_basalt
		//TODO: minecraft:polished_blackstone
		//TODO: minecraft:polished_blackstone_brick_double_slab
		//TODO: minecraft:polished_blackstone_brick_slab
		//TODO: minecraft:polished_blackstone_brick_stairs
		//TODO: minecraft:polished_blackstone_brick_wall
		//TODO: minecraft:polished_blackstone_bricks
		//TODO: minecraft:polished_blackstone_button
		//TODO: minecraft:polished_blackstone_double_slab
		//TODO: minecraft:polished_blackstone_pressure_plate
		//TODO: minecraft:polished_blackstone_slab
		//TODO: minecraft:polished_blackstone_stairs
		//TODO: minecraft:polished_blackstone_wall
		//TODO: minecraft:quartz_bricks
		//TODO: minecraft:respawn_anchor
		//TODO: minecraft:shroomlight
		//TODO: minecraft:soul_campfire
		//TODO: minecraft:soul_fire
		//TODO: minecraft:soul_lantern
		//TODO: minecraft:soul_soil
		//TODO: minecraft:soul_torch
		//TODO: minecraft:stripped_crimson_hyphae
		//TODO: minecraft:stripped_crimson_stem
		//TODO: minecraft:stripped_warped_hyphae
		//TODO: minecraft:stripped_warped_stem
		//TODO: minecraft:target
		//TODO: minecraft:twisting_vines
		//TODO: minecraft:warped_button
		//TODO: minecraft:warped_door
		//TODO: minecraft:warped_double_slab
		//TODO: minecraft:warped_fence
		//TODO: minecraft:warped_fence_gate
		//TODO: minecraft:warped_fungus
		//TODO: minecraft:warped_hyphae
		//TODO: minecraft:warped_nylium
		//TODO: minecraft:warped_planks
		//TODO: minecraft:warped_pressure_plate
		//TODO: minecraft:warped_roots
		//TODO: minecraft:warped_slab
		//TODO: minecraft:warped_stairs
		//TODO: minecraft:warped_standing_sign
		//TODO: minecraft:warped_stem
		//TODO: minecraft:warped_trapdoor
		//TODO: minecraft:warped_wall_sign
		//TODO: minecraft:warped_wart_block
		//TODO: minecraft:weeping_vines
		//endregion
	}

	private function registerMushroomBlocks() : void{
		//shrooms have to be handled one by one because some metas are variants and others aren't, and they can't be
		//separated by a bitmask

		$mushroomBlockBreakInfo = new BreakInfo(0.2, ToolType::AXE);

		$mushroomBlocks = [
			new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK, LegacyIds::BROWN_MUSHROOM_BLOCK, 0), "Brown Mushroom Block", $mushroomBlockBreakInfo),
			new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK, LegacyIds::RED_MUSHROOM_BLOCK, 0), "Red Mushroom Block", $mushroomBlockBreakInfo)
		];

		//caps
		foreach([
			Meta::MUSHROOM_BLOCK_ALL_PORES,
			Meta::MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER,
			Meta::MUSHROOM_BLOCK_CAP_NORTH_SIDE,
			Meta::MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER,
			Meta::MUSHROOM_BLOCK_CAP_WEST_SIDE,
			Meta::MUSHROOM_BLOCK_CAP_TOP_ONLY,
			Meta::MUSHROOM_BLOCK_CAP_EAST_SIDE,
			Meta::MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER,
			Meta::MUSHROOM_BLOCK_CAP_SOUTH_SIDE,
			Meta::MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER,
			Meta::MUSHROOM_BLOCK_ALL_CAP,
		] as $meta){
			foreach($mushroomBlocks as $block){
				$block->readStateFromData($block->getId(), $meta);
				$this->remap($block->getId(), $meta, clone $block);
			}
		}

		//and the invalid states
		for($meta = 11; $meta <= 13; ++$meta){
			foreach($mushroomBlocks as $block){
				$this->remap($block->getId(), $meta, clone $block);
			}
		}

		//finally, the stems
		$mushroomStem = new MushroomStem(new BID(Ids::MUSHROOM_STEM, LegacyIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_STEM), "Mushroom Stem", $mushroomBlockBreakInfo);
		$this->remap(LegacyIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_STEM, $mushroomStem);
		$this->remap(LegacyIds::RED_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_STEM, $mushroomStem);
		$allSidedMushroomStem = new MushroomStem(new BID(Ids::ALL_SIDED_MUSHROOM_STEM, LegacyIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_ALL_STEM), "All Sided Mushroom Stem", $mushroomBlockBreakInfo);
		$this->remap(LegacyIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_ALL_STEM, $allSidedMushroomStem);
		$this->remap(LegacyIds::RED_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_ALL_STEM, $allSidedMushroomStem);
	}

	private function registerElements() : void{
		$instaBreak = BreakInfo::instant();
		$this->registerAllMeta(new Opaque(new BID(Ids::ELEMENT_ZERO, LegacyIds::ELEMENT_0, 0), "???", $instaBreak));

		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_HYDROGEN, LegacyIds::ELEMENT_1, 0), "Hydrogen", $instaBreak, "h", 1, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_HELIUM, LegacyIds::ELEMENT_2, 0), "Helium", $instaBreak, "he", 2, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LITHIUM, LegacyIds::ELEMENT_3, 0), "Lithium", $instaBreak, "li", 3, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BERYLLIUM, LegacyIds::ELEMENT_4, 0), "Beryllium", $instaBreak, "be", 4, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BORON, LegacyIds::ELEMENT_5, 0), "Boron", $instaBreak, "b", 5, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CARBON, LegacyIds::ELEMENT_6, 0), "Carbon", $instaBreak, "c", 6, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NITROGEN, LegacyIds::ELEMENT_7, 0), "Nitrogen", $instaBreak, "n", 7, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_OXYGEN, LegacyIds::ELEMENT_8, 0), "Oxygen", $instaBreak, "o", 8, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_FLUORINE, LegacyIds::ELEMENT_9, 0), "Fluorine", $instaBreak, "f", 9, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NEON, LegacyIds::ELEMENT_10, 0), "Neon", $instaBreak, "ne", 10, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SODIUM, LegacyIds::ELEMENT_11, 0), "Sodium", $instaBreak, "na", 11, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MAGNESIUM, LegacyIds::ELEMENT_12, 0), "Magnesium", $instaBreak, "mg", 12, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ALUMINUM, LegacyIds::ELEMENT_13, 0), "Aluminum", $instaBreak, "al", 13, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SILICON, LegacyIds::ELEMENT_14, 0), "Silicon", $instaBreak, "si", 14, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PHOSPHORUS, LegacyIds::ELEMENT_15, 0), "Phosphorus", $instaBreak, "p", 15, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SULFUR, LegacyIds::ELEMENT_16, 0), "Sulfur", $instaBreak, "s", 16, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CHLORINE, LegacyIds::ELEMENT_17, 0), "Chlorine", $instaBreak, "cl", 17, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ARGON, LegacyIds::ELEMENT_18, 0), "Argon", $instaBreak, "ar", 18, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_POTASSIUM, LegacyIds::ELEMENT_19, 0), "Potassium", $instaBreak, "k", 19, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CALCIUM, LegacyIds::ELEMENT_20, 0), "Calcium", $instaBreak, "ca", 20, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SCANDIUM, LegacyIds::ELEMENT_21, 0), "Scandium", $instaBreak, "sc", 21, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TITANIUM, LegacyIds::ELEMENT_22, 0), "Titanium", $instaBreak, "ti", 22, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_VANADIUM, LegacyIds::ELEMENT_23, 0), "Vanadium", $instaBreak, "v", 23, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CHROMIUM, LegacyIds::ELEMENT_24, 0), "Chromium", $instaBreak, "cr", 24, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MANGANESE, LegacyIds::ELEMENT_25, 0), "Manganese", $instaBreak, "mn", 25, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_IRON, LegacyIds::ELEMENT_26, 0), "Iron", $instaBreak, "fe", 26, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_COBALT, LegacyIds::ELEMENT_27, 0), "Cobalt", $instaBreak, "co", 27, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NICKEL, LegacyIds::ELEMENT_28, 0), "Nickel", $instaBreak, "ni", 28, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_COPPER, LegacyIds::ELEMENT_29, 0), "Copper", $instaBreak, "cu", 29, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ZINC, LegacyIds::ELEMENT_30, 0), "Zinc", $instaBreak, "zn", 30, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_GALLIUM, LegacyIds::ELEMENT_31, 0), "Gallium", $instaBreak, "ga", 31, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_GERMANIUM, LegacyIds::ELEMENT_32, 0), "Germanium", $instaBreak, "ge", 32, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ARSENIC, LegacyIds::ELEMENT_33, 0), "Arsenic", $instaBreak, "as", 33, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SELENIUM, LegacyIds::ELEMENT_34, 0), "Selenium", $instaBreak, "se", 34, 5));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BROMINE, LegacyIds::ELEMENT_35, 0), "Bromine", $instaBreak, "br", 35, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_KRYPTON, LegacyIds::ELEMENT_36, 0), "Krypton", $instaBreak, "kr", 36, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RUBIDIUM, LegacyIds::ELEMENT_37, 0), "Rubidium", $instaBreak, "rb", 37, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_STRONTIUM, LegacyIds::ELEMENT_38, 0), "Strontium", $instaBreak, "sr", 38, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_YTTRIUM, LegacyIds::ELEMENT_39, 0), "Yttrium", $instaBreak, "y", 39, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ZIRCONIUM, LegacyIds::ELEMENT_40, 0), "Zirconium", $instaBreak, "zr", 40, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NIOBIUM, LegacyIds::ELEMENT_41, 0), "Niobium", $instaBreak, "nb", 41, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MOLYBDENUM, LegacyIds::ELEMENT_42, 0), "Molybdenum", $instaBreak, "mo", 42, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TECHNETIUM, LegacyIds::ELEMENT_43, 0), "Technetium", $instaBreak, "tc", 43, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RUTHENIUM, LegacyIds::ELEMENT_44, 0), "Ruthenium", $instaBreak, "ru", 44, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RHODIUM, LegacyIds::ELEMENT_45, 0), "Rhodium", $instaBreak, "rh", 45, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PALLADIUM, LegacyIds::ELEMENT_46, 0), "Palladium", $instaBreak, "pd", 46, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SILVER, LegacyIds::ELEMENT_47, 0), "Silver", $instaBreak, "ag", 47, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CADMIUM, LegacyIds::ELEMENT_48, 0), "Cadmium", $instaBreak, "cd", 48, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_INDIUM, LegacyIds::ELEMENT_49, 0), "Indium", $instaBreak, "in", 49, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TIN, LegacyIds::ELEMENT_50, 0), "Tin", $instaBreak, "sn", 50, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ANTIMONY, LegacyIds::ELEMENT_51, 0), "Antimony", $instaBreak, "sb", 51, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TELLURIUM, LegacyIds::ELEMENT_52, 0), "Tellurium", $instaBreak, "te", 52, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_IODINE, LegacyIds::ELEMENT_53, 0), "Iodine", $instaBreak, "i", 53, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_XENON, LegacyIds::ELEMENT_54, 0), "Xenon", $instaBreak, "xe", 54, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CESIUM, LegacyIds::ELEMENT_55, 0), "Cesium", $instaBreak, "cs", 55, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BARIUM, LegacyIds::ELEMENT_56, 0), "Barium", $instaBreak, "ba", 56, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LANTHANUM, LegacyIds::ELEMENT_57, 0), "Lanthanum", $instaBreak, "la", 57, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CERIUM, LegacyIds::ELEMENT_58, 0), "Cerium", $instaBreak, "ce", 58, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PRASEODYMIUM, LegacyIds::ELEMENT_59, 0), "Praseodymium", $instaBreak, "pr", 59, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NEODYMIUM, LegacyIds::ELEMENT_60, 0), "Neodymium", $instaBreak, "nd", 60, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PROMETHIUM, LegacyIds::ELEMENT_61, 0), "Promethium", $instaBreak, "pm", 61, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SAMARIUM, LegacyIds::ELEMENT_62, 0), "Samarium", $instaBreak, "sm", 62, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_EUROPIUM, LegacyIds::ELEMENT_63, 0), "Europium", $instaBreak, "eu", 63, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_GADOLINIUM, LegacyIds::ELEMENT_64, 0), "Gadolinium", $instaBreak, "gd", 64, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TERBIUM, LegacyIds::ELEMENT_65, 0), "Terbium", $instaBreak, "tb", 65, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_DYSPROSIUM, LegacyIds::ELEMENT_66, 0), "Dysprosium", $instaBreak, "dy", 66, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_HOLMIUM, LegacyIds::ELEMENT_67, 0), "Holmium", $instaBreak, "ho", 67, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ERBIUM, LegacyIds::ELEMENT_68, 0), "Erbium", $instaBreak, "er", 68, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_THULIUM, LegacyIds::ELEMENT_69, 0), "Thulium", $instaBreak, "tm", 69, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_YTTERBIUM, LegacyIds::ELEMENT_70, 0), "Ytterbium", $instaBreak, "yb", 70, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LUTETIUM, LegacyIds::ELEMENT_71, 0), "Lutetium", $instaBreak, "lu", 71, 8));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_HAFNIUM, LegacyIds::ELEMENT_72, 0), "Hafnium", $instaBreak, "hf", 72, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TANTALUM, LegacyIds::ELEMENT_73, 0), "Tantalum", $instaBreak, "ta", 73, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TUNGSTEN, LegacyIds::ELEMENT_74, 0), "Tungsten", $instaBreak, "w", 74, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RHENIUM, LegacyIds::ELEMENT_75, 0), "Rhenium", $instaBreak, "re", 75, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_OSMIUM, LegacyIds::ELEMENT_76, 0), "Osmium", $instaBreak, "os", 76, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_IRIDIUM, LegacyIds::ELEMENT_77, 0), "Iridium", $instaBreak, "ir", 77, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PLATINUM, LegacyIds::ELEMENT_78, 0), "Platinum", $instaBreak, "pt", 78, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_GOLD, LegacyIds::ELEMENT_79, 0), "Gold", $instaBreak, "au", 79, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MERCURY, LegacyIds::ELEMENT_80, 0), "Mercury", $instaBreak, "hg", 80, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_THALLIUM, LegacyIds::ELEMENT_81, 0), "Thallium", $instaBreak, "tl", 81, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LEAD, LegacyIds::ELEMENT_82, 0), "Lead", $instaBreak, "pb", 82, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BISMUTH, LegacyIds::ELEMENT_83, 0), "Bismuth", $instaBreak, "bi", 83, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_POLONIUM, LegacyIds::ELEMENT_84, 0), "Polonium", $instaBreak, "po", 84, 4));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ASTATINE, LegacyIds::ELEMENT_85, 0), "Astatine", $instaBreak, "at", 85, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RADON, LegacyIds::ELEMENT_86, 0), "Radon", $instaBreak, "rn", 86, 7));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_FRANCIUM, LegacyIds::ELEMENT_87, 0), "Francium", $instaBreak, "fr", 87, 0));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RADIUM, LegacyIds::ELEMENT_88, 0), "Radium", $instaBreak, "ra", 88, 1));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ACTINIUM, LegacyIds::ELEMENT_89, 0), "Actinium", $instaBreak, "ac", 89, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_THORIUM, LegacyIds::ELEMENT_90, 0), "Thorium", $instaBreak, "th", 90, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PROTACTINIUM, LegacyIds::ELEMENT_91, 0), "Protactinium", $instaBreak, "pa", 91, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_URANIUM, LegacyIds::ELEMENT_92, 0), "Uranium", $instaBreak, "u", 92, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NEPTUNIUM, LegacyIds::ELEMENT_93, 0), "Neptunium", $instaBreak, "np", 93, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_PLUTONIUM, LegacyIds::ELEMENT_94, 0), "Plutonium", $instaBreak, "pu", 94, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_AMERICIUM, LegacyIds::ELEMENT_95, 0), "Americium", $instaBreak, "am", 95, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CURIUM, LegacyIds::ELEMENT_96, 0), "Curium", $instaBreak, "cm", 96, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BERKELIUM, LegacyIds::ELEMENT_97, 0), "Berkelium", $instaBreak, "bk", 97, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_CALIFORNIUM, LegacyIds::ELEMENT_98, 0), "Californium", $instaBreak, "cf", 98, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_EINSTEINIUM, LegacyIds::ELEMENT_99, 0), "Einsteinium", $instaBreak, "es", 99, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_FERMIUM, LegacyIds::ELEMENT_100, 0), "Fermium", $instaBreak, "fm", 100, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MENDELEVIUM, LegacyIds::ELEMENT_101, 0), "Mendelevium", $instaBreak, "md", 101, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NOBELIUM, LegacyIds::ELEMENT_102, 0), "Nobelium", $instaBreak, "no", 102, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LAWRENCIUM, LegacyIds::ELEMENT_103, 0), "Lawrencium", $instaBreak, "lr", 103, 9));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_RUTHERFORDIUM, LegacyIds::ELEMENT_104, 0), "Rutherfordium", $instaBreak, "rf", 104, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_DUBNIUM, LegacyIds::ELEMENT_105, 0), "Dubnium", $instaBreak, "db", 105, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_SEABORGIUM, LegacyIds::ELEMENT_106, 0), "Seaborgium", $instaBreak, "sg", 106, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_BOHRIUM, LegacyIds::ELEMENT_107, 0), "Bohrium", $instaBreak, "bh", 107, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_HASSIUM, LegacyIds::ELEMENT_108, 0), "Hassium", $instaBreak, "hs", 108, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MEITNERIUM, LegacyIds::ELEMENT_109, 0), "Meitnerium", $instaBreak, "mt", 109, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_DARMSTADTIUM, LegacyIds::ELEMENT_110, 0), "Darmstadtium", $instaBreak, "ds", 110, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_ROENTGENIUM, LegacyIds::ELEMENT_111, 0), "Roentgenium", $instaBreak, "rg", 111, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_COPERNICIUM, LegacyIds::ELEMENT_112, 0), "Copernicium", $instaBreak, "cn", 112, 2));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_NIHONIUM, LegacyIds::ELEMENT_113, 0), "Nihonium", $instaBreak, "nh", 113, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_FLEROVIUM, LegacyIds::ELEMENT_114, 0), "Flerovium", $instaBreak, "fl", 114, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_MOSCOVIUM, LegacyIds::ELEMENT_115, 0), "Moscovium", $instaBreak, "mc", 115, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_LIVERMORIUM, LegacyIds::ELEMENT_116, 0), "Livermorium", $instaBreak, "lv", 116, 3));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_TENNESSINE, LegacyIds::ELEMENT_117, 0), "Tennessine", $instaBreak, "ts", 117, 6));
		$this->registerAllMeta(new Element(new BID(Ids::ELEMENT_OGANESSON, LegacyIds::ELEMENT_118, 0), "Oganesson", $instaBreak, "og", 118, 7));
	}

	/**
	 * Claims the whole metadata range (0-15) for all IDs associated with this block. Any unregistered states will be
	 * mapped to the default (provided) state.
	 *
	 * This should only be used when this block type has sole ownership of an ID. For IDs which contain multiple block
	 * types (variants), the regular register() method should be used instead.
	 */
	private function registerAllMeta(Block $default, Block ...$additional) : void{
		$ids = [];
		$this->register($default);
		foreach($default->getIdInfo()->getAllLegacyBlockIds() as $id){
			$ids[$id] = $id;
		}
		foreach($additional as $block){
			$this->register($block);
			foreach($block->getIdInfo()->getAllLegacyBlockIds() as $id){
				$ids[$id] = $id;
			}
		}

		foreach($ids as $id){
			$this->defaultStateIndexes[$id] = $default->getFullId();
		}
	}

	private function registerSlabWithDoubleHighBitsRemapping(Slab $block) : void{
		$this->register($block);
		$identifierFlattened = $block->getIdInfo();
		if($identifierFlattened instanceof BlockIdentifierFlattened){
			$this->remap($identifierFlattened->getSecondId(), $identifierFlattened->getLegacyVariant() | 0x8, $block->setSlabType(SlabType::DOUBLE()));
		}
	}

	/**
	 * Maps a block type to its corresponding ID. This is necessary to ensure that the block is correctly loaded when
	 * reading from disk storage.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param bool  $override Whether to override existing registrations
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public function register(Block $block, bool $override = false) : void{
		$variant = $block->getIdInfo()->getLegacyVariant();

		$stateMask = $block->getStateBitmask();
		if(($variant & $stateMask) !== 0){
			throw new \InvalidArgumentException("Block variant collides with state bitmask");
		}

		foreach($block->getIdInfo()->getAllLegacyBlockIds() as $id){
			if(!$override && $this->isRegistered($id, $variant)){
				throw new \InvalidArgumentException("Block registration $id:$variant conflicts with an existing block");
			}

			for($m = $variant; $m <= ($variant | $stateMask); ++$m){
				if(($m & ~$stateMask) !== $variant){
					continue;
				}

				if(!$override && $this->isRegistered($id, $m)){
					throw new \InvalidArgumentException("Block registration " . get_class($block) . " has states which conflict with other blocks");
				}

				$index = ($id << Block::INTERNAL_METADATA_BITS) | $m;

				$v = clone $block;
				try{
					$v->readStateFromData($id, $m);
					if($v->getFullId() !== $index){
						//if the fullID comes back different, this is a broken state that we can't rely on; map it to default
						throw new InvalidBlockStateException("Corrupted state");
					}
				}catch(InvalidBlockStateException $e){ //invalid property combination, fill the default state
					$this->fillStaticArrays($index, $block);
					continue;
				}

				$this->fillStaticArrays($index, $v);
			}
		}
	}

	public function remap(int $id, int $meta, Block $block) : void{
		$index = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		if($this->isRegistered($id, $meta)){
			$existing = $this->fullList[$index] ?? null;
			if($existing !== null && $existing->getFullId() === $index){
				throw new \InvalidArgumentException("$id:$meta is already mapped");
			}else{
				//if it's not a match, this was already remapped for some reason; remapping overwrites are OK
			}
		}
		$this->fillStaticArrays(($id << Block::INTERNAL_METADATA_BITS) | $meta, $block);
	}

	private function fillStaticArrays(int $index, Block $block) : void{
		$fullId = $block->getFullId();
		if($index !== $fullId){
			$this->mappedStateIndexes[$index] = $fullId;
		}else{
			$this->fullList[$index] = $block;
			$this->blastResistance[$index] = $block->getBreakInfo()->getBlastResistance();
			$this->light[$index] = $block->getLightLevel();
			$this->lightFilter[$index] = min(15, $block->getLightFilter() + LightUpdate::BASE_LIGHT_FILTER);
			if($block->blocksDirectSkyLight()){
				$this->blocksDirectSkyLight[$index] = true;
			}
		}
	}

	/**
	 * @deprecated This method should ONLY be used for deserializing data, e.g. from a config or database. For all other
	 * purposes, use VanillaBlocks.
	 * @see VanillaBlocks
	 *
	 * Deserializes a block from the provided legacy ID and legacy meta.
	 */
	public function get(int $id, int $meta) : Block{
		if($meta < 0 || $meta >= (1 << Block::INTERNAL_METADATA_BITS)){
			throw new \InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		$index = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		if($index < 0){
			throw new \InvalidArgumentException("Block ID $id is out of bounds");
		}
		if(isset($this->fullList[$index])){ //hot
			$block = clone $this->fullList[$index];
		}elseif(($mappedIndex = $this->getMappedStateId($index)) !== $index && isset($this->fullList[$mappedIndex])){ //cold
			$block = clone $this->fullList[$mappedIndex];
		}else{
			$block = new UnknownBlock(new BID($id, $id, $meta), BreakInfo::instant());
		}

		return $block;
	}

	public function fromFullBlock(int $fullState) : Block{
		return $this->get($fullState >> Block::INTERNAL_METADATA_BITS, $fullState & Block::INTERNAL_METADATA_MASK);
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public function isRegistered(int $id, int $meta = 0) : bool{
		$index = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		$b = $this->fullList[$index] ?? null;
		if($b === null){
			$mappedIndex = $this->mappedStateIndexes[$index] ?? $this->defaultStateIndexes[$id] ?? null;
			if($mappedIndex === null){
				return false;
			}
			$b = $this->fullList[$mappedIndex] ?? null;
		}
		return $b !== null && !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 */
	public function getAllKnownStates() : array{
		return $this->fullList;
	}

	/**
	 * Returns the ID of the state mapped to the given state ID.
	 * Used to correct invalid blockstates found in loaded chunks.
	 */
	public function getMappedStateId(int $fullState) : int{
		if(isset($this->fullList[$fullState])){
			return $fullState;
		}
		return $this->mappedStateIndexes[$fullState] ?? $this->defaultStateIndexes[$fullState >> Block::INTERNAL_METADATA_BITS] ?? $fullState;
	}
}
