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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Block extends Position implements BlockIds, Metadatable{

	/** @var \SplFixedArray */
	public static $list = null;
	/** @var \SplFixedArray */
	public static $fullList = null;

	/** @var \SplFixedArray */
	public static $light = null;
	/** @var \SplFixedArray */
	public static $lightFilter = null;
	/** @var \SplFixedArray */
	public static $solid = null;
	/** @var \SplFixedArray */
	public static $hardness = null;
	/** @var \SplFixedArray */
	public static $transparent = null;
	/** @var \SplFixedArray */
	public static $diffusesSkyLight = null;

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(256);
			self::$fullList = new \SplFixedArray(4096);
			self::$light = new \SplFixedArray(256);
			self::$lightFilter = new \SplFixedArray(256);
			self::$solid = new \SplFixedArray(256);
			self::$hardness = new \SplFixedArray(256);
			self::$transparent = new \SplFixedArray(256);

			self::registerBlock(new Air());
			self::registerBlock(new Stone());
			self::registerBlock(new Grass());
			self::registerBlock(new Dirt());
			self::registerBlock((new Cobblestone(Block::COBBLESTONE))->setName("Cobblestone"));
			self::registerBlock(new Planks());
			self::registerBlock(new Sapling());
			self::registerBlock(new Bedrock());
			self::registerBlock(new FlowingWater());
			self::registerBlock(new StillWater());
			self::registerBlock(new FlowingLava());
			self::registerBlock(new StillLava());
			self::registerBlock(new Sand());
			self::registerBlock(new Gravel());
			self::registerBlock(new GoldOre());
			self::registerBlock(new IronOre());
			self::registerBlock(new CoalOre());
			self::registerBlock(new Wood());
			self::registerBlock(new Leaves());
			self::registerBlock(new Sponge());
			self::registerBlock(new Glass());
			self::registerBlock(new LapisOre());
			self::registerBlock(new Lapis());
			self::registerBlock(new ActivatorRail());
			self::registerBlock(new CocoaBlock());
			self::registerBlock(new Sandstone());
			self::registerBlock(new NoteBlock());
			self::registerBlock(new Bed());
			self::registerBlock(new PoweredRail());
			self::registerBlock(new DetectorRail());
			self::registerBlock(new Cobweb());
			self::registerBlock(new TallGrass());
			self::registerBlock(new DeadBush());
			self::registerBlock(new Wool());
			self::registerBlock(new Dandelion());
			self::registerBlock(new Flower());
			self::registerBlock(new BrownMushroom());
			self::registerBlock(new RedMushroom());
			self::registerBlock(new Gold());
			self::registerBlock(new Iron());
			self::registerBlock(new DoubleStoneSlab());
			self::registerBlock(new StoneSlab());
			self::registerBlock(new Bricks());
			self::registerBlock(new TNT());
			self::registerBlock(new Bookshelf());
			self::registerBlock((new Cobblestone(Block::MOSSY_COBBLESTONE))->setName("Moss Stone"));
			self::registerBlock(new Obsidian());
			self::registerBlock(new Torch());
			self::registerBlock(new Fire());
			self::registerBlock(new MonsterSpawner());
			self::registerBlock((new WoodenStairs(Block::OAK_STAIRS))->setName("Oak Wood Stairs"));
			self::registerBlock(new Chest());

			self::registerBlock(new DiamondOre());
			self::registerBlock(new Diamond());
			self::registerBlock(new CraftingTable());
			self::registerBlock(new Wheat());
			self::registerBlock(new Farmland());
			self::registerBlock(new Furnace());
			self::registerBlock(new BurningFurnace());
			self::registerBlock(new StandingSign());
			self::registerBlock((new WoodenDoor(Block::WOODEN_DOOR_BLOCK))->setName("Wooden Door Block"));
			self::registerBlock((new WoodenDoor(Block::SPRUCE_DOOR_BLOCK))->setName("Spruce Door Block"));
			self::registerBlock((new WoodenDoor(Block::BIRCH_DOOR_BLOCK))->setName("Birch Door Block"));
			self::registerBlock((new WoodenDoor(Block::JUNGLE_DOOR_BLOCK))->setName("Jungle Door Block"));
			self::registerBlock((new WoodenDoor(Block::ACACIA_DOOR_BLOCK))->setName("Acacia Door Block"));
			self::registerBlock((new WoodenDoor(Block::DARK_OAK_DOOR_BLOCK))->setName("Dark Oak Door Block"));
			self::registerBlock(new Ladder());
			self::registerBlock(new Rail());

			self::registerBlock(new CobblestoneStairs());
			self::registerBlock(new WallSign());
			self::registerBlock(new Lever());
			self::registerBlock(new StonePressurePlate());
			self::registerBlock(new IronDoor());
			self::registerBlock(new WoodenPressurePlate());
			self::registerBlock(new RedstoneOre());
			self::registerBlock(new GlowingRedstoneOre());

			self::registerBlock(new RedstoneTorch());
			self::registerBlock(new LitRedstoneTorch());
			self::registerBlock(new StoneButton());
			self::registerBlock(new SnowLayer());
			self::registerBlock(new Ice());
			self::registerBlock(new Snow());
			self::registerBlock(new Cactus());
			self::registerBlock(new Clay());
			self::registerBlock(new Sugarcane());

			self::registerBlock(new Fence());
			self::registerBlock(new Pumpkin());
			self::registerBlock(new Netherrack());
			self::registerBlock(new SoulSand());
			self::registerBlock(new Glowstone());

			self::registerBlock(new LitPumpkin());
			self::registerBlock(new Cake());

			self::registerBlock(new Trapdoor());

			self::registerBlock(new StoneBricks());

			self::registerBlock(new IronBars());
			self::registerBlock(new GlassPane());
			self::registerBlock(new Melon());
			self::registerBlock(new PumpkinStem());
			self::registerBlock(new MelonStem());
			self::registerBlock(new Vine());
			self::registerBlock((new FenceGate(Block::OAK_FENCE_GATE))->setName("Oak Fence Gate"));
			self::registerBlock(new BrickStairs());
			self::registerBlock(new StoneBrickStairs());

			self::registerBlock(new Mycelium());
			self::registerBlock(new WaterLily());
			self::registerBlock(new NetherBrick());
			self::registerBlock(new NetherBrickFence());
			self::registerBlock(new NetherBrickStairs());
			self::registerBlock(new NetherWartPlant());

			self::registerBlock(new EnchantingTable());
			self::registerBlock(new BrewingStand());
			self::registerBlock(new EndPortalFrame());
			self::registerBlock(new EndStone());
			self::registerBlock(new RedstoneLamp());
			self::registerBlock(new LitRedstoneLamp());
			self::registerBlock(new SandstoneStairs());
			self::registerBlock(new EmeraldOre());
			self::registerBlock(new TripwireHook());
			self::registerBlock(new Tripwire());
			self::registerBlock(new Emerald());
			self::registerBlock((new WoodenStairs(Block::SPRUCE_STAIRS))->setName("Spruce Wood Stairs"));
			self::registerBlock((new WoodenStairs(Block::BIRCH_STAIRS))->setName("Birch Wood Stairs"));
			self::registerBlock((new WoodenStairs(Block::JUNGLE_STAIRS))->setName("Jungle Wood Stairs"));
			self::registerBlock(new CobblestoneWall());
			self::registerBlock(new FlowerPot());
			self::registerBlock(new Carrot());
			self::registerBlock(new Potato());
			self::registerBlock(new WoodenButton());
			self::registerBlock(new Skull());
			self::registerBlock(new Anvil());
			self::registerBlock(new TrappedChest());
			self::registerBlock(new LightWeightedPressurePlate());
			self::registerBlock(new HeavyWeightedPressurePlate());

			self::registerBlock(new DaylightSensor());
			self::registerBlock(new Redstone());

			self::registerBlock(new Quartz());
			self::registerBlock(new QuartzStairs());
			self::registerBlock(new DoubleWoodSlab());
			self::registerBlock(new WoodenSlab());
			self::registerBlock(new StainedClay());

			self::registerBlock(new Leaves2());
			self::registerBlock(new Wood2());
			self::registerBlock((new WoodenStairs(Block::ACACIA_STAIRS))->setName("Acacia Wood Stairs"));
			self::registerBlock((new WoodenStairs(Block::DARK_OAK_STAIRS))->setName("Dark Oak Wood Stairs"));
			self::registerBlock(new Prismarine());
			self::registerBlock(new SeaLantern());
			self::registerBlock(new IronTrapdoor());
			self::registerBlock(new HayBale());
			self::registerBlock(new Carpet());
			self::registerBlock(new HardenedClay());
			self::registerBlock(new Coal());
			self::registerBlock(new PackedIce());
			self::registerBlock(new DoublePlant());

			self::registerBlock((new FenceGate(Block::SPRUCE_FENCE_GATE))->setName("Spruce Fence Gate"));
			self::registerBlock((new FenceGate(Block::BIRCH_FENCE_GATE))->setName("Birch Fence Gate"));
			self::registerBlock((new FenceGate(Block::JUNGLE_FENCE_GATE))->setName("Jungle Fence Gate"));
			self::registerBlock((new FenceGate(Block::DARK_OAK_FENCE_GATE))->setName("Dark Oak Fence Gate"));
			self::registerBlock((new FenceGate(Block::ACACIA_FENCE_GATE))->setName("Acacia Fence Gate"));

			self::registerBlock(new ItemFrame());

			self::registerBlock(new GrassPath());

			self::registerBlock((new EndRod(Block::END_ROD))->setName("End Rod"));

			self::registerBlock((new GlazedTerracotta(Block::PURPLE_GLAZED_TERRACOTTA))->setName("Purple Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::WHITE_GLAZED_TERRACOTTA))->setName("White Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::ORANGE_GLAZED_TERRACOTTA))->setName("Orange Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::MAGENTA_GLAZED_TERRACOTTA))->setName("Magenta Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::LIGHT_BLUE_GLAZED_TERRACOTTA))->setName("Light Blue Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::YELLOW_GLAZED_TERRACOTTA))->setName("Yellow Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::LIME_GLAZED_TERRACOTTA))->setName("Lime Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::PINK_GLAZED_TERRACOTTA))->setName("Pink Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::GRAY_GLAZED_TERRACOTTA))->setName("Grey Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::SILVER_GLAZED_TERRACOTTA))->setName("Light Grey Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::CYAN_GLAZED_TERRACOTTA))->setName("Cyan Glazed Terracotta"));
			//chalkboard here
			self::registerBlock((new GlazedTerracotta(Block::BLUE_GLAZED_TERRACOTTA))->setName("Blue Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::BROWN_GLAZED_TERRACOTTA))->setName("Brown Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::GREEN_GLAZED_TERRACOTTA))->setName("Green Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::RED_GLAZED_TERRACOTTA))->setName("Red Glazed Terracotta"));
			self::registerBlock((new GlazedTerracotta(Block::BLACK_GLAZED_TERRACOTTA))->setName("Black Glazed Terracotta"));

			self::registerBlock(new Podzol());
			self::registerBlock(new Beetroot());
			self::registerBlock(new Stonecutter());
			self::registerBlock(new GlowingObsidian());

			foreach(self::$list as $id => $block){
				if($block === null){
					self::registerBlock(new UnknownBlock($id));
				}
			}
		}
	}

	/**
	 * Adds a Block type to the index. Plugins may use this method to register new block types, or override existing ones.
	 * @since API 3.0.0
	 *
	 * @param Block $block
	 */
	public static function registerBlock(Block $block){
		self::$list[$block->id] = $block;
		for($data = 0; $data < 16; ++$data){
			$b = clone $block;
			$b->meta = $data;
			self::$fullList[($block->id << 4) | $data] = $b;
		}

		self::$solid[$block->id] = $block->isSolid();
		self::$transparent[$block->id] = $block->isTransparent();
		self::$hardness[$block->id] = $block->getHardness();
		self::$light[$block->id] = $block->getLightLevel();
		self::$lightFilter[$block->id] = $block->getLightFilter() + 1;
		self::$diffusesSkyLight[$block->id] = $block->diffusesSkyLight();
	}

	/**
	 * @param int      $id
	 * @param int      $meta
	 * @param Position $pos
	 *
	 * @return Block
	 */
	public static function get($id, $meta = 0, Position $pos = null){
		try{
			$block = clone self::$fullList[($id << 4) | $meta];
		}catch(\RuntimeException $e){
			$block = new UnknownBlock($id, $meta);
		}

		if($pos !== null){
			$block->x = $pos->x;
			$block->y = $pos->y;
			$block->z = $pos->z;
			$block->level = $pos->level;
		}

		return $block;
	}

	protected $fallbackName = "Unknown";

	protected $id;
	protected $meta = 0;

	/** @var AxisAlignedBB */
	public $boundingBox = null;


	/**
	 * Constructs a new instance of a Block.
	 *
	 * Note for plugin developers: This constructor should ONLY be used when creating an instance of a new block type to register into the block type index.
	 * To get an instance of an existing block for use, use {@link Block#get} instead of constructing a new one.
	 *
	 * @param int $id
	 * @param int $meta
	 */
	public function __construct($id, $meta = 0){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
	}

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
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	/**
	 * Returns if the item can be broken with an specific Item
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function isBreakable(Item $item){
		return true;
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item $item
	 *
	 * @return mixed
	 */
	public function onBreak(Item $item){
		return $this->getLevel()->setBlock($this, Block::get(Block::AIR), true, true);
	}

	/**
	 * Fires a block update on the Block
	 *
	 * @param int $type
	 *
	 * @return int|bool
	 */
	public function onUpdate($type){
		return false;
	}

	/**
	 * Do actions when activated by Item. Returns if it has done anything
	 *
	 * @param Item   $item
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function onActivate(Item $item, Player $player = null){
		return false;
	}

	/**
	 * @return float
	 */
	public function getHardness(){
		return 10;
	}

	/**
	 * @return float
	 */
	public function getResistance(){
		return $this->getHardness() * 5;
	}

	/**
	 * Returns the best tool type to use for breaking this type of block.
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_NONE;
	}

	/**
	 * Returns whether the tool type specified in {@link Block#getToolType} must be used to get drops when breaking this block.
	 * For example, wood's tool type is Axe, but does not require an Axe to get drops. Stone on the other hand requires a pickaxe, and a pickaxe must be used.
	 *
	 * @return bool
	 */
	public function requiresCorrectToolType() : bool{
		return false;
	}

	/**
	 * Returns the tool tier needed to break this block and get drops.
	 * @return int
	 */
	public function getRequiredToolTier() : int{
		return TieredTool::TIER_ANY;
	}

	/**
	 * @return float
	 */
	public function getFrictionFactor(){
		return 0.6;
	}

	/**
	 * @return int 0-15
	 */
	public function getLightLevel(){
		return 0;
	}

	/**
	 * Returns the amount of light this block will filter out when light passes through this block.
	 * This value is used in light spread calculation.
	 *
	 * @return int 0-15
	 */
	public function getLightFilter() : int{
		return 15;
	}

	/**
	 * Returns whether this block will diffuse sky light passing through it vertically.
	 * Diffusion means that full-strength sky light passing through this block will not be reduced, but will start being filtered below the block.
	 * Examples of this behaviour include leaves and cobwebs.
	 *
	 * Light-diffusing blocks are included by the heightmap.
	 *
	 * @return bool
	 */
	public function diffusesSkyLight() : bool{
		return false;
	}

	/**
	 * AKA: Block->isPlaceable
	 *
	 * @return bool
	 */
	public function canBePlaced(){
		return true;
	}

	/**
	 * @return bool
	 */
	public function canBeReplaced(){
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTransparent(){
		return false;
	}

	public function isSolid(){
		return true;
	}

	/**
	 * AKA: Block->isFlowable
	 *
	 * @return bool
	 */
	public function canBeFlowedInto(){
		return false;
	}

	public function hasEntityCollision(){
		return false;
	}

	public function canPassThrough(){
		return false;
	}

	/**
	 * Returns whether this block type can be turned into farmland by right-clicking on it with a hoe.
	 * @return bool
	 */
	public function canBeTilled() : bool{
		return false;
	}

	/**
	 * Returns whether entities can climb up this block.
	 * @return bool
	 */
	public function canClimb() : bool{
		return false;
	}

	/**
	 * @return string
	 */
	public function getName(){
		return $this->fallbackName;
	}

	/**
	 * Sets the fallback English name of the block.
	 * @since API 3.0.0
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name){
		$this->fallbackName = $name;

		return $this;
	}

	/**
	 * @return int
	 */
	final public function getId(){
		return $this->id;
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector){

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
		$this->meta = $meta & 0x0f;
	}

	/**
	 * Sets the block position to a new Position object
	 *
	 * @param Position $v
	 */
	final public function position(Position $v){
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->level = $v->level;
		$this->boundingBox = null;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item){
		if(!isset(self::$list[$this->getId()])){ //Unknown blocks
			return [];
		}else{
			return [
				Item::get($this->getId(), $this->getDamage(), 1)
			];
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
		$base = $this->getHardness() * 1.5;
		if($this->canBeBrokenWith($item)){
			if($this->getToolType() === Tool::TYPE_SHEARS and $item->isShears()){
				$base /= 15;
			}elseif(
				($this->getToolType() === Tool::TYPE_PICKAXE and ($tier = $item->isPickaxe()) !== false) or
				($this->getToolType() === Tool::TYPE_AXE and ($tier = $item->isAxe()) !== false) or
				($this->getToolType() === Tool::TYPE_SHOVEL and ($tier = $item->isShovel()) !== false)
			){
				switch($tier){
					case TieredTool::TIER_WOODEN:
						$base /= 2;
						break;
					case TieredTool::TIER_STONE:
						$base /= 4;
						break;
					case TieredTool::TIER_IRON:
						$base /= 6;
						break;
					case TieredTool::TIER_DIAMOND:
						$base /= 8;
						break;
					case TieredTool::TIER_GOLD:
						$base /= 12;
						break;
				}
			}
		}else{
			$base *= 3.33;
		}

		if($item->isSword()){
			$base *= 0.5;
		}

		return $base;
	}

	public function canBeBrokenWith(Item $item){
		return $this->getHardness() !== -1;
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
		if($this->isValid()){
			return $this->getLevel()->getBlock(Vector3::getSide($side, $step));
		}

		return Block::get(Item::AIR, 0, Position::fromObject(Vector3::getSide($side, $step)));
	}

	/**
	 * Returns an array of blocks which are considered a part of this block. This is usually just the block itself, but in the case of
	 * - doors and double plants: will return the top and bottom half (if present)
	 * - beds: will return the top and bottom halves of the bed (if present)
	 *
	 * Used for sending block updates to clients to revert interaction and breaking events if they are cancelled server-side for whatever reason.
	 *
	 * @return Block[]
	 */
	public function getAffectedBlocks() : array{
		return [$this];
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getDamage() . ")";
	}

	/**
	 * Checks for collision against an AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 *
	 * @return bool
	 */
	public function collidesWithBB(AxisAlignedBB $bb){
		$bb2 = $this->getBoundingBox();

		return $bb2 !== null and $bb->intersectsWith($bb2);
	}

	/**
	 * @param Entity $entity
	 */
	public function onEntityCollide(Entity $entity){

	}

	/**
	 * @return AxisAlignedBB
	 */
	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	/**
	 * @return AxisAlignedBB
	 */
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

	/**
	 * @param Vector3 $pos1
	 * @param Vector3 $pos2
	 *
	 * @return MovingObjectPosition
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2){
		$bb = $this->getBoundingBox();
		if($bb === null){
			return null;
		}

		$v1 = $pos1->getIntermediateWithXValue($pos2, $bb->minX);
		$v2 = $pos1->getIntermediateWithXValue($pos2, $bb->maxX);
		$v3 = $pos1->getIntermediateWithYValue($pos2, $bb->minY);
		$v4 = $pos1->getIntermediateWithYValue($pos2, $bb->maxY);
		$v5 = $pos1->getIntermediateWithZValue($pos2, $bb->minZ);
		$v6 = $pos1->getIntermediateWithZValue($pos2, $bb->maxZ);

		if($v1 !== null and !$bb->isVectorInYZ($v1)){
			$v1 = null;
		}

		if($v2 !== null and !$bb->isVectorInYZ($v2)){
			$v2 = null;
		}

		if($v3 !== null and !$bb->isVectorInXZ($v3)){
			$v3 = null;
		}

		if($v4 !== null and !$bb->isVectorInXZ($v4)){
			$v4 = null;
		}

		if($v5 !== null and !$bb->isVectorInXY($v5)){
			$v5 = null;
		}

		if($v6 !== null and !$bb->isVectorInXY($v6)){
			$v6 = null;
		}

		$vector = $v1;

		if($v2 !== null and ($vector === null or $pos1->distanceSquared($v2) < $pos1->distanceSquared($vector))){
			$vector = $v2;
		}

		if($v3 !== null and ($vector === null or $pos1->distanceSquared($v3) < $pos1->distanceSquared($vector))){
			$vector = $v3;
		}

		if($v4 !== null and ($vector === null or $pos1->distanceSquared($v4) < $pos1->distanceSquared($vector))){
			$vector = $v4;
		}

		if($v5 !== null and ($vector === null or $pos1->distanceSquared($v5) < $pos1->distanceSquared($vector))){
			$vector = $v5;
		}

		if($v6 !== null and ($vector === null or $pos1->distanceSquared($v6) < $pos1->distanceSquared($vector))){
			$vector = $v6;
		}

		if($vector === null){
			return null;
		}

		$f = -1;

		if($vector === $v1){
			$f = 4;
		}elseif($vector === $v2){
			$f = 5;
		}elseif($vector === $v3){
			$f = 0;
		}elseif($vector === $v4){
			$f = 1;
		}elseif($vector === $v5){
			$f = 2;
		}elseif($vector === $v6){
			$f = 3;
		}

		return MovingObjectPosition::fromBlock($this->x, $this->y, $this->z, $f, $vector->add($this->x, $this->y, $this->z));
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->setMetadata($this, $metadataKey, $metadataValue);
		}
	}

	public function getMetadata($metadataKey){
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->getMetadata($this, $metadataKey);
		}

		return null;
	}

	public function hasMetadata($metadataKey){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->hasMetadata($this, $metadataKey);
		}
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->removeMetadata($this, $metadataKey, $plugin);
		}
	}
}
