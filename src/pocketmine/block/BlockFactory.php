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
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\TieredTool;
use pocketmine\world\Position;
use function array_fill;
use function array_filter;
use function get_class;
use function min;

/**
 * Manages block registration and instance creation
 */
class BlockFactory{
	/** @var \SplFixedArray|Block[] */
	private static $fullList = null;

	/** @var \SplFixedArray|int[] */
	public static $lightFilter = null;
	/** @var \SplFixedArray|bool[] */
	public static $diffusesSkyLight = null;
	/** @var \SplFixedArray|float[] */
	public static $blastResistance = null;

	/**
	 * Initializes the block factory. By default this is called only once on server start, however you may wish to use
	 * this if you need to reset the block factory back to its original defaults for whatever reason.
	 */
	public static function init() : void{
		TileFactory::init();

		self::$fullList = new \SplFixedArray(8192);

		self::$lightFilter = \SplFixedArray::fromArray(array_fill(0, 8192, 1));
		self::$diffusesSkyLight = \SplFixedArray::fromArray(array_fill(0, 8192, false));
		self::$blastResistance = \SplFixedArray::fromArray(array_fill(0, 8192, 0));

		self::register(new ActivatorRail(new BID(Ids::ACTIVATOR_RAIL), "Activator Rail"));
		self::register(new Air(new BID(Ids::AIR), "Air"));
		self::register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_NORMAL), "Anvil"));
		self::register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_SLIGHTLY_DAMAGED), "Slightly Damaged Anvil"));
		self::register(new Anvil(new BID(Ids::ANVIL, Meta::ANVIL_VERY_DAMAGED), "Very Damaged Anvil"));
		self::register(new Banner(new BIDFlattened(Ids::STANDING_BANNER, Ids::WALL_BANNER, 0, ItemIds::BANNER, TileBanner::class), "Banner"));
		self::register(new Transparent(new BID(Ids::BARRIER), "Barrier", BlockBreakInfo::indestructible()));
		self::register(new Bed(new BID(Ids::BED_BLOCK, 0, ItemIds::BED, TileBed::class), "Bed Block"));
		self::register(new Bedrock(new BID(Ids::BEDROCK), "Bedrock"));
		self::register(new Beetroot(new BID(Ids::BEETROOT_BLOCK), "Beetroot Block"));
		self::register(new BlueIce(new BID(Ids::BLUE_ICE), "Blue Ice"));
		self::register(new BoneBlock(new BID(Ids::BONE_BLOCK), "Bone Block"));
		self::register(new Bookshelf(new BID(Ids::BOOKSHELF), "Bookshelf"));
		self::register(new BrewingStand(new BID(Ids::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND, TileBrewingStand::class), "Brewing Stand"));

		$bricksBreakInfo = new BlockBreakInfo(2.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Stair(new BID(Ids::BRICK_STAIRS), "Brick Stairs", $bricksBreakInfo));
		self::register(new Solid(new BID(Ids::BRICK_BLOCK), "Bricks", $bricksBreakInfo));

		self::register(new BrownMushroom(new BID(Ids::BROWN_MUSHROOM), "Brown Mushroom"));
		self::register(new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block"));
		self::register(new Cactus(new BID(Ids::CACTUS), "Cactus"));
		self::register(new Cake(new BID(Ids::CAKE_BLOCK, 0, ItemIds::CAKE), "Cake"));
		self::register(new Carrot(new BID(Ids::CARROTS), "Carrot Block"));
		self::register(new Chest(new BID(Ids::CHEST, 0, null, TileChest::class), "Chest"));
		self::register(new Clay(new BID(Ids::CLAY_BLOCK), "Clay Block"));
		self::register(new Coal(new BID(Ids::COAL_BLOCK), "Coal Block"));
		self::register(new CoalOre(new BID(Ids::COAL_ORE), "Coal Ore"));
		self::register(new CoarseDirt(new BID(Ids::DIRT, Meta::DIRT_COARSE), "Coarse Dirt"));

		$cobblestoneBreakInfo = new BlockBreakInfo(2.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Solid(new BID(Ids::COBBLESTONE), "Cobblestone", $cobblestoneBreakInfo));
		self::register(new Solid(new BID(Ids::MOSSY_COBBLESTONE), "Mossy Cobblestone", $cobblestoneBreakInfo));
		self::register(new Stair(new BID(Ids::COBBLESTONE_STAIRS), "Cobblestone Stairs", $cobblestoneBreakInfo));
		self::register(new Stair(new BID(Ids::MOSSY_COBBLESTONE_STAIRS), "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		self::register(new Cobweb(new BID(Ids::COBWEB), "Cobweb"));
		self::register(new CocoaBlock(new BID(Ids::COCOA), "Cocoa Block"));
		self::register(new CraftingTable(new BID(Ids::CRAFTING_TABLE), "Crafting Table"));
		self::register(new DaylightSensor(new BIDFlattened(Ids::DAYLIGHT_DETECTOR, Ids::DAYLIGHT_DETECTOR_INVERTED, 0, null, TileDaylightSensor::class), "Daylight Sensor"));
		self::register(new DeadBush(new BID(Ids::DEADBUSH), "Dead Bush"));
		self::register(new DetectorRail(new BID(Ids::DETECTOR_RAIL), "Detector Rail"));

		self::register(new Solid(new BID(Ids::DIAMOND_BLOCK), "Diamond Block", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_IRON, 30.0)));
		self::register(new DiamondOre(new BID(Ids::DIAMOND_ORE), "Diamond Ore"));
		self::register(new Dirt(new BID(Ids::DIRT, Meta::DIRT_NORMAL), "Dirt"));
		self::register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_SUNFLOWER), "Sunflower"));
		self::register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LILAC), "Lilac"));
		self::register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_ROSE_BUSH), "Rose Bush"));
		self::register(new DoublePlant(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_PEONY), "Peony"));
		self::register(new DoubleTallGrass(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_TALLGRASS), "Double Tallgrass"));
		self::register(new DoubleTallGrass(new BID(Ids::DOUBLE_PLANT, Meta::DOUBLE_PLANT_LARGE_FERN), "Large Fern"));
		self::register(new DragonEgg(new BID(Ids::DRAGON_EGG), "Dragon Egg"));
		self::register(new DriedKelp(new BID(Ids::DRIED_KELP_BLOCK), "Dried Kelp Block", new BlockBreakInfo(0.5, BlockToolType::TYPE_NONE, 0, 12.5)));
		self::register(new Solid(new BID(Ids::EMERALD_BLOCK), "Emerald Block", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_IRON, 30.0)));
		self::register(new EmeraldOre(new BID(Ids::EMERALD_ORE), "Emerald Ore"));
		self::register(new EnchantingTable(new BID(Ids::ENCHANTING_TABLE, 0, null, TileEnchantingTable::class), "Enchanting Table"));
		self::register(new EndPortalFrame(new BID(Ids::END_PORTAL_FRAME), "End Portal Frame"));
		self::register(new EndRod(new BID(Ids::END_ROD), "End Rod"));
		self::register(new Solid(new BID(Ids::END_STONE), "End Stone", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 45.0)));

		$endBrickBreakInfo = new BlockBreakInfo(0.8, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 4.0);
		self::register(new Solid(new BID(Ids::END_BRICKS), "End Stone Bricks", $endBrickBreakInfo));
		self::register(new Stair(new BID(Ids::END_BRICK_STAIRS), "End Stone Brick Stairs", $endBrickBreakInfo));

		self::register(new EnderChest(new BID(Ids::ENDER_CHEST, 0, null, TileEnderChest::class), "Ender Chest"));
		self::register(new Farmland(new BID(Ids::FARMLAND), "Farmland"));
		self::register(new Fire(new BID(Ids::FIRE), "Fire Block"));
		self::register(new Flower(new BID(Ids::DANDELION), "Dandelion"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_ALLIUM), "Allium"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_AZURE_BLUET), "Azure Bluet"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_BLUE_ORCHID), "Blue Orchid"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_CORNFLOWER), "Cornflower"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_LILY_OF_THE_VALLEY), "Lily of the Valley"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_ORANGE_TULIP), "Orange Tulip"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_OXEYE_DAISY), "Oxeye Daisy"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_PINK_TULIP), "Pink Tulip"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_POPPY), "Poppy"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_RED_TULIP), "Red Tulip"));
		self::register(new Flower(new BID(Ids::RED_FLOWER, Meta::FLOWER_WHITE_TULIP), "White Tulip"));
		self::register(new FlowerPot(new BID(Ids::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT, TileFlowerPot::class), "Flower Pot"));
		self::register(new FrostedIce(new BID(Ids::FROSTED_ICE), "Frosted Ice"));
		self::register(new Furnace(new BIDFlattened(Ids::FURNACE, Ids::LIT_FURNACE, 0, null, TileFurnace::class), "Furnace"));
		self::register(new Glass(new BID(Ids::GLASS), "Glass"));
		self::register(new GlassPane(new BID(Ids::GLASS_PANE), "Glass Pane"));
		self::register(new GlowingObsidian(new BID(Ids::GLOWINGOBSIDIAN), "Glowing Obsidian"));
		self::register(new Glowstone(new BID(Ids::GLOWSTONE), "Glowstone"));
		self::register(new Solid(new BID(Ids::GOLD_BLOCK), "Gold Block", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_IRON, 30.0)));
		self::register(new Solid(new BID(Ids::GOLD_ORE), "Gold Ore", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_IRON)));
		self::register(new Grass(new BID(Ids::GRASS), "Grass"));
		self::register(new GrassPath(new BID(Ids::GRASS_PATH), "Grass Path"));
		self::register(new Gravel(new BID(Ids::GRAVEL), "Gravel"));
		self::register(new HardenedClay(new BID(Ids::HARDENED_CLAY), "Hardened Clay"));
		self::register(new HardenedGlass(new BID(Ids::HARD_GLASS), "Hardened Glass"));
		self::register(new HardenedGlassPane(new BID(Ids::HARD_GLASS_PANE), "Hardened Glass Pane"));
		self::register(new HayBale(new BID(Ids::HAY_BALE), "Hay Bale"));
		self::register(new Hopper(new BID(Ids::HOPPER_BLOCK, 0, ItemIds::HOPPER, TileHopper::class), "Hopper", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 15.0)));
		self::register(new Ice(new BID(Ids::ICE), "Ice"));
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE), "Infested Stone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE)];
			}
		});
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_COBBLESTONE), "Infested Cobblestone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::COBBLESTONE)];
			}
		});
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK), "Infested Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK)];
			}
		});
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_MOSSY), "Infested Mossy Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, Meta::STONE_BRICK_MOSSY)];
			}
		});
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CRACKED), "Infested Cracked Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, Meta::STONE_BRICK_CRACKED)];
			}
		});
		self::register(new class(new BID(Ids::MONSTER_EGG, Meta::INFESTED_STONE_BRICK_CHISELED), "Infested Chiseled Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, Meta::STONE_BRICK_CHISELED)];
			}
		});

		$updateBlockBreakInfo = new BlockBreakInfo(1.0);
		self::register(new Solid(new BID(Ids::INFO_UPDATE), "update!", $updateBlockBreakInfo));
		self::register(new Solid(new BID(Ids::INFO_UPDATE2), "ate!upd", $updateBlockBreakInfo));
		self::register(new Transparent(new BID(Ids::INVISIBLEBEDROCK), "Invisible Bedrock", BlockBreakInfo::indestructible()));
		self::register(new Solid(new BID(Ids::IRON_BLOCK), "Iron Block", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_STONE, 30.0)));
		self::register(new Thin(new BID(Ids::IRON_BARS), "Iron Bars", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0)));
		self::register(new Door(new BID(Ids::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 25.0)));
		self::register(new Solid(new BID(Ids::IRON_ORE), "Iron Ore", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_STONE)));
		self::register(new Trapdoor(new BID(Ids::IRON_TRAPDOOR), "Iron Trapdoor", new BlockBreakInfo(5.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 25.0)));
		self::register(new ItemFrame(new BID(Ids::FRAME_BLOCK, 0, ItemIds::FRAME, TileItemFrame::class), "Item Frame"));
		self::register(new Ladder(new BID(Ids::LADDER), "Ladder"));
		self::register(new Solid(new BID(Ids::LAPIS_BLOCK), "Lapis Lazuli Block", new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_STONE)));
		self::register(new LapisOre(new BID(Ids::LAPIS_ORE), "Lapis Lazuli Ore"));
		self::register(new Lava(new BIDFlattened(Ids::FLOWING_LAVA, Ids::STILL_LAVA), "Lava"));
		self::register(new Lever(new BID(Ids::LEVER), "Lever"));
		self::register(new LitPumpkin(new BID(Ids::JACK_O_LANTERN), "Jack o'Lantern"));
		self::register(new Magma(new BID(Ids::MAGMA), "Magma Block"));
		self::register(new Melon(new BID(Ids::MELON_BLOCK), "Melon Block"));
		self::register(new MelonStem(new BID(Ids::MELON_STEM, 0, ItemIds::MELON_SEEDS), "Melon Stem"));
		self::register(new MonsterSpawner(new BID(Ids::MOB_SPAWNER, 0, null, TileMonsterSpawner::class), "Monster Spawner"));
		self::register(new Mycelium(new BID(Ids::MYCELIUM), "Mycelium"));

		$netherBrickBreakInfo = new BlockBreakInfo(2.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Solid(new BID(Ids::NETHER_BRICK_BLOCK), "Nether Bricks", $netherBrickBreakInfo));
		self::register(new Solid(new BID(Ids::RED_NETHER_BRICK), "Red Nether Bricks", $netherBrickBreakInfo));
		self::register(new Fence(new BID(Ids::NETHER_BRICK_FENCE), "Nether Brick Fence", $netherBrickBreakInfo));
		self::register(new Stair(new BID(Ids::NETHER_BRICK_STAIRS), "Nether Brick Stairs", $netherBrickBreakInfo));
		self::register(new Stair(new BID(Ids::RED_NETHER_BRICK_STAIRS), "Red Nether Brick Stairs", $netherBrickBreakInfo));
		self::register(new NetherPortal(new BID(Ids::PORTAL), "Nether Portal"));
		self::register(new NetherQuartzOre(new BID(Ids::NETHER_QUARTZ_ORE), "Nether Quartz Ore"));
		self::register(new NetherReactor(new BID(Ids::NETHERREACTOR), "Nether Reactor Core"));
		self::register(new Solid(new BID(Ids::NETHER_WART_BLOCK), "Nether Wart Block", new BlockBreakInfo(1.0)));
		self::register(new NetherWartPlant(new BID(Ids::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART), "Nether Wart"));
		self::register(new Netherrack(new BID(Ids::NETHERRACK), "Netherrack"));
		self::register(new Note(new BID(Ids::NOTEBLOCK, 0, null, TileNote::class), "Note Block"));
		self::register(new Solid(new BID(Ids::OBSIDIAN), "Obsidian", new BlockBreakInfo(35.0 /* 50 in PC */, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_DIAMOND, 6000.0)));
		self::register(new PackedIce(new BID(Ids::PACKED_ICE), "Packed Ice"));
		self::register(new Podzol(new BID(Ids::PODZOL), "Podzol"));
		self::register(new Potato(new BID(Ids::POTATOES), "Potato Block"));
		self::register(new PoweredRail(new BID(Ids::GOLDEN_RAIL, Meta::RAIL_STRAIGHT_NORTH_SOUTH), "Powered Rail"));

		$prismarineBreakInfo = new BlockBreakInfo(1.5, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Solid(new BID(Ids::PRISMARINE, Meta::PRISMARINE_BRICKS), "Prismarine Bricks", $prismarineBreakInfo));
		self::register(new Stair(new BID(Ids::PRISMARINE_BRICKS_STAIRS), "Prismarine Bricks Stairs", $prismarineBreakInfo));
		self::register(new Solid(new BID(Ids::PRISMARINE, Meta::PRISMARINE_DARK), "Dark Prismarine", $prismarineBreakInfo));
		self::register(new Stair(new BID(Ids::DARK_PRISMARINE_STAIRS), "Dark Prismarine Stairs", $prismarineBreakInfo));
		self::register(new Solid(new BID(Ids::PRISMARINE, Meta::PRISMARINE_NORMAL), "Prismarine", $prismarineBreakInfo));
		self::register(new Stair(new BID(Ids::PRISMARINE_STAIRS), "Prismarine Stairs", $prismarineBreakInfo));

		self::register(new Pumpkin(new BID(Ids::PUMPKIN), "Pumpkin"));
		self::register(new PumpkinStem(new BID(Ids::PUMPKIN_STEM, 0, ItemIds::PUMPKIN_SEEDS), "Pumpkin Stem"));

		$purpurBreakInfo = new BlockBreakInfo(1.5, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Solid(new BID(Ids::PURPUR_BLOCK, Meta::PURPUR_NORMAL), "Purpur Block", $purpurBreakInfo));
		self::register(new class(new BID(Ids::PURPUR_BLOCK, Meta::PURPUR_PILLAR), "Purpur Pillar", $purpurBreakInfo) extends Solid{
			use PillarRotationTrait;
		});
		self::register(new Stair(new BID(Ids::PURPUR_STAIRS), "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new BlockBreakInfo(0.8, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN);
		self::register(new Solid(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_NORMAL), "Quartz Block", $quartzBreakInfo));
		self::register(new Stair(new BID(Ids::QUARTZ_STAIRS), "Quartz Stairs", $quartzBreakInfo));
		self::register(new class(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_CHISELED), "Chiseled Quartz Block", $quartzBreakInfo) extends Solid{
			use PillarRotationTrait;
		});
		self::register(new class(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_PILLAR), "Quartz Pillar", $quartzBreakInfo) extends Solid{
			use PillarRotationTrait;
		});
		self::register(new Solid(new BID(Ids::QUARTZ_BLOCK, Meta::QUARTZ_SMOOTH), "Smooth Quartz Block", $quartzBreakInfo)); //TODO: this has axis rotation in 1.9, unsure if a bug (https://bugs.mojang.com/browse/MCPE-39074)
		self::register(new Stair(new BID(Ids::SMOOTH_QUARTZ_STAIRS), "Smooth Quartz Stairs", $quartzBreakInfo));

		self::register(new Rail(new BID(Ids::RAIL), "Rail"));
		self::register(new RedMushroom(new BID(Ids::RED_MUSHROOM), "Red Mushroom"));
		self::register(new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK), "Red Mushroom Block"));
		self::register(new Redstone(new BID(Ids::REDSTONE_BLOCK), "Redstone Block"));
		self::register(new RedstoneComparator(new BIDFlattened(Ids::UNPOWERED_COMPARATOR, Ids::POWERED_COMPARATOR, 0, ItemIds::COMPARATOR, TileComparator::class), "Redstone Comparator"));
		self::register(new RedstoneLamp(new BIDFlattened(Ids::REDSTONE_LAMP, Ids::LIT_REDSTONE_LAMP), "Redstone Lamp"));
		self::register(new RedstoneOre(new BIDFlattened(Ids::REDSTONE_ORE, Ids::LIT_REDSTONE_ORE), "Redstone Ore"));
		self::register(new RedstoneRepeater(new BIDFlattened(Ids::UNPOWERED_REPEATER, Ids::POWERED_REPEATER, 0, ItemIds::REPEATER), "Redstone Repeater"));
		self::register(new RedstoneTorch(new BIDFlattened(Ids::REDSTONE_TORCH, Ids::UNLIT_REDSTONE_TORCH), "Redstone Torch"));
		self::register(new RedstoneWire(new BID(Ids::REDSTONE_WIRE, 0, ItemIds::REDSTONE), "Redstone"));
		self::register(new Reserved6(new BID(Ids::RESERVED6), "reserved6"));
		self::register(new Sand(new BID(Ids::SAND), "Sand"));
		self::register(new Sand(new BID(Ids::SAND, 1), "Red Sand"));
		self::register(new SeaLantern(new BID(Ids::SEALANTERN), "Sea Lantern"));
		self::register(new SeaPickle(new BID(Ids::SEA_PICKLE), "Sea Pickle"));
		self::register(new Skull(new BID(Ids::MOB_HEAD_BLOCK, 0, null, TileSkull::class), "Mob Head"));



		self::register(new Snow(new BID(Ids::SNOW), "Snow Block"));
		self::register(new SnowLayer(new BID(Ids::SNOW_LAYER), "Snow Layer"));
		self::register(new SoulSand(new BID(Ids::SOUL_SAND), "Soul Sand"));
		self::register(new Sponge(new BID(Ids::SPONGE), "Sponge"));

		$stoneBreakInfo = new BlockBreakInfo(1.5, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new class(new BID(Ids::STONE, Meta::STONE_NORMAL), "Stone", $stoneBreakInfo) extends Solid{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [ItemFactory::get(Item::COBBLESTONE)];
			}
		});
		self::register(new Stair(new BID(Ids::NORMAL_STONE_STAIRS), "Stone Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::SMOOTH_STONE), "Smooth Stone", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_ANDESITE), "Andesite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::ANDESITE_STAIRS), "Andesite Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_DIORITE), "Diorite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::DIORITE_STAIRS), "Diorite Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_GRANITE), "Granite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::GRANITE_STAIRS), "Granite Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_POLISHED_ANDESITE), "Polished Andesite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::POLISHED_ANDESITE_STAIRS), "Polished Andesite Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_POLISHED_DIORITE), "Polished Diorite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::POLISHED_DIORITE_STAIRS), "Polished Diorite Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONE, Meta::STONE_POLISHED_GRANITE), "Polished Granite", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::POLISHED_GRANITE_STAIRS), "Polished Granite Stairs", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::STONE_BRICK_STAIRS), "Stone Brick Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_CHISELED), "Chiseled Stone Bricks", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_CRACKED), "Cracked Stone Bricks", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_MOSSY), "Mossy Stone Bricks", $stoneBreakInfo));
		self::register(new Stair(new BID(Ids::MOSSY_STONE_BRICK_STAIRS), "Mossy Stone Brick Stairs", $stoneBreakInfo));
		self::register(new Solid(new BID(Ids::STONEBRICK, Meta::STONE_BRICK_NORMAL), "Stone Bricks", $stoneBreakInfo));
		self::register(new StoneButton(new BID(Ids::STONE_BUTTON), "Stone Button"));
		self::register(new StonePressurePlate(new BID(Ids::STONE_PRESSURE_PLATE), "Stone Pressure Plate"));

		//TODO: in the future this won't be the same for all the types
		$stoneSlabBreakInfo = new BlockBreakInfo(2.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN, 30.0);
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_BRICK), "Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_COBBLESTONE), "Cobblestone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_FAKE_WOODEN), "Fake Wooden", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_NETHER_BRICK), "Nether Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_QUARTZ), "Quartz", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_SANDSTONE), "Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_SMOOTH_STONE), "Smooth Stone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB, Meta::STONE_SLAB_STONE_BRICK), "Stone Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_DARK_PRISMARINE), "Dark Prismarine", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_MOSSY_COBBLESTONE), "Mossy Cobblestone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PRISMARINE), "Prismarine", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PRISMARINE_BRICKS), "Prismarine Bricks", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_PURPUR), "Purpur", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_RED_NETHER_BRICK), "Red Nether Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_RED_SANDSTONE), "Red Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2, Meta::STONE_SLAB2_SMOOTH_SANDSTONE), "Smooth Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_ANDESITE), "Andesite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_DIORITE), "Diorite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_END_STONE_BRICK), "End Stone Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_GRANITE), "Granite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_ANDESITE), "Polished Andesite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_DIORITE), "Polished Diorite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_POLISHED_GRANITE), "Polished Granite", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3, Meta::STONE_SLAB3_SMOOTH_RED_SANDSTONE), "Smooth Red Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_CUT_RED_SANDSTONE), "Cut Red Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_CUT_SANDSTONE), "Cut Sandstone", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_MOSSY_STONE_BRICK), "Mossy Stone Brick", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_SMOOTH_QUARTZ), "Smooth Quartz", $stoneSlabBreakInfo));
		self::register(new Slab(new BIDFlattened(Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4, Meta::STONE_SLAB4_STONE), "Stone", $stoneSlabBreakInfo));
		self::register(new Solid(new BID(Ids::STONECUTTER), "Stonecutter", new BlockBreakInfo(3.5, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN)));
		self::register(new Sugarcane(new BID(Ids::REEDS_BLOCK, 0, ItemIds::REEDS), "Sugarcane"));
		self::register(new TNT(new BID(Ids::TNT), "TNT"));
		self::register(new TallGrass(new BID(Ids::TALLGRASS), "Fern")); //TODO: remap this to normal fern
		self::register(new TallGrass(new BID(Ids::TALLGRASS, Meta::TALLGRASS_NORMAL), "Tall Grass"));
		self::register(new TallGrass(new BID(Ids::TALLGRASS, Meta::TALLGRASS_FERN), "Fern"));
		self::register(new TallGrass(new BID(Ids::TALLGRASS, 3), "Fern")); //TODO: remap this to normal fern
		self::register(new Torch(new BID(Ids::COLORED_TORCH_BP), "Blue Torch"));
		self::register(new Torch(new BID(Ids::COLORED_TORCH_BP, 8), "Purple Torch"));
		self::register(new Torch(new BID(Ids::COLORED_TORCH_RG), "Red Torch"));
		self::register(new Torch(new BID(Ids::COLORED_TORCH_RG, 8), "Green Torch"));
		self::register(new Torch(new BID(Ids::TORCH), "Torch"));
		self::register(new TrappedChest(new BID(Ids::TRAPPED_CHEST, 0, null, TileChest::class), "Trapped Chest"));
		self::register(new Tripwire(new BID(Ids::TRIPWIRE, 0, ItemIds::STRING), "Tripwire"));
		self::register(new TripwireHook(new BID(Ids::TRIPWIRE_HOOK), "Tripwire Hook"));
		self::register(new UnderwaterTorch(new BID(Ids::UNDERWATER_TORCH), "Underwater Torch"));
		self::register(new Vine(new BID(Ids::VINE), "Vines"));
		self::register(new Water(new BIDFlattened(Ids::FLOWING_WATER, Ids::STILL_WATER), "Water"));
		self::register(new WaterLily(new BID(Ids::LILY_PAD), "Lily Pad"));
		self::register(new WeightedPressurePlateHeavy(new BID(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Heavy"));
		self::register(new WeightedPressurePlateLight(new BID(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Light"));
		self::register(new Wheat(new BID(Ids::WHEAT_BLOCK), "Wheat Block"));


		//region ugly treetype -> blockID mapping tables
		/** @var int[]|\SplObjectStorage $woodenStairIds */
		$woodenStairIds = new \SplObjectStorage();
		$woodenStairIds[TreeType::OAK()] = Ids::OAK_STAIRS;
		$woodenStairIds[TreeType::SPRUCE()] = Ids::SPRUCE_STAIRS;
		$woodenStairIds[TreeType::BIRCH()] = Ids::BIRCH_STAIRS;
		$woodenStairIds[TreeType::JUNGLE()] = Ids::JUNGLE_STAIRS;
		$woodenStairIds[TreeType::ACACIA()] = Ids::ACACIA_STAIRS;
		$woodenStairIds[TreeType::DARK_OAK()] = Ids::DARK_OAK_STAIRS;

		/** @var int[]|\SplObjectStorage $fenceGateIds */
		$fenceGateIds = new \SplObjectStorage();
		$fenceGateIds[TreeType::OAK()] = Ids::OAK_FENCE_GATE;
		$fenceGateIds[TreeType::SPRUCE()] = Ids::SPRUCE_FENCE_GATE;
		$fenceGateIds[TreeType::BIRCH()] = Ids::BIRCH_FENCE_GATE;
		$fenceGateIds[TreeType::JUNGLE()] = Ids::JUNGLE_FENCE_GATE;
		$fenceGateIds[TreeType::ACACIA()] = Ids::ACACIA_FENCE_GATE;
		$fenceGateIds[TreeType::DARK_OAK()] = Ids::DARK_OAK_FENCE_GATE;

		/** @var BID[]|\SplObjectStorage $woodenDoorIds */
		$woodenDoorIds = new \SplObjectStorage();
		$woodenDoorIds[TreeType::OAK()] = new BID(Ids::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR);
		$woodenDoorIds[TreeType::SPRUCE()] = new BID(Ids::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR);
		$woodenDoorIds[TreeType::BIRCH()] = new BID(Ids::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR);
		$woodenDoorIds[TreeType::JUNGLE()] = new BID(Ids::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR);
		$woodenDoorIds[TreeType::ACACIA()] = new BID(Ids::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR);
		$woodenDoorIds[TreeType::DARK_OAK()] = new BID(Ids::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR);

		/** @var int[]|\SplObjectStorage $woodenPressurePlateIds */
		$woodenPressurePlateIds = new \SplObjectStorage();
		$woodenPressurePlateIds[TreeType::OAK()] = Ids::WOODEN_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::SPRUCE()] = Ids::SPRUCE_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::BIRCH()] = Ids::BIRCH_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::JUNGLE()] = Ids::JUNGLE_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::ACACIA()] = Ids::ACACIA_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::DARK_OAK()] = Ids::DARK_OAK_PRESSURE_PLATE;

		/** @var int[]|\SplObjectStorage $woodenButtonIds */
		$woodenButtonIds = new \SplObjectStorage();
		$woodenButtonIds[TreeType::OAK()] = Ids::WOODEN_BUTTON;
		$woodenButtonIds[TreeType::SPRUCE()] = Ids::SPRUCE_BUTTON;
		$woodenButtonIds[TreeType::BIRCH()] = Ids::BIRCH_BUTTON;
		$woodenButtonIds[TreeType::JUNGLE()] = Ids::JUNGLE_BUTTON;
		$woodenButtonIds[TreeType::ACACIA()] = Ids::ACACIA_BUTTON;
		$woodenButtonIds[TreeType::DARK_OAK()] = Ids::DARK_OAK_BUTTON;

		/** @var int[]|\SplObjectStorage $woodenTrapdoorIds */
		$woodenTrapdoorIds = new \SplObjectStorage();
		$woodenTrapdoorIds[TreeType::OAK()] = Ids::WOODEN_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::SPRUCE()] = Ids::SPRUCE_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::BIRCH()] = Ids::BIRCH_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::JUNGLE()] = Ids::JUNGLE_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::ACACIA()] = Ids::ACACIA_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::DARK_OAK()] = Ids::DARK_OAK_TRAPDOOR;

		/** @var BIDFlattened[]|\SplObjectStorage $woodenSignIds */
		$woodenSignIds = new \SplObjectStorage();
		$woodenSignIds[TreeType::OAK()] = new BIDFlattened(Ids::SIGN_POST, Ids::WALL_SIGN, 0, ItemIds::SIGN, TileSign::class);
		$woodenSignIds[TreeType::SPRUCE()] = new BIDFlattened(Ids::SPRUCE_STANDING_SIGN, Ids::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class);
		$woodenSignIds[TreeType::BIRCH()] = new BIDFlattened(Ids::BIRCH_STANDING_SIGN, Ids::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class);
		$woodenSignIds[TreeType::JUNGLE()] = new BIDFlattened(Ids::JUNGLE_STANDING_SIGN, Ids::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class);
		$woodenSignIds[TreeType::ACACIA()] = new BIDFlattened(Ids::ACACIA_STANDING_SIGN, Ids::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class);
		$woodenSignIds[TreeType::DARK_OAK()] = new BIDFlattened(Ids::DARKOAK_STANDING_SIGN, Ids::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class);
		//endregion

		foreach(TreeType::getAll() as $treeType){
			$magicNumber = $treeType->getMagicNumber();
			$name = $treeType->getDisplayName();
			self::register(new Planks(new BID(Ids::PLANKS, $magicNumber), $name . " Planks"));
			self::register(new Sapling(new BID(Ids::SAPLING, $magicNumber), $name . " Sapling", $treeType));
			self::register(new WoodenFence(new BID(Ids::FENCE, $magicNumber), $name . " Fence"));
			self::register(new WoodenSlab(new BIDFlattened(Ids::WOODEN_SLAB, Ids::DOUBLE_WOODEN_SLAB, $treeType->getMagicNumber()), $treeType->getDisplayName()));

			//TODO: find a better way to deal with this split
			self::register(new Leaves(new BID($magicNumber >= 4 ? Ids::LEAVES2 : Ids::LEAVES, $magicNumber & 0x03), $name . " Leaves", $treeType));
			self::register(new Log(new BID($magicNumber >= 4 ? Ids::LOG2 : Ids::LOG, $magicNumber & 0x03), $name . " Log", $treeType));

			$wood = new Wood(new BID(Ids::WOOD, $magicNumber), $name . " Wood", $treeType);
			self::register($wood);
			self::remap($magicNumber >= 4 ? Ids::LOG2 : Ids::LOG, ($magicNumber & 0x03) | 0b1100, $wood);

			self::register(new FenceGate(new BID($fenceGateIds[$treeType]), $treeType->getDisplayName() . " Fence Gate"));
			self::register(new WoodenStairs(new BID($woodenStairIds[$treeType]), $treeType->getDisplayName() . " Stairs"));
			self::register(new WoodenDoor($woodenDoorIds[$treeType], $treeType->getDisplayName() . " Door"));

			self::register(new WoodenButton(new BID($woodenButtonIds[$treeType]), $treeType->getDisplayName() . " Button"));
			self::register(new WoodenPressurePlate(new BID($woodenPressurePlateIds[$treeType]), $treeType->getDisplayName() . " Pressure Plate"));
			self::register(new WoodenTrapdoor(new BID($woodenTrapdoorIds[$treeType]), $treeType->getDisplayName() . " Trapdoor"));

			self::register(new Sign($woodenSignIds[$treeType], $treeType->getDisplayName() . " Sign"));
		}

		static $sandstoneTypes = [
			Meta::SANDSTONE_NORMAL => "",
			Meta::SANDSTONE_CHISELED => "Chiseled ",
			Meta::SANDSTONE_CUT => "Cut ",
			Meta::SANDSTONE_SMOOTH => "Smooth "
		];
		$sandstoneBreakInfo = new BlockBreakInfo(0.8, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN);
		self::register(new Stair(new BID(Ids::RED_SANDSTONE_STAIRS), "Red Sandstone Stairs", $sandstoneBreakInfo));
		self::register(new Stair(new BID(Ids::SMOOTH_RED_SANDSTONE_STAIRS), "Smooth Red Sandstone Stairs", $sandstoneBreakInfo));
		self::register(new Stair(new BID(Ids::SANDSTONE_STAIRS), "Sandstone Stairs", $sandstoneBreakInfo));
		self::register(new Stair(new BID(Ids::SMOOTH_SANDSTONE_STAIRS), "Smooth Sandstone Stairs", $sandstoneBreakInfo));
		foreach($sandstoneTypes as $variant => $prefix){
			self::register(new Solid(new BID(Ids::SANDSTONE, $variant), $prefix . "Sandstone", $sandstoneBreakInfo));
			self::register(new Solid(new BID(Ids::RED_SANDSTONE, $variant), $prefix . "Red Sandstone", $sandstoneBreakInfo));
		}

		//region ugly glazed-terracotta colour -> ID mapping table
		/** @var int[]|\SplObjectStorage $glazedTerracottaIds */
		$glazedTerracottaIds = new \SplObjectStorage();
		$glazedTerracottaIds[DyeColor::WHITE()] = Ids::WHITE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::ORANGE()] = Ids::ORANGE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::MAGENTA()] = Ids::MAGENTA_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_BLUE()] = Ids::LIGHT_BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::YELLOW()] = Ids::YELLOW_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIME()] = Ids::LIME_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PINK()] = Ids::PINK_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GRAY()] = Ids::GRAY_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_GRAY()] = Ids::SILVER_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::CYAN()] = Ids::CYAN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PURPLE()] = Ids::PURPLE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLUE()] = Ids::BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BROWN()] = Ids::BROWN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GREEN()] = Ids::GREEN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::RED()] = Ids::RED_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLACK()] = Ids::BLACK_GLAZED_TERRACOTTA;
		//endregion

		foreach(DyeColor::getAll() as $color){
			self::register(new Carpet(new BID(Ids::CARPET, $color->getMagicNumber()), $color->getDisplayName() . " Carpet"));
			self::register(new Concrete(new BID(Ids::CONCRETE, $color->getMagicNumber()), $color->getDisplayName() . " Concrete"));
			self::register(new ConcretePowder(new BID(Ids::CONCRETE_POWDER, $color->getMagicNumber()), $color->getDisplayName() . " Concrete Powder"));
			self::register(new Glass(new BID(Ids::STAINED_GLASS, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass"));
			self::register(new GlassPane(new BID(Ids::STAINED_GLASS_PANE, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new GlazedTerracotta(new BID($glazedTerracottaIds[$color]), $color->getDisplayName() . " Glazed Terracotta"));
			self::register(new HardenedClay(new BID(Ids::STAINED_CLAY, $color->getMagicNumber()), $color->getDisplayName() . " Stained Clay"));
			self::register(new HardenedGlass(new BID(Ids::HARD_STAINED_GLASS, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass"));
			self::register(new HardenedGlassPane(new BID(Ids::HARD_STAINED_GLASS_PANE, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new Wool(new BID(Ids::WOOL, $color->getMagicNumber()), $color->getDisplayName() . " Wool"));
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
			self::register(new Wall(new BID(Ids::COBBLESTONE_WALL, $magicNumber), $prefix . " Wall"));
		}

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
		//TODO: minecraft:carved_pumpkin
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
		//TODO: minecraft:dried_kelp_block
		//TODO: minecraft:dropper
		//TODO: minecraft:element_0
		//TODO: minecraft:element_1
		//TODO: minecraft:element_10
		//TODO: minecraft:element_100
		//TODO: minecraft:element_101
		//TODO: minecraft:element_102
		//TODO: minecraft:element_103
		//TODO: minecraft:element_104
		//TODO: minecraft:element_105
		//TODO: minecraft:element_106
		//TODO: minecraft:element_107
		//TODO: minecraft:element_108
		//TODO: minecraft:element_109
		//TODO: minecraft:element_11
		//TODO: minecraft:element_110
		//TODO: minecraft:element_111
		//TODO: minecraft:element_112
		//TODO: minecraft:element_113
		//TODO: minecraft:element_114
		//TODO: minecraft:element_115
		//TODO: minecraft:element_116
		//TODO: minecraft:element_117
		//TODO: minecraft:element_118
		//TODO: minecraft:element_12
		//TODO: minecraft:element_13
		//TODO: minecraft:element_14
		//TODO: minecraft:element_15
		//TODO: minecraft:element_16
		//TODO: minecraft:element_17
		//TODO: minecraft:element_18
		//TODO: minecraft:element_19
		//TODO: minecraft:element_2
		//TODO: minecraft:element_20
		//TODO: minecraft:element_21
		//TODO: minecraft:element_22
		//TODO: minecraft:element_23
		//TODO: minecraft:element_24
		//TODO: minecraft:element_25
		//TODO: minecraft:element_26
		//TODO: minecraft:element_27
		//TODO: minecraft:element_28
		//TODO: minecraft:element_29
		//TODO: minecraft:element_3
		//TODO: minecraft:element_30
		//TODO: minecraft:element_31
		//TODO: minecraft:element_32
		//TODO: minecraft:element_33
		//TODO: minecraft:element_34
		//TODO: minecraft:element_35
		//TODO: minecraft:element_36
		//TODO: minecraft:element_37
		//TODO: minecraft:element_38
		//TODO: minecraft:element_39
		//TODO: minecraft:element_4
		//TODO: minecraft:element_40
		//TODO: minecraft:element_41
		//TODO: minecraft:element_42
		//TODO: minecraft:element_43
		//TODO: minecraft:element_44
		//TODO: minecraft:element_45
		//TODO: minecraft:element_46
		//TODO: minecraft:element_47
		//TODO: minecraft:element_48
		//TODO: minecraft:element_49
		//TODO: minecraft:element_5
		//TODO: minecraft:element_50
		//TODO: minecraft:element_51
		//TODO: minecraft:element_52
		//TODO: minecraft:element_53
		//TODO: minecraft:element_54
		//TODO: minecraft:element_55
		//TODO: minecraft:element_56
		//TODO: minecraft:element_57
		//TODO: minecraft:element_58
		//TODO: minecraft:element_59
		//TODO: minecraft:element_6
		//TODO: minecraft:element_60
		//TODO: minecraft:element_61
		//TODO: minecraft:element_62
		//TODO: minecraft:element_63
		//TODO: minecraft:element_64
		//TODO: minecraft:element_65
		//TODO: minecraft:element_66
		//TODO: minecraft:element_67
		//TODO: minecraft:element_68
		//TODO: minecraft:element_69
		//TODO: minecraft:element_7
		//TODO: minecraft:element_70
		//TODO: minecraft:element_71
		//TODO: minecraft:element_72
		//TODO: minecraft:element_73
		//TODO: minecraft:element_74
		//TODO: minecraft:element_75
		//TODO: minecraft:element_76
		//TODO: minecraft:element_77
		//TODO: minecraft:element_78
		//TODO: minecraft:element_79
		//TODO: minecraft:element_8
		//TODO: minecraft:element_80
		//TODO: minecraft:element_81
		//TODO: minecraft:element_82
		//TODO: minecraft:element_83
		//TODO: minecraft:element_84
		//TODO: minecraft:element_85
		//TODO: minecraft:element_86
		//TODO: minecraft:element_87
		//TODO: minecraft:element_88
		//TODO: minecraft:element_89
		//TODO: minecraft:element_9
		//TODO: minecraft:element_90
		//TODO: minecraft:element_91
		//TODO: minecraft:element_92
		//TODO: minecraft:element_93
		//TODO: minecraft:element_94
		//TODO: minecraft:element_95
		//TODO: minecraft:element_96
		//TODO: minecraft:element_97
		//TODO: minecraft:element_98
		//TODO: minecraft:element_99
		//TODO: minecraft:end_gateway
		//TODO: minecraft:end_portal
		//TODO: minecraft:fletching_table
		//TODO: minecraft:grindstone
		//TODO: minecraft:hopper
		//TODO: minecraft:jigsaw
		//TODO: minecraft:jukebox
		//TODO: minecraft:kelp
		//TODO: minecraft:lantern
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

	public static function isInit() : bool{
		return self::$fullList !== null;
	}

	/**
	 * Registers a block type into the index. Plugins may use this method to register new block types or override
	 * existing ones.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param Block $block
	 * @param bool  $override Whether to override existing registrations
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public static function register(Block $block, bool $override = false) : void{
		$variant = $block->getIdInfo()->getVariant();

		$stateMask = $block->getStateBitmask();
		if(($variant & $stateMask) !== 0){
			throw new \InvalidArgumentException("Block variant collides with state bitmask");
		}

		foreach($block->getIdInfo()->getAllBlockIds() as $id){
			if(!$override and self::isRegistered($id, $variant)){
				throw new \InvalidArgumentException("Block registration $id:$variant conflicts with an existing block");
			}

			for($m = $variant; $m <= ($variant | $stateMask); ++$m){
				if(($m & ~$stateMask) !== $variant){
					continue;
				}

				if(!$override and self::isRegistered($id, $m)){
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

				self::fillStaticArrays($index, $v);
			}

			if(!self::isRegistered($id, $variant)){
				self::fillStaticArrays(($id << 4) | $variant, $block); //register default state mapped to variant, for blocks which don't use 0 as valid state
			}
		}
	}

	public static function remap(int $id, int $meta, Block $block) : void{
		if(self::isRegistered($id, $meta)){
			throw new \InvalidArgumentException("$id:$meta is already mapped");
		}
		self::fillStaticArrays(($id << 4) | $meta, $block);
	}

	private static function fillStaticArrays(int $index, Block $block) : void{
		self::$fullList[$index] = $block;
		self::$lightFilter[$index] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
		self::$diffusesSkyLight[$index] = $block->diffusesSkyLight();
		self::$blastResistance[$index] = $block->getBreakInfo()->getBlastResistance();
	}

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 *
	 * @param int      $id
	 * @param int      $meta
	 * @param Position $pos
	 *
	 * @return Block
	 */
	public static function get(int $id, int $meta = 0, ?Position $pos = null) : Block{
		if($meta < 0 or $meta > 0xf){
			throw new \InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		/** @var Block|null $block */
		$block = null;
		try{
			$index = ($id << 4) | $meta;
			if(self::$fullList[$index] !== null){
				$block = clone self::$fullList[$index];
			}
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException("Block ID $id is out of bounds");
		}

		if($block === null){
			$block = new UnknownBlock(new BID($id, $meta));
		}

		if($pos !== null){
			$block->position($pos->getWorld(), $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		}

		return $block;
	}

	public static function fromFullBlock(int $fullState, ?Position $pos = null) : Block{
		return self::get($fullState >> 4, $fullState & 0xf, $pos);
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 *
	 * @param int $id
	 * @param int $meta
	 *
	 * @return bool
	 */
	public static function isRegistered(int $id, int $meta = 0) : bool{
		$b = self::$fullList[($id << 4) | $meta];
		return $b !== null and !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 */
	public static function getAllKnownStates() : array{
		return array_filter(self::$fullList->toArray(), function(?Block $v) : bool{ return $v !== null; });
	}
}
