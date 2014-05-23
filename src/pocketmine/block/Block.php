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

/**
 * All Block classes are in here
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

abstract class Block extends Position implements Metadatable{
	const AIR = 0;
	const STONE = 1;
	const GRASS = 2;
	const DIRT = 3;
	const COBBLESTONE = 4;
	const COBBLE = 4;
	const PLANK = 5;
	const PLANKS = 5;
	const WOODEN_PLANK = 5;
	const WOODEN_PLANKS = 5;
	const SAPLING = 6;
	const SAPLINGS = 6;
	const BEDROCK = 7;
	const WATER = 8;
	const STILL_WATER = 9;
	const LAVA = 10;
	const STILL_LAVA = 11;
	const SAND = 12;
	const GRAVEL = 13;
	const GOLD_ORE = 14;
	const IRON_ORE = 15;
	const COAL_ORE = 16;
	const WOOD = 17;
	const TRUNK = 17;
	const LOG = 17;
	const LEAVES = 18;
	const LEAVE = 18;
	const SPONGE = 19;
	const GLASS = 20;
	const LAPIS_ORE = 21;
	const LAPIS_BLOCK = 22;

	const SANDSTONE = 24;

	const BED_BLOCK = 26;


	const COBWEB = 30;
	const TALL_GRASS = 31;
	const BUSH = 32;
	const DEAD_BUSH = 32;
	const WOOL = 35;
	const DANDELION = 37;
	const ROSE = 38;
	const CYAN_FLOWER = 38;
	const BROWN_MUSHROOM = 39;
	const RED_MUSHROOM = 40;
	const GOLD_BLOCK = 41;
	const IRON_BLOCK = 42;
	const DOUBLE_SLAB = 43;
	const DOUBLE_SLABS = 43;
	const SLAB = 44;
	const SLABS = 44;
	const BRICKS = 45;
	const BRICKS_BLOCK = 45;
	const TNT = 46;
	const BOOKSHELF = 47;
	const MOSS_STONE = 48;
	const MOSSY_STONE = 48;
	const OBSIDIAN = 49;
	const TORCH = 50;
	const FIRE = 51;

	const WOOD_STAIRS = 53;
	const WOODEN_STAIRS = 53;
	const OAK_WOOD_STAIRS = 53;
	const OAK_WOODEN_STAIRS = 53;
	const CHEST = 54;

	const DIAMOND_ORE = 56;
	const DIAMOND_BLOCK = 57;
	const CRAFTING_TABLE = 58;
	const WORKBENCH = 58;
	const WHEAT_BLOCK = 59;
	const FARMLAND = 60;
	const FURNACE = 61;
	const BURNING_FURNACE = 62;
	const LIT_FURNACE = 62;
	const SIGN_POST = 63;
	const DOOR_BLOCK = 64;
	const WOODEN_DOOR_BLOCK = 64;
	const WOOD_DOOR_BLOCK = 64;
	const LADDER = 65;

	const COBBLE_STAIRS = 67;
	const COBBLESTONE_STAIRS = 67;
	const WALL_SIGN = 68;

	const IRON_DOOR_BLOCK = 71;

	const REDSTONE_ORE = 73;
	const GLOWING_REDSTONE_ORE = 74;
	const LIT_REDSTONE_ORE = 74;

	const SNOW = 78;
	const SNOW_LAYER = 78;
	const ICE = 79;
	const SNOW_BLOCK = 80;
	const CACTUS = 81;
	const CLAY_BLOCK = 82;
	const REEDS = 83;
	const SUGARCANE_BLOCK = 83;

	const FENCE = 85;
	const PUMPKIN = 86;
	const NETHERRACK = 87;
	const SOUL_SAND = 88;
	const GLOWSTONE = 89;
	const GLOWSTONE_BLOCK = 89;


	const LIT_PUMPKIN = 91;
	const JACK_O_LANTERN = 91;
	const CAKE_BLOCK = 92;

	const TRAPDOOR = 96;

	const STONE_BRICKS = 98;
	const STONE_BRICK = 98;

	const IRON_BAR = 101;
	const IRON_BARS = 101;
	const GLASS_PANE = 102;
	const GLASS_PANEL = 102;
	const MELON_BLOCK = 103;
	const PUMPKIN_STEM = 104;
	const MELON_STEM = 105;

	const FENCE_GATE = 107;
	const BRICK_STAIRS = 108;
	const STONE_BRICK_STAIRS = 109;

	const NETHER_BRICKS = 112;
	const NETHER_BRICK_BLOCK = 112;

	const NETHER_BRICKS_STAIRS = 114;

	const SANDSTONE_STAIRS = 128;

	const SPRUCE_WOOD_STAIRS = 134;
	const SPRUCE_WOODEN_STAIRS = 134;
	const BIRCH_WOOD_STAIRS = 135;
	const BIRCH_WOODEN_STAIRS = 135;
	const JUNGLE_WOOD_STAIRS = 136;
	const JUNGLE_WOODEN_STAIRS = 136;

	const COBBLE_WALL = 139;
	const STONE_WALL = 139;
	const COBBLESTONE_WALL = 139;

	const CARROT_BLOCK = 141;
	const POTATO_BLOCK = 142;

	const QUARTZ_BLOCK = 155;
	const QUARTZ_STAIRS = 156;
	const DOUBLE_WOOD_SLAB = 157;
	const DOUBLE_WOODEN_SLAB = 157;
	const DOUBLE_WOOD_SLABS = 157;
	const DOUBLE_WOODEN_SLABS = 157;
	const WOOD_SLAB = 158;
	const WOODEN_SLAB = 158;
	const WOOD_SLABS = 158;
	const WOODEN_SLABS = 158;

	const HAY_BALE = 170;
	const CARPET = 171;

	const COAL_BLOCK = 173;

	const BEETROOT_BLOCK = 244;
	const STONECUTTER = 245;
	const GLOWING_OBSIDIAN = 246;
	const NETHER_REACTOR = 247;

	public static $creative = array(
		//Building
		[Item::STONE, 0],
		[Item::COBBLESTONE, 0],
		[Item::STONE_BRICKS, 0],
		[Item::STONE_BRICKS, 1],
		[Item::STONE_BRICKS, 2],
		[Item::MOSS_STONE, 0],
		[Item::WOODEN_PLANKS, 0],
		[Item::WOODEN_PLANKS, 1],
		[Item::WOODEN_PLANKS, 2],
		[Item::WOODEN_PLANKS, 3],
		[Item::BRICKS, 0],

		[Item::DIRT, 0],
		[Item::GRASS, 0],
		[Item::CLAY_BLOCK, 0],
		[Item::SANDSTONE, 0],
		[Item::SANDSTONE, 1],
		[Item::SANDSTONE, 2],
		[Item::SAND, 0],
		[Item::GRAVEL, 0],
		[Item::TRUNK, 0],
		[Item::TRUNK, 1],
		[Item::TRUNK, 2],
		[Item::TRUNK, 3],
		[Item::NETHER_BRICKS, 0],
		[Item::NETHERRACK, 0],
		[Item::BEDROCK, 0],
		[Item::COBBLESTONE_STAIRS, 0],
		[Item::OAK_WOODEN_STAIRS, 0],
		[Item::SPRUCE_WOODEN_STAIRS, 0],
		[Item::BIRCH_WOODEN_STAIRS, 0],
		[Item::JUNGLE_WOODEN_STAIRS, 0],
		[Item::BRICK_STAIRS, 0],
		[Item::SANDSTONE_STAIRS, 0],
		[Item::STONE_BRICK_STAIRS, 0],
		[Item::NETHER_BRICKS_STAIRS, 0],
		[Item::QUARTZ_STAIRS, 0],
		[Item::SLAB, 0],
		[Item::SLAB, 1],
		[Item::WOODEN_SLAB, 0],
		[Item::WOODEN_SLAB, 1],
		[Item::WOODEN_SLAB, 2],
		[Item::WOODEN_SLAB, 3],
		[Item::SLAB, 3],
		[Item::SLAB, 4],
		[Item::SLAB, 5],
		[Item::SLAB, 6],
		[Item::QUARTZ_BLOCK, 0],
		[Item::QUARTZ_BLOCK, 1],
		[Item::QUARTZ_BLOCK, 2],
		[Item::COAL_ORE, 0],
		[Item::IRON_ORE, 0],
		[Item::GOLD_ORE, 0],
		[Item::DIAMOND_ORE, 0],
		[Item::LAPIS_ORE, 0],
		[Item::REDSTONE_ORE, 0],
		[Item::OBSIDIAN, 0],
		[Item::ICE, 0],
		[Item::SNOW_BLOCK, 0],

		//Decoration
		[Item::COBBLESTONE_WALL, 0],
		[Item::COBBLESTONE_WALL, 1],
		[Item::GOLD_BLOCK, 0],
		[Item::IRON_BLOCK, 0],
		[Item::DIAMOND_BLOCK, 0],
		[Item::LAPIS_BLOCK, 0],
		[Item::COAL_BLOCK, 0],
		[Item::SNOW_LAYER, 0],
		[Item::GLASS, 0],
		[Item::GLOWSTONE_BLOCK, 0],
		[Item::NETHER_REACTOR, 0],
		[Item::WOOL, 0],
		[Item::WOOL, 7],
		[Item::WOOL, 6],
		[Item::WOOL, 5],
		[Item::WOOL, 4],
		[Item::WOOL, 3],
		[Item::WOOL, 2],
		[Item::WOOL, 1],
		[Item::WOOL, 15],
		[Item::WOOL, 14],
		[Item::WOOL, 13],
		[Item::WOOL, 12],
		[Item::WOOL, 11],
		[Item::WOOL, 10],
		[Item::WOOL, 9],
		[Item::WOOL, 8],
		[Item::LADDER, 0],
		[Item::SPONGE, 0],
		[Item::GLASS_PANE, 0],
		[Item::WOODEN_DOOR, 0],
		[Item::TRAPDOOR, 0],
		[Item::FENCE, 0],
		[Item::FENCE_GATE, 0],
		[Item::IRON_BARS, 0],
		[Item::BED, 0],
		[Item::BOOKSHELF, 0],
		[Item::PAINTING, 0],
		[Item::WORKBENCH, 0],
		[Item::STONECUTTER, 0],
		[Item::CHEST, 0],
		[Item::FURNACE, 0],
		[Item::DANDELION, 0],
		[Item::CYAN_FLOWER, 0],
		[Item::BROWN_MUSHROOM, 0],
		[Item::RED_MUSHROOM, 0],
		[Item::CACTUS, 0],
		[Item::MELON_BLOCK, 0],
		[Item::PUMPKIN, 0],
		[Item::LIT_PUMPKIN, 0],
		[Item::COBWEB, 0],
		[Item::HAY_BALE, 0],
		[Item::TALL_GRASS, 1],
		[Item::TALL_GRASS, 2],
		[Item::DEAD_BUSH, 0],
		[Item::SAPLING, 0],
		[Item::SAPLING, 1],
		[Item::SAPLING, 2],
		[Item::SAPLING, 3],
		[Item::LEAVES, 0],
		[Item::LEAVES, 1],
		[Item::LEAVES, 2],
		[Item::LEAVES, 3],
		[Item::CAKE, 0],
		[Item::SIGN, 0],
		[Item::CARPET, 0],
		[Item::CARPET, 7],
		[Item::CARPET, 6],
		[Item::CARPET, 5],
		[Item::CARPET, 4],
		[Item::CARPET, 3],
		[Item::CARPET, 2],
		[Item::CARPET, 1],
		[Item::CARPET, 15],
		[Item::CARPET, 14],
		[Item::CARPET, 13],
		[Item::CARPET, 12],
		[Item::CARPET, 11],
		[Item::CARPET, 10],
		[Item::CARPET, 9],
		[Item::CARPET, 8],

		//Tools
		//[Item::RAILS, 0],
		//[Item::POWERED_RAILS, 0],
		[Item::TORCH, 0],
		[Item::BUCKET, 0],
		[Item::BUCKET, 8],
		[Item::BUCKET, 10],
		[Item::TNT, 0],
		[Item::IRON_HOE, 0],
		[Item::IRON_SWORD, 0],
		[Item::BOW, 0],
		[Item::SHEARS, 0],
		[Item::FLINT_AND_STEEL, 0],
		[Item::CLOCK, 0],
		[Item::COMPASS, 0],
		[Item::MINECART, 0],
		[Item::SPAWN_EGG, 10], //Chicken
		[Item::SPAWN_EGG, 11], //Cow
		[Item::SPAWN_EGG, 12], //Pig
		[Item::SPAWN_EGG, 13], //Sheep
		//TODO: Replace with Entity constants


		//Seeds
		[Item::SUGARCANE, 0],
		[Item::WHEAT, 0],
		[Item::SEEDS, 0],
		[Item::MELON_SEEDS, 0],
		[Item::PUMPKIN_SEEDS, 0],
		[Item::CARROT, 0],
		[Item::POTATO, 0],
		[Item::BEETROOT_SEEDS, 0],
		[Item::EGG, 0],
		[Item::DYE, 0],
		[Item::DYE, 7],
		[Item::DYE, 6],
		[Item::DYE, 5],
		[Item::DYE, 4],
		[Item::DYE, 3],
		[Item::DYE, 2],
		[Item::DYE, 1],
		[Item::DYE, 15],
		[Item::DYE, 14],
		[Item::DYE, 13],
		[Item::DYE, 12],
		[Item::DYE, 11],
		[Item::DYE, 10],
		[Item::DYE, 9],
		[Item::DYE, 8],

	);

	/** @var Block[] */
	public static $list = [];
	protected $id;
	protected $meta;
	protected $name;
	protected $breakTime;
	protected $hardness;
	public $isActivable = false;
	public $breakable = true;
	public $isFlowable = false;
	public $isSolid = true;
	public $isTransparent = false;
	public $isReplaceable = false;
	public $isPlaceable = true;
	public $hasPhysics = false;
	public $isLiquid = false;
	public $isFullBlock = true;
	public $x = 0;
	public $y = 0;
	public $z = 0;

	public static function init(){
		if(count(self::$list) === 0){
			self::$list = array(
				self::AIR => new Air(),
				self::STONE => new Stone(),
				self::GRASS => new Grass(),
				self::DIRT => new Dirt(),
				self::COBBLESTONE => new Cobblestone(),
				self::PLANKS => new Planks(),
				self::SAPLING => new Sapling(),
				self::BEDROCK => new Bedrock(),
				self::WATER => new Water(),
				self::STILL_WATER => new StillWater(),
				self::LAVA => new Lava(),
				self::STILL_LAVA => new StillLava(),
				self::SAND => new Sand(),
				self::GRAVEL => new Gravel(),
				self::GOLD_ORE => new GoldOre(),
				self::IRON_ORE => new IronOre(),
				self::COAL_ORE => new CoalOre(),
				self::WOOD => new Wood(),
				self::LEAVES => new Leaves(),
				self::SPONGE => new Sponge(),
				self::GLASS => new Glass(),
				self::LAPIS_ORE => new LapisOre(),
				self::LAPIS_BLOCK => new Lapis(),
				self::SANDSTONE => new Sandstone(),
				self::BED_BLOCK => new Bed(),
				self::COBWEB => new Cobweb(),
				self::TALL_GRASS => new TallGrass(),
				self::DEAD_BUSH => new DeadBush(),
				self::WOOL => new Wool(),
				self::DANDELION => new Dandelion(),
				self::CYAN_FLOWER => new CyanFlower(),
				self::BROWN_MUSHROOM => new BrownMushroom(),
				self::RED_MUSHROOM => new RedMushroom(),
				self::GOLD_BLOCK => new Gold(),
				self::IRON_BLOCK => new Iron(),
				self::DOUBLE_SLAB => new DoubleSlab(),
				self::SLAB => new Slab(),
				self::BRICKS_BLOCK => new Bricks(),
				self::TNT => new TNT(),
				self::BOOKSHELF => new Bookshelf(),
				self::MOSS_STONE => new MossStone(),
				self::OBSIDIAN => new Obsidian(),
				self::TORCH => new Torch(),
				self::FIRE => new Fire(),

				self::WOOD_STAIRS => new WoodStairs(),
				self::CHEST => new Chest(),

				self::DIAMOND_ORE => new DiamondOre(),
				self::DIAMOND_BLOCK => new Diamond(),
				self::WORKBENCH => new Workbench(),
				self::WHEAT_BLOCK => new Wheat(),
				self::FARMLAND => new Farmland(),
				self::FURNACE => new Furnace(),
				self::BURNING_FURNACE => new BurningFurnace(),
				self::SIGN_POST => new SignPost(),
				self::WOOD_DOOR_BLOCK => new WoodDoor(),
				self::LADDER => new Ladder(),

				self::COBBLESTONE_STAIRS => new CobblestoneStairs(),
				self::WALL_SIGN => new WallSign(),

				self::IRON_DOOR_BLOCK => new IronDoor(),
				self::REDSTONE_ORE => new RedstoneOre(),
				self::GLOWING_REDSTONE_ORE => new GlowingRedstoneOre(),

				self::SNOW_LAYER => new SnowLayer(),
				self::ICE => new Ice(),
				self::SNOW_BLOCK => new Snow(),
				self::CACTUS => new Cactus(),
				self::CLAY_BLOCK => new Clay(),
				self::SUGARCANE_BLOCK => new Sugarcane(),

				self::FENCE => new Fence(),
				self::PUMPKIN => new Pumpkin(),
				self::NETHERRACK => new Netherrack(),
				self::SOUL_SAND => new SoulSand(),
				self::GLOWSTONE_BLOCK => new Glowstone(),

				self::LIT_PUMPKIN => new LitPumpkin(),
				self::CAKE_BLOCK => new Cake(),

				self::TRAPDOOR => new Trapdoor(),

				self::STONE_BRICKS => new StoneBricks(),

				self::IRON_BARS => new IronBars(),
				self::GLASS_PANE => new GlassPane(),
				self::MELON_BLOCK => new Melon(),
				self::PUMPKIN_STEM => new PumpkinStem(),
				self::MELON_STEM => new MelonStem(),

				self::FENCE_GATE => new FenceGate(),
				self::BRICK_STAIRS => new BrickStairs(),
				self::STONE_BRICK_STAIRS => new StoneBrickStairs(),

				self::NETHER_BRICKS => new NetherBrick(),

				self::NETHER_BRICKS_STAIRS => new NetherBrickStairs(),

				self::SANDSTONE_STAIRS => new SandstoneStairs(),

				self::SPRUCE_WOOD_STAIRS => new SpruceWoodStairs(),
				self::BIRCH_WOOD_STAIRS => new BirchWoodStairs(),
				self::JUNGLE_WOOD_STAIRS => new JungleWoodStairs(),
				self::STONE_WALL => new StoneWall(),

				self::CARROT_BLOCK => new Carrot(),
				self::POTATO_BLOCK => new Potato(),

				self::QUARTZ_BLOCK => new Quartz(),
				self::QUARTZ_STAIRS => new QuartzStairs(),
				self::DOUBLE_WOOD_SLAB => new DoubleWoodSlab(),
				self::WOOD_SLAB => new WoodSlab(),

				self::HAY_BALE => new HayBale(),
				self::CARPET => new Carpet(),

				self::COAL_BLOCK => new Coal(),

				self::BEETROOT_BLOCK => new Beetroot(),
				self::STONECUTTER => new Stonecutter(),
				self::GLOWING_OBSIDIAN => new GlowingObsidian(),
			);
		}
	}

	/**
	 * @param int      $id
	 * @param int      $meta
	 * @param Position $pos
	 *
	 * @return Block
	 */
	public static function get($id, $meta = 0, Position $pos = null){
		if(isset(self::$list[$id])){
			$block = clone self::$list[$id];
			$block->setDamage($meta);
		}else{
			$block = new Generic($id, $meta);
		}
		if($pos instanceof Position){
			$block->position($pos);
		}

		return $block;
	}

	/**
	 * @param int    $id
	 * @param int    $meta
	 * @param string $name
	 */
	public function __construct($id, $meta = 0, $name = "Unknown"){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
		$this->name = $name;
		$this->breakTime = 0.20;
		$this->hardness = 10;
	}

	/**
	 * @return int
	 */
	final public function getHardness(){
		return $this->hardness;
	}

	/**
	 * @return string
	 */
	final public function getName(){
		return $this->name;
	}

	/**
	 * @return int
	 */
	final public function getID(){
		return $this->id;
	}

	/**
	 * @return int
	 */
	final public function getDamage(){
		return $this->meta;
	}

	/**
	 * @param int $meta
	 */
	final public function setDamage($meta){
		$this->meta = $meta & 0x0F;
	}

	/**
	 * Sets the block position to a new Position object
	 *
	 * @param Position $v
	 *
	 * @throws \RuntimeException
	 */
	final public function position(Position $v){
		if(!$v->isValid()){
			throw new \RuntimeException("Undefined Level reference");
		}
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->setLevel($v->getLevel(), false);
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item){
		if(!isset(self::$list[$this->id])){ //Unknown blocks
			return [];
		}else{
			return array(
				array($this->id, $this->meta, 1),
			);
		}
	}

	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @param Item $item
	 *
	 * @return float
	 */
	public function getBreakTime(Item $item){
		return $this->breakTime;
	}

	/**
	 * Returns the Block on the side $side, works like Vector3::side()
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Block
	 */
	public function getSide($side, $step = 1){
		$v = parent::getSide($side, $step);
		if($this->isValid()){
			return $this->getLevel()->getBlock($v);
		}

		return Block::get(Item::AIR, 0, $v);
	}

	/**
	 * @return string
	 */
	final public function __toString(){
		return "Block " . $this->name . " (" . $this->id . ":" . $this->meta . ")";
	}

	/**
	 * Returns if the item can be broken with an specific Item
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	abstract function isBreakable(Item $item);

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item $item
	 *
	 * @return mixed
	 */
	abstract function onBreak(Item $item);

	/**
	 * Places the Block, using block space and block target, and side. Returns if the block has been placed.
	 *
	 * @param Item   $item
	 * @param Block  $block
	 * @param Block  $target
	 * @param int    $face
	 * @param float  $fx
	 * @param float  $fy
	 * @param float  $fz
	 * @param Player $player = null
	 *
	 * @return bool
	 */
	abstract function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null);

	/**
	 * Do actions when activated by Item. Returns if it has done anything
	 *
	 * @param Item   $item
	 * @param Player $player
	 *
	 * @return bool
	 */
	abstract function onActivate(Item $item, Player $player = null);

	/**
	 * Fires a block update on the Block
	 *
	 * @param int $type
	 *
	 * @return void
	 */
	abstract function onUpdate($type);

	//TODO: Level block metadata

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		//$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return null; //return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return false; //return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		//$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}
}
