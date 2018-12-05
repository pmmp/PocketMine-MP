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

use pocketmine\block\utils\Color;
use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\block\utils\WoodType;
use pocketmine\item\Item;
use pocketmine\level\Position;

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

		self::registerBlock(new Air());

		self::registerBlock(new SmoothStone(Block::STONE, Stone::NORMAL, "Stone"));

		self::registerBlock(new Stone(Block::STONE, Stone::GRANITE, "Granite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_GRANITE, "Polished Granite"));
		self::registerBlock(new Stone(Block::STONE, Stone::DIORITE, "Diorite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_DIORITE, "Polished Diorite"));
		self::registerBlock(new Stone(Block::STONE, Stone::ANDESITE, "Andesite"));
		self::registerBlock(new Stone(Block::STONE, Stone::POLISHED_ANDESITE, "Polished Andesite"));

		self::registerBlock(new Grass());

		self::registerBlock(new Dirt(Block::DIRT, Dirt::NORMAL, "Dirt"));
		self::registerBlock(new CoarseDirt(Block::DIRT, Dirt::COARSE, "Coarse Dirt"));

		self::registerBlock(new Cobblestone());

		foreach(WoodType::ALL as $type){
			self::registerBlock(new Planks(Block::PLANKS, $type, WoodType::NAMES[$type] . " Planks"));
			self::registerBlock(new Sapling(Block::SAPLING, $type, WoodType::NAMES[$type] . " Sapling"));
			self::registerBlock(new WoodenFence(Block::FENCE, $type, WoodType::NAMES[$type] . " Fence"));
		}

		foreach(WoodType::ALL as $type){
			//TODO: find a better way to deal with this split
			self::registerBlock(new Log($type >= 4 ? Block::WOOD2 : Block::WOOD, $type & 0x03, WoodType::NAMES[$type] . " Log"));
			self::registerBlock(new Wood($type >= 4 ? Block::WOOD2 : Block::WOOD, ($type & 0x03) | 0b1100, WoodType::NAMES[$type] . " Wood"));
			self::registerBlock(new Leaves($type >= 4 ? Block::LEAVES2 : Block::LEAVES, $type & 0x03, $type, WoodType::NAMES[$type] . " Leaves"));
		}

		self::registerBlock(new Bedrock());
		self::registerBlock(new Water());
		self::registerBlock((new Water())->setStill()); //flattening hack
		self::registerBlock(new Lava());
		self::registerBlock((new Lava())->setStill()); //flattening hack

		self::registerBlock(new Sand(Block::SAND, 0, "Sand"));
		self::registerBlock(new Sand(Block::SAND, 1, "Red Sand"));

		self::registerBlock(new Gravel());
		self::registerBlock(new GoldOre());
		self::registerBlock(new IronOre());
		self::registerBlock(new CoalOre());
		self::registerBlock(new Sponge());
		self::registerBlock(new Glass(Block::GLASS, 0, "Glass"));
		self::registerBlock(new LapisOre());
		self::registerBlock(new Lapis());
		//TODO: DISPENSER

		static $sandstoneTypes = [
			Sandstone::NORMAL => "",
			Sandstone::CHISELED => "Chiseled ",
			Sandstone::SMOOTH => "Smooth "
		];
		foreach($sandstoneTypes as $variant => $prefix){
			self::registerBlock(new Sandstone(Block::SANDSTONE, $variant, $prefix . "Sandstone"));
			self::registerBlock(new Sandstone(Block::RED_SANDSTONE, $variant, $prefix . "Red Sandstone"));
		}

		self::registerBlock(new NoteBlock());
		self::registerBlock(new Bed());
		self::registerBlock(new PoweredRail());
		self::registerBlock(new DetectorRail());
		//TODO: STICKY_PISTON
		self::registerBlock(new Cobweb());

		self::registerBlock(new TallGrass(Block::TALL_GRASS, 0, "Fern"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 1, "Tall Grass"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 2, "Fern"));
		self::registerBlock(new TallGrass(Block::TALL_GRASS, 3, "Fern"));

		self::registerBlock(new DeadBush());
		//TODO: PISTON
		//TODO: PISTONARMCOLLISION

		foreach(Color::ALL as $color){
			self::registerBlock(new Wool(Block::WOOL, $color, Color::NAMES[$color] . " Wool"));
			self::registerBlock(new HardenedClay(Block::STAINED_CLAY, $color, Color::NAMES[$color] . " Stained Clay"));
			self::registerBlock(new Glass(Block::STAINED_GLASS, $color, Color::NAMES[$color] . " Stained Glass"));
			self::registerBlock(new GlassPane(Block::STAINED_GLASS_PANE, $color, Color::NAMES[$color] . " Stained Glass Pane"));
			self::registerBlock(new Carpet(Block::CARPET, $color, Color::NAMES[$color] . " Carpet"));
			self::registerBlock(new Concrete(Block::CONCRETE, $color, Color::NAMES[$color] . " Concrete"));
			self::registerBlock(new ConcretePowder(Block::CONCRETE_POWDER, $color, Color::NAMES[$color] . " Concrete Powder"));
		}

		self::registerBlock(new Dandelion());

		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_POPPY, "Poppy"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_BLUE_ORCHID, "Blue Orchid"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_ALLIUM, "Allium"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_AZURE_BLUET, "Azure Bluet"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_RED_TULIP, "Red Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_ORANGE_TULIP, "Orange Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_WHITE_TULIP, "White Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_PINK_TULIP, "Pink Tulip"));
		self::registerBlock(new Flower(Block::RED_FLOWER, Flower::TYPE_OXEYE_DAISY, "Oxeye Daisy"));

		self::registerBlock(new BrownMushroom());
		self::registerBlock(new RedMushroom());
		self::registerBlock(new Gold());
		self::registerBlock(new Iron());

		/** @var Slab[] $slabTypes */
		$slabTypes = [
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 0, "Stone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 1, "Sandstone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 2, "Fake Wooden"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 3, "Cobblestone"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 4, "Brick"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 5, "Stone Brick"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 6, "Quartz"),
			new StoneSlab(Block::STONE_SLAB, Block::DOUBLE_STONE_SLAB, 7, "Nether Brick"),
			new StoneSlab(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 0, "Red Sandstone"),
			new StoneSlab(Block::STONE_SLAB2, Block::DOUBLE_STONE_SLAB2, 1, "Purpur")
		];
		foreach(WoodType::ALL as $woodType){
			$slabTypes[] = new WoodenSlab($woodType);
		}
		foreach($slabTypes as $type){
			self::registerBlock($type);
			self::registerBlock(new DoubleSlab($type->getDoubleSlabId(), $type->getId(), $type->getVariant()));
		}

		self::registerBlock(new Bricks());
		self::registerBlock(new TNT());
		self::registerBlock(new Bookshelf());
		self::registerBlock(new MossyCobblestone());
		self::registerBlock(new Obsidian());
		self::registerBlock(new Torch());
		self::registerBlock(new Fire());
		self::registerBlock(new MonsterSpawner());
		self::registerBlock(new WoodenStairs(Block::OAK_STAIRS, 0, "Oak Stairs"));
		self::registerBlock(new Chest());
		self::registerBlock(new RedstoneWire());
		self::registerBlock(new DiamondOre());
		self::registerBlock(new Diamond());
		self::registerBlock(new CraftingTable());
		self::registerBlock(new Wheat());
		self::registerBlock(new Farmland());

		self::registerBlock(new Furnace());
		self::registerBlock((new Furnace())->setLit()); //flattening hack

		self::registerBlock(new SignPost());
		self::registerBlock(new WoodenDoor(Block::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR));
		self::registerBlock(new Ladder());
		self::registerBlock(new Rail());
		self::registerBlock(new CobblestoneStairs());
		self::registerBlock(new WallSign());
		self::registerBlock(new Lever());
		self::registerBlock(new StonePressurePlate());
		self::registerBlock(new IronDoor());
		self::registerBlock(new WoodenPressurePlate());
		self::registerBlock(new RedstoneOre());
		self::registerBlock((new RedstoneOre())->setLit()); //flattening hack
		self::registerBlock(new RedstoneTorch());
		self::registerBlock((new RedstoneTorch())->setLit(false)); //flattening hack
		self::registerBlock(new StoneButton());
		self::registerBlock(new SnowLayer());
		self::registerBlock(new Ice());
		self::registerBlock(new Snow());
		self::registerBlock(new Cactus());
		self::registerBlock(new Clay());
		self::registerBlock(new Sugarcane());
		//TODO: JUKEBOX

		self::registerBlock(new Pumpkin());
		self::registerBlock(new Netherrack());
		self::registerBlock(new SoulSand());
		self::registerBlock(new Glowstone());
		//TODO: PORTAL
		self::registerBlock(new LitPumpkin());
		self::registerBlock(new Cake());
		self::registerBlock(new RedstoneRepeater());
		self::registerBlock((new RedstoneRepeater())->setPowered());
		//TODO: INVISIBLEBEDROCK
		self::registerBlock(new Trapdoor());
		//TODO: MONSTER_EGG

		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::NORMAL, "Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::MOSSY, "Mossy Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::CRACKED, "Cracked Stone Bricks"));
		self::registerBlock(new StoneBricks(Block::STONE_BRICKS, StoneBricks::CHISELED, "Chiseled Stone Bricks"));

		self::registerBlock(new BrownMushroomBlock());
		self::registerBlock(new RedMushroomBlock());
		self::registerBlock(new IronBars());
		self::registerBlock(new GlassPane(Block::GLASS_PANE, 0, "Glass Pane"));
		self::registerBlock(new Melon());
		self::registerBlock(new PumpkinStem());
		self::registerBlock(new MelonStem());
		self::registerBlock(new Vine());
		self::registerBlock(new FenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"));
		self::registerBlock(new BrickStairs());
		self::registerBlock(new StoneBrickStairs());
		self::registerBlock(new Mycelium());
		self::registerBlock(new WaterLily());
		self::registerBlock(new NetherBrick(Block::NETHER_BRICK_BLOCK, 0, "Nether Bricks"));
		self::registerBlock(new NetherBrickFence());
		self::registerBlock(new NetherBrickStairs());
		self::registerBlock(new NetherWartPlant());
		self::registerBlock(new EnchantingTable());
		self::registerBlock(new BrewingStand());
		//TODO: CAULDRON_BLOCK
		//TODO: END_PORTAL
		self::registerBlock(new EndPortalFrame());
		self::registerBlock(new EndStone());
		//TODO: DRAGON_EGG
		self::registerBlock(new RedstoneLamp());
		self::registerBlock((new RedstoneLamp())->setLit()); //flattening hack
		//TODO: DROPPER
		self::registerBlock(new ActivatorRail());
		self::registerBlock(new CocoaBlock());
		self::registerBlock(new SandstoneStairs());
		self::registerBlock(new EmeraldOre());
		self::registerBlock(new EnderChest());
		self::registerBlock(new TripwireHook());
		self::registerBlock(new Tripwire());
		self::registerBlock(new Emerald());
		self::registerBlock(new WoodenStairs(Block::SPRUCE_STAIRS, 0, "Spruce Stairs"));
		self::registerBlock(new WoodenStairs(Block::BIRCH_STAIRS, 0, "Birch Stairs"));
		self::registerBlock(new WoodenStairs(Block::JUNGLE_STAIRS, 0, "Jungle Stairs"));
		//TODO: COMMAND_BLOCK
		//TODO: BEACON

		self::registerBlock(new CobblestoneWall(Block::COBBLESTONE_WALL, CobblestoneWall::NONE_MOSSY_WALL, "Cobblestone Wall"));
		self::registerBlock(new CobblestoneWall(Block::COBBLESTONE_WALL, CobblestoneWall::MOSSY_WALL, "Mossy Cobblestone Wall"));

		self::registerBlock(new FlowerPot());
		self::registerBlock(new Carrot());
		self::registerBlock(new Potato());
		self::registerBlock(new WoodenButton());
		self::registerBlock(new Skull());

		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_NORMAL, "Anvil"));
		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_SLIGHTLY_DAMAGED, "Slightly Damaged Anvil"));
		self::registerBlock(new Anvil(Block::ANVIL, Anvil::TYPE_VERY_DAMAGED, "Very Damaged Anvil"));

		self::registerBlock(new TrappedChest());
		self::registerBlock(new WeightedPressurePlateLight());
		self::registerBlock(new WeightedPressurePlateHeavy());
		//TODO: COMPARATOR_BLOCK
		//TODO: POWERED_COMPARATOR
		self::registerBlock(new DaylightSensor());
		self::registerBlock((new DaylightSensor())->setInverted()); //flattening hack

		self::registerBlock(new Redstone());
		self::registerBlock(new NetherQuartzOre());
		//TODO: HOPPER_BLOCK

		self::registerBlock(new Quartz(Block::QUARTZ_BLOCK, Quartz::NORMAL, "Quartz Block"));
		self::registerBlock(new class(Block::QUARTZ_BLOCK, Quartz::CHISELED, "Chiseled Quartz Block") extends Quartz{
			use PillarRotationTrait;
		});
		self::registerBlock(new class(Block::QUARTZ_BLOCK, Quartz::PILLAR, "Quartz Pillar") extends Quartz{
			use PillarRotationTrait;
		});

		self::registerBlock(new Purpur(Block::PURPUR_BLOCK, 0, "Purpur Block"));
		self::registerBlock(new class(Block::PURPUR_BLOCK, 2, "Purpur Pillar") extends Purpur{
			use PillarRotationTrait;
		});

		self::registerBlock(new QuartzStairs());

		self::registerBlock(new WoodenStairs(Block::ACACIA_STAIRS, 0, "Acacia Stairs"));
		self::registerBlock(new WoodenStairs(Block::DARK_OAK_STAIRS, 0, "Dark Oak Stairs"));
		//TODO: SLIME

		self::registerBlock(new IronTrapdoor());

		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::NORMAL, "Prismarine"));
		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::DARK, "Dark Prismarine"));
		self::registerBlock(new Prismarine(Block::PRISMARINE, Prismarine::BRICKS, "Prismarine Bricks"));

		self::registerBlock(new SeaLantern());
		self::registerBlock(new HayBale());

		self::registerBlock(new HardenedClay(Block::HARDENED_CLAY, 0, "Hardened Clay"));
		self::registerBlock(new Coal());
		self::registerBlock(new PackedIce());

		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 0, "Sunflower"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 1, "Lilac"));
		self::registerBlock(new DoubleTallGrass(Block::DOUBLE_PLANT, 2, "Double Tallgrass"));
		self::registerBlock(new DoubleTallGrass(Block::DOUBLE_PLANT, 3, "Large Fern"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 4, "Rose Bush"));
		self::registerBlock(new DoublePlant(Block::DOUBLE_PLANT, 5, "Peony"));

		self::registerBlock(new StandingBanner());
		self::registerBlock(new WallBanner());

		self::registerBlock(new RedSandstoneStairs());
		self::registerBlock(new FenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"));
		self::registerBlock(new FenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"));
		self::registerBlock(new FenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"));
		self::registerBlock(new FenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"));
		self::registerBlock(new FenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"));
		//TODO: REPEATING_COMMAND_BLOCK
		//TODO: CHAIN_COMMAND_BLOCK

		self::registerBlock(new WoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR));
		self::registerBlock(new WoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR));
		self::registerBlock(new WoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR));
		self::registerBlock(new WoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR));
		self::registerBlock(new WoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR));
		self::registerBlock(new GrassPath());
		self::registerBlock(new ItemFrame());
		//TODO: CHORUS_FLOWER

		self::registerBlock(new PurpurStairs());

		//TODO: UNDYED_SHULKER_BOX
		self::registerBlock(new EndStoneBricks());
		//TODO: FROSTED_ICE
		self::registerBlock(new EndRod());
		//TODO: END_GATEWAY

		self::registerBlock(new Magma());
		self::registerBlock(new NetherWartBlock());
		self::registerBlock(new NetherBrick(Block::RED_NETHER_BRICK, 0, "Red Nether Bricks"));
		self::registerBlock(new BoneBlock());

		//TODO: SHULKER_BOX
		self::registerBlock(new GlazedTerracotta(Block::PURPLE_GLAZED_TERRACOTTA, 0, "Purple Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::WHITE_GLAZED_TERRACOTTA, 0, "White Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::ORANGE_GLAZED_TERRACOTTA, 0, "Orange Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::MAGENTA_GLAZED_TERRACOTTA, 0, "Magenta Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::LIGHT_BLUE_GLAZED_TERRACOTTA, 0, "Light Blue Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::YELLOW_GLAZED_TERRACOTTA, 0, "Yellow Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::LIME_GLAZED_TERRACOTTA, 0, "Lime Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::PINK_GLAZED_TERRACOTTA, 0, "Pink Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::GRAY_GLAZED_TERRACOTTA, 0, "Grey Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::SILVER_GLAZED_TERRACOTTA, 0, "Light Grey Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::CYAN_GLAZED_TERRACOTTA, 0, "Cyan Glazed Terracotta"));

		self::registerBlock(new GlazedTerracotta(Block::BLUE_GLAZED_TERRACOTTA, 0, "Blue Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::BROWN_GLAZED_TERRACOTTA, 0, "Brown Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::GREEN_GLAZED_TERRACOTTA, 0, "Green Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::RED_GLAZED_TERRACOTTA, 0, "Red Glazed Terracotta"));
		self::registerBlock(new GlazedTerracotta(Block::BLACK_GLAZED_TERRACOTTA, 0, "Black Glazed Terracotta"));

		//TODO: CHORUS_PLANT

		self::registerBlock(new Podzol());
		self::registerBlock(new Beetroot());
		self::registerBlock(new Stonecutter());
		self::registerBlock(new GlowingObsidian());
		self::registerBlock(new NetherReactor());
		//TODO: INFO_UPDATE
		//TODO: INFO_UPDATE2
		//TODO: MOVINGBLOCK
		//TODO: OBSERVER
		//TODO: STRUCTURE_BLOCK

		//TODO: RESERVED6
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
	public static function registerBlock(Block $block, bool $override = false) : void{
		$id = $block->getId();
		$variant = $block->getVariant();


		$stateMask = $block->getStateBitmask();
		if(($variant & $stateMask) !== 0){
			throw new \InvalidArgumentException("Block variant collides with state bitmask");
		}

		if(!$override and self::isRegistered($id, $variant)){
			throw new \InvalidArgumentException("Block registration conflicts with an existing block");
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
			$v->readStateFromMeta($m & $stateMask);
			if($v->getDamage() === $m){ //don't register anything that isn't the same when we read it back again
				self::fillStaticArrays($index, $v);
			}
		}

		if(!self::isRegistered($id, $variant)){
			self::fillStaticArrays(($id << 4) | $variant, $block); //register default state mapped to variant, for blocks which don't use 0 as valid state
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
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
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
			$block = new UnknownBlock($id, $meta);
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
		foreach($runtimeIdMap as $k => $obj){
			self::registerMapping($k, $obj["id"], $obj["data"]);
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
