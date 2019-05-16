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
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\tile\Comparator;
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
		self::$fullList = new \SplFixedArray(8192);

		self::$lightFilter = \SplFixedArray::fromArray(array_fill(0, 8192, 1));
		self::$diffusesSkyLight = \SplFixedArray::fromArray(array_fill(0, 8192, false));
		self::$blastResistance = \SplFixedArray::fromArray(array_fill(0, 8192, 0));

		self::register(new ActivatorRail(new BID(BlockLegacyIds::ACTIVATOR_RAIL), "Activator Rail"));
		self::register(new Air(new BID(BlockLegacyIds::AIR), "Air"));
		self::register(new Anvil(new BID(BlockLegacyIds::ANVIL, BlockLegacyMetadata::ANVIL_NORMAL), "Anvil"));
		self::register(new Anvil(new BID(BlockLegacyIds::ANVIL, BlockLegacyMetadata::ANVIL_SLIGHTLY_DAMAGED), "Slightly Damaged Anvil"));
		self::register(new Anvil(new BID(BlockLegacyIds::ANVIL, BlockLegacyMetadata::ANVIL_VERY_DAMAGED), "Very Damaged Anvil"));
		self::register(new Banner(new BlockIdentifierFlattened(BlockLegacyIds::STANDING_BANNER, BlockLegacyIds::WALL_BANNER, 0, ItemIds::BANNER, \pocketmine\tile\Banner::class), "Banner"));
		self::register(new Barrier(new BID(BlockLegacyIds::BARRIER), "Barrier"));
		self::register(new Bed(new BID(BlockLegacyIds::BED_BLOCK, 0, ItemIds::BED, \pocketmine\tile\Bed::class), "Bed Block"));
		self::register(new Bedrock(new BID(BlockLegacyIds::BEDROCK), "Bedrock"));
		self::register(new Beetroot(new BID(BlockLegacyIds::BEETROOT_BLOCK), "Beetroot Block"));
		self::register(new BlueIce(new BID(BlockLegacyIds::BLUE_ICE), "Blue Ice"));
		self::register(new BoneBlock(new BID(BlockLegacyIds::BONE_BLOCK), "Bone Block"));
		self::register(new Bookshelf(new BID(BlockLegacyIds::BOOKSHELF), "Bookshelf"));
		self::register(new BrewingStand(new BID(BlockLegacyIds::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND), "Brewing Stand"));
		self::register(new BrickStairs(new BID(BlockLegacyIds::BRICK_STAIRS), "Brick Stairs"));
		self::register(new Bricks(new BID(BlockLegacyIds::BRICK_BLOCK), "Bricks"));
		self::register(new BrownMushroom(new BID(BlockLegacyIds::BROWN_MUSHROOM), "Brown Mushroom"));
		self::register(new BrownMushroomBlock(new BID(BlockLegacyIds::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block"));
		self::register(new Cactus(new BID(BlockLegacyIds::CACTUS), "Cactus"));
		self::register(new Cake(new BID(BlockLegacyIds::CAKE_BLOCK, 0, ItemIds::CAKE), "Cake"));
		self::register(new Carrot(new BID(BlockLegacyIds::CARROTS), "Carrot Block"));
		self::register(new Chest(new BID(BlockLegacyIds::CHEST, 0, null, \pocketmine\tile\Chest::class), "Chest"));
		self::register(new Clay(new BID(BlockLegacyIds::CLAY_BLOCK), "Clay Block"));
		self::register(new Coal(new BID(BlockLegacyIds::COAL_BLOCK), "Coal Block"));
		self::register(new CoalOre(new BID(BlockLegacyIds::COAL_ORE), "Coal Ore"));
		self::register(new CoarseDirt(new BID(BlockLegacyIds::DIRT, BlockLegacyMetadata::DIRT_COARSE), "Coarse Dirt"));
		self::register(new Cobblestone(new BID(BlockLegacyIds::COBBLESTONE), "Cobblestone"));
		self::register(new Cobblestone(new BID(BlockLegacyIds::MOSSY_COBBLESTONE), "Moss Stone"));
		self::register(new CobblestoneStairs(new BID(BlockLegacyIds::COBBLESTONE_STAIRS), "Cobblestone Stairs"));
		self::register(new Cobweb(new BID(BlockLegacyIds::COBWEB), "Cobweb"));
		self::register(new CocoaBlock(new BID(BlockLegacyIds::COCOA), "Cocoa Block"));
		self::register(new CraftingTable(new BID(BlockLegacyIds::CRAFTING_TABLE), "Crafting Table"));
		self::register(new DaylightSensor(new BlockIdentifierFlattened(BlockLegacyIds::DAYLIGHT_DETECTOR, BlockLegacyIds::DAYLIGHT_DETECTOR_INVERTED), "Daylight Sensor"));
		self::register(new DeadBush(new BID(BlockLegacyIds::DEADBUSH), "Dead Bush"));
		self::register(new DetectorRail(new BID(BlockLegacyIds::DETECTOR_RAIL), "Detector Rail"));
		self::register(new Diamond(new BID(BlockLegacyIds::DIAMOND_BLOCK), "Diamond Block"));
		self::register(new DiamondOre(new BID(BlockLegacyIds::DIAMOND_ORE), "Diamond Ore"));
		self::register(new Dirt(new BID(BlockLegacyIds::DIRT, BlockLegacyMetadata::DIRT_NORMAL), "Dirt"));
		self::register(new DoublePlant(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_SUNFLOWER), "Sunflower"));
		self::register(new DoublePlant(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_LILAC), "Lilac"));
		self::register(new DoublePlant(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_ROSE_BUSH), "Rose Bush"));
		self::register(new DoublePlant(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_PEONY), "Peony"));
		self::register(new DoubleTallGrass(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_TALLGRASS), "Double Tallgrass"));
		self::register(new DoubleTallGrass(new BID(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_LARGE_FERN), "Large Fern"));
		self::register(new DragonEgg(new BID(BlockLegacyIds::DRAGON_EGG), "Dragon Egg"));
		self::register(new Emerald(new BID(BlockLegacyIds::EMERALD_BLOCK), "Emerald Block"));
		self::register(new EmeraldOre(new BID(BlockLegacyIds::EMERALD_ORE), "Emerald Ore"));
		self::register(new EnchantingTable(new BID(BlockLegacyIds::ENCHANTING_TABLE, 0, null, \pocketmine\tile\EnchantTable::class), "Enchanting Table"));
		self::register(new EndPortalFrame(new BID(BlockLegacyIds::END_PORTAL_FRAME), "End Portal Frame"));
		self::register(new EndRod(new BID(BlockLegacyIds::END_ROD), "End Rod"));
		self::register(new EndStone(new BID(BlockLegacyIds::END_STONE), "End Stone"));
		self::register(new EndStoneBricks(new BID(BlockLegacyIds::END_BRICKS), "End Stone Bricks"));
		self::register(new EnderChest(new BID(BlockLegacyIds::ENDER_CHEST, 0, null, \pocketmine\tile\EnderChest::class), "Ender Chest"));
		self::register(new Farmland(new BID(BlockLegacyIds::FARMLAND), "Farmland"));
		self::register(new Fire(new BID(BlockLegacyIds::FIRE), "Fire Block"));
		self::register(new Flower(new BID(BlockLegacyIds::DANDELION), "Dandelion"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_ALLIUM), "Allium"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_AZURE_BLUET), "Azure Bluet"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_BLUE_ORCHID), "Blue Orchid"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_CORNFLOWER), "Cornflower"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_LILY_OF_THE_VALLEY), "Lily of the Valley"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_ORANGE_TULIP), "Orange Tulip"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_OXEYE_DAISY), "Oxeye Daisy"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_PINK_TULIP), "Pink Tulip"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_POPPY), "Poppy"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_RED_TULIP), "Red Tulip"));
		self::register(new Flower(new BID(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_WHITE_TULIP), "White Tulip"));
		self::register(new FlowerPot(new BID(BlockLegacyIds::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT, \pocketmine\tile\FlowerPot::class), "Flower Pot"));
		self::register(new FrostedIce(new BID(BlockLegacyIds::FROSTED_ICE), "Frosted Ice"));
		self::register(new Furnace(new BlockIdentifierFlattened(BlockLegacyIds::FURNACE, BlockLegacyIds::LIT_FURNACE, 0, null, \pocketmine\tile\Furnace::class), "Furnace"));
		self::register(new Glass(new BID(BlockLegacyIds::GLASS), "Glass"));
		self::register(new GlassPane(new BID(BlockLegacyIds::GLASS_PANE), "Glass Pane"));
		self::register(new GlowingObsidian(new BID(BlockLegacyIds::GLOWINGOBSIDIAN), "Glowing Obsidian"));
		self::register(new Glowstone(new BID(BlockLegacyIds::GLOWSTONE), "Glowstone"));
		self::register(new Gold(new BID(BlockLegacyIds::GOLD_BLOCK), "Gold Block"));
		self::register(new GoldOre(new BID(BlockLegacyIds::GOLD_ORE), "Gold Ore"));
		self::register(new Grass(new BID(BlockLegacyIds::GRASS), "Grass"));
		self::register(new GrassPath(new BID(BlockLegacyIds::GRASS_PATH), "Grass Path"));
		self::register(new Gravel(new BID(BlockLegacyIds::GRAVEL), "Gravel"));
		self::register(new HardenedClay(new BID(BlockLegacyIds::HARDENED_CLAY), "Hardened Clay"));
		self::register(new HardenedGlass(new BID(BlockLegacyIds::HARD_GLASS), "Hardened Glass"));
		self::register(new HardenedGlassPane(new BID(BlockLegacyIds::HARD_GLASS_PANE), "Hardened Glass Pane"));
		self::register(new HayBale(new BID(BlockLegacyIds::HAY_BALE), "Hay Bale"));
		self::register(new Ice(new BID(BlockLegacyIds::ICE), "Ice"));
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_STONE), "Infested Stone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE)];
			}
		});
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_COBBLESTONE), "Infested Cobblestone") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::COBBLESTONE)];
			}
		});
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_STONE_BRICK), "Infested Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK)];
			}
		});
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_STONE_BRICK_MOSSY), "Infested Mossy Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, BlockLegacyMetadata::STONE_BRICK_MOSSY)];
			}
		});
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_STONE_BRICK_CRACKED), "Infested Cracked Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, BlockLegacyMetadata::STONE_BRICK_CRACKED)];
			}
		});
		self::register(new class(new BID(BlockLegacyIds::MONSTER_EGG, BlockLegacyMetadata::INFESTED_STONE_BRICK_CHISELED), "Infested Chiseled Stone Brick") extends InfestedStone{
			public function getSilkTouchDrops(Item $item) : array{
				return [ItemFactory::get(ItemIds::STONE_BRICK, BlockLegacyMetadata::STONE_BRICK_CHISELED)];
			}
		});
		self::register(new InfoUpdate(new BID(BlockLegacyIds::INFO_UPDATE), "update!"));
		self::register(new InfoUpdate(new BID(BlockLegacyIds::INFO_UPDATE2), "ate!upd"));
		self::register(new InvisibleBedrock(new BID(BlockLegacyIds::INVISIBLEBEDROCK), "Invisible Bedrock"));
		self::register(new Iron(new BID(BlockLegacyIds::IRON_BLOCK), "Iron Block"));
		self::register(new IronBars(new BID(BlockLegacyIds::IRON_BARS), "Iron Bars"));
		self::register(new IronDoor(new BID(BlockLegacyIds::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door"));
		self::register(new IronOre(new BID(BlockLegacyIds::IRON_ORE), "Iron Ore"));
		self::register(new IronTrapdoor(new BID(BlockLegacyIds::IRON_TRAPDOOR), "Iron Trapdoor"));
		self::register(new ItemFrame(new BID(BlockLegacyIds::FRAME_BLOCK, 0, ItemIds::FRAME, \pocketmine\tile\ItemFrame::class), "Item Frame"));
		self::register(new Ladder(new BID(BlockLegacyIds::LADDER), "Ladder"));
		self::register(new Lapis(new BID(BlockLegacyIds::LAPIS_BLOCK), "Lapis Lazuli Block"));
		self::register(new LapisOre(new BID(BlockLegacyIds::LAPIS_ORE), "Lapis Lazuli Ore"));
		self::register(new Lava(new BlockIdentifierFlattened(BlockLegacyIds::FLOWING_LAVA, BlockLegacyIds::STILL_LAVA), "Lava"));
		self::register(new Lever(new BID(BlockLegacyIds::LEVER), "Lever"));
		self::register(new LitPumpkin(new BID(BlockLegacyIds::JACK_O_LANTERN), "Jack o'Lantern"));
		self::register(new Magma(new BID(BlockLegacyIds::MAGMA), "Magma Block"));
		self::register(new Melon(new BID(BlockLegacyIds::MELON_BLOCK), "Melon Block"));
		self::register(new MelonStem(new BID(BlockLegacyIds::MELON_STEM, 0, ItemIds::MELON_SEEDS), "Melon Stem"));
		self::register(new MonsterSpawner(new BID(BlockLegacyIds::MOB_SPAWNER), "Monster Spawner"));
		self::register(new Mycelium(new BID(BlockLegacyIds::MYCELIUM), "Mycelium"));
		self::register(new NetherBrick(new BID(BlockLegacyIds::NETHER_BRICK_BLOCK), "Nether Bricks"));
		self::register(new NetherBrick(new BID(BlockLegacyIds::RED_NETHER_BRICK), "Red Nether Bricks"));
		self::register(new NetherBrickFence(new BID(BlockLegacyIds::NETHER_BRICK_FENCE), "Nether Brick Fence"));
		self::register(new NetherBrickStairs(new BID(BlockLegacyIds::NETHER_BRICK_STAIRS), "Nether Brick Stairs"));
		self::register(new NetherPortal(new BID(BlockLegacyIds::PORTAL), "Nether Portal"));
		self::register(new NetherQuartzOre(new BID(BlockLegacyIds::NETHER_QUARTZ_ORE), "Nether Quartz Ore"));
		self::register(new NetherReactor(new BID(BlockLegacyIds::NETHERREACTOR), "Nether Reactor Core"));
		self::register(new NetherWartBlock(new BID(BlockLegacyIds::NETHER_WART_BLOCK), "Nether Wart Block"));
		self::register(new NetherWartPlant(new BID(BlockLegacyIds::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART), "Nether Wart"));
		self::register(new Netherrack(new BID(BlockLegacyIds::NETHERRACK), "Netherrack"));
		self::register(new NoteBlock(new BID(BlockLegacyIds::NOTEBLOCK), "Note Block"));
		self::register(new Obsidian(new BID(BlockLegacyIds::OBSIDIAN), "Obsidian"));
		self::register(new PackedIce(new BID(BlockLegacyIds::PACKED_ICE), "Packed Ice"));
		self::register(new Podzol(new BID(BlockLegacyIds::PODZOL), "Podzol"));
		self::register(new Potato(new BID(BlockLegacyIds::POTATOES), "Potato Block"));
		self::register(new PoweredRail(new BID(BlockLegacyIds::GOLDEN_RAIL, BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH), "Powered Rail"));
		self::register(new Prismarine(new BID(BlockLegacyIds::PRISMARINE, BlockLegacyMetadata::PRISMARINE_BRICKS), "Prismarine Bricks"));
		self::register(new Prismarine(new BID(BlockLegacyIds::PRISMARINE, BlockLegacyMetadata::PRISMARINE_DARK), "Dark Prismarine"));
		self::register(new Prismarine(new BID(BlockLegacyIds::PRISMARINE, BlockLegacyMetadata::PRISMARINE_NORMAL), "Prismarine"));
		self::register(new Pumpkin(new BID(BlockLegacyIds::PUMPKIN), "Pumpkin"));
		self::register(new PumpkinStem(new BID(BlockLegacyIds::PUMPKIN_STEM, 0, ItemIds::PUMPKIN_SEEDS), "Pumpkin Stem"));
		self::register(new Purpur(new BID(BlockLegacyIds::PURPUR_BLOCK, BlockLegacyMetadata::PURPUR_NORMAL), "Purpur Block"));
		self::register(new class(new BID(BlockLegacyIds::PURPUR_BLOCK, BlockLegacyMetadata::PURPUR_PILLAR), "Purpur Pillar") extends Purpur{
			use PillarRotationTrait;
		});
		self::register(new PurpurStairs(new BID(BlockLegacyIds::PURPUR_STAIRS), "Purpur Stairs"));
		self::register(new Quartz(new BID(BlockLegacyIds::QUARTZ_BLOCK, BlockLegacyMetadata::QUARTZ_NORMAL), "Quartz Block"));
		self::register(new class(new BID(BlockLegacyIds::QUARTZ_BLOCK, BlockLegacyMetadata::QUARTZ_CHISELED), "Chiseled Quartz Block") extends Quartz{
			use PillarRotationTrait;
		});
		self::register(new class(new BID(BlockLegacyIds::QUARTZ_BLOCK, BlockLegacyMetadata::QUARTZ_PILLAR), "Quartz Pillar") extends Quartz{
			use PillarRotationTrait;
		});
		self::register(new Quartz(new BID(BlockLegacyIds::QUARTZ_BLOCK, BlockLegacyMetadata::QUARTZ_SMOOTH), "Smooth Quartz Block")); //TODO: this has axis rotation in 1.9, unsure if a bug (https://bugs.mojang.com/browse/MCPE-39074)
		self::register(new QuartzStairs(new BID(BlockLegacyIds::QUARTZ_STAIRS), "Quartz Stairs"));
		self::register(new Rail(new BID(BlockLegacyIds::RAIL), "Rail"));
		self::register(new RedMushroom(new BID(BlockLegacyIds::RED_MUSHROOM), "Red Mushroom"));
		self::register(new RedMushroomBlock(new BID(BlockLegacyIds::RED_MUSHROOM_BLOCK), "Red Mushroom Block"));
		self::register(new Redstone(new BID(BlockLegacyIds::REDSTONE_BLOCK), "Redstone Block"));
		self::register(new RedstoneComparator(new BlockIdentifierFlattened(BlockLegacyIds::UNPOWERED_COMPARATOR, BlockLegacyIds::POWERED_COMPARATOR, 0, ItemIds::COMPARATOR, Comparator::class), "Redstone Comparator"));
		self::register(new RedstoneLamp(new BlockIdentifierFlattened(BlockLegacyIds::REDSTONE_LAMP, BlockLegacyIds::LIT_REDSTONE_LAMP), "Redstone Lamp"));
		self::register(new RedstoneOre(new BlockIdentifierFlattened(BlockLegacyIds::REDSTONE_ORE, BlockLegacyIds::LIT_REDSTONE_ORE), "Redstone Ore"));
		self::register(new RedstoneRepeater(new BlockIdentifierFlattened(BlockLegacyIds::UNPOWERED_REPEATER, BlockLegacyIds::POWERED_REPEATER, 0, ItemIds::REPEATER), "Redstone Repeater"));
		self::register(new RedstoneTorch(new BlockIdentifierFlattened(BlockLegacyIds::REDSTONE_TORCH, BlockLegacyIds::UNLIT_REDSTONE_TORCH), "Redstone Torch"));
		self::register(new RedstoneWire(new BID(BlockLegacyIds::REDSTONE_WIRE, 0, ItemIds::REDSTONE), "Redstone"));
		self::register(new Reserved6(new BID(BlockLegacyIds::RESERVED6), "reserved6"));
		self::register(new Sand(new BID(BlockLegacyIds::SAND), "Sand"));
		self::register(new Sand(new BID(BlockLegacyIds::SAND, 1), "Red Sand"));
		self::register(new SandstoneStairs(new BID(BlockLegacyIds::RED_SANDSTONE_STAIRS), "Red Sandstone Stairs"));
		self::register(new SandstoneStairs(new BID(BlockLegacyIds::SANDSTONE_STAIRS), "Sandstone Stairs"));
		self::register(new SeaLantern(new BID(BlockLegacyIds::SEALANTERN), "Sea Lantern"));
		self::register(new SeaPickle(new BID(BlockLegacyIds::SEA_PICKLE), "Sea Pickle"));
		self::register(new Skull(new BID(BlockLegacyIds::MOB_HEAD_BLOCK, 0, null, \pocketmine\tile\Skull::class), "Mob Head"));
		self::register(new SmoothStone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_NORMAL), "Stone"));
		self::register(new Snow(new BID(BlockLegacyIds::SNOW), "Snow Block"));
		self::register(new SnowLayer(new BID(BlockLegacyIds::SNOW_LAYER), "Snow Layer"));
		self::register(new SoulSand(new BID(BlockLegacyIds::SOUL_SAND), "Soul Sand"));
		self::register(new Sponge(new BID(BlockLegacyIds::SPONGE), "Sponge"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_ANDESITE), "Andesite"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_DIORITE), "Diorite"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_GRANITE), "Granite"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_POLISHED_ANDESITE), "Polished Andesite"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_POLISHED_DIORITE), "Polished Diorite"));
		self::register(new Stone(new BID(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_POLISHED_GRANITE), "Polished Granite"));
		self::register(new StoneBrickStairs(new BID(BlockLegacyIds::STONE_BRICK_STAIRS), "Stone Brick Stairs"));
		self::register(new StoneBricks(new BID(BlockLegacyIds::STONEBRICK, BlockLegacyMetadata::STONE_BRICK_CHISELED), "Chiseled Stone Bricks"));
		self::register(new StoneBricks(new BID(BlockLegacyIds::STONEBRICK, BlockLegacyMetadata::STONE_BRICK_CRACKED), "Cracked Stone Bricks"));
		self::register(new StoneBricks(new BID(BlockLegacyIds::STONEBRICK, BlockLegacyMetadata::STONE_BRICK_MOSSY), "Mossy Stone Bricks"));
		self::register(new StoneBricks(new BID(BlockLegacyIds::STONEBRICK, BlockLegacyMetadata::STONE_BRICK_NORMAL), "Stone Bricks"));
		self::register(new StoneButton(new BID(BlockLegacyIds::STONE_BUTTON), "Stone Button"));
		self::register(new StonePressurePlate(new BID(BlockLegacyIds::STONE_PRESSURE_PLATE), "Stone Pressure Plate"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_BRICK), "Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_COBBLESTONE), "Cobblestone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_FAKE_WOODEN), "Fake Wooden"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_NETHER_BRICK), "Nether Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_QUARTZ), "Quartz"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_SANDSTONE), "Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_SMOOTH_STONE), "Smooth Stone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB, BlockLegacyIds::DOUBLE_STONE_SLAB, BlockLegacyMetadata::STONE_SLAB_STONE_BRICK), "Stone Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_DARK_PRISMARINE), "Dark Prismarine"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_MOSSY_COBBLESTONE), "Mossy Cobblestone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_PRISMARINE), "Prismarine"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_PRISMARINE_BRICKS), "Prismarine Bricks"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_PURPUR), "Purpur"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_RED_NETHER_BRICK), "Red Nether Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_RED_SANDSTONE), "Red Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB2, BlockLegacyIds::DOUBLE_STONE_SLAB2, BlockLegacyMetadata::STONE_SLAB2_SMOOTH_SANDSTONE), "Smooth Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_ANDESITE), "Andesite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_DIORITE), "Diorite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_END_STONE_BRICK), "End Stone Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_GRANITE), "Granite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_POLISHED_ANDESITE), "Polished Andesite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_POLISHED_DIORITE), "Polished Diorite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_POLISHED_GRANITE), "Polished Granite"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB3, BlockLegacyIds::DOUBLE_STONE_SLAB3, BlockLegacyMetadata::STONE_SLAB3_SMOOTH_RED_SANDSTONE), "Smooth Red Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB4, BlockLegacyIds::DOUBLE_STONE_SLAB4, BlockLegacyMetadata::STONE_SLAB4_CUT_RED_SANDSTONE), "Cut Red Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB4, BlockLegacyIds::DOUBLE_STONE_SLAB4, BlockLegacyMetadata::STONE_SLAB4_CUT_SANDSTONE), "Cut Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB4, BlockLegacyIds::DOUBLE_STONE_SLAB4, BlockLegacyMetadata::STONE_SLAB4_MOSSY_STONE_BRICK), "Mossy Stone Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB4, BlockLegacyIds::DOUBLE_STONE_SLAB4, BlockLegacyMetadata::STONE_SLAB4_SMOOTH_QUARTZ), "Smooth Quartz"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(BlockLegacyIds::STONE_SLAB4, BlockLegacyIds::DOUBLE_STONE_SLAB4, BlockLegacyMetadata::STONE_SLAB4_STONE), "Stone"));
		self::register(new Stonecutter(new BID(BlockLegacyIds::STONECUTTER), "Stonecutter"));
		self::register(new Sugarcane(new BID(BlockLegacyIds::REEDS_BLOCK, 0, ItemIds::REEDS), "Sugarcane"));
		self::register(new TNT(new BID(BlockLegacyIds::TNT), "TNT"));
		self::register(new TallGrass(new BID(BlockLegacyIds::TALLGRASS), "Fern")); //TODO: remap this to normal fern
		self::register(new TallGrass(new BID(BlockLegacyIds::TALLGRASS, BlockLegacyMetadata::TALLGRASS_NORMAL), "Tall Grass"));
		self::register(new TallGrass(new BID(BlockLegacyIds::TALLGRASS, BlockLegacyMetadata::TALLGRASS_FERN), "Fern"));
		self::register(new TallGrass(new BID(BlockLegacyIds::TALLGRASS, 3), "Fern")); //TODO: remap this to normal fern
		self::register(new Torch(new BID(BlockLegacyIds::COLORED_TORCH_BP), "Blue Torch"));
		self::register(new Torch(new BID(BlockLegacyIds::COLORED_TORCH_BP, 8), "Purple Torch"));
		self::register(new Torch(new BID(BlockLegacyIds::COLORED_TORCH_RG), "Red Torch"));
		self::register(new Torch(new BID(BlockLegacyIds::COLORED_TORCH_RG, 8), "Green Torch"));
		self::register(new Torch(new BID(BlockLegacyIds::TORCH), "Torch"));
		self::register(new TrappedChest(new BID(BlockLegacyIds::TRAPPED_CHEST, 0, null, \pocketmine\tile\Chest::class), "Trapped Chest"));
		self::register(new Tripwire(new BID(BlockLegacyIds::TRIPWIRE, 0, ItemIds::STRING), "Tripwire"));
		self::register(new TripwireHook(new BID(BlockLegacyIds::TRIPWIRE_HOOK), "Tripwire Hook"));
		self::register(new UnderwaterTorch(new BID(BlockLegacyIds::UNDERWATER_TORCH), "Underwater Torch"));
		self::register(new Vine(new BID(BlockLegacyIds::VINE), "Vines"));
		self::register(new Water(new BlockIdentifierFlattened(BlockLegacyIds::FLOWING_WATER, BlockLegacyIds::STILL_WATER), "Water"));
		self::register(new WaterLily(new BID(BlockLegacyIds::LILY_PAD), "Lily Pad"));
		self::register(new WeightedPressurePlateHeavy(new BID(BlockLegacyIds::HEAVY_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Heavy"));
		self::register(new WeightedPressurePlateLight(new BID(BlockLegacyIds::LIGHT_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Light"));
		self::register(new Wheat(new BID(BlockLegacyIds::WHEAT_BLOCK), "Wheat Block"));


		//region ugly treetype -> blockID mapping tables
		/** @var int[]|\SplObjectStorage $woodenStairIds */
		$woodenStairIds = new \SplObjectStorage();
		$woodenStairIds[TreeType::OAK()] = BlockLegacyIds::OAK_STAIRS;
		$woodenStairIds[TreeType::SPRUCE()] = BlockLegacyIds::SPRUCE_STAIRS;
		$woodenStairIds[TreeType::BIRCH()] = BlockLegacyIds::BIRCH_STAIRS;
		$woodenStairIds[TreeType::JUNGLE()] = BlockLegacyIds::JUNGLE_STAIRS;
		$woodenStairIds[TreeType::ACACIA()] = BlockLegacyIds::ACACIA_STAIRS;
		$woodenStairIds[TreeType::DARK_OAK()] = BlockLegacyIds::DARK_OAK_STAIRS;

		/** @var int[]|\SplObjectStorage $fenceGateIds */
		$fenceGateIds = new \SplObjectStorage();
		$fenceGateIds[TreeType::OAK()] = BlockLegacyIds::OAK_FENCE_GATE;
		$fenceGateIds[TreeType::SPRUCE()] = BlockLegacyIds::SPRUCE_FENCE_GATE;
		$fenceGateIds[TreeType::BIRCH()] = BlockLegacyIds::BIRCH_FENCE_GATE;
		$fenceGateIds[TreeType::JUNGLE()] = BlockLegacyIds::JUNGLE_FENCE_GATE;
		$fenceGateIds[TreeType::ACACIA()] = BlockLegacyIds::ACACIA_FENCE_GATE;
		$fenceGateIds[TreeType::DARK_OAK()] = BlockLegacyIds::DARK_OAK_FENCE_GATE;

		/** @var BID[]|\SplObjectStorage $woodenDoorIds */
		$woodenDoorIds = new \SplObjectStorage();
		$woodenDoorIds[TreeType::OAK()] = new BID(BlockLegacyIds::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR);
		$woodenDoorIds[TreeType::SPRUCE()] = new BID(BlockLegacyIds::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR);
		$woodenDoorIds[TreeType::BIRCH()] = new BID(BlockLegacyIds::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR);
		$woodenDoorIds[TreeType::JUNGLE()] = new BID(BlockLegacyIds::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR);
		$woodenDoorIds[TreeType::ACACIA()] = new BID(BlockLegacyIds::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR);
		$woodenDoorIds[TreeType::DARK_OAK()] = new BID(BlockLegacyIds::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR);

		/** @var int[]|\SplObjectStorage $woodenPressurePlateIds */
		$woodenPressurePlateIds = new \SplObjectStorage();
		$woodenPressurePlateIds[TreeType::OAK()] = BlockLegacyIds::WOODEN_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::SPRUCE()] = BlockLegacyIds::SPRUCE_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::BIRCH()] = BlockLegacyIds::BIRCH_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::JUNGLE()] = BlockLegacyIds::JUNGLE_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::ACACIA()] = BlockLegacyIds::ACACIA_PRESSURE_PLATE;
		$woodenPressurePlateIds[TreeType::DARK_OAK()] = BlockLegacyIds::DARK_OAK_PRESSURE_PLATE;

		/** @var int[]|\SplObjectStorage $woodenButtonIds */
		$woodenButtonIds = new \SplObjectStorage();
		$woodenButtonIds[TreeType::OAK()] = BlockLegacyIds::WOODEN_BUTTON;
		$woodenButtonIds[TreeType::SPRUCE()] = BlockLegacyIds::SPRUCE_BUTTON;
		$woodenButtonIds[TreeType::BIRCH()] = BlockLegacyIds::BIRCH_BUTTON;
		$woodenButtonIds[TreeType::JUNGLE()] = BlockLegacyIds::JUNGLE_BUTTON;
		$woodenButtonIds[TreeType::ACACIA()] = BlockLegacyIds::ACACIA_BUTTON;
		$woodenButtonIds[TreeType::DARK_OAK()] = BlockLegacyIds::DARK_OAK_BUTTON;

		/** @var int[]|\SplObjectStorage $woodenTrapdoorIds */
		$woodenTrapdoorIds = new \SplObjectStorage();
		$woodenTrapdoorIds[TreeType::OAK()] = BlockLegacyIds::WOODEN_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::SPRUCE()] = BlockLegacyIds::SPRUCE_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::BIRCH()] = BlockLegacyIds::BIRCH_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::JUNGLE()] = BlockLegacyIds::JUNGLE_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::ACACIA()] = BlockLegacyIds::ACACIA_TRAPDOOR;
		$woodenTrapdoorIds[TreeType::DARK_OAK()] = BlockLegacyIds::DARK_OAK_TRAPDOOR;

		/** @var BlockIdentifierFlattened[]|\SplObjectStorage $woodenSignIds */
		$woodenSignIds = new \SplObjectStorage();
		$woodenSignIds[TreeType::OAK()] = new BlockIdentifierFlattened(BlockLegacyIds::SIGN_POST, BlockLegacyIds::WALL_SIGN, 0, ItemIds::SIGN, \pocketmine\tile\Sign::class);
		$woodenSignIds[TreeType::SPRUCE()] = new BlockIdentifierFlattened(BlockLegacyIds::SPRUCE_STANDING_SIGN, BlockLegacyIds::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, \pocketmine\tile\Sign::class);
		$woodenSignIds[TreeType::BIRCH()] = new BlockIdentifierFlattened(BlockLegacyIds::BIRCH_STANDING_SIGN, BlockLegacyIds::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, \pocketmine\tile\Sign::class);
		$woodenSignIds[TreeType::JUNGLE()] = new BlockIdentifierFlattened(BlockLegacyIds::JUNGLE_STANDING_SIGN, BlockLegacyIds::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, \pocketmine\tile\Sign::class);
		$woodenSignIds[TreeType::ACACIA()] = new BlockIdentifierFlattened(BlockLegacyIds::ACACIA_STANDING_SIGN, BlockLegacyIds::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, \pocketmine\tile\Sign::class);
		$woodenSignIds[TreeType::DARK_OAK()] = new BlockIdentifierFlattened(BlockLegacyIds::DARKOAK_STANDING_SIGN, BlockLegacyIds::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, \pocketmine\tile\Sign::class);
		//endregion

		foreach(TreeType::getAll() as $treeType){
			$magicNumber = $treeType->getMagicNumber();
			$name = $treeType->getDisplayName();
			self::register(new Planks(new BID(BlockLegacyIds::PLANKS, $magicNumber), $name . " Planks"));
			self::register(new Sapling(new BID(BlockLegacyIds::SAPLING, $magicNumber), $name . " Sapling", $treeType));
			self::register(new WoodenFence(new BID(BlockLegacyIds::FENCE, $magicNumber), $name . " Fence"));
			self::register(new WoodenSlab(new BlockIdentifierFlattened(BlockLegacyIds::WOODEN_SLAB, BlockLegacyIds::DOUBLE_WOODEN_SLAB, $treeType->getMagicNumber()), $treeType->getDisplayName()));

			//TODO: find a better way to deal with this split
			self::register(new Leaves(new BID($magicNumber >= 4 ? BlockLegacyIds::LEAVES2 : BlockLegacyIds::LEAVES, $magicNumber & 0x03), $name . " Leaves", $treeType));
			self::register(new Log(new BID($magicNumber >= 4 ? BlockLegacyIds::LOG2 : BlockLegacyIds::LOG, $magicNumber & 0x03), $name . " Log", $treeType));

			//TODO: the old bug-block needs to be remapped to the new dedicated block
			self::register(new Wood(new BID($magicNumber >= 4 ? BlockLegacyIds::LOG2 : BlockLegacyIds::LOG, ($magicNumber & 0x03) | 0b1100), $name . " Wood", $treeType));
			self::register(new Wood(new BID(BlockLegacyIds::WOOD, $magicNumber), $name . " Wood", $treeType));

			self::register(new FenceGate(new BID($fenceGateIds[$treeType]), $treeType->getDisplayName() . " Fence Gate"));
			self::register(new WoodenStairs(new BID($woodenStairIds[$treeType]), $treeType->getDisplayName() . " Stairs"));
			self::register(new WoodenDoor($woodenDoorIds[$treeType], $treeType->getDisplayName() . " Door"));

			self::register(new WoodenButton(new BID($woodenButtonIds[$treeType]), $treeType->getDisplayName() . " Button"));
			self::register(new WoodenPressurePlate(new BID($woodenPressurePlateIds[$treeType]), $treeType->getDisplayName() . " Pressure Plate"));
			self::register(new WoodenTrapdoor(new BID($woodenTrapdoorIds[$treeType]), $treeType->getDisplayName() . " Trapdoor"));

			self::register(new Sign($woodenSignIds[$treeType], $treeType->getDisplayName() . " Sign"));
		}

		static $sandstoneTypes = [
			BlockLegacyMetadata::SANDSTONE_NORMAL => "",
			BlockLegacyMetadata::SANDSTONE_CHISELED => "Chiseled ",
			BlockLegacyMetadata::SANDSTONE_CUT => "Cut ",
			BlockLegacyMetadata::SANDSTONE_SMOOTH => "Smooth "
		];
		foreach($sandstoneTypes as $variant => $prefix){
			self::register(new Sandstone(new BID(BlockLegacyIds::SANDSTONE, $variant), $prefix . "Sandstone"));
			self::register(new Sandstone(new BID(BlockLegacyIds::RED_SANDSTONE, $variant), $prefix . "Red Sandstone"));
		}

		//region ugly glazed-terracotta colour -> ID mapping table
		/** @var int[]|\SplObjectStorage $glazedTerracottaIds */
		$glazedTerracottaIds = new \SplObjectStorage();
		$glazedTerracottaIds[DyeColor::WHITE()] = BlockLegacyIds::WHITE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::ORANGE()] = BlockLegacyIds::ORANGE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::MAGENTA()] = BlockLegacyIds::MAGENTA_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_BLUE()] = BlockLegacyIds::LIGHT_BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::YELLOW()] = BlockLegacyIds::YELLOW_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIME()] = BlockLegacyIds::LIME_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PINK()] = BlockLegacyIds::PINK_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GRAY()] = BlockLegacyIds::GRAY_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_GRAY()] = BlockLegacyIds::SILVER_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::CYAN()] = BlockLegacyIds::CYAN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PURPLE()] = BlockLegacyIds::PURPLE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLUE()] = BlockLegacyIds::BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BROWN()] = BlockLegacyIds::BROWN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GREEN()] = BlockLegacyIds::GREEN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::RED()] = BlockLegacyIds::RED_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLACK()] = BlockLegacyIds::BLACK_GLAZED_TERRACOTTA;
		//endregion

		foreach(DyeColor::getAll() as $color){
			self::register(new Carpet(new BID(BlockLegacyIds::CARPET, $color->getMagicNumber()), $color->getDisplayName() . " Carpet"));
			self::register(new Concrete(new BID(BlockLegacyIds::CONCRETE, $color->getMagicNumber()), $color->getDisplayName() . " Concrete"));
			self::register(new ConcretePowder(new BID(BlockLegacyIds::CONCRETE_POWDER, $color->getMagicNumber()), $color->getDisplayName() . " Concrete Powder"));
			self::register(new Glass(new BID(BlockLegacyIds::STAINED_GLASS, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass"));
			self::register(new GlassPane(new BID(BlockLegacyIds::STAINED_GLASS_PANE, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new GlazedTerracotta(new BID($glazedTerracottaIds[$color]), $color->getDisplayName() . " Glazed Terracotta"));
			self::register(new HardenedClay(new BID(BlockLegacyIds::STAINED_CLAY, $color->getMagicNumber()), $color->getDisplayName() . " Stained Clay"));
			self::register(new HardenedGlass(new BID(BlockLegacyIds::HARD_STAINED_GLASS, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass"));
			self::register(new HardenedGlassPane(new BID(BlockLegacyIds::HARD_STAINED_GLASS_PANE, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new Wool(new BID(BlockLegacyIds::WOOL, $color->getMagicNumber()), $color->getDisplayName() . " Wool"));
		}

		static $wallTypes = [
			BlockLegacyMetadata::WALL_ANDESITE => "Andesite",
			BlockLegacyMetadata::WALL_BRICK => "Brick",
			BlockLegacyMetadata::WALL_DIORITE => "Diorite",
			BlockLegacyMetadata::WALL_END_STONE_BRICK => "End Stone Brick",
			BlockLegacyMetadata::WALL_GRANITE => "Granite",
			BlockLegacyMetadata::WALL_MOSSY_STONE_BRICK => "Mossy Stone Brick",
			BlockLegacyMetadata::WALL_MOSSY_COBBLESTONE => "Mossy Cobblestone",
			BlockLegacyMetadata::WALL_NETHER_BRICK => "Nether Brick",
			BlockLegacyMetadata::WALL_COBBLESTONE => "Cobblestone",
			BlockLegacyMetadata::WALL_PRISMARINE => "Prismarine",
			BlockLegacyMetadata::WALL_RED_NETHER_BRICK => "Red Nether Brick",
			BlockLegacyMetadata::WALL_RED_SANDSTONE => "Red Sandstone",
			BlockLegacyMetadata::WALL_SANDSTONE => "Sandstone",
			BlockLegacyMetadata::WALL_STONE_BRICK => "Stone Brick"
		];
		foreach($wallTypes as $magicNumber => $prefix){
			self::register(new Wall(new BID(BlockLegacyIds::COBBLESTONE_WALL, $magicNumber), $prefix . " Wall"));
		}

		//region --- auto-generated TODOs ---
		//TODO: minecraft:andesite_stairs
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
		//TODO: minecraft:dark_prismarine_stairs
		//TODO: minecraft:diorite_stairs
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
		//TODO: minecraft:end_brick_stairs
		//TODO: minecraft:end_gateway
		//TODO: minecraft:end_portal
		//TODO: minecraft:fletching_table
		//TODO: minecraft:granite_stairs
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
		//TODO: minecraft:mossy_cobblestone_stairs
		//TODO: minecraft:mossy_stone_brick_stairs
		//TODO: minecraft:movingBlock
		//TODO: minecraft:normal_stone_stairs
		//TODO: minecraft:observer
		//TODO: minecraft:piston
		//TODO: minecraft:pistonArmCollision
		//TODO: minecraft:polished_andesite_stairs
		//TODO: minecraft:polished_diorite_stairs
		//TODO: minecraft:polished_granite_stairs
		//TODO: minecraft:prismarine_bricks_stairs
		//TODO: minecraft:prismarine_stairs
		//TODO: minecraft:red_nether_brick_stairs
		//TODO: minecraft:repeating_command_block
		//TODO: minecraft:scaffolding
		//TODO: minecraft:seagrass
		//TODO: minecraft:shulker_box
		//TODO: minecraft:slime
		//TODO: minecraft:smithing_table
		//TODO: minecraft:smoker
		//TODO: minecraft:smooth_quartz_stairs
		//TODO: minecraft:smooth_red_sandstone_stairs
		//TODO: minecraft:smooth_sandstone_stairs
		//TODO: minecraft:smooth_stone
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
