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
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
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
		$this->register(new ActivatorRail(new BID(Ids::ACTIVATOR_RAIL), "Activator Rail", $railBreakInfo));
		$this->register(new Air(new BID(Ids::AIR), "Air", BreakInfo::indestructible(-1.0)));
		$this->register(new Anvil(new BID(Ids::ANVIL), "Anvil", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->register(new Bamboo(new BID(Ids::BAMBOO), "Bamboo", new class(2.0 /* 1.0 in PC */, ToolType::AXE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}));
		$this->register(new BambooSapling(new BID(Ids::BAMBOO_SAPLING), "Bamboo Sapling", BreakInfo::instant()));

		$bannerBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->register(new FloorBanner(new BID(Ids::BANNER, TileBanner::class), "Banner", $bannerBreakInfo));
		$this->register(new WallBanner(new BID(Ids::WALL_BANNER, TileBanner::class), "Wall Banner", $bannerBreakInfo));
		$this->register(new Barrel(new BID(Ids::BARREL, TileBarrel::class), "Barrel", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new Transparent(new BID(Ids::BARRIER), "Barrier", BreakInfo::indestructible()));
		$this->register(new Beacon(new BID(Ids::BEACON, TileBeacon::class), "Beacon", new BreakInfo(3.0)));
		$this->register(new Bed(new BID(Ids::BED, TileBed::class), "Bed Block", new BreakInfo(0.2)));
		$this->register(new Bedrock(new BID(Ids::BEDROCK), "Bedrock", BreakInfo::indestructible()));

		$this->register(new Beetroot(new BID(Ids::BEETROOTS), "Beetroot Block", BreakInfo::instant()));
		$this->register(new Bell(new BID(Ids::BELL, TileBell::class), "Bell", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new BlueIce(new BID(Ids::BLUE_ICE), "Blue Ice", new BreakInfo(2.8, ToolType::PICKAXE)));
		$this->register(new BoneBlock(new BID(Ids::BONE_BLOCK), "Bone Block", new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Bookshelf(new BID(Ids::BOOKSHELF), "Bookshelf", new BreakInfo(1.5, ToolType::AXE)));
		$this->register(new BrewingStand(new BID(Ids::BREWING_STAND, TileBrewingStand::class), "Brewing Stand", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$bricksBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Stair(new BID(Ids::BRICK_STAIRS), "Brick Stairs", $bricksBreakInfo));
		$this->register(new Opaque(new BID(Ids::BRICKS), "Bricks", $bricksBreakInfo));

		$this->register(new BrownMushroom(new BID(Ids::BROWN_MUSHROOM), "Brown Mushroom", BreakInfo::instant()));
		$this->register(new Cactus(new BID(Ids::CACTUS), "Cactus", new BreakInfo(0.4)));
		$this->register(new Cake(new BID(Ids::CAKE), "Cake", new BreakInfo(0.5)));
		$this->register(new Carrot(new BID(Ids::CARROTS), "Carrot Block", BreakInfo::instant()));

		$chestBreakInfo = new BreakInfo(2.5, ToolType::AXE);
		$this->register(new Chest(new BID(Ids::CHEST, TileChest::class), "Chest", $chestBreakInfo));
		$this->register(new Clay(new BID(Ids::CLAY), "Clay Block", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->register(new Coal(new BID(Ids::COAL), "Coal Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->register(new CoalOre(new BID(Ids::COAL_ORE), "Coal Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$cobblestoneBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register($cobblestone = new Opaque(new BID(Ids::COBBLESTONE), "Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::MOSSY_COBBLESTONE), "Mossy Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::COBBLESTONE_STAIRS), "Cobblestone Stairs", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_COBBLESTONE_STAIRS), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		$this->register(new Cobweb(new BID(Ids::COBWEB), "Cobweb", new BreakInfo(4.0, ToolType::SWORD | ToolType::SHEARS, 1)));
		$this->register(new CocoaBlock(new BID(Ids::COCOA_POD), "Cocoa Block", new BreakInfo(0.2, ToolType::AXE, 0, 15.0)));
		$this->register(new CoralBlock(new BID(Ids::CORAL_BLOCK), "Coral Block", new BreakInfo(7.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new CraftingTable(new BID(Ids::CRAFTING_TABLE), "Crafting Table", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new DaylightSensor(new BID(Ids::DAYLIGHT_SENSOR, TileDaylightSensor::class), "Daylight Sensor", new BreakInfo(0.2, ToolType::AXE)));
		$this->register(new DeadBush(new BID(Ids::DEAD_BUSH), "Dead Bush", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DetectorRail(new BID(Ids::DETECTOR_RAIL), "Detector Rail", $railBreakInfo));

		$this->register(new Opaque(new BID(Ids::DIAMOND), "Diamond Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new DiamondOre(new BID(Ids::DIAMOND_ORE), "Diamond Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new Dirt(new BID(Ids::DIRT), "Dirt", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new DoublePlant(new BID(Ids::SUNFLOWER), "Sunflower", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::LILAC), "Lilac", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::ROSE_BUSH), "Rose Bush", BreakInfo::instant()));
		$this->register(new DoublePlant(new BID(Ids::PEONY), "Peony", BreakInfo::instant()));
		$this->register(new DoubleTallGrass(new BID(Ids::DOUBLE_TALLGRASS), "Double Tallgrass", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DoubleTallGrass(new BID(Ids::LARGE_FERN), "Large Fern", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new DragonEgg(new BID(Ids::DRAGON_EGG), "Dragon Egg", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new DriedKelp(new BID(Ids::DRIED_KELP), "Dried Kelp Block", new BreakInfo(0.5, ToolType::NONE, 0, 12.5)));
		$this->register(new Opaque(new BID(Ids::EMERALD), "Emerald Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new EmeraldOre(new BID(Ids::EMERALD_ORE), "Emerald Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new EnchantingTable(new BID(Ids::ENCHANTING_TABLE, TileEnchantingTable::class), "Enchanting Table", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6000.0)));
		$this->register(new EndPortalFrame(new BID(Ids::END_PORTAL_FRAME), "End Portal Frame", BreakInfo::indestructible()));
		$this->register(new EndRod(new BID(Ids::END_ROD), "End Rod", BreakInfo::instant()));
		$this->register(new Opaque(new BID(Ids::END_STONE), "End Stone", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 45.0)));

		$endBrickBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.0);
		$this->register(new Opaque(new BID(Ids::END_STONE_BRICKS), "End Stone Bricks", $endBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::END_STONE_BRICK_STAIRS), "End Stone Brick Stairs", $endBrickBreakInfo));

		$this->register(new EnderChest(new BID(Ids::ENDER_CHEST, TileEnderChest::class), "Ender Chest", new BreakInfo(22.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3000.0)));
		$this->register(new Farmland(new BID(Ids::FARMLAND), "Farmland", new BreakInfo(0.6, ToolType::SHOVEL)));
		$this->register(new Fire(new BID(Ids::FIRE), "Fire Block", BreakInfo::instant()));
		$this->register(new FletchingTable(new BID(Ids::FLETCHING_TABLE), "Fletching Table", new BreakInfo(2.5, ToolType::AXE, 0, 2.5)));
		$this->register(new Flower(new BID(Ids::DANDELION), "Dandelion", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::POPPY), "Poppy", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::ALLIUM), "Allium", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::AZURE_BLUET), "Azure Bluet", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::BLUE_ORCHID), "Blue Orchid", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::CORNFLOWER), "Cornflower", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::LILY_OF_THE_VALLEY), "Lily of the Valley", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::ORANGE_TULIP), "Orange Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::OXEYE_DAISY), "Oxeye Daisy", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::PINK_TULIP), "Pink Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::RED_TULIP), "Red Tulip", BreakInfo::instant()));
		$this->register(new Flower(new BID(Ids::WHITE_TULIP), "White Tulip", BreakInfo::instant()));
		$this->register(new FlowerPot(new BID(Ids::FLOWER_POT, TileFlowerPot::class), "Flower Pot", BreakInfo::instant()));
		$this->register(new FrostedIce(new BID(Ids::FROSTED_ICE), "Frosted Ice", new BreakInfo(2.5, ToolType::PICKAXE)));
		$this->register(new Furnace(new BID(Ids::FURNACE, TileNormalFurnace::class), "Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Furnace(new BID(Ids::BLAST_FURNACE, TileBlastFurnace::class), "Blast Furnace", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Furnace(new BID(Ids::SMOKER, TileSmoker::class), "Smoker", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$glassBreakInfo = new BreakInfo(0.3);
		$this->register(new Glass(new BID(Ids::GLASS), "Glass", $glassBreakInfo));
		$this->register(new GlassPane(new BID(Ids::GLASS_PANE), "Glass Pane", $glassBreakInfo));
		$this->register(new GlowingObsidian(new BID(Ids::GLOWING_OBSIDIAN), "Glowing Obsidian", new BreakInfo(10.0, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 50.0)));
		$this->register(new Glowstone(new BID(Ids::GLOWSTONE), "Glowstone", new BreakInfo(0.3, ToolType::PICKAXE)));
		$this->register(new Opaque(new BID(Ids::GOLD), "Gold Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new Opaque(new BID(Ids::GOLD_ORE), "Gold Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));

		$grassBreakInfo = new BreakInfo(0.6, ToolType::SHOVEL);
		$this->register(new Grass(new BID(Ids::GRASS), "Grass", $grassBreakInfo));
		$this->register(new GrassPath(new BID(Ids::GRASS_PATH), "Grass Path", $grassBreakInfo));
		$this->register(new Gravel(new BID(Ids::GRAVEL), "Gravel", new BreakInfo(0.6, ToolType::SHOVEL)));

		$hardenedClayBreakInfo = new BreakInfo(1.25, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 21.0);
		$this->register(new HardenedClay(new BID(Ids::HARDENED_CLAY), "Hardened Clay", $hardenedClayBreakInfo));

		$hardenedGlassBreakInfo = new BreakInfo(10.0);
		$this->register(new HardenedGlass(new BID(Ids::HARDENED_GLASS), "Hardened Glass", $hardenedGlassBreakInfo));
		$this->register(new HardenedGlassPane(new BID(Ids::HARDENED_GLASS_PANE), "Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->register(new HayBale(new BID(Ids::HAY_BALE), "Hay Bale", new BreakInfo(0.5)));
		$this->register(new Hopper(new BID(Ids::HOPPER, TileHopper::class), "Hopper", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0)));
		$this->register(new Ice(new BID(Ids::ICE), "Ice", new BreakInfo(0.5, ToolType::PICKAXE)));

		$updateBlockBreakInfo = new BreakInfo(1.0);
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE), "update!", $updateBlockBreakInfo));
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE2), "ate!upd", $updateBlockBreakInfo));
		$this->register(new Transparent(new BID(Ids::INVISIBLE_BEDROCK), "Invisible Bedrock", BreakInfo::indestructible()));

		$ironBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::IRON), "Iron Block", $ironBreakInfo));
		$this->register(new Thin(new BID(Ids::IRON_BARS), "Iron Bars", $ironBreakInfo));
		$ironDoorBreakInfo = new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 25.0);
		$this->register(new Door(new BID(Ids::IRON_DOOR), "Iron Door", $ironDoorBreakInfo));
		$this->register(new Trapdoor(new BID(Ids::IRON_TRAPDOOR), "Iron Trapdoor", $ironDoorBreakInfo));
		$this->register(new Opaque(new BID(Ids::IRON_ORE), "Iron Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new ItemFrame(new BID(Ids::ITEM_FRAME, TileItemFrame::class), "Item Frame", new BreakInfo(0.25)));
		$this->register(new Jukebox(new BID(Ids::JUKEBOX, TileJukebox::class), "Jukebox", new BreakInfo(0.8, ToolType::AXE))); //TODO: in PC the hardness is 2.0, not 0.8, unsure if this is a MCPE bug or not
		$this->register(new Ladder(new BID(Ids::LADDER), "Ladder", new BreakInfo(0.4, ToolType::AXE)));
		$this->register(new Lantern(new BID(Ids::LANTERN), "Lantern", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::LAPIS_LAZULI), "Lapis Lazuli Block", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new LapisOre(new BID(Ids::LAPIS_LAZULI_ORE), "Lapis Lazuli Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new Lava(new BID(Ids::LAVA), "Lava", BreakInfo::indestructible(500.0)));
		$this->register(new Lectern(new BID(Ids::LECTERN, TileLectern::class), "Lectern", new BreakInfo(2.0, ToolType::AXE)));
		$this->register(new Lever(new BID(Ids::LEVER), "Lever", new BreakInfo(0.5)));
		$this->register(new Loom(new BID(Ids::LOOM), "Loom", new BreakInfo(2.5, ToolType::AXE)));
		$this->register(new Magma(new BID(Ids::MAGMA), "Magma Block", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Melon(new BID(Ids::MELON), "Melon Block", new BreakInfo(1.0, ToolType::AXE)));
		$this->register(new MelonStem(new BID(Ids::MELON_STEM), "Melon Stem", BreakInfo::instant()));
		$this->register(new MonsterSpawner(new BID(Ids::MONSTER_SPAWNER, TileMonsterSpawner::class), "Monster Spawner", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Mycelium(new BID(Ids::MYCELIUM), "Mycelium", new BreakInfo(0.6, ToolType::SHOVEL)));

		$netherBrickBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::NETHER_BRICKS), "Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::RED_NETHER_BRICKS), "Red Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Fence(new BID(Ids::NETHER_BRICK_FENCE), "Nether Brick Fence", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::NETHER_BRICK_STAIRS), "Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::RED_NETHER_BRICK_STAIRS), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_NETHER_BRICKS), "Chiseled Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::CRACKED_NETHER_BRICKS), "Cracked Nether Bricks", $netherBrickBreakInfo));

		$this->register(new NetherPortal(new BID(Ids::NETHER_PORTAL), "Nether Portal", BreakInfo::indestructible(0.0)));
		$this->register(new NetherQuartzOre(new BID(Ids::NETHER_QUARTZ_ORE), "Nether Quartz Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new NetherReactor(new BID(Ids::NETHER_REACTOR_CORE), "Nether Reactor Core", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::NETHER_WART_BLOCK), "Nether Wart Block", new BreakInfo(1.0, ToolType::HOE)));
		$this->register(new NetherWartPlant(new BID(Ids::NETHER_WART), "Nether Wart", BreakInfo::instant()));
		$this->register(new Netherrack(new BID(Ids::NETHERRACK), "Netherrack", new BreakInfo(0.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Note(new BID(Ids::NOTE_BLOCK, TileNote::class), "Note Block", new BreakInfo(0.8, ToolType::AXE)));
		$this->register(new Opaque(new BID(Ids::OBSIDIAN), "Obsidian", new BreakInfo(35.0 /* 50 in PC */, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000.0)));
		$this->register(new PackedIce(new BID(Ids::PACKED_ICE), "Packed Ice", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->register(new Podzol(new BID(Ids::PODZOL), "Podzol", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Potato(new BID(Ids::POTATOES), "Potato Block", BreakInfo::instant()));
		$this->register(new PoweredRail(new BID(Ids::POWERED_RAIL), "Powered Rail", $railBreakInfo));

		$prismarineBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PRISMARINE), "Prismarine", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::DARK_PRISMARINE), "Dark Prismarine", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::PRISMARINE_BRICKS), "Prismarine Bricks", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_BRICKS_STAIRS), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::DARK_PRISMARINE_STAIRS), "Dark Prismarine Stairs", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_STAIRS), "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new BreakInfo(1.0, ToolType::AXE);
		$this->register(new Pumpkin(new BID(Ids::PUMPKIN), "Pumpkin", $pumpkinBreakInfo));
		$this->register(new CarvedPumpkin(new BID(Ids::CARVED_PUMPKIN), "Carved Pumpkin", $pumpkinBreakInfo));
		$this->register(new LitPumpkin(new BID(Ids::LIT_PUMPKIN), "Jack o'Lantern", $pumpkinBreakInfo));

		$this->register(new PumpkinStem(new BID(Ids::PUMPKIN_STEM), "Pumpkin Stem", BreakInfo::instant()));

		$purpurBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PURPUR), "Purpur Block", $purpurBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::PURPUR_PILLAR), "Purpur Pillar", $purpurBreakInfo));
		$this->register(new Stair(new BID(Ids::PURPUR_STAIRS), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::QUARTZ), "Quartz Block", $quartzBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::CHISELED_QUARTZ), "Chiseled Quartz Block", $quartzBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::QUARTZ_PILLAR), "Quartz Pillar", $quartzBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_QUARTZ), "Smooth Quartz Block", $quartzBreakInfo));
		$this->register(new Opaque(new BID(Ids::QUARTZ_BRICKS), "Quartz Bricks", $quartzBreakInfo));

		$this->register(new Stair(new BID(Ids::QUARTZ_STAIRS), "Quartz Stairs", $quartzBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_QUARTZ_STAIRS), "Smooth Quartz Stairs", $quartzBreakInfo));

		$this->register(new Rail(new BID(Ids::RAIL), "Rail", $railBreakInfo));
		$this->register(new RedMushroom(new BID(Ids::RED_MUSHROOM), "Red Mushroom", BreakInfo::instant()));
		$this->register(new Redstone(new BID(Ids::REDSTONE), "Redstone Block", new BreakInfo(5.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->register(new RedstoneComparator(new BID(Ids::REDSTONE_COMPARATOR, TileComparator::class), "Redstone Comparator", BreakInfo::instant()));
		$this->register(new RedstoneLamp(new BID(Ids::REDSTONE_LAMP), "Redstone Lamp", new BreakInfo(0.3)));
		$this->register(new RedstoneOre(new BID(Ids::REDSTONE_ORE), "Redstone Ore", new BreakInfo(3.0, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new RedstoneRepeater(new BID(Ids::REDSTONE_REPEATER), "Redstone Repeater", BreakInfo::instant()));
		$this->register(new RedstoneTorch(new BID(Ids::REDSTONE_TORCH), "Redstone Torch", BreakInfo::instant()));
		$this->register(new RedstoneWire(new BID(Ids::REDSTONE_WIRE), "Redstone", BreakInfo::instant()));
		$this->register(new Reserved6(new BID(Ids::RESERVED6), "reserved6", BreakInfo::instant()));

		$sandBreakInfo = new BreakInfo(0.5, ToolType::SHOVEL);
		$this->register(new Sand(new BID(Ids::SAND), "Sand", $sandBreakInfo));
		$this->register(new Sand(new BID(Ids::RED_SAND), "Red Sand", $sandBreakInfo));

		$this->register(new SeaLantern(new BID(Ids::SEA_LANTERN), "Sea Lantern", new BreakInfo(0.3)));
		$this->register(new SeaPickle(new BID(Ids::SEA_PICKLE), "Sea Pickle", BreakInfo::instant()));
		$this->register(new Skull(new BID(Ids::MOB_HEAD, TileSkull::class), "Mob Head", new BreakInfo(1.0)));
		$this->register(new Slime(new BID(Ids::SLIME), "Slime Block", BreakInfo::instant()));
		$this->register(new Snow(new BID(Ids::SNOW), "Snow Block", new BreakInfo(0.2, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new SnowLayer(new BID(Ids::SNOW_LAYER), "Snow Layer", new BreakInfo(0.1, ToolType::SHOVEL, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new SoulSand(new BID(Ids::SOUL_SAND), "Soul Sand", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Sponge(new BID(Ids::SPONGE), "Sponge", new BreakInfo(0.6, ToolType::HOE)));
		$shulkerBoxBreakInfo = new BreakInfo(2, ToolType::PICKAXE);
		$this->register(new ShulkerBox(new BID(Ids::SHULKER_BOX, TileShulkerBox::class), "Shulker Box", $shulkerBoxBreakInfo));

		$stoneBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(
			$stone = new class(new BID(Ids::STONE), "Stone", $stoneBreakInfo) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			}
		);
		$this->register(new Opaque(new BID(Ids::ANDESITE), "Andesite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::DIORITE), "Diorite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::GRANITE), "Granite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_ANDESITE), "Polished Andesite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_DIORITE), "Polished Diorite", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::POLISHED_GRANITE), "Polished Granite", $stoneBreakInfo));

		$this->register($stoneBrick = new Opaque(new BID(Ids::STONE_BRICKS), "Stone Bricks", $stoneBreakInfo));
		$this->register($mossyStoneBrick = new Opaque(new BID(Ids::MOSSY_STONE_BRICKS), "Mossy Stone Bricks", $stoneBreakInfo));
		$this->register($crackedStoneBrick = new Opaque(new BID(Ids::CRACKED_STONE_BRICKS), "Cracked Stone Bricks", $stoneBreakInfo));
		$this->register($chiseledStoneBrick = new Opaque(new BID(Ids::CHISELED_STONE_BRICKS), "Chiseled Stone Bricks", $stoneBreakInfo));

		$infestedStoneBreakInfo = new BreakInfo(0.75, ToolType::PICKAXE);
		$this->register(new InfestedStone(new BID(Ids::INFESTED_STONE), "Infested Stone", $infestedStoneBreakInfo, $stone));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_STONE_BRICK), "Infested Stone Brick", $infestedStoneBreakInfo, $stoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_COBBLESTONE), "Infested Cobblestone", $infestedStoneBreakInfo, $cobblestone));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_MOSSY_STONE_BRICK), "Infested Mossy Stone Brick", $infestedStoneBreakInfo, $mossyStoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_CRACKED_STONE_BRICK), "Infested Cracked Stone Brick", $infestedStoneBreakInfo, $crackedStoneBrick));
		$this->register(new InfestedStone(new BID(Ids::INFESTED_CHISELED_STONE_BRICK), "Infested Chiseled Stone Brick", $infestedStoneBreakInfo, $chiseledStoneBrick));

		$this->register(new Stair(new BID(Ids::STONE_STAIRS), "Stone Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_STONE), "Smooth Stone", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::ANDESITE_STAIRS), "Andesite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::DIORITE_STAIRS), "Diorite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::GRANITE_STAIRS), "Granite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_ANDESITE_STAIRS), "Polished Andesite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_DIORITE_STAIRS), "Polished Diorite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_GRANITE_STAIRS), "Polished Granite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::STONE_BRICK_STAIRS), "Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_STONE_BRICK_STAIRS), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new StoneButton(new BID(Ids::STONE_BUTTON), "Stone Button", new BreakInfo(0.5, ToolType::PICKAXE)));
		$this->register(new Stonecutter(new BID(Ids::STONECUTTER), "Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE)));
		$this->register(new StonePressurePlate(new BID(Ids::STONE_PRESSURE_PLATE), "Stone Pressure Plate", new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		//TODO: in the future this won't be the same for all the types
		$stoneSlabBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		foreach([
			new Slab(new BID(Ids::BRICK_SLAB), "Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::COBBLESTONE_SLAB), "Cobblestone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::FAKE_WOODEN_SLAB), "Fake Wooden", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::NETHER_BRICK_SLAB), "Nether Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::QUARTZ_SLAB), "Quartz", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::SANDSTONE_SLAB), "Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::SMOOTH_STONE_SLAB), "Smooth Stone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::STONE_BRICK_SLAB), "Stone Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::DARK_PRISMARINE_SLAB), "Dark Prismarine", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::MOSSY_COBBLESTONE_SLAB), "Mossy Cobblestone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::PRISMARINE_SLAB), "Prismarine", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::PRISMARINE_BRICKS_SLAB), "Prismarine Bricks", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::PURPUR_SLAB), "Purpur", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::RED_NETHER_BRICK_SLAB), "Red Nether Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::RED_SANDSTONE_SLAB), "Red Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::SMOOTH_SANDSTONE_SLAB), "Smooth Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::ANDESITE_SLAB), "Andesite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::DIORITE_SLAB), "Diorite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::END_STONE_BRICK_SLAB), "End Stone Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::GRANITE_SLAB), "Granite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::POLISHED_ANDESITE_SLAB), "Polished Andesite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::POLISHED_DIORITE_SLAB), "Polished Diorite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::POLISHED_GRANITE_SLAB), "Polished Granite", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::SMOOTH_RED_SANDSTONE_SLAB), "Smooth Red Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::CUT_RED_SANDSTONE_SLAB), "Cut Red Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::CUT_SANDSTONE_SLAB), "Cut Sandstone", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::MOSSY_STONE_BRICK_SLAB), "Mossy Stone Brick", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::SMOOTH_QUARTZ_SLAB), "Smooth Quartz", $stoneSlabBreakInfo),
			new Slab(new BID(Ids::STONE_SLAB), "Stone", $stoneSlabBreakInfo),
		] as $slabType){
			$this->register($slabType);
		}

		$this->register(new Opaque(new BID(Ids::LEGACY_STONECUTTER), "Legacy Stonecutter", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Sugarcane(new BID(Ids::SUGARCANE), "Sugarcane", BreakInfo::instant()));
		$this->register(new SweetBerryBush(new BID(Ids::SWEET_BERRY_BUSH), "Sweet Berry Bush", BreakInfo::instant()));
		$this->register(new TNT(new BID(Ids::TNT), "TNT", BreakInfo::instant()));
		$this->register(new TallGrass(new BID(Ids::FERN), "Fern", BreakInfo::instant(ToolType::SHEARS, 1)));
		$this->register(new TallGrass(new BID(Ids::TALL_GRASS), "Tall Grass", BreakInfo::instant(ToolType::SHEARS, 1)));

		$this->register(new Torch(new BID(Ids::BLUE_TORCH), "Blue Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::PURPLE_TORCH), "Purple Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::RED_TORCH), "Red Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::GREEN_TORCH), "Green Torch", BreakInfo::instant()));
		$this->register(new Torch(new BID(Ids::TORCH), "Torch", BreakInfo::instant()));

		$this->register(new TrappedChest(new BID(Ids::TRAPPED_CHEST, TileChest::class), "Trapped Chest", $chestBreakInfo));
		$this->register(new Tripwire(new BID(Ids::TRIPWIRE), "Tripwire", BreakInfo::instant()));
		$this->register(new TripwireHook(new BID(Ids::TRIPWIRE_HOOK), "Tripwire Hook", BreakInfo::instant()));
		$this->register(new UnderwaterTorch(new BID(Ids::UNDERWATER_TORCH), "Underwater Torch", BreakInfo::instant()));
		$this->register(new Vine(new BID(Ids::VINES), "Vines", new BreakInfo(0.2, ToolType::AXE)));
		$this->register(new Water(new BID(Ids::WATER), "Water", BreakInfo::indestructible(500.0)));
		$this->register(new WaterLily(new BID(Ids::LILY_PAD), "Lily Pad", BreakInfo::instant()));

		$weightedPressurePlateBreakInfo = new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new WeightedPressurePlateHeavy(new BID(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY), "Weighted Pressure Plate Heavy", $weightedPressurePlateBreakInfo));
		$this->register(new WeightedPressurePlateLight(new BID(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT), "Weighted Pressure Plate Light", $weightedPressurePlateBreakInfo));
		$this->register(new Wheat(new BID(Ids::WHEAT), "Wheat Block", BreakInfo::instant()));

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

			$this->register(new Log(BlockLegacyIdHelper::getLogIdentifier($treeType), $name . " Log", $logBreakInfo, $treeType));
			$this->register(new Wood(BlockLegacyIdHelper::getAllSidedLogIdentifier($treeType), $name . " Wood", $logBreakInfo, $treeType));

			$this->register(new FenceGate(BlockLegacyIdHelper::getWoodenFenceGateIdentifier($treeType), $name . " Fence Gate", $planksBreakInfo));
			$this->register(new WoodenStairs(BlockLegacyIdHelper::getWoodenStairsIdentifier($treeType), $name . " Stairs", $planksBreakInfo));
			$this->register(new WoodenDoor(BlockLegacyIdHelper::getWoodenDoorIdentifier($treeType), $name . " Door", $woodenDoorBreakInfo));

			$this->register(new WoodenButton(BlockLegacyIdHelper::getWoodenButtonIdentifier($treeType), $name . " Button", $woodenButtonBreakInfo));
			$this->register(new WoodenPressurePlate(BlockLegacyIdHelper::getWoodenPressurePlateIdentifier($treeType), $name . " Pressure Plate", $woodenPressurePlateBreakInfo));
			$this->register(new WoodenTrapdoor(BlockLegacyIdHelper::getWoodenTrapdoorIdentifier($treeType), $name . " Trapdoor", $woodenDoorBreakInfo));

			[$floorSignId, $wallSignId, $signAsItem] = BlockLegacyIdHelper::getWoodenSignInfo($treeType);
			$this->register(new FloorSign($floorSignId, $name . " Sign", $signBreakInfo, $signAsItem));
			$this->register(new WallSign($wallSignId, $name . " Wall Sign", $signBreakInfo, $signAsItem));
		}

		$sandstoneBreakInfo = new BreakInfo(0.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Stair(new BID(Ids::RED_SANDSTONE_STAIRS), "Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_RED_SANDSTONE_STAIRS), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::RED_SANDSTONE), "Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_RED_SANDSTONE), "Chiseled Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CUT_RED_SANDSTONE), "Cut Red Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_RED_SANDSTONE), "Smooth Red Sandstone", $sandstoneBreakInfo));

		$this->register(new Stair(new BID(Ids::SANDSTONE_STAIRS), "Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_SANDSTONE_STAIRS), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SANDSTONE), "Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_SANDSTONE), "Chiseled Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CUT_SANDSTONE), "Cut Sandstone", $sandstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_SANDSTONE), "Smooth Sandstone", $sandstoneBreakInfo));

		$this->register(new GlazedTerracotta(new BID(Ids::GLAZED_TERRACOTTA), "Glazed Terracotta", new BreakInfo(1.4, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new DyedShulkerBox(new BID(Ids::DYED_SHULKER_BOX, TileShulkerBox::class), "Dyed Shulker Box", $shulkerBoxBreakInfo));
		$this->register(new StainedGlass(new BID(Ids::STAINED_GLASS), "Stained Glass", $glassBreakInfo));
		$this->register(new StainedGlassPane(new BID(Ids::STAINED_GLASS_PANE), "Stained Glass Pane", $glassBreakInfo));
		$this->register(new StainedHardenedClay(new BID(Ids::STAINED_CLAY), "Stained Clay", $hardenedClayBreakInfo));
		$this->register(new StainedHardenedGlass(new BID(Ids::STAINED_HARDENED_GLASS), "Stained Hardened Glass", $hardenedGlassBreakInfo));
		$this->register(new StainedHardenedGlassPane(new BID(Ids::STAINED_HARDENED_GLASS_PANE), "Stained Hardened Glass Pane", $hardenedGlassBreakInfo));
		$this->register(new Carpet(new BID(Ids::CARPET), "Carpet", new BreakInfo(0.1)));
		$this->register(new Concrete(new BID(Ids::CONCRETE), "Concrete", new BreakInfo(1.8, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new ConcretePowder(new BID(Ids::CONCRETE_POWDER), "Concrete Powder", new BreakInfo(0.5, ToolType::SHOVEL)));
		$this->register(new Wool(new BID(Ids::WOOL), "Wool", new class(0.8, ToolType::SHEARS) extends BreakInfo{
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
		$this->register(new Wall(new BID(Ids::COBBLESTONE_WALL), "Cobblestone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::ANDESITE_WALL), "Andesite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::BRICK_WALL), "Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::DIORITE_WALL), "Diorite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::END_STONE_BRICK_WALL), "End Stone Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::GRANITE_WALL), "Granite Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::MOSSY_STONE_BRICK_WALL), "Mossy Stone Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::MOSSY_COBBLESTONE_WALL), "Mossy Cobblestone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::NETHER_BRICK_WALL), "Nether Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::PRISMARINE_WALL), "Prismarine Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::RED_NETHER_BRICK_WALL), "Red Nether Brick Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::RED_SANDSTONE_WALL), "Red Sandstone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::SANDSTONE_WALL), "Sandstone Wall", $wallBreakInfo));
		$this->register(new Wall(new BID(Ids::STONE_BRICK_WALL), "Stone Brick Wall", $wallBreakInfo));

		$this->registerElements();

		$chemistryTableBreakInfo = new BreakInfo(2.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new ChemistryTable(new BID(Ids::COMPOUND_CREATOR), "Compound Creator", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::ELEMENT_CONSTRUCTOR), "Element Constructor", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::LAB_TABLE), "Lab Table", $chemistryTableBreakInfo));
		$this->register(new ChemistryTable(new BID(Ids::MATERIAL_REDUCER), "Material Reducer", $chemistryTableBreakInfo));

		$this->register(new ChemicalHeat(new BID(Ids::CHEMICAL_HEAT), "Heat Block", $chemistryTableBreakInfo));

		$this->registerMushroomBlocks();

		$this->register(new Coral(
			new BID(Ids::CORAL),
			"Coral",
			BreakInfo::instant(),
		));
		$this->register(new FloorCoralFan(
			new BID(Ids::CORAL_FAN),
			"Coral Fan",
			BreakInfo::instant(),
		));
		$this->register(new WallCoralFan(
			new BID(Ids::WALL_CORAL_FAN),
			"Wall Coral Fan",
			BreakInfo::instant(),
		));

		$this->registerBlocksR13();
		$this->registerBlocksR16();
		$this->registerBlocksR17();

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

		$this->register(new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block", $mushroomBlockBreakInfo));
		$this->register(new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK), "Red Mushroom Block", $mushroomBlockBreakInfo));

		//finally, the stems
		$this->register(new MushroomStem(new BID(Ids::MUSHROOM_STEM), "Mushroom Stem", $mushroomBlockBreakInfo));
		$this->register(new MushroomStem(new BID(Ids::ALL_SIDED_MUSHROOM_STEM), "All Sided Mushroom Stem", $mushroomBlockBreakInfo));
	}

	private function registerElements() : void{
		$instaBreak = BreakInfo::instant();
		$this->register(new Opaque(new BID(Ids::ELEMENT_ZERO), "???", $instaBreak));

		$this->register(new Element(new BID(Ids::ELEMENT_HYDROGEN), "Hydrogen", $instaBreak, "h", 1, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_HELIUM), "Helium", $instaBreak, "he", 2, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_LITHIUM), "Lithium", $instaBreak, "li", 3, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_BERYLLIUM), "Beryllium", $instaBreak, "be", 4, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_BORON), "Boron", $instaBreak, "b", 5, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_CARBON), "Carbon", $instaBreak, "c", 6, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_NITROGEN), "Nitrogen", $instaBreak, "n", 7, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_OXYGEN), "Oxygen", $instaBreak, "o", 8, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_FLUORINE), "Fluorine", $instaBreak, "f", 9, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_NEON), "Neon", $instaBreak, "ne", 10, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_SODIUM), "Sodium", $instaBreak, "na", 11, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_MAGNESIUM), "Magnesium", $instaBreak, "mg", 12, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_ALUMINUM), "Aluminum", $instaBreak, "al", 13, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_SILICON), "Silicon", $instaBreak, "si", 14, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_PHOSPHORUS), "Phosphorus", $instaBreak, "p", 15, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_SULFUR), "Sulfur", $instaBreak, "s", 16, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_CHLORINE), "Chlorine", $instaBreak, "cl", 17, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_ARGON), "Argon", $instaBreak, "ar", 18, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_POTASSIUM), "Potassium", $instaBreak, "k", 19, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_CALCIUM), "Calcium", $instaBreak, "ca", 20, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_SCANDIUM), "Scandium", $instaBreak, "sc", 21, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TITANIUM), "Titanium", $instaBreak, "ti", 22, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_VANADIUM), "Vanadium", $instaBreak, "v", 23, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_CHROMIUM), "Chromium", $instaBreak, "cr", 24, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MANGANESE), "Manganese", $instaBreak, "mn", 25, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_IRON), "Iron", $instaBreak, "fe", 26, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COBALT), "Cobalt", $instaBreak, "co", 27, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NICKEL), "Nickel", $instaBreak, "ni", 28, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COPPER), "Copper", $instaBreak, "cu", 29, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ZINC), "Zinc", $instaBreak, "zn", 30, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_GALLIUM), "Gallium", $instaBreak, "ga", 31, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_GERMANIUM), "Germanium", $instaBreak, "ge", 32, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_ARSENIC), "Arsenic", $instaBreak, "as", 33, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_SELENIUM), "Selenium", $instaBreak, "se", 34, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_BROMINE), "Bromine", $instaBreak, "br", 35, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_KRYPTON), "Krypton", $instaBreak, "kr", 36, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_RUBIDIUM), "Rubidium", $instaBreak, "rb", 37, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_STRONTIUM), "Strontium", $instaBreak, "sr", 38, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_YTTRIUM), "Yttrium", $instaBreak, "y", 39, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ZIRCONIUM), "Zirconium", $instaBreak, "zr", 40, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NIOBIUM), "Niobium", $instaBreak, "nb", 41, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MOLYBDENUM), "Molybdenum", $instaBreak, "mo", 42, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TECHNETIUM), "Technetium", $instaBreak, "tc", 43, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RUTHENIUM), "Ruthenium", $instaBreak, "ru", 44, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RHODIUM), "Rhodium", $instaBreak, "rh", 45, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_PALLADIUM), "Palladium", $instaBreak, "pd", 46, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_SILVER), "Silver", $instaBreak, "ag", 47, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_CADMIUM), "Cadmium", $instaBreak, "cd", 48, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_INDIUM), "Indium", $instaBreak, "in", 49, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_TIN), "Tin", $instaBreak, "sn", 50, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_ANTIMONY), "Antimony", $instaBreak, "sb", 51, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_TELLURIUM), "Tellurium", $instaBreak, "te", 52, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_IODINE), "Iodine", $instaBreak, "i", 53, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_XENON), "Xenon", $instaBreak, "xe", 54, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_CESIUM), "Cesium", $instaBreak, "cs", 55, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_BARIUM), "Barium", $instaBreak, "ba", 56, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_LANTHANUM), "Lanthanum", $instaBreak, "la", 57, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_CERIUM), "Cerium", $instaBreak, "ce", 58, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_PRASEODYMIUM), "Praseodymium", $instaBreak, "pr", 59, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_NEODYMIUM), "Neodymium", $instaBreak, "nd", 60, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_PROMETHIUM), "Promethium", $instaBreak, "pm", 61, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_SAMARIUM), "Samarium", $instaBreak, "sm", 62, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_EUROPIUM), "Europium", $instaBreak, "eu", 63, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_GADOLINIUM), "Gadolinium", $instaBreak, "gd", 64, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_TERBIUM), "Terbium", $instaBreak, "tb", 65, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_DYSPROSIUM), "Dysprosium", $instaBreak, "dy", 66, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_HOLMIUM), "Holmium", $instaBreak, "ho", 67, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_ERBIUM), "Erbium", $instaBreak, "er", 68, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_THULIUM), "Thulium", $instaBreak, "tm", 69, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_YTTERBIUM), "Ytterbium", $instaBreak, "yb", 70, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_LUTETIUM), "Lutetium", $instaBreak, "lu", 71, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_HAFNIUM), "Hafnium", $instaBreak, "hf", 72, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TANTALUM), "Tantalum", $instaBreak, "ta", 73, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_TUNGSTEN), "Tungsten", $instaBreak, "w", 74, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_RHENIUM), "Rhenium", $instaBreak, "re", 75, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_OSMIUM), "Osmium", $instaBreak, "os", 76, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_IRIDIUM), "Iridium", $instaBreak, "ir", 77, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_PLATINUM), "Platinum", $instaBreak, "pt", 78, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_GOLD), "Gold", $instaBreak, "au", 79, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MERCURY), "Mercury", $instaBreak, "hg", 80, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_THALLIUM), "Thallium", $instaBreak, "tl", 81, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_LEAD), "Lead", $instaBreak, "pb", 82, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_BISMUTH), "Bismuth", $instaBreak, "bi", 83, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_POLONIUM), "Polonium", $instaBreak, "po", 84, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_ASTATINE), "Astatine", $instaBreak, "at", 85, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_RADON), "Radon", $instaBreak, "rn", 86, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_FRANCIUM), "Francium", $instaBreak, "fr", 87, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_RADIUM), "Radium", $instaBreak, "ra", 88, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_ACTINIUM), "Actinium", $instaBreak, "ac", 89, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_THORIUM), "Thorium", $instaBreak, "th", 90, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_PROTACTINIUM), "Protactinium", $instaBreak, "pa", 91, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_URANIUM), "Uranium", $instaBreak, "u", 92, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_NEPTUNIUM), "Neptunium", $instaBreak, "np", 93, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_PLUTONIUM), "Plutonium", $instaBreak, "pu", 94, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_AMERICIUM), "Americium", $instaBreak, "am", 95, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_CURIUM), "Curium", $instaBreak, "cm", 96, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_BERKELIUM), "Berkelium", $instaBreak, "bk", 97, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_CALIFORNIUM), "Californium", $instaBreak, "cf", 98, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_EINSTEINIUM), "Einsteinium", $instaBreak, "es", 99, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_FERMIUM), "Fermium", $instaBreak, "fm", 100, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_MENDELEVIUM), "Mendelevium", $instaBreak, "md", 101, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_NOBELIUM), "Nobelium", $instaBreak, "no", 102, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_LAWRENCIUM), "Lawrencium", $instaBreak, "lr", 103, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_RUTHERFORDIUM), "Rutherfordium", $instaBreak, "rf", 104, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_DUBNIUM), "Dubnium", $instaBreak, "db", 105, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_SEABORGIUM), "Seaborgium", $instaBreak, "sg", 106, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_BOHRIUM), "Bohrium", $instaBreak, "bh", 107, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_HASSIUM), "Hassium", $instaBreak, "hs", 108, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_MEITNERIUM), "Meitnerium", $instaBreak, "mt", 109, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_DARMSTADTIUM), "Darmstadtium", $instaBreak, "ds", 110, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_ROENTGENIUM), "Roentgenium", $instaBreak, "rg", 111, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_COPERNICIUM), "Copernicium", $instaBreak, "cn", 112, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_NIHONIUM), "Nihonium", $instaBreak, "nh", 113, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_FLEROVIUM), "Flerovium", $instaBreak, "fl", 114, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_MOSCOVIUM), "Moscovium", $instaBreak, "mc", 115, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_LIVERMORIUM), "Livermorium", $instaBreak, "lv", 116, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_TENNESSINE), "Tennessine", $instaBreak, "ts", 117, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_OGANESSON), "Oganesson", $instaBreak, "og", 118, 7));
	}

	private function registerBlocksR13() : void{
		$this->register(new Light(new BID(Ids::LIGHT), "Light Block", BreakInfo::indestructible()));
	}

	private function registerBlocksR16() : void{
		//for some reason, slabs have weird hardness like the legacy ones
		$slabBreakInfo = new BreakInfo(2.0, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());

		$this->register(new Opaque(new BID(Ids::ANCIENT_DEBRIS), "Ancient Debris", new BreakInfo(30, ToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel())));

		$basaltBreakInfo = new BreakInfo(1.25, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new SimplePillar(new BID(Ids::BASALT), "Basalt", $basaltBreakInfo));
		$this->register(new SimplePillar(new BID(Ids::POLISHED_BASALT), "Polished Basalt", $basaltBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_BASALT), "Smooth Basalt", $basaltBreakInfo));

		$blackstoneBreakInfo = new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::BLACKSTONE), "Blackstone", $blackstoneBreakInfo));
		$this->register(new Slab(new BID(Ids::BLACKSTONE_SLAB), "Blackstone", $slabBreakInfo));
		$this->register(new Stair(new BID(Ids::BLACKSTONE_STAIRS), "Blackstone Stairs", $blackstoneBreakInfo));
		$this->register(new Wall(new BID(Ids::BLACKSTONE_WALL), "Blackstone Wall", $blackstoneBreakInfo));

		//TODO: polished blackstone ought to have 2.0 hardness (as per java) but it's 1.5 in Bedrock (probably parity bug)
		$prefix = fn(string $thing) => "Polished Blackstone" . ($thing !== "" ? " $thing" : "");
		$this->register(new Opaque(new BID(Ids::POLISHED_BLACKSTONE), $prefix(""), $blackstoneBreakInfo));
		$this->register(new StoneButton(new BID(Ids::POLISHED_BLACKSTONE_BUTTON), $prefix("Button"), new BreakInfo(0.5, ToolType::PICKAXE))); //same as regular stone button
		$this->register(new StonePressurePlate(new BID(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE), $prefix("Pressure Plate"), new BreakInfo(0.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()))); //same as regular stone pressure plate
		$this->register(new Slab(new BID(Ids::POLISHED_BLACKSTONE_SLAB), $prefix(""), $slabBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_BLACKSTONE_STAIRS), $prefix("Stairs"), $blackstoneBreakInfo));
		$this->register(new Wall(new BID(Ids::POLISHED_BLACKSTONE_WALL), $prefix("Wall"), $blackstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CHISELED_POLISHED_BLACKSTONE), "Chiseled Polished Blackstone", $blackstoneBreakInfo));

		$prefix = fn(string $thing) => "Polished Blackstone Brick" . ($thing !== "" ? " $thing" : "");
		$this->register(new Opaque(new BID(Ids::POLISHED_BLACKSTONE_BRICKS), "Polished Blackstone Bricks", $blackstoneBreakInfo));
		$this->register(new Slab(new BID(Ids::POLISHED_BLACKSTONE_BRICK_SLAB), "Polished Blackstone Brick", $slabBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS), $prefix("Stairs"), $blackstoneBreakInfo));
		$this->register(new Wall(new BID(Ids::POLISHED_BLACKSTONE_BRICK_WALL), $prefix("Wall"), $blackstoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS), "Cracked Polished Blackstone Bricks", $blackstoneBreakInfo));
	}

	private function registerBlocksR17() : void{
		//in java this can be acquired using any tool - seems to be a parity issue in bedrock
		$this->register(new Opaque(new BID(Ids::AMETHYST), "Amethyst", new BreakInfo(1.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$this->register(new Opaque(new BID(Ids::CALCITE), "Calcite", new BreakInfo(0.75, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		$this->register(new Opaque(new BID(Ids::RAW_COPPER), "Raw Copper Block", new BreakInfo(5, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::RAW_GOLD), "Raw Gold Block", new BreakInfo(5, ToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::RAW_IRON), "Raw Iron Block", new BreakInfo(5, ToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));

		//TODO: check blast resistance
		$deepslateBreakInfo = new BreakInfo(3, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new SimplePillar(new BID(Ids::DEEPSLATE), "Deepslate", $deepslateBreakInfo));

		//TODO: parity issue here - in Java this has a hardness of 3.0, but in bedrock it's 3.5
		$this->register(new Opaque(new BID(Ids::CHISELED_DEEPSLATE), "Chiseled Deepslate", new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

		//TODO: check blast resistance
		$deepslateBrickBreakInfo = new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::DEEPSLATE_BRICKS), "Deepslate Bricks", $deepslateBrickBreakInfo));
		$this->register(new Slab(new BID(Ids::DEEPSLATE_BRICK_SLAB), "Deepslate Brick", $deepslateBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::DEEPSLATE_BRICK_STAIRS), "Deepslate Brick Stairs", $deepslateBrickBreakInfo));
		$this->register(new Wall(new BID(Ids::DEEPSLATE_BRICK_WALL), "Deepslate Brick Wall", $deepslateBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::CRACKED_DEEPSLATE_BRICKS), "Cracked Deepslate Bricks", $deepslateBrickBreakInfo));

		//TODO: check blast resistance
		$deepslateTilesBreakInfo = new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::DEEPSLATE_TILES), "Deepslate Tiles", $deepslateTilesBreakInfo));
		$this->register(new Slab(new BID(Ids::DEEPSLATE_TILE_SLAB), "Deepslate Tile", $deepslateTilesBreakInfo));
		$this->register(new Stair(new BID(Ids::DEEPSLATE_TILE_STAIRS), "Deepslate Tile Stairs", $deepslateTilesBreakInfo));
		$this->register(new Wall(new BID(Ids::DEEPSLATE_TILE_WALL), "Deepslate Tile Wall", $deepslateTilesBreakInfo));
		$this->register(new Opaque(new BID(Ids::CRACKED_DEEPSLATE_TILES), "Cracked Deepslate Tiles", $deepslateTilesBreakInfo));

		//TODO: check blast resistance
		$cobbledDeepslateBreakInfo = new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::COBBLED_DEEPSLATE), "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		$this->register(new Slab(new BID(Ids::COBBLED_DEEPSLATE_SLAB), "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		$this->register(new Stair(new BID(Ids::COBBLED_DEEPSLATE_STAIRS), "Cobbled Deepslate Stairs", $cobbledDeepslateBreakInfo));
		$this->register(new Wall(new BID(Ids::COBBLED_DEEPSLATE_WALL), "Cobbled Deepslate Wall", $cobbledDeepslateBreakInfo));

		//TODO: check blast resistance
		$polishedDeepslateBreakInfo = new BreakInfo(3.5, ToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::POLISHED_DEEPSLATE), "Polished Deepslate", $polishedDeepslateBreakInfo));
		$this->register(new Slab(new BID(Ids::POLISHED_DEEPSLATE_SLAB), "Polished Deepslate", $polishedDeepslateBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_DEEPSLATE_STAIRS), "Polished Deepslate Stairs", $polishedDeepslateBreakInfo));
		$this->register(new Wall(new BID(Ids::POLISHED_DEEPSLATE_WALL), "Polished Deepslate Wall", $polishedDeepslateBreakInfo));
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
			$block = new UnknownBlock(new BID($typeId), BreakInfo::instant(), $stateData);
		}

		return $block;
	}

	/**
	 * @internal
	 * Returns the default state of the block type associated with the given type ID.
	 */
	public function fromTypeId(int $typeId) : ?Block{
		if(isset($this->typeIndex[$typeId])){
			return clone $this->typeIndex[$typeId];
		}

		return null;
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
