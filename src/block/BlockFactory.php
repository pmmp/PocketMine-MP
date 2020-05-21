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

use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockLegacyMetadata as Meta;
use pocketmine\block\tile\Banner as TileBanner;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\Comparator as TileComparator;
use pocketmine\block\tile\DaylightSensor as TileDaylightSensor;
use pocketmine\block\tile\EnchantTable as TileEnchantingTable;
use pocketmine\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\tile\FlowerPot as TileFlowerPot;
use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\tile\ItemFrame as TileItemFrame;
use pocketmine\block\tile\MonsterSpawner as TileMonsterSpawner;
use pocketmine\block\tile\Note as TileNote;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\tile\Skull as TileSkull;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\utils\SingletonTrait;
use function array_fill;
use function array_filter;
use function get_class;
use function min;

/**
 * Manages block registration and instance creation
 */
class BlockFactory{
	use SingletonTrait;

	/**
	 * @var \SplFixedArray|Block[]
	 * @phpstan-var \SplFixedArray<Block>
	 */
	private $fullList;

	/**
	 * @var \SplFixedArray|int[]
	 * @phpstan-var \SplFixedArray<int>
	 */
	public $lightFilter;
	/**
	 * @var \SplFixedArray|bool[]
	 * @phpstan-var \SplFixedArray<bool>
	 */
	public $diffusesSkyLight;
	/**
	 * @var \SplFixedArray|float[]
	 * @phpstan-var \SplFixedArray<float>
	 */
	public $blastResistance;

	public function __construct(){
		$this->fullList = new \SplFixedArray(8192);

		$this->lightFilter = \SplFixedArray::fromArray(array_fill(0, 8192, 1));
		$this->diffusesSkyLight = \SplFixedArray::fromArray(array_fill(0, 8192, false));
		$this->blastResistance = \SplFixedArray::fromArray(array_fill(0, 8192, 0.0));

		$this->register(new ActivatorRail(new BID(Ids::ACTIVATOR_RAIL), "Activator Rail"));
		$this->register(new Air(new BID(Ids::AIR), "Air"));
		$this->register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_NORMAL), "Anvil"));
		$this->register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_SLIGHTLY_DAMAGED), "Slightly Damaged Anvil"));
		$this->register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_VERY_DAMAGED), "Very Damaged Anvil"));
		$this->register(new Banner(new BIDFlattened(Ids::STANDING_BANNER, Ids::WALL_BANNER, 0, ItemIds::BANNER, TileBanner::class), "Banner"));
		$this->register(new Transparent(new BID(Ids::BARRIER), "Barrier", BlockBreakInfo::indestructible()));
		$this->register(new Bed(new BID(Ids::BED_BLOCK, 0, ItemIds::BED, TileBed::class), "Bed Block"));
		$this->register(new Bedrock(new BID(Ids::BEDROCK), "Bedrock"));
		$this->register(new Beetroot(new BID(Ids::BEETROOT_BLOCK), "Beetroot Block"));
		$this->register(new BlueIce(new BID(Ids::BLUE_ICE), "Blue Ice"));
		$this->register(new BoneBlock(new BID(Ids::BONE_BLOCK), "Bone Block"));
		$this->register(new Bookshelf(new BID(Ids::BOOKSHELF), "Bookshelf"));
		$this->register(new BrewingStand(new BID(Ids::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND, TileBrewingStand::class), "Brewing Stand"));

		$bricksBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Stair(new BID(Ids::BRICK_STAIRS), "Brick Stairs", $bricksBreakInfo));
		$this->register(new Opaque(new BID(Ids::BRICK_BLOCK), "Bricks", $bricksBreakInfo));

		$this->register(new BrownMushroom(new BID(Ids::BROWN_MUSHROOM), "Brown Mushroom"));
		$this->register(new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block"));
		$this->register(new Cactus(new BID(Ids::CACTUS), "Cactus"));
		$this->register(new Cake(new BID(Ids::CAKE_BLOCK, 0, ItemIds::CAKE), "Cake"));
		$this->register(new Carrot(new BID(Ids::CARROTS), "Carrot Block"));
		$this->register(new Chest(new BID(Ids::CHEST, 0, null, TileChest::class), "Chest"));
		$this->register(new Clay(new BID(Ids::CLAY_BLOCK), "Clay Block"));
		$this->register(new Coal(new BID(Ids::COAL_BLOCK), "Coal Block"));
		$this->register(new CoalOre(new BID(Ids::COAL_ORE), "Coal Ore"));
		$this->register(new CoarseDirt(new BID(Ids::DIRT, Meta::DIRT_COARSE), "Coarse Dirt"));

		$cobblestoneBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::COBBLESTONE), "Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::MOSSY_COBBLESTONE), "Mossy Cobblestone", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::COBBLESTONE_STAIRS), "Cobblestone Stairs", $cobblestoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_COBBLESTONE_STAIRS), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		$this->register(new Cobweb(new BID(Ids::COBWEB), "Cobweb"));
		$this->register(new CocoaBlock(new BID(Ids::COCOA), "Cocoa Block"));
		$this->register(new CraftingTable(new BID(Ids::CRAFTING_TABLE), "Crafting Table"));
		$this->register(new DaylightSensor(new BIDFlattened(Ids::DAYLIGHT_DETECTOR, Ids::DAYLIGHT_DETECTOR_INVERTED, 0, null, TileDaylightSensor::class), "Daylight Sensor"));
		$this->register(new DeadBush(new BID(Ids::DEADBUSH), "Dead Bush"));
		$this->register(new DetectorRail(new BID(Ids::DETECTOR_RAIL), "Detector Rail"));

		$this->register(new Opaque(new BID(Ids::DIAMOND_BLOCK), "Diamond Block", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new DiamondOre(new BID(Ids::DIAMOND_ORE), "Diamond Ore"));
		$this->register(new Dirt(new BID(Ids::DIRT, Meta::DIRT_NORMAL), "Dirt"));
		$this->register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_SUNFLOWER), "Sunflower"));
		$this->register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LILAC), "Lilac"));
		$this->register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_ROSE_BUSH), "Rose Bush"));
		$this->register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_PEONY), "Peony"));
		$this->register(new DoubleTallGrass(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_TALLGRASS), "Double Tallgrass"));
		$this->register(new DoubleTallGrass(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LARGE_FERN), "Large Fern"));
		$this->register(new DragonEgg(new BID(Ids::DRAGON_EGG), "Dragon Egg"));
		$this->register(new DriedKelp(new BID(Ids::DRIED_KELP_BLOCK), "Dried Kelp Block", new BlockBreakInfo(0.5, BlockToolType::NONE, 0, 12.5)));
		$this->register(new Opaque(new BID(Ids::EMERALD_BLOCK), "Emerald Block", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new EmeraldOre(new BID(Ids::EMERALD_ORE), "Emerald Ore"));
		$this->register(new EnchantingTable(new BID(Ids::ENCHANTING_TABLE, 0, null, TileEnchantingTable::class), "Enchanting Table"));
		$this->register(new EndPortalFrame(new BID(Ids::END_PORTAL_FRAME), "End Portal Frame"));
		$this->register(new EndRod(new BID(Ids::END_ROD), "End Rod"));
		$this->register(new Opaque(new BID(Ids::END_STONE), "End Stone", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 45.0)));

		$endBrickBreakInfo = new BlockBreakInfo(0.8, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.0);
		$this->register(new Opaque(new BID(Ids::END_BRICKS), "End Stone Bricks", $endBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::END_BRICK_STAIRS), "End Stone Brick Stairs", $endBrickBreakInfo));

		$this->register(new EnderChest(new BID(Ids::ENDER_CHEST, 0, null, TileEnderChest::class), "Ender Chest"));
		$this->register(new Farmland(new BID(Ids::FARMLAND), "Farmland"));
		$this->register(new Fire(new BID(Ids::FIRE), "Fire Block"));
		$this->register(new Flower(new BID(Ids::DANDELION), "Dandelion"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_ALLIUM), "Allium"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_AZURE_BLUET), "Azure Bluet"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_BLUE_ORCHID), "Blue Orchid"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_CORNFLOWER), "Cornflower"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_LILY_OF_THE_VALLEY), "Lily of the Valley"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_ORANGE_TULIP), "Orange Tulip"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_OXEYE_DAISY), "Oxeye Daisy"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_PINK_TULIP), "Pink Tulip"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_POPPY), "Poppy"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_RED_TULIP), "Red Tulip"));
		$this->register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_WHITE_TULIP), "White Tulip"));
		$this->register(new FlowerPot(new BID(Ids::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT, TileFlowerPot::class), "Flower Pot"));
		$this->register(new FrostedIce(new BID(Ids::FROSTED_ICE), "Frosted Ice"));
		$this->register(new Furnace(new BIDFlattened(Ids::FURNACE, Ids::LIT_FURNACE, 0, null, TileFurnace::class), "Furnace"));
		$this->register(new Glass(new BID(Ids::GLASS), "Glass"));
		$this->register(new GlassPane(new BID(Ids::GLASS_PANE), "Glass Pane"));
		$this->register(new GlowingObsidian(new BID(Ids::GLOWINGOBSIDIAN), "Glowing Obsidian"));
		$this->register(new Glowstone(new BID(Ids::GLOWSTONE), "Glowstone"));
		$this->register(new Opaque(new BID(Ids::GOLD_BLOCK), "Gold Block", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel(), 30.0)));
		$this->register(new Opaque(new BID(Ids::GOLD_ORE), "Gold Ore", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel())));
		$this->register(new Grass(new BID(Ids::GRASS), "Grass"));
		$this->register(new GrassPath(new BID(Ids::GRASS_PATH), "Grass Path"));
		$this->register(new Gravel(new BID(Ids::GRAVEL), "Gravel"));
		$this->register(new HardenedClay(new BID(Ids::HARDENED_CLAY), "Hardened Clay"));
		$this->register(new HardenedGlass(new BID(Ids::HARD_GLASS), "Hardened Glass"));
		$this->register(new HardenedGlassPane(new BID(Ids::HARD_GLASS_PANE), "Hardened Glass Pane"));
		$this->register(new HayBale(new BID(Ids::HAY_BALE), "Hay Bale"));
		$this->register(new Hopper(new BID(Ids::HOPPER_BLOCK, 0, ItemIds::HOPPER, TileHopper::class), "Hopper", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0)));
		$this->register(new Ice(new BID(Ids::ICE), "Ice"));
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE), "Infested Stone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::STONE()->asItem()];
			}
		});
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_COBBLESTONE), "Infested Cobblestone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::COBBLESTONE()->asItem()];
			}
		});
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK), "Infested Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::STONE_BRICKS()->asItem()];
			}
		});
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_MOSSY), "Infested Mossy Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::MOSSY_STONE_BRICKS()->asItem()];
			}
		});
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CRACKED), "Infested Cracked Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::CRACKED_STONE_BRICKS()->asItem()];
			}
		});
		$this->register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CHISELED), "Infested Chiseled Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [VanillaBlocks::CHISELED_STONE_BRICKS()->asItem()];
			}
		});

		$updateBlockBreakInfo = new BlockBreakInfo(1.0);
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE), "update!", $updateBlockBreakInfo));
		$this->register(new Opaque(new BID(Ids::INFO_UPDATE2), "ate!upd", $updateBlockBreakInfo));
		$this->register(new Transparent(new BID(Ids::INVISIBLEBEDROCK), "Invisible Bedrock", BlockBreakInfo::indestructible()));
		$this->register(new Opaque(new BID(Ids::IRON_BLOCK), "Iron Block", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel(), 30.0)));
		$this->register(new Thin(new BID(Ids::IRON_BARS), "Iron Bars", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0)));
		$this->register(new Door(new BID(Ids::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 25.0)));
		$this->register(new Opaque(new BID(Ids::IRON_ORE), "Iron Ore", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new Trapdoor(new BID(Ids::IRON_TRAPDOOR), "Iron Trapdoor", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 25.0)));
		$this->register(new ItemFrame(new BID(Ids::FRAME_BLOCK, 0, ItemIds::FRAME, TileItemFrame::class), "Item Frame"));
		$this->register(new Ladder(new BID(Ids::LADDER), "Ladder"));
		$this->register(new Lantern(new BID(Ids::LANTERN), "Lantern", new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Opaque(new BID(Ids::LAPIS_BLOCK), "Lapis Lazuli Block", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel())));
		$this->register(new LapisOre(new BID(Ids::LAPIS_ORE), "Lapis Lazuli Ore"));
		$this->register(new Lava(new BIDFlattened(Ids::FLOWING_LAVA, Ids::STILL_LAVA), "Lava"));
		$this->register(new Lever(new BID(Ids::LEVER), "Lever"));
		$this->register(new Magma(new BID(Ids::MAGMA), "Magma Block"));
		$this->register(new Melon(new BID(Ids::MELON_BLOCK), "Melon Block"));
		$this->register(new MelonStem(new BID(Ids::MELON_STEM, 0, ItemIds::MELON_SEEDS), "Melon Stem"));
		$this->register(new MonsterSpawner(new BID(Ids::MOB_SPAWNER, 0, null, TileMonsterSpawner::class), "Monster Spawner"));
		$this->register(new Mycelium(new BID(Ids::MYCELIUM), "Mycelium"));

		$netherBrickBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::NETHER_BRICK_BLOCK), "Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Opaque(new BID(Ids::RED_NETHER_BRICK), "Red Nether Bricks", $netherBrickBreakInfo));
		$this->register(new Fence(new BID(Ids::NETHER_BRICK_FENCE), "Nether Brick Fence", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::NETHER_BRICK_STAIRS), "Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new Stair(new BID(Ids::RED_NETHER_BRICK_STAIRS), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		$this->register(new NetherPortal(new BID(Ids::PORTAL), "Nether Portal"));
		$this->register(new NetherQuartzOre(new BID(Ids::NETHER_QUARTZ_ORE), "Nether Quartz Ore"));
		$this->register(new NetherReactor(new BID(Ids::NETHERREACTOR), "Nether Reactor Core"));
		$this->register(new Opaque(new BID(Ids::NETHER_WART_BLOCK), "Nether Wart Block", new BlockBreakInfo(1.0)));
		$this->register(new NetherWartPlant(new BID(Ids::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART), "Nether Wart"));
		$this->register(new Netherrack(new BID(Ids::NETHERRACK), "Netherrack"));
		$this->register(new Note(new BID(Ids::NOTEBLOCK, 0, null, TileNote::class), "Note Block"));
		$this->register(new Opaque(new BID(Ids::OBSIDIAN), "Obsidian", new BlockBreakInfo(35.0 /* 50 in PC */, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000.0)));
		$this->register(new PackedIce(new BID(Ids::PACKED_ICE), "Packed Ice"));
		$this->register(new Podzol(new BID(Ids::PODZOL), "Podzol"));
		$this->register(new Potato(new BID(Ids::POTATOES), "Potato Block"));
		$this->register(new PoweredRail(new BID(Ids::GOLDEN_RAIL, Meta::RAIL_STRAIGHT_NORTH_SOUTH), "Powered Rail"));

		$prismarineBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PRISMARINE, Meta::PRISMARINE_BRICKS), "Prismarine Bricks", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_BRICKS_STAIRS), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::PRISMARINE, Meta::PRISMARINE_DARK), "Dark Prismarine", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::DARK_PRISMARINE_STAIRS), "Dark Prismarine Stairs", $prismarineBreakInfo));
		$this->register(new Opaque(new BID(Ids::PRISMARINE, Meta::PRISMARINE_NORMAL), "Prismarine", $prismarineBreakInfo));
		$this->register(new Stair(new BID(Ids::PRISMARINE_STAIRS), "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new BlockBreakInfo(1.0, BlockToolType::AXE);
		$this->register($pumpkin = new Opaque(new BID(Ids::PUMPKIN), "Pumpkin", $pumpkinBreakInfo));
		for($i = 1; $i <= 3; ++$i){
			$this->remap(Ids::PUMPKIN, $i, $pumpkin);
		}
		$this->register(new CarvedPumpkin(new BID(Ids::CARVED_PUMPKIN), "Carved Pumpkin", $pumpkinBreakInfo));
		$this->register(new LitPumpkin(new BID(Ids::JACK_O_LANTERN), "Jack o'Lantern", $pumpkinBreakInfo));

		$this->register(new PumpkinStem(new BID(Ids::PUMPKIN_STEM, 0, ItemIds::PUMPKIN_SEEDS), "Pumpkin Stem"));

		$purpurBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Opaque(new BID(Ids::PURPUR_BLOCK, Meta::PURPUR_NORMAL), "Purpur Block", $purpurBreakInfo));
		$this->register(new class(new BID(Ids::PURPUR_BLOCK, Meta::PURPUR_PILLAR), "Purpur Pillar", $purpurBreakInfo) extends Opaque{
			use PillarRotationTrait;
		});
		$this->register(new Stair(new BID(Ids::PURPUR_STAIRS), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new BlockBreakInfo(0.8, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Opaque(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_NORMAL), "Quartz Block", $quartzBreakInfo));
		$this->register(new Stair(new BID(Ids::QUARTZ_STAIRS), "Quartz Stairs", $quartzBreakInfo));
		$this->register(new class(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_CHISELED), "Chiseled Quartz Block", $quartzBreakInfo) extends Opaque{
			use PillarRotationTrait;
		});
		$this->register(new class(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_PILLAR), "Quartz Pillar", $quartzBreakInfo) extends Opaque{
			use PillarRotationTrait;
		});
		$this->register(new Opaque(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_SMOOTH), "Smooth Quartz Block", $quartzBreakInfo)); //TODO: this has axis rotation in 1.9, unsure if a bug (https://bugs.mojang.com/browse/MCPE-39074)
		$this->register(new Stair(new BID(Ids::SMOOTH_QUARTZ_STAIRS), "Smooth Quartz Stairs", $quartzBreakInfo));

		$this->register(new Rail(new BID(Ids::RAIL), "Rail"));
		$this->register(new RedMushroom(new BID(Ids::RED_MUSHROOM), "Red Mushroom"));
		$this->register(new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK), "Red Mushroom Block"));
		$this->register(new Redstone(new BID(Ids::REDSTONE_BLOCK), "Redstone Block"));
		$this->register(new RedstoneComparator(new BIDFlattened(Ids::UNPOWERED_COMPARATOR, Ids::POWERED_COMPARATOR, 0, ItemIds::COMPARATOR, TileComparator::class), "Redstone Comparator"));
		$this->register(new RedstoneLamp(new BIDFlattened(Ids::REDSTONE_LAMP, Ids::LIT_REDSTONE_LAMP), "Redstone Lamp"));
		$this->register(new RedstoneOre(new BIDFlattened(Ids::REDSTONE_ORE, Ids::LIT_REDSTONE_ORE), "Redstone Ore"));
		$this->register(new RedstoneRepeater(new BIDFlattened(Ids::UNPOWERED_REPEATER, Ids::POWERED_REPEATER, 0, ItemIds::REPEATER), "Redstone Repeater"));
		$this->register(new RedstoneTorch(new BIDFlattened(Ids::REDSTONE_TORCH, Ids::UNLIT_REDSTONE_TORCH), "Redstone Torch"));
		$this->register(new RedstoneWire(new BID(Ids::REDSTONE_WIRE, 0, ItemIds::REDSTONE), "Redstone"));
		$this->register(new Reserved6(new BID(Ids::RESERVED6), "reserved6"));
		$this->register(new Sand(new BID(Ids::SAND), "Sand"));
		$this->register(new Sand(new BID(Ids::SAND, 1), "Red Sand"));
		$this->register(new SeaLantern(new BID(Ids::SEALANTERN), "Sea Lantern"));
		$this->register(new SeaPickle(new BID(Ids::SEA_PICKLE), "Sea Pickle"));
		$this->register(new Skull(new BID(Ids::MOB_HEAD_BLOCK, 0, null, TileSkull::class), "Mob Head"));

		$this->register(new Snow(new BID(Ids::SNOW), "Snow Block"));
		$this->register(new SnowLayer(new BID(Ids::SNOW_LAYER), "Snow Layer"));
		$this->register(new SoulSand(new BID(Ids::SOUL_SAND), "Soul Sand"));
		$this->register(new Sponge(new BID(Ids::SPONGE), "Sponge"));

		$stoneBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new class(new BID(Ids::STONE, Meta::STONE_NORMAL), "Stone", $stoneBreakInfo) extends Opaque{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [VanillaBlocks::COBBLESTONE()->asItem()];
			}

			public function isAffectedBySilkTouch() : bool{
				return true;
			}
		});
		$this->register(new Stair(new BID(Ids::NORMAL_STONE_STAIRS), "Stone Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::SMOOTH_STONE), "Smooth Stone", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_ANDESITE), "Andesite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::ANDESITE_STAIRS), "Andesite Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_DIORITE), "Diorite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::DIORITE_STAIRS), "Diorite Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_GRANITE), "Granite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::GRANITE_STAIRS), "Granite Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_POLISHED_ANDESITE), "Polished Andesite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_ANDESITE_STAIRS), "Polished Andesite Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_POLISHED_DIORITE), "Polished Diorite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_DIORITE_STAIRS), "Polished Diorite Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONE, Meta::STONE_POLISHED_GRANITE), "Polished Granite", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::POLISHED_GRANITE_STAIRS), "Polished Granite Stairs", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::STONE_BRICK_STAIRS), "Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_CHISELED), "Chiseled Stone Bricks", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_CRACKED), "Cracked Stone Bricks", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_MOSSY), "Mossy Stone Bricks", $stoneBreakInfo));
		$this->register(new Stair(new BID(Ids::MOSSY_STONE_BRICK_STAIRS), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_NORMAL), "Stone Bricks", $stoneBreakInfo));
		$this->register(new StoneButton(new BID(Ids::STONE_BUTTON), "Stone Button"));
		$this->register(new StonePressurePlate(new BID(Ids::STONE_PRESSURE_PLATE), "Stone Pressure Plate"));

		//TODO: in the future this won't be the same for all the types
		$stoneSlabBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_BRICK), "Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_COBBLESTONE), "Cobblestone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_FAKE_WOODEN), "Fake Wooden", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_NETHER_BRICK), "Nether Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_QUARTZ), "Quartz", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_SANDSTONE), "Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_SMOOTH_STONE), "Smooth Stone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_STONE_BRICK), "Stone Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_DARK_PRISMARINE), "Dark Prismarine", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_MOSSY_COBBLESTONE), "Mossy Cobblestone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PRISMARINE), "Prismarine", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PRISMARINE_BRICKS), "Prismarine Bricks", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PURPUR), "Purpur", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_RED_NETHER_BRICK), "Red Nether Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_RED_SANDSTONE), "Red Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_SMOOTH_SANDSTONE), "Smooth Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_ANDESITE), "Andesite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_DIORITE), "Diorite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_END_STONE_BRICK), "End Stone Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_GRANITE), "Granite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_ANDESITE), "Polished Andesite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_DIORITE), "Polished Diorite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_GRANITE), "Polished Granite", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_SMOOTH_RED_SANDSTONE), "Smooth Red Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_CUT_RED_SANDSTONE), "Cut Red Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_CUT_SANDSTONE), "Cut Sandstone", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_MOSSY_STONE_BRICK), "Mossy Stone Brick", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_SMOOTH_QUARTZ), "Smooth Quartz", $stoneSlabBreakInfo));
		$this->register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_STONE), "Stone", $stoneSlabBreakInfo));
		$this->register(new Opaque(new BID(Ids::STONECUTTER), "Stonecutter", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		$this->register(new Sugarcane(new BID(Ids::REEDS_BLOCK, 0, ItemIds::REEDS), "Sugarcane"));
		$this->register(new TNT(new BID(Ids::TNT), "TNT"));

		$fern = new TallGrass(new BID(Ids::TALLGRASS, Meta::TALLGRASS_FERN), "Fern");
		$this->register($fern);
		$this->remap(Ids::TALLGRASS, 0, $fern);
		$this->remap(Ids::TALLGRASS, 3, $fern);
		$this->register(new TallGrass(new BID(Ids::TALLGRASS, Meta::TALLGRASS_NORMAL), "Tall Grass"));
		$this->register(new Torch(new BID(Ids::COLORED_TORCH_BP), "Blue Torch"));
		$this->register(new Torch(new BID(Ids::COLORED_TORCH_BP, 8), "Purple Torch"));
		$this->register(new Torch(new BID(Ids::COLORED_TORCH_RG), "Red Torch"));
		$this->register(new Torch(new BID(Ids::COLORED_TORCH_RG, 8), "Green Torch"));
		$this->register(new Torch(new BID(Ids::TORCH), "Torch"));
		$this->register(new TrappedChest(new BID(Ids::TRAPPED_CHEST, 0, null, TileChest::class), "Trapped Chest"));
		$this->register(new Tripwire(new BID(Ids::TRIPWIRE, 0, ItemIds::STRING), "Tripwire"));
		$this->register(new TripwireHook(new BID(Ids::TRIPWIRE_HOOK), "Tripwire Hook"));
		$this->register(new UnderwaterTorch(new BID(Ids::UNDERWATER_TORCH), "Underwater Torch"));
		$this->register(new Vine(new BID(Ids::VINE), "Vines"));
		$this->register(new Water(new BIDFlattened(Ids::FLOWING_WATER, Ids::STILL_WATER), "Water"));
		$this->register(new WaterLily(new BID(Ids::LILY_PAD), "Lily Pad"));
		$this->register(new WeightedPressurePlateHeavy(new BID(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Heavy"));
		$this->register(new WeightedPressurePlateLight(new BID(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Light"));
		$this->register(new Wheat(new BID(Ids::WHEAT_BLOCK), "Wheat Block"));

		//region ugly treetype -> blockID mapping tables
		$woodenStairIds = [
			TreeType::OAK()->id() => Ids::OAK_STAIRS,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_STAIRS,
			TreeType::BIRCH()->id() => Ids::BIRCH_STAIRS,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_STAIRS,
			TreeType::ACACIA()->id() => Ids::ACACIA_STAIRS,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_STAIRS
		];
		$fenceGateIds = [
			TreeType::OAK()->id() => Ids::OAK_FENCE_GATE,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_FENCE_GATE,
			TreeType::BIRCH()->id() => Ids::BIRCH_FENCE_GATE,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_FENCE_GATE,
			TreeType::ACACIA()->id() => Ids::ACACIA_FENCE_GATE,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE_GATE
		];

		/** @var BID[] $woodenDoorIds */
		$woodenDoorIds = [
			TreeType::OAK()->id() => new BID(Ids::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR)
		];
		$woodenPressurePlateIds = [
			TreeType::OAK()->id() => Ids::WOODEN_PRESSURE_PLATE,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_PRESSURE_PLATE,
			TreeType::BIRCH()->id() => Ids::BIRCH_PRESSURE_PLATE,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_PRESSURE_PLATE,
			TreeType::ACACIA()->id() => Ids::ACACIA_PRESSURE_PLATE,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_PRESSURE_PLATE
		];
		$woodenButtonIds = [
			TreeType::OAK()->id() => Ids::WOODEN_BUTTON,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_BUTTON,
			TreeType::BIRCH()->id() => Ids::BIRCH_BUTTON,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_BUTTON,
			TreeType::ACACIA()->id() => Ids::ACACIA_BUTTON,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_BUTTON
		];
		$woodenTrapdoorIds = [
			TreeType::OAK()->id() => Ids::WOODEN_TRAPDOOR,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_TRAPDOOR,
			TreeType::BIRCH()->id() => Ids::BIRCH_TRAPDOOR,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_TRAPDOOR,
			TreeType::ACACIA()->id() => Ids::ACACIA_TRAPDOOR,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_TRAPDOOR
		];

		/** @var BIDFlattened[] $woodenSignIds */
		$woodenSignIds = [
			TreeType::OAK()->id() => new BIDFlattened(Ids::SIGN_POST, Ids::WALL_SIGN, 0, ItemIds::SIGN, TileSign::class),
			TreeType::SPRUCE()->id() => new BIDFlattened(Ids::SPRUCE_STANDING_SIGN, Ids::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class),
			TreeType::BIRCH()->id() => new BIDFlattened(Ids::BIRCH_STANDING_SIGN, Ids::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class),
			TreeType::JUNGLE()->id() => new BIDFlattened(Ids::JUNGLE_STANDING_SIGN, Ids::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class),
			TreeType::ACACIA()->id() => new BIDFlattened(Ids::ACACIA_STANDING_SIGN, Ids::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class),
			TreeType::DARK_OAK()->id() => new BIDFlattened(Ids::DARKOAK_STANDING_SIGN, Ids::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class)
		];
		//endregion

		foreach(TreeType::getAll() as $treeType){
			$magicNumber = $treeType->getMagicNumber();
			$name = $treeType->getDisplayName();
			$this->register(new Planks(new BID(Ids::PLANKS, $magicNumber), $name . " Planks"));
			$this->register(new Sapling(new BID(Ids::SAPLING, $magicNumber), $name . " Sapling", $treeType));
			$this->register(new WoodenFence(new BID(Ids::FENCE, $magicNumber), $name . " Fence"));
			$this->register(new WoodenSlab(new BIDFlattened(Ids::WOODEN_SLAB, Ids::DOUBLE_WOODEN_SLAB, $treeType->getMagicNumber()), $treeType->getDisplayName()));

			//TODO: find a better way to deal with this split
			$this->register(new Leaves(new BID($magicNumber >= 4 ? Ids::LEAVES2 : Ids::LEAVES, $magicNumber & 0x03), $name . " Leaves", $treeType));
			$this->register(new Log(new BID($magicNumber >= 4 ? Ids::LOG2 : Ids::LOG, $magicNumber & 0x03), $name . " Log", $treeType));

			$wood = new Wood(new BID(Ids::WOOD, $magicNumber), $name . " Wood", $treeType);
			$this->register($wood);
			$this->remap($magicNumber >= 4 ? Ids::LOG2 : Ids::LOG, ($magicNumber & 0x03) | 0b1100, $wood);

			$this->register(new FenceGate(new BID($fenceGateIds[$treeType->id()]), $treeType->getDisplayName() . " Fence Gate"));
			$this->register(new WoodenStairs(new BID($woodenStairIds[$treeType->id()]), $treeType->getDisplayName() . " Stairs"));
			$this->register(new WoodenDoor($woodenDoorIds[$treeType->id()], $treeType->getDisplayName() . " Door"));

			$this->register(new WoodenButton(new BID($woodenButtonIds[$treeType->id()]), $treeType->getDisplayName() . " Button"));
			$this->register(new WoodenPressurePlate(new BID($woodenPressurePlateIds[$treeType->id()]), $treeType->getDisplayName() . " Pressure Plate"));
			$this->register(new WoodenTrapdoor(new BID($woodenTrapdoorIds[$treeType->id()]), $treeType->getDisplayName() . " Trapdoor"));

			$this->register(new Sign($woodenSignIds[$treeType->id()], $treeType->getDisplayName() . " Sign"));
		}

		static $sandstoneTypes = [
			Meta::SANDSTONE_NORMAL => "",
			Meta::SANDSTONE_CHISELED => "Chiseled ",
			Meta::SANDSTONE_CUT => "Cut ",
			Meta::SANDSTONE_SMOOTH => "Smooth "
		];
		$sandstoneBreakInfo = new BlockBreakInfo(0.8, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
		$this->register(new Stair(new BID(Ids::RED_SANDSTONE_STAIRS), "Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_RED_SANDSTONE_STAIRS), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SANDSTONE_STAIRS), "Sandstone Stairs", $sandstoneBreakInfo));
		$this->register(new Stair(new BID(Ids::SMOOTH_SANDSTONE_STAIRS), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		foreach($sandstoneTypes as $variant => $prefix){
			$this->register(new Opaque(new BID(Ids::SANDSTONE, $variant), $prefix . "Sandstone", $sandstoneBreakInfo));
			$this->register(new Opaque(new BID(Ids::RED_SANDSTONE, $variant), $prefix . "Red Sandstone", $sandstoneBreakInfo));
		}

		//region ugly glazed-terracotta colour -> ID mapping table
		/** @var int[] */
		$glazedTerracottaIds = [
			DyeColor::WHITE()->id() => Ids::WHITE_GLAZED_TERRACOTTA,
			DyeColor::ORANGE()->id() => Ids::ORANGE_GLAZED_TERRACOTTA,
			DyeColor::MAGENTA()->id() => Ids::MAGENTA_GLAZED_TERRACOTTA,
			DyeColor::LIGHT_BLUE()->id() => Ids::LIGHT_BLUE_GLAZED_TERRACOTTA,
			DyeColor::YELLOW()->id() => Ids::YELLOW_GLAZED_TERRACOTTA,
			DyeColor::LIME()->id() => Ids::LIME_GLAZED_TERRACOTTA,
			DyeColor::PINK()->id() => Ids::PINK_GLAZED_TERRACOTTA,
			DyeColor::GRAY()->id() => Ids::GRAY_GLAZED_TERRACOTTA,
			DyeColor::LIGHT_GRAY()->id() => Ids::SILVER_GLAZED_TERRACOTTA,
			DyeColor::CYAN()->id() => Ids::CYAN_GLAZED_TERRACOTTA,
			DyeColor::PURPLE()->id() => Ids::PURPLE_GLAZED_TERRACOTTA,
			DyeColor::BLUE()->id() => Ids::BLUE_GLAZED_TERRACOTTA,
			DyeColor::BROWN()->id() => Ids::BROWN_GLAZED_TERRACOTTA,
			DyeColor::GREEN()->id() => Ids::GREEN_GLAZED_TERRACOTTA,
			DyeColor::RED()->id() => Ids::RED_GLAZED_TERRACOTTA,
			DyeColor::BLACK()->id() => Ids::BLACK_GLAZED_TERRACOTTA
		];
		//endregion

		foreach(DyeColor::getAll() as $color){
			$this->register(new Carpet(new BID(Ids::CARPET, $color->getMagicNumber()), $color->getDisplayName() . " Carpet"));
			$this->register(new Concrete(new BID(Ids::CONCRETE, $color->getMagicNumber()), $color->getDisplayName() . " Concrete"));
			$this->register(new ConcretePowder(new BID(Ids::CONCRETE_POWDER, $color->getMagicNumber()), $color->getDisplayName() . " Concrete Powder"));
			$this->register(new Glass(new BID(Ids::STAINED_GLASS, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass"));
			$this->register(new GlassPane(new BID(Ids::STAINED_GLASS_PANE, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass Pane"));
			$this->register(new GlazedTerracotta(new BID($glazedTerracottaIds[$color->id()]), $color->getDisplayName() . " Glazed Terracotta"));
			$this->register(new HardenedClay(new BID(Ids::STAINED_CLAY, $color->getMagicNumber()), $color->getDisplayName() . " Stained Clay"));
			$this->register(new HardenedGlass(new BID(Ids::HARD_STAINED_GLASS, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass"));
			$this->register(new HardenedGlassPane(new BID(Ids::HARD_STAINED_GLASS_PANE, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass Pane"));
			$this->register(new Wool(new BID(Ids::WOOL, $color->getMagicNumber()), $color->getDisplayName() . " Wool"));
		}

		static $wallTypes = [
			Meta::WALL_ANDESITE => "Andesite",
			Meta::WALL_BRICK => "Brick",
			Meta::WALL_DIORITE => "Diorite",
			Meta::WALL_END_STONE_BRICK => "End Stone Brick",
			Meta::WALL_GRANITE => "Granite",
			Meta::WALL_MOSSY_STONE_BRICK => "Mossy Stone Brick",
			Meta::WALL_MOSSY_COBBLESTONE => "Mossy Cobblestone",
			Meta::WALL_NETHER_BRICK => "Nether Brick",
			Meta::WALL_COBBLESTONE => "Cobblestone",
			Meta::WALL_PRISMARINE => "Prismarine",
			Meta::WALL_RED_NETHER_BRICK => "Red Nether Brick",
			Meta::WALL_RED_SANDSTONE => "Red Sandstone",
			Meta::WALL_SANDSTONE => "Sandstone",
			Meta::WALL_STONE_BRICK => "Stone Brick"
		];
		foreach($wallTypes as $magicNumber => $prefix){
			$this->register(new Wall(new BID(Ids::COBBLESTONE_WALL, $magicNumber), $prefix . " Wall"));
		}

		$this->registerElements();

		//region --- auto-generated TODOs ---
		//TODO: minecraft:bamboo
		//TODO: minecraft:bamboo_sapling
		//TODO: minecraft:barrel
		//TODO: minecraft:beacon
		//TODO: minecraft:bell
		//TODO: minecraft:blast_furnace
		//TODO: minecraft:bubble_column
		//TODO: minecraft:campfire
		//TODO: minecraft:cartography_table
		//TODO: minecraft:cauldron
		//TODO: minecraft:chain_command_block
		//TODO: minecraft:chemical_heat
		//TODO: minecraft:chemistry_table
		//TODO: minecraft:chorus_flower
		//TODO: minecraft:chorus_plant
		//TODO: minecraft:command_block
		//TODO: minecraft:composter
		//TODO: minecraft:conduit
		//TODO: minecraft:coral
		//TODO: minecraft:coral_block
		//TODO: minecraft:coral_fan
		//TODO: minecraft:coral_fan_dead
		//TODO: minecraft:coral_fan_hang
		//TODO: minecraft:coral_fan_hang2
		//TODO: minecraft:coral_fan_hang3
		//TODO: minecraft:dispenser
		//TODO: minecraft:dropper
		//TODO: minecraft:end_gateway
		//TODO: minecraft:end_portal
		//TODO: minecraft:fletching_table
		//TODO: minecraft:grindstone
		//TODO: minecraft:jigsaw
		//TODO: minecraft:jukebox
		//TODO: minecraft:kelp
		//TODO: minecraft:lava_cauldron
		//TODO: minecraft:lectern
		//TODO: minecraft:lit_blast_furnace
		//TODO: minecraft:lit_smoker
		//TODO: minecraft:loom
		//TODO: minecraft:movingBlock
		//TODO: minecraft:observer
		//TODO: minecraft:piston
		//TODO: minecraft:pistonArmCollision
		//TODO: minecraft:repeating_command_block
		//TODO: minecraft:scaffolding
		//TODO: minecraft:seagrass
		//TODO: minecraft:shulker_box
		//TODO: minecraft:slime
		//TODO: minecraft:smithing_table
		//TODO: minecraft:smoker
		//TODO: minecraft:sticky_piston
		//TODO: minecraft:stonecutter_block
		//TODO: minecraft:stripped_acacia_log
		//TODO: minecraft:stripped_birch_log
		//TODO: minecraft:stripped_dark_oak_log
		//TODO: minecraft:stripped_jungle_log
		//TODO: minecraft:stripped_oak_log
		//TODO: minecraft:stripped_spruce_log
		//TODO: minecraft:structure_block
		//TODO: minecraft:sweet_berry_bush
		//TODO: minecraft:turtle_egg
		//TODO: minecraft:undyed_shulker_box
		//endregion
	}

	private function registerElements() : void{
		$this->register(new Opaque(new BID(Ids::ELEMENT_0), "???", BlockBreakInfo::instant()));

		$this->register(new Element(new BID(Ids::ELEMENT_1), "Hydrogen", BlockBreakInfo::instant(), "h", 1, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_2), "Helium", BlockBreakInfo::instant(), "he", 2, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_3), "Lithium", BlockBreakInfo::instant(), "li", 3, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_4), "Beryllium", BlockBreakInfo::instant(), "be", 4, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_5), "Boron", BlockBreakInfo::instant(), "b", 5, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_6), "Carbon", BlockBreakInfo::instant(), "c", 6, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_7), "Nitrogen", BlockBreakInfo::instant(), "n", 7, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_8), "Oxygen", BlockBreakInfo::instant(), "o", 8, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_9), "Fluorine", BlockBreakInfo::instant(), "f", 9, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_10), "Neon", BlockBreakInfo::instant(), "ne", 10, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_11), "Sodium", BlockBreakInfo::instant(), "na", 11, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_12), "Magnesium", BlockBreakInfo::instant(), "mg", 12, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_13), "Aluminum", BlockBreakInfo::instant(), "al", 13, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_14), "Silicon", BlockBreakInfo::instant(), "si", 14, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_15), "Phosphorus", BlockBreakInfo::instant(), "p", 15, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_16), "Sulfur", BlockBreakInfo::instant(), "s", 16, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_17), "Chlorine", BlockBreakInfo::instant(), "cl", 17, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_18), "Argon", BlockBreakInfo::instant(), "ar", 18, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_19), "Potassium", BlockBreakInfo::instant(), "k", 19, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_20), "Calcium", BlockBreakInfo::instant(), "ca", 20, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_21), "Scandium", BlockBreakInfo::instant(), "sc", 21, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_22), "Titanium", BlockBreakInfo::instant(), "ti", 22, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_23), "Vanadium", BlockBreakInfo::instant(), "v", 23, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_24), "Chromium", BlockBreakInfo::instant(), "cr", 24, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_25), "Manganese", BlockBreakInfo::instant(), "mn", 25, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_26), "Iron", BlockBreakInfo::instant(), "fe", 26, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_27), "Cobalt", BlockBreakInfo::instant(), "co", 27, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_28), "Nickel", BlockBreakInfo::instant(), "ni", 28, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_29), "Copper", BlockBreakInfo::instant(), "cu", 29, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_30), "Zinc", BlockBreakInfo::instant(), "zn", 30, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_31), "Gallium", BlockBreakInfo::instant(), "ga", 31, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_32), "Germanium", BlockBreakInfo::instant(), "ge", 32, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_33), "Arsenic", BlockBreakInfo::instant(), "as", 33, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_34), "Selenium", BlockBreakInfo::instant(), "se", 34, 5));
		$this->register(new Element(new BID(Ids::ELEMENT_35), "Bromine", BlockBreakInfo::instant(), "br", 35, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_36), "Krypton", BlockBreakInfo::instant(), "kr", 36, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_37), "Rubidium", BlockBreakInfo::instant(), "rb", 37, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_38), "Strontium", BlockBreakInfo::instant(), "sr", 38, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_39), "Yttrium", BlockBreakInfo::instant(), "y", 39, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_40), "Zirconium", BlockBreakInfo::instant(), "zr", 40, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_41), "Niobium", BlockBreakInfo::instant(), "nb", 41, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_42), "Molybdenum", BlockBreakInfo::instant(), "mo", 42, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_43), "Technetium", BlockBreakInfo::instant(), "tc", 43, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_44), "Ruthenium", BlockBreakInfo::instant(), "ru", 44, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_45), "Rhodium", BlockBreakInfo::instant(), "rh", 45, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_46), "Palladium", BlockBreakInfo::instant(), "pd", 46, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_47), "Silver", BlockBreakInfo::instant(), "ag", 47, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_48), "Cadmium", BlockBreakInfo::instant(), "cd", 48, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_49), "Indium", BlockBreakInfo::instant(), "in", 49, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_50), "Tin", BlockBreakInfo::instant(), "sn", 50, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_51), "Antimony", BlockBreakInfo::instant(), "sb", 51, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_52), "Tellurium", BlockBreakInfo::instant(), "te", 52, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_53), "Iodine", BlockBreakInfo::instant(), "i", 53, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_54), "Xenon", BlockBreakInfo::instant(), "xe", 54, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_55), "Cesium", BlockBreakInfo::instant(), "cs", 55, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_56), "Barium", BlockBreakInfo::instant(), "ba", 56, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_57), "Lanthanum", BlockBreakInfo::instant(), "la", 57, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_58), "Cerium", BlockBreakInfo::instant(), "ce", 58, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_59), "Praseodymium", BlockBreakInfo::instant(), "pr", 59, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_60), "Neodymium", BlockBreakInfo::instant(), "nd", 60, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_61), "Promethium", BlockBreakInfo::instant(), "pm", 61, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_62), "Samarium", BlockBreakInfo::instant(), "sm", 62, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_63), "Europium", BlockBreakInfo::instant(), "eu", 63, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_64), "Gadolinium", BlockBreakInfo::instant(), "gd", 64, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_65), "Terbium", BlockBreakInfo::instant(), "tb", 65, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_66), "Dysprosium", BlockBreakInfo::instant(), "dy", 66, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_67), "Holmium", BlockBreakInfo::instant(), "ho", 67, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_68), "Erbium", BlockBreakInfo::instant(), "er", 68, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_69), "Thulium", BlockBreakInfo::instant(), "tm", 69, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_70), "Ytterbium", BlockBreakInfo::instant(), "yb", 70, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_71), "Lutetium", BlockBreakInfo::instant(), "lu", 71, 8));
		$this->register(new Element(new BID(Ids::ELEMENT_72), "Hafnium", BlockBreakInfo::instant(), "hf", 72, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_73), "Tantalum", BlockBreakInfo::instant(), "ta", 73, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_74), "Tungsten", BlockBreakInfo::instant(), "w", 74, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_75), "Rhenium", BlockBreakInfo::instant(), "re", 75, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_76), "Osmium", BlockBreakInfo::instant(), "os", 76, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_77), "Iridium", BlockBreakInfo::instant(), "ir", 77, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_78), "Platinum", BlockBreakInfo::instant(), "pt", 78, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_79), "Gold", BlockBreakInfo::instant(), "au", 79, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_80), "Mercury", BlockBreakInfo::instant(), "hg", 80, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_81), "Thallium", BlockBreakInfo::instant(), "tl", 81, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_82), "Lead", BlockBreakInfo::instant(), "pb", 82, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_83), "Bismuth", BlockBreakInfo::instant(), "bi", 83, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_84), "Polonium", BlockBreakInfo::instant(), "po", 84, 4));
		$this->register(new Element(new BID(Ids::ELEMENT_85), "Astatine", BlockBreakInfo::instant(), "at", 85, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_86), "Radon", BlockBreakInfo::instant(), "rn", 86, 7));
		$this->register(new Element(new BID(Ids::ELEMENT_87), "Francium", BlockBreakInfo::instant(), "fr", 87, 0));
		$this->register(new Element(new BID(Ids::ELEMENT_88), "Radium", BlockBreakInfo::instant(), "ra", 88, 1));
		$this->register(new Element(new BID(Ids::ELEMENT_89), "Actinium", BlockBreakInfo::instant(), "ac", 89, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_90), "Thorium", BlockBreakInfo::instant(), "th", 90, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_91), "Protactinium", BlockBreakInfo::instant(), "pa", 91, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_92), "Uranium", BlockBreakInfo::instant(), "u", 92, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_93), "Neptunium", BlockBreakInfo::instant(), "np", 93, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_94), "Plutonium", BlockBreakInfo::instant(), "pu", 94, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_95), "Americium", BlockBreakInfo::instant(), "am", 95, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_96), "Curium", BlockBreakInfo::instant(), "cm", 96, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_97), "Berkelium", BlockBreakInfo::instant(), "bk", 97, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_98), "Californium", BlockBreakInfo::instant(), "cf", 98, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_99), "Einsteinium", BlockBreakInfo::instant(), "es", 99, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_100), "Fermium", BlockBreakInfo::instant(), "fm", 100, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_101), "Mendelevium", BlockBreakInfo::instant(), "md", 101, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_102), "Nobelium", BlockBreakInfo::instant(), "no", 102, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_103), "Lawrencium", BlockBreakInfo::instant(), "lr", 103, 9));
		$this->register(new Element(new BID(Ids::ELEMENT_104), "Rutherfordium", BlockBreakInfo::instant(), "rf", 104, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_105), "Dubnium", BlockBreakInfo::instant(), "db", 105, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_106), "Seaborgium", BlockBreakInfo::instant(), "sg", 106, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_107), "Bohrium", BlockBreakInfo::instant(), "bh", 107, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_108), "Hassium", BlockBreakInfo::instant(), "hs", 108, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_109), "Meitnerium", BlockBreakInfo::instant(), "mt", 109, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_110), "Darmstadtium", BlockBreakInfo::instant(), "ds", 110, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_111), "Roentgenium", BlockBreakInfo::instant(), "rg", 111, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_112), "Copernicium", BlockBreakInfo::instant(), "cn", 112, 2));
		$this->register(new Element(new BID(Ids::ELEMENT_113), "Nihonium", BlockBreakInfo::instant(), "nh", 113, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_114), "Flerovium", BlockBreakInfo::instant(), "fl", 114, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_115), "Moscovium", BlockBreakInfo::instant(), "mc", 115, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_116), "Livermorium", BlockBreakInfo::instant(), "lv", 116, 3));
		$this->register(new Element(new BID(Ids::ELEMENT_117), "Tennessine", BlockBreakInfo::instant(), "ts", 117, 6));
		$this->register(new Element(new BID(Ids::ELEMENT_118), "Oganesson", BlockBreakInfo::instant(), "og", 118, 7));
	}

	/**
	 * Registers a block type into the index. Plugins may use this method to register new block types or override
	 * existing ones.
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
		$variant = $block->getIdInfo()->getVariant();

		$stateMask = $block->getStateBitmask();
		if(($variant & $stateMask) !== 0){
			throw new \InvalidArgumentException("Block variant collides with state bitmask");
		}

		foreach($block->getIdInfo()->getAllBlockIds() as $id){
			if(!$override and $this->isRegistered($id, $variant)){
				throw new \InvalidArgumentException("Block registration $id:$variant conflicts with an existing block");
			}

			for($m = $variant; $m <= ($variant | $stateMask); ++$m){
				if(($m & ~$stateMask) !== $variant){
					continue;
				}

				if(!$override and $this->isRegistered($id, $m)){
					throw new \InvalidArgumentException("Block registration " . get_class($block) . " has states which conflict with other blocks");
				}

				$index = ($id << 4) | $m;

				$v = clone $block;
				try{
					$v->readStateFromData($id, $m & $stateMask);
					if($v->getMeta() !== $m){
						throw new InvalidBlockStateException("Corrupted meta"); //don't register anything that isn't the same when we read it back again
					}
				}catch(InvalidBlockStateException $e){ //invalid property combination
					continue;
				}

				$this->fillStaticArrays($index, $v);
			}

			if(!$this->isRegistered($id, $variant)){
				$this->fillStaticArrays(($id << 4) | $variant, $block); //register default state mapped to variant, for blocks which don't use 0 as valid state
			}
		}
	}

	public function remap(int $id, int $meta, Block $block) : void{
		if($this->isRegistered($id, $meta)){
			throw new \InvalidArgumentException("$id:$meta is already mapped");
		}
		$this->fillStaticArrays(($id << 4) | $meta, $block);
	}

	private function fillStaticArrays(int $index, Block $block) : void{
		$this->fullList[$index] = $block;
		$this->lightFilter[$index] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
		$this->diffusesSkyLight[$index] = $block->diffusesSkyLight();
		$this->blastResistance[$index] = $block->getBreakInfo()->getBlastResistance();
	}

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 */
	public function get(int $id, int $meta = 0) : Block{
		if($meta < 0 or $meta > 0xf){
			throw new \InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		/** @var Block|null $block */
		$block = null;
		try{
			$index = ($id << 4) | $meta;
			if($this->fullList[$index] !== null){
				$block = clone $this->fullList[$index];
			}
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException("Block ID $id is out of bounds");
		}

		if($block === null){
			$block = new UnknownBlock(new BID($id, $meta));
		}

		return $block;
	}

	public function fromFullBlock(int $fullState) : Block{
		return $this->get($fullState >> 4, $fullState & 0xf);
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public function isRegistered(int $id, int $meta = 0) : bool{
		$b = $this->fullList[($id << 4) | $meta];
		return $b !== null and !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 */
	public function getAllKnownStates() : array{
		return array_filter($this->fullList->toArray(), function(?Block $v) : bool{ return $v !== null; });
	}
}
