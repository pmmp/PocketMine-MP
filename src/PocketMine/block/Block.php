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
namespace PocketMine\Block;

use PocketMine\Item\Item;
use PocketMine\Level\Level;
use PocketMine\Level\Position;
use PocketMine\Player;

abstract class Block extends Position{
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


	public static $list = array();
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
	public $level = false;
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
			$block->setMetadata($meta);
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
	final public function getMetadata(){
		return $this->meta;
	}

	/**
	 * @param int $meta
	 */
	final public function setMetadata($meta){
		$this->meta = $meta & 0x0F;
	}

	/**
	 * Sets the block position to a new Position object
	 *
	 * @param Position $v
	 */
	final public function position(Position $v){
		$this->level = $v->level;
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
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
			return array();
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
	 *
	 * @return Block
	 */
	public function getSide($side){
		$v = parent::getSide($side);
		if($this->level instanceof Level){
			return $this->level->getBlock($v);
		}

		return $v;
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
}
