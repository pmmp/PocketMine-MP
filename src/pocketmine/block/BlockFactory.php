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
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use function array_fill;
use function file_get_contents;
use function get_class;
use function json_decode;
use function max;
use function min;

/**
 * Manages block registration and instance creation
 */
class BlockFactory{
	/** @var \SplFixedArray<Block> */
	private static $fullList = null;

	/** @var \SplFixedArray<int> */
	public static $lightFilter = null;
	/** @var \SplFixedArray<bool> */
	public static $diffusesSkyLight = null;
	/** @var \SplFixedArray<float> */
	public static $blastResistance = null;

	/** @var \SplFixedArray|int[] */
	private static $stateMasks = null;

	/** @var int[] */
	public static $staticRuntimeIdMap = [];

	/** @var int[] */
	public static $legacyIdMap = [];

	/** @var int */
	private static $lastRuntimeId = 0;

	/**
	 * Initializes the block factory. By default this is called only once on server start, however you may wish to use
	 * this if you need to reset the block factory back to its original defaults for whatever reason.
	 */
	public static function init() : void{
		self::$fullList = new \SplFixedArray(8192);

		self::$lightFilter = \SplFixedArray::fromArray(array_fill(0, 8192, 1));
		self::$diffusesSkyLight = \SplFixedArray::fromArray(array_fill(0, 8192, false));
		self::$blastResistance = \SplFixedArray::fromArray(array_fill(0, 8192, 0));

		self::$stateMasks = new \SplFixedArray(8192);

		self::register(new ActivatorRail(new BID(Block::ACTIVATOR_RAIL, BaseRail::STRAIGHT_NORTH_SOUTH), "Activator Rail"));
		self::register(new Air(new BID(Block::AIR), "Air"));
		self::register(new Anvil(new BID(Block::ANVIL, Anvil::TYPE_NORMAL), "Anvil"));
		self::register(new Anvil(new BID(Block::ANVIL, Anvil::TYPE_SLIGHTLY_DAMAGED), "Slightly Damaged Anvil"));
		self::register(new Anvil(new BID(Block::ANVIL, Anvil::TYPE_VERY_DAMAGED), "Very Damaged Anvil"));
		self::register(new Bed(new BID(Block::BED_BLOCK, 0, ItemIds::BED, \pocketmine\tile\Bed::class), "Bed Block"));
		self::register(new Bedrock(new BID(Block::BEDROCK), "Bedrock"));
		self::register(new Beetroot(new BID(Block::BEETROOT_BLOCK), "Beetroot Block"));
		self::register(new BoneBlock(new BID(Block::BONE_BLOCK), "Bone Block"));
		self::register(new Bookshelf(new BID(Block::BOOKSHELF), "Bookshelf"));
		self::register(new BrewingStand(new BID(Block::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND), "Brewing Stand"));
		self::register(new BrickStairs(new BID(Block::BRICK_STAIRS), "Brick Stairs"));
		self::register(new Bricks(new BID(Block::BRICK_BLOCK), "Bricks"));
		self::register(new BrownMushroom(new BID(Block::BROWN_MUSHROOM), "Brown Mushroom"));
		self::register(new BrownMushroomBlock(new BID(Block::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block"));
		self::register(new Cactus(new BID(Block::CACTUS), "Cactus"));
		self::register(new Cake(new BID(Block::CAKE_BLOCK, 0, ItemIds::CAKE), "Cake"));
		self::register(new Carrot(new BID(Block::CARROTS), "Carrot Block"));
		self::register(new Chest(new BID(Block::CHEST, 0, null, \pocketmine\tile\Chest::class), "Chest"));
		self::register(new Clay(new BID(Block::CLAY_BLOCK), "Clay Block"));
		self::register(new Coal(new BID(Block::COAL_BLOCK), "Coal Block"));
		self::register(new CoalOre(new BID(Block::COAL_ORE), "Coal Ore"));
		self::register(new CoarseDirt(new BID(Block::DIRT, Dirt::COARSE), "Coarse Dirt"));
		self::register(new Cobblestone(new BID(Block::COBBLESTONE), "Cobblestone"));
		self::register(new Cobblestone(new BID(Block::MOSSY_COBBLESTONE), "Moss Stone"));
		self::register(new CobblestoneStairs(new BID(Block::COBBLESTONE_STAIRS), "Cobblestone Stairs"));
		self::register(new Cobweb(new BID(Block::COBWEB), "Cobweb"));
		self::register(new CocoaBlock(new BID(Block::COCOA), "Cocoa Block"));
		self::register(new CraftingTable(new BID(Block::CRAFTING_TABLE), "Crafting Table"));
		self::register(new Dandelion(new BID(Block::DANDELION), "Dandelion"));
		self::register(new DaylightSensor(new BlockIdentifierFlattened(Block::DAYLIGHT_DETECTOR, Block::DAYLIGHT_DETECTOR_INVERTED), "Daylight Sensor"));
		self::register(new DeadBush(new BID(Block::DEADBUSH), "Dead Bush"));
		self::register(new DetectorRail(new BID(Block::DETECTOR_RAIL), "Detector Rail"));
		self::register(new Diamond(new BID(Block::DIAMOND_BLOCK), "Diamond Block"));
		self::register(new DiamondOre(new BID(Block::DIAMOND_ORE), "Diamond Ore"));
		self::register(new Dirt(new BID(Block::DIRT, Dirt::NORMAL), "Dirt"));
		self::register(new DoublePlant(new BID(Block::DOUBLE_PLANT, 0), "Sunflower"));
		self::register(new DoublePlant(new BID(Block::DOUBLE_PLANT, 1), "Lilac"));
		self::register(new DoublePlant(new BID(Block::DOUBLE_PLANT, 4), "Rose Bush"));
		self::register(new DoublePlant(new BID(Block::DOUBLE_PLANT, 5), "Peony"));
		self::register(new DoubleTallGrass(new BID(Block::DOUBLE_PLANT, 2), "Double Tallgrass"));
		self::register(new DoubleTallGrass(new BID(Block::DOUBLE_PLANT, 3), "Large Fern"));
		self::register(new Emerald(new BID(Block::EMERALD_BLOCK), "Emerald Block"));
		self::register(new EmeraldOre(new BID(Block::EMERALD_ORE), "Emerald Ore"));
		self::register(new EnchantingTable(new BID(Block::ENCHANTING_TABLE, 0, null, \pocketmine\tile\EnchantTable::class), "Enchanting Table"));
		self::register(new EndPortalFrame(new BID(Block::END_PORTAL_FRAME), "End Portal Frame"));
		self::register(new EndRod(new BID(Block::END_ROD), "End Rod"));
		self::register(new EndStone(new BID(Block::END_STONE), "End Stone"));
		self::register(new EndStoneBricks(new BID(Block::END_BRICKS), "End Stone Bricks"));
		self::register(new EnderChest(new BID(Block::ENDER_CHEST, 0, null, \pocketmine\tile\EnderChest::class), "Ender Chest"));
		self::register(new Farmland(new BID(Block::FARMLAND), "Farmland"));
		self::register(new Fire(new BID(Block::FIRE), "Fire Block"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_ALLIUM), "Allium"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_AZURE_BLUET), "Azure Bluet"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_BLUE_ORCHID), "Blue Orchid"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_CORNFLOWER), "Cornflower"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_LILY_OF_THE_VALLEY), "Lily of the Valley"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_ORANGE_TULIP), "Orange Tulip"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_OXEYE_DAISY), "Oxeye Daisy"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_PINK_TULIP), "Pink Tulip"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_POPPY), "Poppy"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_RED_TULIP), "Red Tulip"));
		self::register(new Flower(new BID(Block::RED_FLOWER, Flower::TYPE_WHITE_TULIP), "White Tulip"));
		self::register(new FlowerPot(new BID(Block::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT, \pocketmine\tile\FlowerPot::class), "Flower Pot"));
		self::register(new Furnace(new BlockIdentifierFlattened(Block::FURNACE, Block::LIT_FURNACE, 0, null, \pocketmine\tile\Furnace::class), "Furnace"));
		self::register(new Glass(new BID(Block::GLASS), "Glass"));
		self::register(new GlassPane(new BID(Block::GLASS_PANE), "Glass Pane"));
		self::register(new GlowingObsidian(new BID(Block::GLOWINGOBSIDIAN), "Glowing Obsidian"));
		self::register(new Glowstone(new BID(Block::GLOWSTONE), "Glowstone"));
		self::register(new Gold(new BID(Block::GOLD_BLOCK), "Gold Block"));
		self::register(new GoldOre(new BID(Block::GOLD_ORE), "Gold Ore"));
		self::register(new Grass(new BID(Block::GRASS), "Grass"));
		self::register(new GrassPath(new BID(Block::GRASS_PATH), "Grass Path"));
		self::register(new Gravel(new BID(Block::GRAVEL), "Gravel"));
		self::register(new HardenedClay(new BID(Block::HARDENED_CLAY), "Hardened Clay"));
		self::register(new HardenedGlass(new BID(Block::HARD_GLASS), "Hardened Glass"));
		self::register(new HardenedGlassPane(new BID(Block::HARD_GLASS_PANE), "Hardened Glass Pane"));
		self::register(new HayBale(new BID(Block::HAY_BALE), "Hay Bale"));
		self::register(new Ice(new BID(Block::ICE), "Ice"));
		self::register(new InfoUpdate(new BID(Block::INFO_UPDATE), "update!"));
		self::register(new InfoUpdate(new BID(Block::INFO_UPDATE2), "ate!upd"));
		self::register(new InvisibleBedrock(new BID(Block::INVISIBLEBEDROCK), "Invisible Bedrock"));
		self::register(new Iron(new BID(Block::IRON_BLOCK), "Iron Block"));
		self::register(new IronBars(new BID(Block::IRON_BARS), "Iron Bars"));
		self::register(new IronDoor(new BID(Block::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door"));
		self::register(new IronOre(new BID(Block::IRON_ORE), "Iron Ore"));
		self::register(new IronTrapdoor(new BID(Block::IRON_TRAPDOOR), "Iron Trapdoor"));
		self::register(new ItemFrame(new BID(Block::FRAME_BLOCK, 0, ItemIds::FRAME, \pocketmine\tile\ItemFrame::class), "Item Frame"));
		self::register(new Ladder(new BID(Block::LADDER), "Ladder"));
		self::register(new Lapis(new BID(Block::LAPIS_BLOCK), "Lapis Lazuli Block"));
		self::register(new LapisOre(new BID(Block::LAPIS_ORE), "Lapis Lazuli Ore"));
		self::register(new Lava(new BlockIdentifierFlattened(Block::FLOWING_LAVA, Block::STILL_LAVA), "Lava"));
		self::register(new Lever(new BID(Block::LEVER), "Lever"));
		self::register(new LitPumpkin(new BID(Block::JACK_O_LANTERN), "Jack o'Lantern"));
		self::register(new Magma(new BID(Block::MAGMA), "Magma Block"));
		self::register(new Melon(new BID(Block::MELON_BLOCK), "Melon Block"));
		self::register(new MelonStem(new BID(Block::MELON_STEM, 0, ItemIds::MELON_SEEDS), "Melon Stem"));
		self::register(new MonsterSpawner(new BID(Block::MOB_SPAWNER), "Monster Spawner"));
		self::register(new Mycelium(new BID(Block::MYCELIUM), "Mycelium"));
		self::register(new NetherBrick(new BID(Block::NETHER_BRICK_BLOCK), "Nether Bricks"));
		self::register(new NetherBrick(new BID(Block::RED_NETHER_BRICK), "Red Nether Bricks"));
		self::register(new NetherBrickFence(new BID(Block::NETHER_BRICK_FENCE), "Nether Brick Fence"));
		self::register(new NetherBrickStairs(new BID(Block::NETHER_BRICK_STAIRS), "Nether Brick Stairs"));
		self::register(new NetherQuartzOre(new BID(Block::NETHER_QUARTZ_ORE), "Nether Quartz Ore"));
		self::register(new NetherReactor(new BID(Block::NETHERREACTOR), "Nether Reactor Core"));
		self::register(new NetherWartBlock(new BID(Block::NETHER_WART_BLOCK), "Nether Wart Block"));
		self::register(new NetherWartPlant(new BID(Block::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART), "Nether Wart"));
		self::register(new Netherrack(new BID(Block::NETHERRACK), "Netherrack"));
		self::register(new NoteBlock(new BID(Block::NOTEBLOCK), "Note Block"));
		self::register(new Obsidian(new BID(Block::OBSIDIAN), "Obsidian"));
		self::register(new PackedIce(new BID(Block::PACKED_ICE), "Packed Ice"));
		self::register(new Podzol(new BID(Block::PODZOL), "Podzol"));
		self::register(new Potato(new BID(Block::POTATOES), "Potato Block"));
		self::register(new PoweredRail(new BID(Block::GOLDEN_RAIL, BaseRail::STRAIGHT_NORTH_SOUTH), "Powered Rail"));
		self::register(new Prismarine(new BID(Block::PRISMARINE, Prismarine::BRICKS), "Prismarine Bricks"));
		self::register(new Prismarine(new BID(Block::PRISMARINE, Prismarine::DARK), "Dark Prismarine"));
		self::register(new Prismarine(new BID(Block::PRISMARINE, Prismarine::NORMAL), "Prismarine"));
		self::register(new Pumpkin(new BID(Block::PUMPKIN), "Pumpkin"));
		self::register(new PumpkinStem(new BID(Block::PUMPKIN_STEM, 0, ItemIds::PUMPKIN_SEEDS), "Pumpkin Stem"));
		self::register(new Purpur(new BID(Block::PURPUR_BLOCK), "Purpur Block"));
		self::register(new class(new BID(Block::PURPUR_BLOCK, 2), "Purpur Pillar") extends Purpur{
			use PillarRotationTrait;
		});
		self::register(new PurpurStairs(new BID(Block::PURPUR_STAIRS), "Purpur Stairs"));
		self::register(new Quartz(new BID(Block::QUARTZ_BLOCK, Quartz::NORMAL), "Quartz Block"));
		self::register(new class(new BID(Block::QUARTZ_BLOCK, Quartz::CHISELED), "Chiseled Quartz Block") extends Quartz{
			use PillarRotationTrait;
		});
		self::register(new class(new BID(Block::QUARTZ_BLOCK, Quartz::PILLAR), "Quartz Pillar") extends Quartz{
			use PillarRotationTrait;
		});
		self::register(new Quartz(new BID(Block::QUARTZ_BLOCK, Quartz::SMOOTH), "Smooth Quartz Block")); //TODO: this has axis rotation in 1.9, unsure if a bug (https://bugs.mojang.com/browse/MCPE-39074)
		self::register(new QuartzStairs(new BID(Block::QUARTZ_STAIRS), "Quartz Stairs"));
		self::register(new Rail(new BID(Block::RAIL), "Rail"));
		self::register(new RedMushroom(new BID(Block::RED_MUSHROOM), "Red Mushroom"));
		self::register(new RedMushroomBlock(new BID(Block::RED_MUSHROOM_BLOCK), "Red Mushroom Block"));
		self::register(new Redstone(new BID(Block::REDSTONE_BLOCK), "Redstone Block"));
		self::register(new RedstoneLamp(new BlockIdentifierFlattened(Block::REDSTONE_LAMP, Block::LIT_REDSTONE_LAMP), "Redstone Lamp"));
		self::register(new RedstoneOre(new BlockIdentifierFlattened(Block::REDSTONE_ORE, Block::LIT_REDSTONE_ORE), "Redstone Ore"));
		self::register(new RedstoneRepeater(new BlockIdentifierFlattened(Block::UNPOWERED_REPEATER, Block::POWERED_REPEATER, 0, ItemIds::REPEATER), "Redstone Repeater"));
		self::register(new RedstoneTorch(new BlockIdentifierFlattened(Block::REDSTONE_TORCH, Block::UNLIT_REDSTONE_TORCH), "Redstone Torch"));
		self::register(new RedstoneWire(new BID(Block::REDSTONE_WIRE, 0, ItemIds::REDSTONE), "Redstone"));
		self::register(new Reserved6(new BID(Block::RESERVED6), "reserved6"));
		self::register(new Sand(new BID(Block::SAND), "Sand"));
		self::register(new Sand(new BID(Block::SAND, 1), "Red Sand"));
		self::register(new SandstoneStairs(new BID(Block::RED_SANDSTONE_STAIRS), "Red Sandstone Stairs"));
		self::register(new SandstoneStairs(new BID(Block::SANDSTONE_STAIRS), "Sandstone Stairs"));
		self::register(new SeaLantern(new BID(Block::SEALANTERN), "Sea Lantern"));
		self::register(new SignPost(new BID(Block::SIGN_POST, 0, ItemIds::SIGN, \pocketmine\tile\Sign::class), "Sign Post"));
		self::register(new Skull(new BID(Block::MOB_HEAD_BLOCK, 0, null, \pocketmine\tile\Skull::class), "Mob Head"));
		self::register(new SmoothStone(new BID(Block::STONE, Stone::NORMAL), "Stone"));
		self::register(new Snow(new BID(Block::SNOW), "Snow Block"));
		self::register(new SnowLayer(new BID(Block::SNOW_LAYER), "Snow Layer"));
		self::register(new SoulSand(new BID(Block::SOUL_SAND), "Soul Sand"));
		self::register(new Sponge(new BID(Block::SPONGE), "Sponge"));
		self::register(new StandingBanner(new BID(Block::STANDING_BANNER, 0, ItemIds::BANNER, \pocketmine\tile\Banner::class), "Standing Banner"));
		self::register(new Stone(new BID(Block::STONE, Stone::ANDESITE), "Andesite"));
		self::register(new Stone(new BID(Block::STONE, Stone::DIORITE), "Diorite"));
		self::register(new Stone(new BID(Block::STONE, Stone::GRANITE), "Granite"));
		self::register(new Stone(new BID(Block::STONE, Stone::POLISHED_ANDESITE), "Polished Andesite"));
		self::register(new Stone(new BID(Block::STONE, Stone::POLISHED_DIORITE), "Polished Diorite"));
		self::register(new Stone(new BID(Block::STONE, Stone::POLISHED_GRANITE), "Polished Granite"));
		self::register(new StoneBrickStairs(new BID(Block::STONE_BRICK_STAIRS), "Stone Brick Stairs"));
		self::register(new StoneBricks(new BID(Block::STONEBRICK, StoneBricks::CHISELED), "Chiseled Stone Bricks"));
		self::register(new StoneBricks(new BID(Block::STONEBRICK, StoneBricks::CRACKED), "Cracked Stone Bricks"));
		self::register(new StoneBricks(new BID(Block::STONEBRICK, StoneBricks::MOSSY), "Mossy Stone Bricks"));
		self::register(new StoneBricks(new BID(Block::STONEBRICK, StoneBricks::NORMAL), "Stone Bricks"));
		self::register(new StoneButton(new BID(Block::STONE_BUTTON), "Stone Button"));
		self::register(new StonePressurePlate(new BID(Block::STONE_PRESSURE_PLATE), "Stone Pressure Plate"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 0), "Stone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 1), "Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 2), "Fake Wooden"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 3), "Cobblestone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 4), "Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 5), "Stone Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 6), "Quartz"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 7), "Nether Brick"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 0), "Red Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 1), "Purpur"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 2), "Prismarine"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 3), "Dark Prismarine"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 4), "Prismarine Bricks"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 5), "Mossy Cobblestone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 6), "Smooth Sandstone"));
		self::register(new StoneSlab(new BlockIdentifierFlattened(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 7), "Red Nether Brick"));
		self::register(new Stonecutter(new BID(Block::STONECUTTER), "Stonecutter"));
		self::register(new Sugarcane(new BID(Block::REEDS_BLOCK, 0, ItemIds::REEDS), "Sugarcane"));
		self::register(new TNT(new BID(Block::TNT), "TNT"));
		self::register(new TallGrass(new BID(Block::TALLGRASS), "Fern"));
		self::register(new TallGrass(new BID(Block::TALLGRASS, 1), "Tall Grass"));
		self::register(new TallGrass(new BID(Block::TALLGRASS, 2), "Fern"));
		self::register(new TallGrass(new BID(Block::TALLGRASS, 3), "Fern"));
		self::register(new Torch(new BID(Block::COLORED_TORCH_BP), "Blue Torch"));
		self::register(new Torch(new BID(Block::COLORED_TORCH_BP, 8), "Purple Torch"));
		self::register(new Torch(new BID(Block::COLORED_TORCH_RG), "Red Torch"));
		self::register(new Torch(new BID(Block::COLORED_TORCH_RG, 8), "Green Torch"));
		self::register(new Torch(new BID(Block::TORCH), "Torch"));
		self::register(new Trapdoor(new BID(Block::TRAPDOOR), "Wooden Trapdoor"));
		self::register(new TrappedChest(new BID(Block::TRAPPED_CHEST, 0, null, \pocketmine\tile\Chest::class), "Trapped Chest"));
		self::register(new Tripwire(new BID(Block::TRIPWIRE), "Tripwire"));
		self::register(new TripwireHook(new BID(Block::TRIPWIRE_HOOK), "Tripwire Hook"));
		self::register(new UnderwaterTorch(new BID(Block::UNDERWATER_TORCH), "Underwater Torch"));
		self::register(new Vine(new BID(Block::VINE), "Vines"));
		self::register(new WallBanner(new BID(Block::WALL_BANNER, 0, ItemIds::BANNER, \pocketmine\tile\Banner::class), "Wall Banner"));
		self::register(new WallSign(new BID(Block::WALL_SIGN, 0, ItemIds::SIGN, \pocketmine\tile\Sign::class), "Wall Sign"));
		self::register(new Water(new BlockIdentifierFlattened(Block::FLOWING_WATER, Block::STILL_WATER), "Water"));
		self::register(new WaterLily(new BID(Block::LILY_PAD), "Lily Pad"));
		self::register(new WeightedPressurePlateHeavy(new BID(Block::HEAVY_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Heavy"));
		self::register(new WeightedPressurePlateLight(new BID(Block::LIGHT_WEIGHTED_PRESSURE_PLATE), "Weighted Pressure Plate Light"));
		self::register(new Wheat(new BID(Block::WHEAT_BLOCK), "Wheat Block"));
		self::register(new WoodenButton(new BID(Block::WOODEN_BUTTON), "Wooden Button"));
		self::register(new WoodenPressurePlate(new BID(Block::WOODEN_PRESSURE_PLATE), "Wooden Pressure Plate"));

		/** @var int[]|\SplObjectStorage $woodenStairIds */
		$woodenStairIds = new \SplObjectStorage();
		$woodenStairIds[TreeType::OAK()] = Block::OAK_STAIRS;
		$woodenStairIds[TreeType::SPRUCE()] = Block::SPRUCE_STAIRS;
		$woodenStairIds[TreeType::BIRCH()] = Block::BIRCH_STAIRS;
		$woodenStairIds[TreeType::JUNGLE()] = Block::JUNGLE_STAIRS;
		$woodenStairIds[TreeType::ACACIA()] = Block::ACACIA_STAIRS;
		$woodenStairIds[TreeType::DARK_OAK()] = Block::DARK_OAK_STAIRS;

		/** @var int[]|\SplObjectStorage $fenceGateIds */
		$fenceGateIds = new \SplObjectStorage();
		$fenceGateIds[TreeType::OAK()] = Block::OAK_FENCE_GATE;
		$fenceGateIds[TreeType::SPRUCE()] = Block::SPRUCE_FENCE_GATE;
		$fenceGateIds[TreeType::BIRCH()] = Block::BIRCH_FENCE_GATE;
		$fenceGateIds[TreeType::JUNGLE()] = Block::JUNGLE_FENCE_GATE;
		$fenceGateIds[TreeType::ACACIA()] = Block::ACACIA_FENCE_GATE;
		$fenceGateIds[TreeType::DARK_OAK()] = Block::DARK_OAK_FENCE_GATE;

		/** @var BID[]|\SplObjectStorage $woodenDoorIds */
		$woodenDoorIds = new \SplObjectStorage();
		$woodenDoorIds[TreeType::OAK()] = new BID(Block::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR);
		$woodenDoorIds[TreeType::SPRUCE()] = new BID(Block::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR);
		$woodenDoorIds[TreeType::BIRCH()] = new BID(Block::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR);
		$woodenDoorIds[TreeType::JUNGLE()] = new BID(Block::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR);
		$woodenDoorIds[TreeType::ACACIA()] = new BID(Block::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR);
		$woodenDoorIds[TreeType::DARK_OAK()] = new BID(Block::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR);

		foreach(TreeType::getAll() as $treeType){
			$magicNumber = $treeType->getMagicNumber();
			$name = $treeType->getDisplayName();
			self::register(new Planks(new BID(Block::PLANKS, $magicNumber), $name . " Planks"));
			self::register(new Sapling(new BID(Block::SAPLING, $magicNumber), $name . " Sapling", $treeType));
			self::register(new WoodenFence(new BID(Block::FENCE, $magicNumber), $name . " Fence"));
			self::register(new WoodenSlab(new BlockIdentifierFlattened(Block::WOODEN_SLAB, Block::DOUBLE_WOODEN_SLAB, $treeType->getMagicNumber()), $treeType->getDisplayName()));

			//TODO: find a better way to deal with this split
			self::register(new Leaves(new BID($magicNumber >= 4 ? Block::LEAVES2 : Block::LEAVES, $magicNumber & 0x03), $name . " Leaves", $treeType));
			self::register(new Log(new BID($magicNumber >= 4 ? Block::WOOD2 : Block::WOOD, $magicNumber & 0x03), $name . " Log", $treeType));
			self::register(new Wood(new BID($magicNumber >= 4 ? Block::WOOD2 : Block::WOOD, ($magicNumber & 0x03) | 0b1100), $name . " Wood", $treeType));

			self::register(new FenceGate(new BID($fenceGateIds[$treeType]), $treeType->getDisplayName() . " Fence Gate"));
			self::register(new WoodenStairs(new BID($woodenStairIds[$treeType]), $treeType->getDisplayName() . " Stairs"));
			self::register(new WoodenDoor($woodenDoorIds[$treeType], $treeType->getDisplayName() . " Door"));
		}

		static $sandstoneTypes = [
			Sandstone::NORMAL => "",
			Sandstone::CHISELED => "Chiseled ",
			Sandstone::CUT => "Cut ",
			Sandstone::SMOOTH => "Smooth "
		];
		foreach($sandstoneTypes as $variant => $prefix){
			self::register(new Sandstone(new BID(Block::SANDSTONE, $variant), $prefix . "Sandstone"));
			self::register(new Sandstone(new BID(Block::RED_SANDSTONE, $variant), $prefix . "Red Sandstone"));
		}

		/** @var int[]|\SplObjectStorage $glazedTerracottaIds */
		$glazedTerracottaIds = new \SplObjectStorage();
		$glazedTerracottaIds[DyeColor::WHITE()] = Block::WHITE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::ORANGE()] = Block::ORANGE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::MAGENTA()] = Block::MAGENTA_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_BLUE()] = Block::LIGHT_BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::YELLOW()] = Block::YELLOW_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIME()] = Block::LIME_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PINK()] = Block::PINK_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GRAY()] = Block::GRAY_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::LIGHT_GRAY()] = Block::SILVER_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::CYAN()] = Block::CYAN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::PURPLE()] = Block::PURPLE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLUE()] = Block::BLUE_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BROWN()] = Block::BROWN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::GREEN()] = Block::GREEN_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::RED()] = Block::RED_GLAZED_TERRACOTTA;
		$glazedTerracottaIds[DyeColor::BLACK()] = Block::BLACK_GLAZED_TERRACOTTA;

		foreach(DyeColor::getAll() as $color){
			self::register(new Carpet(new BID(Block::CARPET, $color->getMagicNumber()), $color->getDisplayName() . " Carpet"));
			self::register(new Concrete(new BID(Block::CONCRETE, $color->getMagicNumber()), $color->getDisplayName() . " Concrete"));
			self::register(new ConcretePowder(new BID(Block::CONCRETE_POWDER, $color->getMagicNumber()), $color->getDisplayName() . " Concrete Powder"));
			self::register(new Glass(new BID(Block::STAINED_GLASS, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass"));
			self::register(new GlassPane(new BID(Block::STAINED_GLASS_PANE, $color->getMagicNumber()), $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new GlazedTerracotta(new BID($glazedTerracottaIds[$color]), $color->getDisplayName() . " Glazed Terracotta"));
			self::register(new HardenedClay(new BID(Block::STAINED_CLAY, $color->getMagicNumber()), $color->getDisplayName() . " Stained Clay"));
			self::register(new HardenedGlass(new BID(Block::HARD_STAINED_GLASS, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass"));
			self::register(new HardenedGlassPane(new BID(Block::HARD_STAINED_GLASS_PANE, $color->getMagicNumber()), "Hardened " . $color->getDisplayName() . " Stained Glass Pane"));
			self::register(new Wool(new BID(Block::WOOL, $color->getMagicNumber()), $color->getDisplayName() . " Wool"));
		}

		static $wallTypes = [
			CobblestoneWall::ANDESITE_WALL => "Andesite",
			CobblestoneWall::BRICK_WALL => "Brick",
			CobblestoneWall::DIORITE_WALL => "Diorite",
			CobblestoneWall::END_STONE_BRICK_WALL => "End Stone Brick",
			CobblestoneWall::GRANITE_WALL => "Granite",
			CobblestoneWall::MOSSY_STONE_BRICK_WALL => "Mossy Stone Brick",
			CobblestoneWall::MOSSY_WALL => "Mossy Cobblestone",
			CobblestoneWall::NETHER_BRICK_WALL => "Nether Brick",
			CobblestoneWall::NONE_MOSSY_WALL => "Cobblestone",
			CobblestoneWall::PRISMARINE_WALL => "Prismarine",
			CobblestoneWall::RED_NETHER_BRICK_WALL => "Red Nether Brick",
			CobblestoneWall::RED_SANDSTONE_WALL => "Red Sandstone",
			CobblestoneWall::SANDSTONE_WALL => "Sandstone",
			CobblestoneWall::STONE_BRICK_WALL => "Stone Brick"
		];
		foreach($wallTypes as $magicNumber => $prefix){
			self::register(new CobblestoneWall(new BID(Block::COBBLESTONE_WALL, $magicNumber), $prefix . " Wall"));
		}

		//TODO: minecraft:acacia_button
		//TODO: minecraft:acacia_pressure_plate
		//TODO: minecraft:acacia_standing_sign
		//TODO: minecraft:acacia_trapdoor
		//TODO: minecraft:acacia_wall_sign
		//TODO: minecraft:andesite_stairs
		//TODO: minecraft:bamboo
		//TODO: minecraft:bamboo_sapling
		//TODO: minecraft:barrel
		//TODO: minecraft:barrier
		//TODO: minecraft:beacon
		//TODO: minecraft:bell
		//TODO: minecraft:birch_button
		//TODO: minecraft:birch_pressure_plate
		//TODO: minecraft:birch_standing_sign
		//TODO: minecraft:birch_trapdoor
		//TODO: minecraft:birch_wall_sign
		//TODO: minecraft:blast_furnace
		//TODO: minecraft:blue_ice
		//TODO: minecraft:bubble_column
		//TODO: minecraft:cartography_table
		//TODO: minecraft:carved_pumpkin
		//TODO: minecraft:cauldron
		//TODO: minecraft:chain_command_block
		//TODO: minecraft:chemical_heat
		//TODO: minecraft:chemistry_table
		//TODO: minecraft:chorus_flower
		//TODO: minecraft:chorus_plant
		//TODO: minecraft:command_block
		//TODO: minecraft:conduit
		//TODO: minecraft:coral
		//TODO: minecraft:coral_block
		//TODO: minecraft:coral_fan
		//TODO: minecraft:coral_fan_dead
		//TODO: minecraft:coral_fan_hang
		//TODO: minecraft:coral_fan_hang2
		//TODO: minecraft:coral_fan_hang3
		//TODO: minecraft:dark_oak_button
		//TODO: minecraft:dark_oak_pressure_plate
		//TODO: minecraft:dark_oak_trapdoor
		//TODO: minecraft:dark_prismarine_stairs
		//TODO: minecraft:darkoak_standing_sign
		//TODO: minecraft:darkoak_wall_sign
		//TODO: minecraft:diorite_stairs
		//TODO: minecraft:dispenser
		//TODO: minecraft:double_stone_slab3
		//TODO: minecraft:double_stone_slab4
		//TODO: minecraft:dragon_egg
		//TODO: minecraft:dried_kelp_block
		//TODO: minecraft:dropper
		//TODO: minecraft:element_0
		//TODO: minecraft:end_brick_stairs
		//TODO: minecraft:end_gateway
		//TODO: minecraft:end_portal
		//TODO: minecraft:fletching_table
		//TODO: minecraft:frosted_ice
		//TODO: minecraft:granite_stairs
		//TODO: minecraft:grindstone
		//TODO: minecraft:hopper
		//TODO: minecraft:jukebox
		//TODO: minecraft:jungle_button
		//TODO: minecraft:jungle_pressure_plate
		//TODO: minecraft:jungle_standing_sign
		//TODO: minecraft:jungle_trapdoor
		//TODO: minecraft:jungle_wall_sign
		//TODO: minecraft:kelp
		//TODO: minecraft:lantern
		//TODO: minecraft:lava_cauldron
		//TODO: minecraft:monster_egg
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
		//TODO: minecraft:portal
		//TODO: minecraft:powered_comparator
		//TODO: minecraft:prismarine_bricks_stairs
		//TODO: minecraft:prismarine_stairs
		//TODO: minecraft:red_nether_brick_stairs
		//TODO: minecraft:repeating_command_block
		//TODO: minecraft:scaffolding
		//TODO: minecraft:sea_pickle
		//TODO: minecraft:seagrass
		//TODO: minecraft:shulker_box
		//TODO: minecraft:slime
		//TODO: minecraft:smithing_table
		//TODO: minecraft:smoker
		//TODO: minecraft:smooth_quartz_stairs
		//TODO: minecraft:smooth_red_sandstone_stairs
		//TODO: minecraft:smooth_sandstone_stairs
		//TODO: minecraft:smooth_stone
		//TODO: minecraft:spruce_button
		//TODO: minecraft:spruce_pressure_plate
		//TODO: minecraft:spruce_standing_sign
		//TODO: minecraft:spruce_trapdoor
		//TODO: minecraft:spruce_wall_sign
		//TODO: minecraft:sticky_piston
		//TODO: minecraft:stone_slab3
		//TODO: minecraft:stone_slab4
		//TODO: minecraft:stripped_acacia_log
		//TODO: minecraft:stripped_birch_log
		//TODO: minecraft:stripped_dark_oak_log
		//TODO: minecraft:stripped_jungle_log
		//TODO: minecraft:stripped_oak_log
		//TODO: minecraft:stripped_spruce_log
		//TODO: minecraft:structure_block
		//TODO: minecraft:turtle_egg
		//TODO: minecraft:undyed_shulker_box
		//TODO: minecraft:unpowered_comparator
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
					if($v->getDamage() !== $m){
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
		self::$stateMasks[$index] = $block->getStateBitmask();
		self::$lightFilter[$index] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
		self::$diffusesSkyLight[$index] = $block->diffusesSkyLight();
		self::$blastResistance[$index] = $block->getBlastResistance();
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
			$block->position($pos->getLevel(), $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		}

		return $block;
	}

	public static function fromFullBlock(int $fullState, ?Position $pos = null) : Block{
		return self::get($fullState >> 4, $fullState & 0xf, $pos);
	}

	public static function getStateMask(int $id) : int{
		return self::$stateMasks[$id] ?? 0;
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

	public static function registerStaticRuntimeIdMappings() : void{
		/** @var mixed[] $runtimeIdMap */
		$runtimeIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "runtimeid_table.json"), true);
		$legacyIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "legacy_id_map.json"), true);
		foreach($runtimeIdMap as $k => $obj){
			//this has to use the json offset to make sure the mapping is consistent with what we send over network, even though we aren't using all the entries
			if(!isset($legacyIdMap[$obj["name"]])){
				continue;
			}
			self::registerMapping($k, $legacyIdMap[$obj["name"]], $obj["data"]);
		}
	}

	/**
	 * @internal
	 *
	 * @param int $id
	 * @param int $meta
	 *
	 * @return int
	 */
	public static function toStaticRuntimeId(int $id, int $meta = 0) : int{
		/*
		 * try id+meta first
		 * if not found, try id+0 (strip meta)
		 * if still not found, return update! block
		 */
		return self::$staticRuntimeIdMap[($id << 4) | $meta] ?? self::$staticRuntimeIdMap[$id << 4] ?? self::$staticRuntimeIdMap[BlockIds::INFO_UPDATE << 4];
	}

	/**
	 * @internal
	 *
	 * @param int $runtimeId
	 *
	 * @return int[] [id, meta]
	 */
	public static function fromStaticRuntimeId(int $runtimeId) : array{
		$v = self::$legacyIdMap[$runtimeId];
		return [$v >> 4, $v & 0xf];
	}

	private static function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		self::$staticRuntimeIdMap[($legacyId << 4) | $legacyMeta] = $staticRuntimeId;
		self::$legacyIdMap[$staticRuntimeId] = ($legacyId << 4) | $legacyMeta;
		self::$lastRuntimeId = max(self::$lastRuntimeId, $staticRuntimeId);
	}
}
