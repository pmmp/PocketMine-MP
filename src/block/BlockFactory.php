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
use pocketmine\block\utils\TreeType;
use pocketmine\data\bedrock\block\BlockLegacyMetadata as Meta;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\light\LightUpdate;
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
	 * Index of default states for every block type
	 * @var Block[]
	 * @phpstan-var array<int, Block>
	 */
	private array $typeIndex = [];

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
		$this->register(new ActivatorRail(new BID(Ids::ACTIVATOR_RAIL, ItemIds::ACTIVATOR_RAIL, 0), "Activator Rail", $railBreakInfo));
		$this->register(new Air(new BID(Ids::AIR, ItemIds::AIR, 0), "Air", BreakInfo::indestructible(-1.0)));
		$this->register(new Anvil(new BID(Ids::ANVIL, ItemIds::ANVIL, 0), "Anvil", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->register(new Bamboo(new BID(Ids::BAMBOO, ItemIds::BAMBOO, 0), "Bamboo", new class(2.0 /* 1.0 in PC */, ToolType::AXE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}));
		$this->register(new BambooSapling(new BID(Ids::BAMBOO_SAPLING, ItemIds::BAMBOO_SAPLING, 0), "Bamboo Sapling", BreakInfo::instant()));

		$bannerBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->register(new FloorBanner(new BID(Ids::BANNER, ItemIds::BANNER, 0, TileBanner::class), "Banner", $bannerBreakInfo));
		$this->register(new WallBanner(new BID(Ids::WALL_BANNER, ItemIds::BANNER, 0, TileBanner::class), "Wall Banner", $bannerBreakInfo));
		$this->register(new Barrel(new BID(Ids::BARREL, ItemIds::BARREL, 0, TileBarrel::class), "Barrel", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new Transparent(new BID(Ids::BARRIER, ItemIds::BARRIER, 0), "Barrier", BreakInfo::indestructible()));
		$this->register(new Beacon(new BID(Ids::BEACON, ItemIds::BEACON, 0, TileBeacon::class), "Beacon", new BreakInfo(3.0)));
		$this->register(new Bed(new BID(Ids::BED, ItemIds::BED, 0, TileBed::class), "Bed Block", new BreakInfo(0.2)));
		$this->register(new Bedrock(new BID(Ids::BEDROCK, ItemIds::BEDROCK, 0), "Bedrock", BreakInfo::indestructible()));

		$this->register(new Beetroot(new BID(Ids::BEETROOTS, ItemIds::BEETROOT_BLOCK, 0), "Beetroot Block", BreakInfo::instant()));
		$this->register(new Bell(new BID(Ids::BELL, ItemIds::BELL, 0, TileBell::class), "Bell", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new BlueIce(new BID(Ids::BLUE_ICE, ItemIds::BLUE_ICE, 0), "Blue Ice", new BreakInfo(2.8, ToolType::PICKAXE)));
		$this->register(new BoneBlock(new BID(Ids::BONE_BLOCK, ItemIds::BONE_BLOCK, 0), "Bone Block", new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Bookshelf(new BID(Ids::BOOKSHELF, ItemIds::BOOKSHELF, 0), "Bookshelf", new BreakInfo(1.5, ToolType::AXE)));
		$this->register(new BrewingStand(new BID(Ids::BREWING_STAND, ItemIds::BREWING_STAND, 0, TileBrewingStand::class), "Brewing Stand", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$bricksBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Stair(new BID(Ids::BRICK_STAIRS, ItemIds::BRICK_STAIRS, 0), "Brick Stairs", $bricksBreakInfo));
		$this->register(new Opaque(new BID(Ids::BRICKS, ItemIds::BRICK_BLOCK, 0), "Bricks", $bricksBreakInfo));

		$this->register(new BrownMushroom(new BID(Ids::BROWN_MUSHROOM, ItemIds::BROWN_MUSHROOM, 0), "Brown Mushroom", BreakInfo::instant()));
		$this->register(new Cactus(new BID(Ids::CACTUS, ItemIds::CACTUS, 0), "Cactus", new BreakInfo(0.4)));
		$this->register(new Cake(new BID(Ids::CAKE, ItemIds::CAKE, 0), "Cake", new BreakInfo(0.5)));
		$this->register(new Carrot(new BID(Ids::CARROTS, ItemIds::CARROTS, 0), "Carrot Block", BreakInfo::instant()));

		$chestBreakInfo = new BreakInfo(2.5, ToolType::AXE);
		$this->register(new Chest(new BID(Ids::CHEST, ItemIds::CHEST, 0, TileChest::class), "Chest", $chestBreakInfo));
		$this->register(new Clay(new BID(Ids::CLAY, ItemIds::CLAY_BLOCK, 0), "Clay Block", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->register(new Coal(new BID(Ids::COAL, ItemIds::COAL_BLOCK, 0), "Coal Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->register(new CoalOre(new BID(Ids::COAL_ORE, ItemIds::COAL_ORE, 0), "Coal Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$cobblestoneBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register($cobblestone = new Opaque(new BID(Ids::COBBLESTONE, ItemIds::COBBLESTONE, 0), "Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::MOSSY_COBBLESTONE, ItemIds::MOSSY_COBBLESTONE, 0), "Mossy Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::COBBLESTONE_STAIRS, ItemIds::COBBLESTONE_STAIRS, 0), "Cobblestone Stairs", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_COBBLESTONE_STAIRS, ItemIds::MOSSY_COBBLESTONE_STAIRS, 0), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		$this->register(new Cobweb(new BID(Ids::COBWEB, ItemIds::COBWEB, 0), "Cobweb", new BreakInfo(4.0, ToolType::SWORD | ToolType::SHEARS, 1)));
		$this->register(new CocoaBlock(new BID(Ids::COCOA_POD, ItemIds::COCOA, 0), "Cocoa Block", new BreakInfo(0.2, ToolType::AXE, 0, 15.0)));
		$this->register(new CoralBlock(new BID(Ids::CORAL_BLOCK, ItemIds::CORAL_BLOCK, 0), "Coral Block", new BreakInfo(7.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new CraftingTable(new BID(Ids::CRAFTING_TABLE, ItemIds::CRAFTING_TABLE, 0), "Crafting Table", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new DaylightSensor(new BID(Ids::DAYLIGHT_SENSOR, ItemIds::DAYLIGHT_DETECTOR, 0, TileDaylightSensor::class), "Daylight Sensor", new BreakInfo(0.2, ToolType::AXE)));
		$this->register(new DeadBush(new BID(Ids::DEAD_BUSH, ItemIds::DEADBUSH, 0), "Dead Bush", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DetectorRail(new BID(Ids::DETECTOR_RAIL, ItemIds::DETECTOR_RAIL, 0), "Detector Rail", $railBreakInfo));

		$this->register(new Opaque(new BID(Ids::DIAMOND, ItemIds::DIAMOND_BLOCK, 0), "Diamond Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new DiamondOre(new BID(Ids::DIAMOND_ORE, ItemIds::DIAMOND_ORE, 0), "Diamond Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new Dirt(new BID(Ids::DIRT, ItemIds::DIRT, 0), "Dirt", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new DoublePlant(new BID(Ids::SUNFLOWER, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_SUNFLOWER), "Sunflower", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::LILAC, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LILAC), "Lilac", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::ROSE_BUSH, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_ROSE_BUSH), "Rose Bush", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::PEONY, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_PEONY), "Peony", BreakInfo::instant()));
		$this->register(new DoubleTallGrass(new BID(Ids::DOUBLE_TALLGRASS, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_TALLGRASS), "Double Tallgrass", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DoubleTallGrass(new BID(Ids::LARGE_FERN, ItemIds::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LARGE_FERN), "Large Fern", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DragonEgg(new BID(Ids::DRAGON_EGG, ItemIds::DRAGON_EGG, 0), "Dragon Egg", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new DriedKelp(new BID(Ids::DRIED_KELP, ItemIds::DRIED_KELP_BLOCK, 0), "Dried Kelp Block", new BreakInfo(0.5, ToolType::NONE, 0, 12.5)));
		$this->register(new Opaque(new BID(Ids::EMERALD, ItemIds::EMERALD_BLOCK, 0), "Emerald Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new EmeraldOre(new BID(Ids::EMERALD_ORE, ItemIds::EMERALD_ORE, 0), "Emerald Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new EnchantingTable(new BID(Ids::ENCHANTING_TABLE, ItemIds::ENCHANTING_TABLE, 0, TileEnchantingTable::class), "Enchanting Table", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->register(new EndPortalFrame(new BID(Ids::END_PORTAL_FRAME, ItemIds::END_PORTAL_FRAME, 0), "End Portal Frame", BreakInfo::indestructible()));
		$this->register(new EndRod(new BID(Ids::END_ROD, ItemIds::END_ROD, 0), "End Rod", BreakInfo::instant()));
		$this->register(new Opaque(new BID(Ids::END_STONE, ItemIds::END_STONE, 0), "End Stone", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 45.0)));

		$endBrickBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.0);
		$this->register(new Opaque(new BID(Ids::END_STONE_BRICKS, ItemIds::END_BRICKS, 0), "End Stone Bricks", $endBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::END_STONE_BRICK_STAIRS, ItemIds::END_BRICK_STAIRS, 0), "End Stone Brick Stairs", $endBrickBreakInfo));

		$this->register(new EnderChest(new BID(Ids::ENDER_CHEST, ItemIds::ENDER_CHEST, 0, TileEnderChest::class), "Ender Chest", new BreakInfo(22.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3000.0)));
		$this->register(new Farmland(new BID(Ids::FARMLAND, ItemIds::FARMLAND, 0), "Farmland", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->register(new Fire(new BID(Ids::FIRE, ItemIds::FIRE, 0), "Fire Block", BreakInfo::instant()));
		$this->register(new FletchingTable(new BID(Ids::FLETCHING_TABLE, ItemIds::FLETCHING_TABLE, 0), "Fletching Table", new BreakInfo(2.5, ToolType::AXE, 0, 2.5)));
		$this->register(new Flower(new BID(Ids::DANDELION, ItemIds::DANDELION, 0), "Dandelion", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::POPPY, ItemIds::RED_FLOWER, Meta::FLOWER_POPPY), "Poppy", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::ALLIUM, ItemIds::RED_FLOWER, Meta::FLOWER_ALLIUM), "Allium", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::AZURE_BLUET, ItemIds::RED_FLOWER, Meta::FLOWER_AZURE_BLUET), "Azure Bluet", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::BLUE_ORCHID, ItemIds::RED_FLOWER, Meta::FLOWER_BLUE_ORCHID), "Blue Orchid", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::CORNFLOWER, ItemIds::RED_FLOWER, Meta::FLOWER_CORNFLOWER), "Cornflower", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::LILY_OF_THE_VALLEY, ItemIds::RED_FLOWER, Meta::FLOWER_LILY_OF_THE_VALLEY), "Lily of the Valley", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::ORANGE_TULIP, ItemIds::RED_FLOWER, Meta::FLOWER_ORANGE_TULIP), "Orange Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::OXEYE_DAISY, ItemIds::RED_FLOWER, Meta::FLOWER_OXEYE_DAISY), "Oxeye Daisy", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::PINK_TULIP, ItemIds::RED_FLOWER, Meta::FLOWER_PINK_TULIP), "Pink Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::RED_TULIP, ItemIds::RED_FLOWER, Meta::FLOWER_RED_TULIP), "Red Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::WHITE_TULIP, ItemIds::RED_FLOWER, Meta::FLOWER_WHITE_TULIP), "White Tulip", BreakInfo::instant()));
		$this->register(new FlowerPot(new BID(Ids::FLOWER_POT, ItemIds::FLOWER_POT, 0, TileFlowerPot::class), "Flower Pot", BreakInfo::instant()));
		$this->register(new FrostedIce(new BID(Ids::FROSTED_ICE, ItemIds::FROSTED_ICE, 0), "Frosted Ice", new BreakInfo(2.5, ToolType::PICKAXE)));
		$this->register(new Furnace(new BID(Ids::FURNACE, ItemIds::FURNACE, 0, TileNormalFurnace::class), "Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Furnace(new BID(Ids::BLAST_FURNACE, ItemIds::BLAST_FURNACE, 0, TileBlastFurnace::class), "Blast Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Furnace(new BID(Ids::SMOKER, ItemIds::SMOKER, 0, TileSmoker::class), "Smoker", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$glassBreakInfo = new BreakInfo(0.3);
		$this->register(new Glass(new BID(Ids::GLASS, ItemIds::GLASS, 0), "Glass", $glassBreakInfo));
		$this->register(new GlassPane(new BID(Ids::GLASS_PANE, ItemIds::GLASS_PANE, 0), "Glass Pane", $glassBreakInfo));
		$this->register(new GlowingObsidian(new BID(Ids::GLOWING_OBSIDIAN, ItemIds::GLOWINGOBSIDIAN, 0), "Glowing Obsidian", new BreakInfo(10.0, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 50.0)));
		$this->register(new Glowstone(new BID(Ids::GLOWSTONE, ItemIds::GLOWSTONE, 0), "Glowstone", new BreakInfo(0.3, ToolType::PICKAXE)));
		$this->register(new Opaque(new BID(Ids::GOLD, ItemIds::GOLD_BLOCK, 0), "Gold Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new Opaque(new BID(Ids::GOLD_ORE, ItemIds::GOLD_ORE, 0), "Gold Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));

		$grassBreakInfo = new BreakInfo(0.6, ToolType::SHOVEL);
		$this->register(new Grass(new BID(Ids::GRASS, ItemIds::GRASS, 0), "Grass", $grassBreakInfo));
		$this->register(new GrassPath(new BID(Ids::GRASS_PATH, ItemIds::GRASS_PATH, 0), "Grass Path", $grassBreakInfo));
		$this->register(new Gravel(new BID(Ids::GRAVEL, ItemIds::GRAVEL, 0), "Gravel", new BreakInfo(0.6, ToolType::SHOVEL)));

		$hardenedClayBreakInfo = new BreakInfo(1.25, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 21.0);
		$this->register(new HardenedClay(new BID(Ids::HARDENED_CLAY, ItemIds::HARDENED_CLAY, 0), "Hardened Clay", $hardenedClayBreakInfo));

		$hardenedGlassBreakInfo = new BreakInfo(10.0);
		$this->register(new HardenedGlass(new BID(Ids::HARDENED_GLASS, ItemIds::HARD_GLASS, 0), "Hardened Glass", $hardenedGlassBreakInfo));
		$this->register(new HardenedGlassPane(new BID(Ids::HARDENED_GLASS_PANE, ItemIds::HARD_GLASS_PANE, 0), "Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->register(new HayBale(new BID(Ids::HAY_BALE, ItemIds::HAY_BALE, 0), "Hay Bale", new BreakInfo(0.5)));
		$this->register(new Hopper(new BID(Ids::HOPPER, ItemIds::HOPPER, 0, TileHopper::class), "Hopper", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0)));
		$this->register(new Ice(new BID(Ids::ICE, ItemIds::ICE, 0), "Ice", new BreakInfo(0.5, ToolType::PICKAXE)));

		$updateBlockBreakInfo = new BreakInfo(1.0);
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE, ItemIds::INFO_UPDATE, 0), "update!", $updateBlockBreakInfo));
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE2, ItemIds::INFO_UPDATE2, 0), "ate!upd", $updateBlockBreakInfo));
		$this->register(new Transparent(new BID(Ids::INVISIBLE_BEDROCK, ItemIds::INVISIBLEBEDROCK, 0), "Invisible Bedrock", BreakInfo::indestructible()));

		$ironBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::IRON, ItemIds::IRON_BLOCK, 0), "Iron Block", $ironBreakInfo));
		$this->register(new Thin(new BID(Ids::IRON_BARS, ItemIds::IRON_BARS, 0), "Iron Bars", $ironBreakInfo));
		$ironDoorBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 25.0);
		$this->register(new Door(new BID(Ids::IRON_DOOR, ItemIds::IRON_DOOR, 0), "Iron Door", $ironDoorBreakInfo));
		$this->register(new Trapdoor(new BID(Ids::IRON_TRAPDOOR, ItemIds::IRON_TRAPDOOR, 0), "Iron Trapdoor", $ironDoorBreakInfo));
		$this->register(new Opaque(new BID(Ids::IRON_ORE, ItemIds::IRON_ORE, 0), "Iron Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new ItemFrame(new BID(Ids::ITEM_FRAME, ItemIds::FRAME, 0, TileItemFrame::class), "Item Frame", new BreakInfo(0.25)));
		$this->register(new Jukebox(new BID(Ids::JUKEBOX, ItemIds::JUKEBOX, 0, TileJukebox::class), "Jukebox", new BreakInfo(0.8, ToolType::AXE))); //TODO: in PC the hardness is 2.0, not 0.8, unsure if this is a MCPE bug or not
		$this->register(new Ladder(new BID(Ids::LADDER, ItemIds::LADDER, 0), "Ladder", new BreakInfo(0.4, ToolType::AXE)));
		$this->register(new Lantern(new BID(Ids::LANTERN, ItemIds::LANTERN, 0), "Lantern", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::LAPIS_LAZULI, ItemIds::LAPIS_BLOCK, 0), "Lapis Lazuli Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new LapisOre(new BID(Ids::LAPIS_LAZULI_ORE, ItemIds::LAPIS_ORE, 0), "Lapis Lazuli Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new Lava(new BID(Ids::LAVA, ItemIds::FLOWING_LAVA, 0), "Lava", BreakInfo::indestructible(500.0)));
		$this->register(new Lectern(new BID(Ids::LECTERN, ItemIds::LECTERN, 0, TileLectern::class), "Lectern", new BreakInfo(2.0, ToolType::AXE)));
		$this->register(new Lever(new BID(Ids::LEVER, ItemIds::LEVER, 0), "Lever", new BreakInfo(0.5)));
		$this->register(new Loom(new BID(Ids::LOOM, ItemIds::LOOM, 0), "Loom", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new Magma(new BID(Ids::MAGMA, ItemIds::MAGMA, 0), "Magma Block", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Melon(new BID(Ids::MELON, ItemIds::MELON_BLOCK, 0), "Melon Block", new BreakInfo(1.0, ToolType::AXE)));
		$this->register(new MelonStem(new BID(Ids::MELON_STEM, ItemIds::MELON_SEEDS, 0), "Melon Stem", BreakInfo::instant()));
		$this->register(new MonsterSpawner(new BID(Ids::MONSTER_SPAWNER, ItemIds::MOB_SPAWNER, 0, TileMonsterSpawner::class), "Monster Spawner", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Mycelium(new BID(Ids::MYCELIUM, ItemIds::MYCELIUM, 0), "Mycelium", new BreakInfo(0.6, ToolType::SHOVEL)));

		$netherBrickBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::NETHER_BRICKS, ItemIds::NETHER_BRICK_BLOCK, 0), "Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::RED_NETHER_BRICKS, ItemIds::RED_NETHER_BRICK, 0), "Red Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Fence(new BID(Ids::NETHER_BRICK_FENCE, ItemIds::NETHER_BRICK_FENCE, 0), "Nether Brick Fence", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::NETHER_BRICK_STAIRS, ItemIds::NETHER_BRICK_STAIRS, 0), "Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::RED_NETHER_BRICK_STAIRS, ItemIds::RED_NETHER_BRICK_STAIRS, 0), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new NetherPortal(new BID(Ids::NETHER_PORTAL, ItemIds::PORTAL, 0), "Nether Portal", BreakInfo::indestructible(0.0)));
		$this->register(new NetherQuartzOre(new BID(Ids::NETHER_QUARTZ_ORE, ItemIds::NETHER_QUARTZ_ORE, 0), "Nether Quartz Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new NetherReactor(new BID(Ids::NETHER_REACTOR_CORE, ItemIds::NETHERREACTOR, 0), "Nether Reactor Core", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::NETHER_WART_BLOCK, ItemIds::NETHER_WART_BLOCK, 0), "Nether Wart Block", new BreakInfo(1.0, ToolType::HOE)));
		$this->register(new NetherWartPlant(new BID(Ids::NETHER_WART, ItemIds::NETHER_WART, 0), "Nether Wart", BreakInfo::instant()));
		$this->register(new Netherrack(new BID(Ids::NETHERRACK, ItemIds::NETHERRACK, 0), "Netherrack", new BreakInfo(0.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Note(new BID(Ids::NOTE_BLOCK, ItemIds::NOTEBLOCK, 0, TileNote::class), "Note Block", new BreakInfo(0.8, ToolType::AXE)));
		$this->register(new Opaque(new BID(Ids::OBSIDIAN, ItemIds::OBSIDIAN, 0), "Obsidian", new BreakInfo(35.0 /* 50 in PC */, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000.0)));
		$this->register(new PackedIce(new BID(Ids::PACKED_ICE, ItemIds::PACKED_ICE, 0), "Packed Ice", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->register(new Podzol(new BID(Ids::PODZOL, ItemIds::PODZOL, 0), "Podzol", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Potato(new BID(Ids::POTATOES, ItemIds::POTATOES, 0), "Potato Block", BreakInfo::instant()));
		$this->register(new PoweredRail(new BID(Ids::POWERED_RAIL, ItemIds::GOLDEN_RAIL, 0), "Powered Rail", $railBreakInfo));

		$prismarineBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PRISMARINE, ItemIds::PRISMARINE, Meta::PRISMARINE_NORMAL), "Prismarine", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::DARK_PRISMARINE, ItemIds::PRISMARINE, Meta::PRISMARINE_DARK), "Dark Prismarine", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::PRISMARINE_BRICKS, ItemIds::PRISMARINE, Meta::PRISMARINE_BRICKS), "Prismarine Bricks", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_BRICKS_STAIRS, ItemIds::PRISMARINE_BRICKS_STAIRS, 0), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::DARK_PRISMARINE_STAIRS, ItemIds::DARK_PRISMARINE_STAIRS, 0), "Dark Prismarine Stairs", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_STAIRS, ItemIds::PRISMARINE_STAIRS, 0), "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->register(new Pumpkin(new BID(Ids::PUMPKIN, ItemIds::PUMPKIN, 0), "Pumpkin", $pumpkinBreakInfo));
		$this->register(new CarvedPumpkin(new BID(Ids::CARVED_PUMPKIN, ItemIds::CARVED_PUMPKIN, 0), "Carved Pumpkin", $pumpkinBreakInfo));
		$this->register(new LitPumpkin(new BID(Ids::LIT_PUMPKIN, ItemIds::JACK_O_LANTERN, 0), "Jack o'Lantern", $pumpkinBreakInfo));

		$this->register(new PumpkinStem(new BID(Ids::PUMPKIN_STEM, ItemIds::PUMPKIN_SEEDS, 0), "Pumpkin Stem", BreakInfo::instant()));

		$purpurBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PURPUR, ItemIds::PURPUR_BLOCK, Meta::PURPUR_NORMAL), "Purpur Block", $purpurBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::PURPUR_PILLAR, ItemIds::PURPUR_BLOCK, Meta::PURPUR_PILLAR), "Purpur Pillar", $purpurBreakInfo));
		$this->register(new Stair(new BID(Ids::PURPUR_STAIRS, ItemIds::PURPUR_STAIRS, 0), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::QUARTZ, ItemIds::QUARTZ_BLOCK, Meta::QUARTZ_NORMAL), "Quartz Block", $quartzBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::CHISELED_QUARTZ, ItemIds::QUARTZ_BLOCK, Meta::QUARTZ_CHISELED), "Chiseled Quartz Block", $quartzBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::QUARTZ_PILLAR, ItemIds::QUARTZ_BLOCK, Meta::QUARTZ_PILLAR), "Quartz Pillar", $quartzBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_QUARTZ, ItemIds::QUARTZ_BLOCK, Meta::QUARTZ_SMOOTH), "Smooth Quartz Block", $quartzBreakInfo));

		$this->register(new Stair(new BID(Ids::QUARTZ_STAIRS, ItemIds::QUARTZ_STAIRS, 0), "Quartz Stairs", $quartzBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_QUARTZ_STAIRS, ItemIds::SMOOTH_QUARTZ_STAIRS, 0), "Smooth Quartz Stairs", $quartzBreakInfo));

		$this->register(new Rail(new BID(Ids::RAIL, ItemIds::RAIL, 0), "Rail", $railBreakInfo));
		$this->register(new RedMushroom(new BID(Ids::RED_MUSHROOM, ItemIds::RED_MUSHROOM, 0), "Red Mushroom", BreakInfo::instant()));
		$this->register(new Redstone(new BID(Ids::REDSTONE, ItemIds::REDSTONE_BLOCK, 0), "Redstone Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->register(new RedstoneComparator(new BID(Ids::REDSTONE_COMPARATOR, ItemIds::COMPARATOR, 0, TileComparator::class), "Redstone Comparator", BreakInfo::instant()));
		$this->register(new RedstoneLamp(new BID(Ids::REDSTONE_LAMP, ItemIds::REDSTONE_LAMP, 0), "Redstone Lamp", new BreakInfo(0.3)));
		$this->register(new RedstoneOre(new BID(Ids::REDSTONE_ORE, ItemIds::REDSTONE_ORE, 0), "Redstone Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new RedstoneRepeater(new BID(Ids::REDSTONE_REPEATER, ItemIds::REPEATER, 0), "Redstone Repeater", BreakInfo::instant()));
		$this->register(new RedstoneTorch(new BID(Ids::REDSTONE_TORCH, ItemIds::REDSTONE_TORCH, 0), "Redstone Torch", BreakInfo::instant()));
		$this->register(new RedstoneWire(new BID(Ids::REDSTONE_WIRE, ItemIds::REDSTONE, 0), "Redstone", BreakInfo::instant()));
		$this->register(new Reserved6(new BID(Ids::RESERVED6, ItemIds::RESERVED6, 0), "reserved6", BreakInfo::instant()));

		$sandBreakInfo = new BreakInfo(0.5, ToolType::SHOVEL);
		$this->register(new Sand(new BID(Ids::SAND, ItemIds::SAND, 0), "Sand", $sandBreakInfo));
		$this->register(new Sand(new BID(Ids::RED_SAND, ItemIds::SAND, 1), "Red Sand", $sandBreakInfo));

		$this->register(new SeaLantern(new BID(Ids::SEA_LANTERN, ItemIds::SEALANTERN, 0), "Sea Lantern", new BreakInfo(0.3)));
		$this->register(new SeaPickle(new BID(Ids::SEA_PICKLE, ItemIds::SEA_PICKLE, 0), "Sea Pickle", BreakInfo::instant()));
		$this->register(new Skull(new BID(Ids::MOB_HEAD, ItemIds::SKULL, 0, TileSkull::class), "Mob Head", new BreakInfo(1.0)));
		$this->register(new Slime(new BID(Ids::SLIME, ItemIds::SLIME, 0), "Slime Block", BreakInfo::instant()));
		$this->register(new Snow(new BID(Ids::SNOW, ItemIds::SNOW, 0), "Snow Block", new BreakInfo(0.2, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new SnowLayer(new BID(Ids::SNOW_LAYER, ItemIds::SNOW_LAYER, 0), "Snow Layer", new BreakInfo(0.1, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new SoulSand(new BID(Ids::SOUL_SAND, ItemIds::SOUL_SAND, 0), "Soul Sand", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Sponge(new BID(Ids::SPONGE, ItemIds::SPONGE, 0), "Sponge", new BreakInfo(0.6, ToolType::HOE)));
		$shulkerBoxBreakInfo = new BreakInfo(2, ToolType::PICKAXE);
		$this->register(new ShulkerBox(new BID(Ids::SHULKER_BOX, ItemIds::UNDYED_SHULKER_BOX, 0, TileShulkerBox::class), "Shulker Box", $shulkerBoxBreakInfo));

		$stoneBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(
			$stone = new class(new BID(Ids::STONE, ItemIds::STONE, Meta::STONE_NORMAL), "Stone", $stoneBreakInfo) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			}
		);
		$this->register(new Opaque(new BID(Ids::ANDESITE, ItemIds::STONE, Meta::STONE_ANDESITE), "Andesite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::DIORITE, ItemIds::STONE, Meta::STONE_DIORITE), "Diorite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::GRANITE, ItemIds::STONE, Meta::STONE_GRANITE), "Granite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_ANDESITE, ItemIds::STONE, Meta::STONE_POLISHED_ANDESITE), "Polished Andesite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_DIORITE, ItemIds::STONE, Meta::STONE_POLISHED_DIORITE), "Polished Diorite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_GRANITE, ItemIds::STONE, Meta::STONE_POLISHED_GRANITE), "Polished Granite", $stoneBreakInfo));

		$this->register($stoneBrick = new Opaque(new BID(Ids::STONE_BRICKS, ItemIds::STONEBRICK, Meta::STONE_BRICK_NORMAL), "Stone Bricks", $stoneBreakInfo));
		$this->register($mossyStoneBrick = new Opaque(new BID(Ids::MOSSY_STONE_BRICKS, ItemIds::STONEBRICK, Meta::STONE_BRICK_MOSSY), "Mossy Stone Bricks", $stoneBreakInfo));
		$this->register($crackedStoneBrick = new Opaque(new BID(Ids::CRACKED_STONE_BRICKS, ItemIds::STONEBRICK, Meta::STONE_BRICK_CRACKED), "Cracked Stone Bricks", $stoneBreakInfo));
		$this->register($chiseledStoneBrick = new Opaque(new BID(Ids::CHISELED_STONE_BRICKS, ItemIds::STONEBRICK, Meta::STONE_BRICK_CHISELED), "Chiseled Stone Bricks", $stoneBreakInfo));

		$infestedStoneBreakInfo = new BreakInfo(0.75, ToolType::PICKAXE);
		$this->register(new InfestedStone(new BID(Ids::INFESTED_STONE, ItemIds::MONSTER_EGG, Meta::INFESTED_STONE), "Infested Stone", $infestedStoneBreakInfo, $stone));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_STONE_BRICK, ItemIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK), "Infested Stone Brick", $infestedStoneBreakInfo, $stoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_COBBLESTONE, ItemIds::MONSTER_EGG, Meta::INFESTED_COBBLESTONE), "Infested Cobblestone", $infestedStoneBreakInfo, $cobblestone));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_MOSSY_STONE_BRICK, ItemIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_MOSSY), "Infested Mossy Stone Brick", $infestedStoneBreakInfo, $mossyStoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_CRACKED_STONE_BRICK, ItemIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CRACKED), "Infested Cracked Stone Brick", $infestedStoneBreakInfo, $crackedStoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_CHISELED_STONE_BRICK, ItemIds::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CHISELED), "Infested Chiseled Stone Brick", $infestedStoneBreakInfo, $chiseledStoneBrick));

		$this->register(new Stair(new BID(Ids::STONE_STAIRS, ItemIds::NORMAL_STONE_STAIRS, 0), "Stone Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_STONE, ItemIds::SMOOTH_STONE, 0), "Smooth Stone", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::ANDESITE_STAIRS, ItemIds::ANDESITE_STAIRS, 0), "Andesite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::DIORITE_STAIRS, ItemIds::DIORITE_STAIRS, 0), "Diorite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::GRANITE_STAIRS, ItemIds::GRANITE_STAIRS, 0), "Granite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_ANDESITE_STAIRS, ItemIds::POLISHED_ANDESITE_STAIRS, 0), "Polished Andesite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_DIORITE_STAIRS, ItemIds::POLISHED_DIORITE_STAIRS, 0), "Polished Diorite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_GRANITE_STAIRS, ItemIds::POLISHED_GRANITE_STAIRS, 0), "Polished Granite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::STONE_BRICK_STAIRS, ItemIds::STONE_BRICK_STAIRS, 0), "Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_STONE_BRICK_STAIRS, ItemIds::MOSSY_STONE_BRICK_STAIRS, 0), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new StoneButton(new BID(Ids::STONE_BUTTON, ItemIds::STONE_BUTTON, 0), "Stone Button", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->register(new Stonecutter(new BID(Ids::STONECUTTER, ItemIds::STONECUTTER_BLOCK, 0), "Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE)));
		$this->register(new StonePressurePlate(new BID(Ids::STONE_PRESSURE_PLATE, ItemIds::STONE_PRESSURE_PLATE, 0), "Stone Pressure Plate", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

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
			$this->register($slabType);
		}

		$this->register(new Opaque(new BID(Ids::LEGACY_STONECUTTER, ItemIds::STONECUTTER, 0), "Legacy Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Sugarcane(new BID(Ids::SUGARCANE, ItemIds::REEDS, 0), "Sugarcane", BreakInfo::instant()));
		$this->register(new SweetBerryBush(new BID(Ids::SWEET_BERRY_BUSH, ItemIds::SWEET_BERRIES, 0), "Sweet Berry Bush", BreakInfo::instant()));
		$this->register(new TNT(new BID(Ids::TNT, ItemIds::TNT, 0), "TNT", BreakInfo::instant()));
		$this->register(new TallGrass(new BID(Ids::FERN, ItemIds::TALLGRASS, Meta::TALLGRASS_FERN), "Fern", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new TallGrass(new BID(Ids::TALL_GRASS, ItemIds::TALLGRASS, Meta::TALLGRASS_NORMAL), "Tall Grass", BreakInfo::instant(ToolType::SHEARS, 1)));

		$this->register(new Torch(new BID(Ids::BLUE_TORCH, ItemIds::COLORED_TORCH_BP, 0), "Blue Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::PURPLE_TORCH, ItemIds::COLORED_TORCH_BP, 8), "Purple Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::RED_TORCH, ItemIds::COLORED_TORCH_RG, 0), "Red Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::GREEN_TORCH, ItemIds::COLORED_TORCH_RG, 8), "Green Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::TORCH, ItemIds::TORCH, 0), "Torch", BreakInfo::instant()));

		$this->register(new TrappedChest(new BID(Ids::TRAPPED_CHEST, ItemIds::TRAPPED_CHEST, 0, TileChest::class), "Trapped Chest", $chestBreakInfo));
		$this->register(new Tripwire(new BID(Ids::TRIPWIRE, ItemIds::STRING, 0), "Tripwire", BreakInfo::instant()));
		$this->register(new TripwireHook(new BID(Ids::TRIPWIRE_HOOK, ItemIds::TRIPWIRE_HOOK, 0), "Tripwire Hook", BreakInfo::instant()));
		$this->register(new UnderwaterTorch(new BID(Ids::UNDERWATER_TORCH, ItemIds::UNDERWATER_TORCH, 0), "Underwater Torch", BreakInfo::instant()));
		$this->register(new Vine(new BID(Ids::VINES, ItemIds::VINE, 0), "Vines", new BreakInfo(0.2, ToolType::AXE)));
		$this->register(new Water(new BID(Ids::WATER, ItemIds::FLOWING_WATER, 0), "Water", BreakInfo::indestructible(500.0)));
		$this->register(new WaterLily(new BID(Ids::LILY_PAD, ItemIds::LILY_PAD, 0), "Lily Pad", BreakInfo::instant()));

		$weightedPressurePlateBreakInfo = new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new WeightedPressurePlateHeavy(new BID(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY, ItemIds::HEAVY_WEIGHTED_PRESSURE_PLATE, 0), "Weighted Pressure Plate Heavy", $weightedPressurePlateBreakInfo));
		$this->register(new WeightedPressurePlateLight(new BID(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT, ItemIds::LIGHT_WEIGHTED_PRESSURE_PLATE, 0), "Weighted Pressure Plate Light", $weightedPressurePlateBreakInfo));
		$this->register(new Wheat(new BID(Ids::WHEAT, ItemIds::WHEAT_BLOCK, 0), "Wheat Block", BreakInfo::instant()));

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

		foreach(TreeType::getAll() as $treeType){
			$name = $treeType->getDisplayName();
			$this->register(new Planks(BlockLegacyIdHelper::getWoodenPlanksIdentifier($treeType), $name . " Planks", $planksBreakInfo));
			$this->register(new Sapling(BlockLegacyIdHelper::getSaplingIdentifier($treeType), $name . " Sapling", BreakInfo::instant(), $treeType));
			$this->register(new WoodenFence(BlockLegacyIdHelper::getWoodenFenceIdentifier($treeType), $name . " Fence", $planksBreakInfo));
			$this->register(new WoodenSlab(BlockLegacyIdHelper::getWoodenSlabIdentifier($treeType), $name, $planksBreakInfo));

			$this->register(new Leaves(BlockLegacyIdHelper::getLeavesIdentifier($treeType), $name . " Leaves", $leavesBreakInfo, $treeType));

			$this->register(new Log(BlockLegacyIdHelper::getLogIdentifier($treeType), $name . " Log", $logBreakInfo, $treeType, false));
			$this->register(new Log(BlockLegacyIdHelper::getStrippedLogIdentifier($treeType), "Stripped " . $name . " Log", $logBreakInfo, $treeType, true));

			$this->register(new Wood(BlockLegacyIdHelper::getAllSidedLogIdentifier($treeType), $name . " Wood", $logBreakInfo, $treeType, false));
			$this->register(new Wood(BlockLegacyIdHelper::getAllSidedStrippedLogIdentifier($treeType), "Stripped $name Wood", $logBreakInfo, $treeType, true));

			$this->register(new FenceGate(BlockLegacyIdHelper::getWoodenFenceGateIdentifier($treeType), $name . " Fence Gate", $planksBreakInfo));
			$this->register(new WoodenStairs(BlockLegacyIdHelper::getWoodenStairsIdentifier($treeType), $name . " Stairs", $planksBreakInfo));
			$this->register(new WoodenDoor(BlockLegacyIdHelper::getWoodenDoorIdentifier($treeType), $name . " Door", $woodenDoorBreakInfo));

			$this->register(new WoodenButton(BlockLegacyIdHelper::getWoodenButtonIdentifier($treeType), $name . " Button", $woodenButtonBreakInfo));
			$this->register(new WoodenPressurePlate(BlockLegacyIdHelper::getWoodenPressurePlateIdentifier($treeType), $name . " Pressure Plate", $woodenPressurePlateBreakInfo));
			$this->register(new WoodenTrapdoor(BlockLegacyIdHelper::getWoodenTrapdoorIdentifier($treeType), $name . " Trapdoor", $woodenDoorBreakInfo));

			$this->register(new FloorSign(BlockLegacyIdHelper::getWoodenFloorSignIdentifier($treeType), $name . " Sign", $signBreakInfo));
			$this->register(new WallSign(BlockLegacyIdHelper::getWoodenWallSignIdentifier($treeType), $name . " Wall Sign", $signBreakInfo));
		}

		$sandstoneBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Stair(new BID(Ids::RED_SANDSTONE_STAIRS, ItemIds::RED_SANDSTONE_STAIRS, 0), "Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_RED_SANDSTONE_STAIRS, ItemIds::SMOOTH_RED_SANDSTONE_STAIRS, 0), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::RED_SANDSTONE, ItemIds::RED_SANDSTONE, Meta::SANDSTONE_NORMAL), "Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_RED_SANDSTONE, ItemIds::RED_SANDSTONE, Meta::SANDSTONE_CHISELED), "Chiseled Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CUT_RED_SANDSTONE, ItemIds::RED_SANDSTONE, Meta::SANDSTONE_CUT), "Cut Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_RED_SANDSTONE, ItemIds::RED_SANDSTONE, Meta::SANDSTONE_SMOOTH), "Smooth Red Sandstone", $sandstoneBreakInfo));

		$this->register(new Stair(new BID(Ids::SANDSTONE_STAIRS, ItemIds::SANDSTONE_STAIRS, 0), "Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_SANDSTONE_STAIRS, ItemIds::SMOOTH_SANDSTONE_STAIRS, 0), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SANDSTONE, ItemIds::SANDSTONE, Meta::SANDSTONE_NORMAL), "Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_SANDSTONE, ItemIds::SANDSTONE, Meta::SANDSTONE_CHISELED), "Chiseled Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CUT_SANDSTONE, ItemIds::SANDSTONE, Meta::SANDSTONE_CUT), "Cut Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_SANDSTONE, ItemIds::SANDSTONE, Meta::SANDSTONE_SMOOTH), "Smooth Sandstone", $sandstoneBreakInfo));

		$glazedTerracottaBreakInfo = new BreakInfo(1.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		foreach(DyeColor::getAll() as $color){
			$coloredName = function(string $name) use($color) : string{
				return $color->getDisplayName() . " " . $name;
			};
			$this->register(new GlazedTerracotta(BlockLegacyIdHelper::getGlazedTerracottaIdentifier($color), $coloredName("Glazed Terracotta"), $glazedTerracottaBreakInfo));
		}
		$this->register(new DyedShulkerBox(new BID(Ids::DYED_SHULKER_BOX, ItemIds::SHULKER_BOX, 0, TileShulkerBox::class), "Dyed Shulker Box", $shulkerBoxBreakInfo));
		$this->register(new StainedGlass(new BID(Ids::STAINED_GLASS, ItemIds::STAINED_GLASS, 0), "Stained Glass", $glassBreakInfo));
		$this->register(new StainedGlassPane(new BID(Ids::STAINED_GLASS_PANE, ItemIds::STAINED_GLASS_PANE, 0), "Stained Glass Pane", $glassBreakInfo));
		$this->register(new StainedHardenedClay(new BID(Ids::STAINED_CLAY, ItemIds::STAINED_CLAY, 0), "Stained Clay", $hardenedClayBreakInfo));
		$this->register(new StainedHardenedGlass(new BID(Ids::STAINED_HARDENED_GLASS, ItemIds::HARD_STAINED_GLASS, 0), "Stained Hardened Glass", $hardenedGlassBreakInfo));
		$this->register(new StainedHardenedGlassPane(new BID(Ids::STAINED_HARDENED_GLASS_PANE, ItemIds::HARD_STAINED_GLASS_PANE, 0), "Stained Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->register(new Carpet(new BID(Ids::CARPET, ItemIds::CARPET, 0), "Carpet", new BreakInfo(0.1)));
		$this->register(new Concrete(new BID(Ids::CONCRETE, ItemIds::CONCRETE, 0), "Concrete", new BreakInfo(1.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new ConcretePowder(new BID(Ids::CONCRETE_POWDER, ItemIds::CONCRETE_POWDER, 0), "Concrete Powder", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Wool(new BID(Ids::WOOL, ItemIds::WOOL, 0), "Wool", new class(0.8, ToolType::SHEARS) extends BreakInfo{
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
		$this->register(new Wall(new BID(Ids::COBBLESTONE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_COBBLESTONE), "Cobblestone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::ANDESITE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_ANDESITE), "Andesite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_BRICK), "Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::DIORITE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_DIORITE), "Diorite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::END_STONE_BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_END_STONE_BRICK), "End Stone Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::GRANITE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_GRANITE), "Granite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::MOSSY_STONE_BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_MOSSY_STONE_BRICK), "Mossy Stone Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::MOSSY_COBBLESTONE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_MOSSY_COBBLESTONE), "Mossy Cobblestone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::NETHER_BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_NETHER_BRICK), "Nether Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::PRISMARINE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_PRISMARINE), "Prismarine Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::RED_NETHER_BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_RED_NETHER_BRICK), "Red Nether Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::RED_SANDSTONE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_RED_SANDSTONE), "Red Sandstone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::SANDSTONE_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_SANDSTONE), "Sandstone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::STONE_BRICK_WALL, ItemIds::COBBLESTONE_WALL, Meta::WALL_STONE_BRICK), "Stone Brick Wall", $wallBreakInfo));

		$this->registerElements();

		$chemistryTableBreakInfo = new BreakInfo(2.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new ChemistryTable(new BID(Ids::COMPOUND_CREATOR, ItemIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_COMPOUND_CREATOR), "Compound Creator", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::ELEMENT_CONSTRUCTOR, ItemIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_ELEMENT_CONSTRUCTOR), "Element Constructor", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::LAB_TABLE, ItemIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_LAB_TABLE), "Lab Table", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::MATERIAL_REDUCER, ItemIds::CHEMISTRY_TABLE, Meta::CHEMISTRY_MATERIAL_REDUCER), "Material Reducer", $chemistryTableBreakInfo));

		$this->register(new ChemicalHeat(new BID(Ids::CHEMICAL_HEAT, ItemIds::CHEMICAL_HEAT, 0), "Heat Block", $chemistryTableBreakInfo));

		$this->registerMushroomBlocks();

		$this->register(new Coral(
			new BID(Ids::CORAL, ItemIds::CORAL, 0),
			"Coral",
			BreakInfo::instant(),
		));
		$this->register(new FloorCoralFan(
			new BID(Ids::CORAL_FAN, ItemIds::CORAL_FAN, 0),
			"Coral Fan",
			BreakInfo::instant(),
		));
		$this->register(new WallCoralFan(
			new BID(Ids::WALL_CORAL_FAN, ItemIds::CORAL_FAN, 0),
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
		$mushroomBlockBreakInfo = new BreakInfo(0.2, ToolType::AXE);

		$this->register(new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK, ItemIds::BROWN_MUSHROOM_BLOCK, 0), "Brown Mushroom Block", $mushroomBlockBreakInfo));
		$this->register(new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK, ItemIds::RED_MUSHROOM_BLOCK, 0), "Red Mushroom Block", $mushroomBlockBreakInfo));

		//finally, the stems
		$this->register(new MushroomStem(new BID(Ids::MUSHROOM_STEM, ItemIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_STEM), "Mushroom Stem", $mushroomBlockBreakInfo));
		$this->register(new MushroomStem(new BID(Ids::ALL_SIDED_MUSHROOM_STEM, ItemIds::BROWN_MUSHROOM_BLOCK, Meta::MUSHROOM_BLOCK_ALL_STEM), "All Sided Mushroom Stem", $mushroomBlockBreakInfo));
	}

	private function registerElements() : void{
		$instaBreak = BreakInfo::instant();
		$this->register(new Opaque(new BID(Ids::ELEMENT_ZERO, ItemIds::ELEMENT_0, 0), "???", $instaBreak));

		$this->register(new Element(new BID(Ids::ELEMENT_HYDROGEN, ItemIds::ELEMENT_1, 0), "Hydrogen", $instaBreak, "h", 1, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_HELIUM, ItemIds::ELEMENT_2, 0), "Helium", $instaBreak, "he", 2, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_LITHIUM, ItemIds::ELEMENT_3, 0), "Lithium", $instaBreak, "li", 3, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_BERYLLIUM, ItemIds::ELEMENT_4, 0), "Beryllium", $instaBreak, "be", 4, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_BORON, ItemIds::ELEMENT_5, 0), "Boron", $instaBreak, "b", 5, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_CARBON, ItemIds::ELEMENT_6, 0), "Carbon", $instaBreak, "c", 6, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_NITROGEN, ItemIds::ELEMENT_7, 0), "Nitrogen", $instaBreak, "n", 7, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_OXYGEN, ItemIds::ELEMENT_8, 0), "Oxygen", $instaBreak, "o", 8, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_FLUORINE, ItemIds::ELEMENT_9, 0), "Fluorine", $instaBreak, "f", 9, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_NEON, ItemIds::ELEMENT_10, 0), "Neon", $instaBreak, "ne", 10, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_SODIUM, ItemIds::ELEMENT_11, 0), "Sodium", $instaBreak, "na", 11, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_MAGNESIUM, ItemIds::ELEMENT_12, 0), "Magnesium", $instaBreak, "mg", 12, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_ALUMINUM, ItemIds::ELEMENT_13, 0), "Aluminum", $instaBreak, "al", 13, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_SILICON, ItemIds::ELEMENT_14, 0), "Silicon", $instaBreak, "si", 14, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_PHOSPHORUS, ItemIds::ELEMENT_15, 0), "Phosphorus", $instaBreak, "p", 15, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_SULFUR, ItemIds::ELEMENT_16, 0), "Sulfur", $instaBreak, "s", 16, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_CHLORINE, ItemIds::ELEMENT_17, 0), "Chlorine", $instaBreak, "cl", 17, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_ARGON, ItemIds::ELEMENT_18, 0), "Argon", $instaBreak, "ar", 18, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_POTASSIUM, ItemIds::ELEMENT_19, 0), "Potassium", $instaBreak, "k", 19, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_CALCIUM, ItemIds::ELEMENT_20, 0), "Calcium", $instaBreak, "ca", 20, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_SCANDIUM, ItemIds::ELEMENT_21, 0), "Scandium", $instaBreak, "sc", 21, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TITANIUM, ItemIds::ELEMENT_22, 0), "Titanium", $instaBreak, "ti", 22, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_VANADIUM, ItemIds::ELEMENT_23, 0), "Vanadium", $instaBreak, "v", 23, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_CHROMIUM, ItemIds::ELEMENT_24, 0), "Chromium", $instaBreak, "cr", 24, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MANGANESE, ItemIds::ELEMENT_25, 0), "Manganese", $instaBreak, "mn", 25, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_IRON, ItemIds::ELEMENT_26, 0), "Iron", $instaBreak, "fe", 26, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COBALT, ItemIds::ELEMENT_27, 0), "Cobalt", $instaBreak, "co", 27, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NICKEL, ItemIds::ELEMENT_28, 0), "Nickel", $instaBreak, "ni", 28, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COPPER, ItemIds::ELEMENT_29, 0), "Copper", $instaBreak, "cu", 29, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ZINC, ItemIds::ELEMENT_30, 0), "Zinc", $instaBreak, "zn", 30, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_GALLIUM, ItemIds::ELEMENT_31, 0), "Gallium", $instaBreak, "ga", 31, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_GERMANIUM, ItemIds::ELEMENT_32, 0), "Germanium", $instaBreak, "ge", 32, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_ARSENIC, ItemIds::ELEMENT_33, 0), "Arsenic", $instaBreak, "as", 33, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_SELENIUM, ItemIds::ELEMENT_34, 0), "Selenium", $instaBreak, "se", 34, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_BROMINE, ItemIds::ELEMENT_35, 0), "Bromine", $instaBreak, "br", 35, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_KRYPTON, ItemIds::ELEMENT_36, 0), "Krypton", $instaBreak, "kr", 36, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_RUBIDIUM, ItemIds::ELEMENT_37, 0), "Rubidium", $instaBreak, "rb", 37, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_STRONTIUM, ItemIds::ELEMENT_38, 0), "Strontium", $instaBreak, "sr", 38, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_YTTRIUM, ItemIds::ELEMENT_39, 0), "Yttrium", $instaBreak, "y", 39, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ZIRCONIUM, ItemIds::ELEMENT_40, 0), "Zirconium", $instaBreak, "zr", 40, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NIOBIUM, ItemIds::ELEMENT_41, 0), "Niobium", $instaBreak, "nb", 41, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MOLYBDENUM, ItemIds::ELEMENT_42, 0), "Molybdenum", $instaBreak, "mo", 42, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TECHNETIUM, ItemIds::ELEMENT_43, 0), "Technetium", $instaBreak, "tc", 43, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RUTHENIUM, ItemIds::ELEMENT_44, 0), "Ruthenium", $instaBreak, "ru", 44, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RHODIUM, ItemIds::ELEMENT_45, 0), "Rhodium", $instaBreak, "rh", 45, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_PALLADIUM, ItemIds::ELEMENT_46, 0), "Palladium", $instaBreak, "pd", 46, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_SILVER, ItemIds::ELEMENT_47, 0), "Silver", $instaBreak, "ag", 47, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_CADMIUM, ItemIds::ELEMENT_48, 0), "Cadmium", $instaBreak, "cd", 48, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_INDIUM, ItemIds::ELEMENT_49, 0), "Indium", $instaBreak, "in", 49, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_TIN, ItemIds::ELEMENT_50, 0), "Tin", $instaBreak, "sn", 50, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_ANTIMONY, ItemIds::ELEMENT_51, 0), "Antimony", $instaBreak, "sb", 51, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_TELLURIUM, ItemIds::ELEMENT_52, 0), "Tellurium", $instaBreak, "te", 52, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_IODINE, ItemIds::ELEMENT_53, 0), "Iodine", $instaBreak, "i", 53, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_XENON, ItemIds::ELEMENT_54, 0), "Xenon", $instaBreak, "xe", 54, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_CESIUM, ItemIds::ELEMENT_55, 0), "Cesium", $instaBreak, "cs", 55, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_BARIUM, ItemIds::ELEMENT_56, 0), "Barium", $instaBreak, "ba", 56, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_LANTHANUM, ItemIds::ELEMENT_57, 0), "Lanthanum", $instaBreak, "la", 57, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_CERIUM, ItemIds::ELEMENT_58, 0), "Cerium", $instaBreak, "ce", 58, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_PRASEODYMIUM, ItemIds::ELEMENT_59, 0), "Praseodymium", $instaBreak, "pr", 59, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_NEODYMIUM, ItemIds::ELEMENT_60, 0), "Neodymium", $instaBreak, "nd", 60, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_PROMETHIUM, ItemIds::ELEMENT_61, 0), "Promethium", $instaBreak, "pm", 61, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_SAMARIUM, ItemIds::ELEMENT_62, 0), "Samarium", $instaBreak, "sm", 62, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_EUROPIUM, ItemIds::ELEMENT_63, 0), "Europium", $instaBreak, "eu", 63, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_GADOLINIUM, ItemIds::ELEMENT_64, 0), "Gadolinium", $instaBreak, "gd", 64, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_TERBIUM, ItemIds::ELEMENT_65, 0), "Terbium", $instaBreak, "tb", 65, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_DYSPROSIUM, ItemIds::ELEMENT_66, 0), "Dysprosium", $instaBreak, "dy", 66, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_HOLMIUM, ItemIds::ELEMENT_67, 0), "Holmium", $instaBreak, "ho", 67, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_ERBIUM, ItemIds::ELEMENT_68, 0), "Erbium", $instaBreak, "er", 68, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_THULIUM, ItemIds::ELEMENT_69, 0), "Thulium", $instaBreak, "tm", 69, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_YTTERBIUM, ItemIds::ELEMENT_70, 0), "Ytterbium", $instaBreak, "yb", 70, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_LUTETIUM, ItemIds::ELEMENT_71, 0), "Lutetium", $instaBreak, "lu", 71, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_HAFNIUM, ItemIds::ELEMENT_72, 0), "Hafnium", $instaBreak, "hf", 72, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TANTALUM, ItemIds::ELEMENT_73, 0), "Tantalum", $instaBreak, "ta", 73, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TUNGSTEN, ItemIds::ELEMENT_74, 0), "Tungsten", $instaBreak, "w", 74, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RHENIUM, ItemIds::ELEMENT_75, 0), "Rhenium", $instaBreak, "re", 75, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_OSMIUM, ItemIds::ELEMENT_76, 0), "Osmium", $instaBreak, "os", 76, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_IRIDIUM, ItemIds::ELEMENT_77, 0), "Iridium", $instaBreak, "ir", 77, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_PLATINUM, ItemIds::ELEMENT_78, 0), "Platinum", $instaBreak, "pt", 78, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_GOLD, ItemIds::ELEMENT_79, 0), "Gold", $instaBreak, "au", 79, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MERCURY, ItemIds::ELEMENT_80, 0), "Mercury", $instaBreak, "hg", 80, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_THALLIUM, ItemIds::ELEMENT_81, 0), "Thallium", $instaBreak, "tl", 81, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_LEAD, ItemIds::ELEMENT_82, 0), "Lead", $instaBreak, "pb", 82, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_BISMUTH, ItemIds::ELEMENT_83, 0), "Bismuth", $instaBreak, "bi", 83, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_POLONIUM, ItemIds::ELEMENT_84, 0), "Polonium", $instaBreak, "po", 84, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_ASTATINE, ItemIds::ELEMENT_85, 0), "Astatine", $instaBreak, "at", 85, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_RADON, ItemIds::ELEMENT_86, 0), "Radon", $instaBreak, "rn", 86, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_FRANCIUM, ItemIds::ELEMENT_87, 0), "Francium", $instaBreak, "fr", 87, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_RADIUM, ItemIds::ELEMENT_88, 0), "Radium", $instaBreak, "ra", 88, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_ACTINIUM, ItemIds::ELEMENT_89, 0), "Actinium", $instaBreak, "ac", 89, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_THORIUM, ItemIds::ELEMENT_90, 0), "Thorium", $instaBreak, "th", 90, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_PROTACTINIUM, ItemIds::ELEMENT_91, 0), "Protactinium", $instaBreak, "pa", 91, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_URANIUM, ItemIds::ELEMENT_92, 0), "Uranium", $instaBreak, "u", 92, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_NEPTUNIUM, ItemIds::ELEMENT_93, 0), "Neptunium", $instaBreak, "np", 93, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_PLUTONIUM, ItemIds::ELEMENT_94, 0), "Plutonium", $instaBreak, "pu", 94, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_AMERICIUM, ItemIds::ELEMENT_95, 0), "Americium", $instaBreak, "am", 95, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_CURIUM, ItemIds::ELEMENT_96, 0), "Curium", $instaBreak, "cm", 96, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_BERKELIUM, ItemIds::ELEMENT_97, 0), "Berkelium", $instaBreak, "bk", 97, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_CALIFORNIUM, ItemIds::ELEMENT_98, 0), "Californium", $instaBreak, "cf", 98, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_EINSTEINIUM, ItemIds::ELEMENT_99, 0), "Einsteinium", $instaBreak, "es", 99, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_FERMIUM, ItemIds::ELEMENT_100, 0), "Fermium", $instaBreak, "fm", 100, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_MENDELEVIUM, ItemIds::ELEMENT_101, 0), "Mendelevium", $instaBreak, "md", 101, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_NOBELIUM, ItemIds::ELEMENT_102, 0), "Nobelium", $instaBreak, "no", 102, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_LAWRENCIUM, ItemIds::ELEMENT_103, 0), "Lawrencium", $instaBreak, "lr", 103, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_RUTHERFORDIUM, ItemIds::ELEMENT_104, 0), "Rutherfordium", $instaBreak, "rf", 104, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_DUBNIUM, ItemIds::ELEMENT_105, 0), "Dubnium", $instaBreak, "db", 105, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_SEABORGIUM, ItemIds::ELEMENT_106, 0), "Seaborgium", $instaBreak, "sg", 106, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_BOHRIUM, ItemIds::ELEMENT_107, 0), "Bohrium", $instaBreak, "bh", 107, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_HASSIUM, ItemIds::ELEMENT_108, 0), "Hassium", $instaBreak, "hs", 108, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MEITNERIUM, ItemIds::ELEMENT_109, 0), "Meitnerium", $instaBreak, "mt", 109, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_DARMSTADTIUM, ItemIds::ELEMENT_110, 0), "Darmstadtium", $instaBreak, "ds", 110, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ROENTGENIUM, ItemIds::ELEMENT_111, 0), "Roentgenium", $instaBreak, "rg", 111, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COPERNICIUM, ItemIds::ELEMENT_112, 0), "Copernicium", $instaBreak, "cn", 112, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NIHONIUM, ItemIds::ELEMENT_113, 0), "Nihonium", $instaBreak, "nh", 113, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_FLEROVIUM, ItemIds::ELEMENT_114, 0), "Flerovium", $instaBreak, "fl", 114, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_MOSCOVIUM, ItemIds::ELEMENT_115, 0), "Moscovium", $instaBreak, "mc", 115, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_LIVERMORIUM, ItemIds::ELEMENT_116, 0), "Livermorium", $instaBreak, "lv", 116, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_TENNESSINE, ItemIds::ELEMENT_117, 0), "Tennessine", $instaBreak, "ts", 117, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_OGANESSON, ItemIds::ELEMENT_118, 0), "Oganesson", $instaBreak, "og", 118, 7));
	}

	/**
	 * Maps a block type to its corresponding type ID. This is necessary for the block to be recognized when loading
	 * from disk, and also when being read at runtime.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param bool  $override Whether to override existing registrations
	 *
	 * @throws \InvalidArgumentException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public function register(Block $block, bool $override = false) : void{
		$typeId = $block->getTypeId();

		if(!$override && isset($this->typeIndex[$typeId])){
			throw new \InvalidArgumentException("Block ID $typeId is already used by another block, and override was not requested");
		}

		$this->typeIndex[$typeId] = clone $block;

		//TODO: this bruteforce approach to discovering all valid states is very inefficient for larger state data sizes
		//at some point we'll need to find a better way to do this
		$bits = $block->getRequiredTypeDataBits() + $block->getRequiredStateDataBits();
		if($bits > Block::INTERNAL_STATE_DATA_BITS){
			throw new \InvalidArgumentException("Block state data cannot use more than " . Block::INTERNAL_STATE_DATA_BITS . " bits");
		}
		for($stateData = 0; $stateData < (1 << $bits); ++$stateData){
			$v = clone $block;
			try{
				$v->decodeStateData($stateData);
				if($v->computeStateData() !== $stateData){
					//if the fullID comes back different, this is a broken state that we can't rely on; map it to default
					throw new InvalidBlockStateException("Corrupted state");
				}
			}catch(InvalidBlockStateException $e){ //invalid property combination, leave it
				continue;
			}

			$this->fillStaticArrays($v->getStateId(), $v);
		}
	}

	private function fillStaticArrays(int $index, Block $block) : void{
		$fullId = $block->getStateId();
		if($index !== $fullId){
			throw new AssumptionFailedError("Cannot fill static arrays for an invalid blockstate");
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
	 * @internal
	 * @see VanillaBlocks
	 *
	 * Deserializes a block from the provided type ID and internal state data.
	 */
	public function get(int $typeId, int $stateData) : Block{
		if($stateData < 0 || $stateData >= (1 << Block::INTERNAL_STATE_DATA_BITS)){
			throw new \InvalidArgumentException("Block meta value $stateData is out of bounds");
		}

		$index = ($typeId << Block::INTERNAL_STATE_DATA_BITS) | $stateData;
		if($index < 0){
			throw new \InvalidArgumentException("Block ID $typeId is out of bounds");
		}
		if(isset($this->fullList[$index])) { //hot
			$block = clone $this->fullList[$index];
		}else{
			$block = new UnknownBlock(new BID($typeId, $stateData, $stateData), BreakInfo::instant());
		}

		return $block;
	}

	public function fromFullBlock(int $fullState) : Block{
		return $this->get($fullState >> Block::INTERNAL_STATE_DATA_BITS, $fullState & Block::INTERNAL_STATE_DATA_MASK);
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public function isRegistered(int $typeId, int $stateData = 0) : bool{
		$index = ($typeId << Block::INTERNAL_STATE_DATA_BITS) | $stateData;
		$b = $this->fullList[$index] ?? null;
		return $b !== null && !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 * @phpstan-return array<int, Block>
	 */
	public function getAllKnownTypes() : array{
		return $this->typeIndex;
	}

	/**
	 * @return Block[]
	 */
	public function getAllKnownStates() : array{
		return $this->fullList;
	}
}
