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

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(256);
			self::$fullList = new \SplFixedArray(4096);
			self::$light = new \SplFixedArray(256);
			self::$lightFilter = new \SplFixedArray(256);
			self::$solid = new \SplFixedArray(256);
			self::$hardness = new \SplFixedArray(256);
			self::$transparent = new \SplFixedArray(256);
			self::$list[self::AIR] = new Air();
			self::$list[self::STONE] = new Stone();
			self::$list[self::GRASS] = new Grass();
			self::$list[self::DIRT] = new Dirt();
			self::$list[self::COBBLESTONE] = new Cobblestone();
			self::$list[self::PLANKS] = new Planks();
			self::$list[self::SAPLING] = new Sapling();
			self::$list[self::BEDROCK] = new Bedrock();
			self::$list[self::WATER] = new Water();
			self::$list[self::STILL_WATER] = new StillWater();
			self::$list[self::LAVA] = new Lava();
			self::$list[self::STILL_LAVA] = new StillLava();
			self::$list[self::SAND] = new Sand();
			self::$list[self::GRAVEL] = new Gravel();
			self::$list[self::GOLD_ORE] = new GoldOre();
			self::$list[self::IRON_ORE] = new IronOre();
			self::$list[self::COAL_ORE] = new CoalOre();
			self::$list[self::WOOD] = new Wood();
			self::$list[self::LEAVES] = new Leaves();
			self::$list[self::SPONGE] = new Sponge();
			self::$list[self::GLASS] = new Glass();
			self::$list[self::LAPIS_ORE] = new LapisOre();
			self::$list[self::LAPIS_BLOCK] = new Lapis();
			self::$list[self::ACTIVATOR_RAIL] = new ActivatorRail();
			self::$list[self::COCOA_BLOCK] = new CocoaBlock();
			self::$list[self::SANDSTONE] = new Sandstone();
			self::$list[self::NOTE_BLOCK] = new NoteBlock();
			self::$list[self::BED_BLOCK] = new Bed();
			self::$list[self::POWERED_RAIL] = new PoweredRail();
			self::$list[self::DETECTOR_RAIL] = new DetectorRail();
			self::$list[self::COBWEB] = new Cobweb();
			self::$list[self::TALL_GRASS] = new TallGrass();
			self::$list[self::DEAD_BUSH] = new DeadBush();
			self::$list[self::WOOL] = new Wool();
			self::$list[self::DANDELION] = new Dandelion();
			self::$list[self::RED_FLOWER] = new Flower();
			self::$list[self::BROWN_MUSHROOM] = new BrownMushroom();
			self::$list[self::RED_MUSHROOM] = new RedMushroom();
			self::$list[self::GOLD_BLOCK] = new Gold();
			self::$list[self::IRON_BLOCK] = new Iron();
			self::$list[self::DOUBLE_SLAB] = new DoubleSlab();
			self::$list[self::SLAB] = new Slab();
			self::$list[self::BRICKS_BLOCK] = new Bricks();
			self::$list[self::TNT] = new TNT();
			self::$list[self::BOOKSHELF] = new Bookshelf();
			self::$list[self::MOSS_STONE] = new MossStone();
			self::$list[self::OBSIDIAN] = new Obsidian();
			self::$list[self::TORCH] = new Torch();
			self::$list[self::FIRE] = new Fire();
			self::$list[self::MONSTER_SPAWNER] = new MonsterSpawner();
			self::$list[self::WOOD_STAIRS] = new WoodStairs();
			self::$list[self::CHEST] = new Chest();

			self::$list[self::DIAMOND_ORE] = new DiamondOre();
			self::$list[self::DIAMOND_BLOCK] = new Diamond();
			self::$list[self::WORKBENCH] = new Workbench();
			self::$list[self::WHEAT_BLOCK] = new Wheat();
			self::$list[self::FARMLAND] = new Farmland();
			self::$list[self::FURNACE] = new Furnace();
			self::$list[self::BURNING_FURNACE] = new BurningFurnace();
			self::$list[self::SIGN_POST] = new SignPost();
			self::$list[self::WOOD_DOOR_BLOCK] = new WoodDoor();
			self::$list[self::LADDER] = new Ladder();
			self::$list[self::RAIL] = new Rail();

			self::$list[self::COBBLESTONE_STAIRS] = new CobblestoneStairs();
			self::$list[self::WALL_SIGN] = new WallSign();
			self::$list[self::LEVER] = new Lever();
			self::$list[self::STONE_PRESSURE_PLATE] = new StonePressurePlate();
			self::$list[self::IRON_DOOR_BLOCK] = new IronDoor();
			self::$list[self::WOODEN_PRESSURE_PLATE] = new WoodenPressurePlate();
			self::$list[self::REDSTONE_ORE] = new RedstoneOre();
			self::$list[self::GLOWING_REDSTONE_ORE] = new GlowingRedstoneOre();

			self::$list[self::REDSTONE_TORCH] = new RedstoneTorch();
			self::$list[self::LIT_REDSTONE_TORCH] = new LitRedstoneTorch();
			self::$list[self::STONE_BUTTON] = new StoneButton();
			self::$list[self::SNOW_LAYER] = new SnowLayer();
			self::$list[self::ICE] = new Ice();
			self::$list[self::SNOW_BLOCK] = new Snow();
			self::$list[self::CACTUS] = new Cactus();
			self::$list[self::CLAY_BLOCK] = new Clay();
			self::$list[self::SUGARCANE_BLOCK] = new Sugarcane();

			self::$list[self::FENCE] = new Fence();
			self::$list[self::PUMPKIN] = new Pumpkin();
			self::$list[self::NETHERRACK] = new Netherrack();
			self::$list[self::SOUL_SAND] = new SoulSand();
			self::$list[self::GLOWSTONE_BLOCK] = new Glowstone();

			self::$list[self::LIT_PUMPKIN] = new LitPumpkin();
			self::$list[self::CAKE_BLOCK] = new Cake();

			self::$list[self::TRAPDOOR] = new Trapdoor();

			self::$list[self::STONE_BRICKS] = new StoneBricks();

			self::$list[self::IRON_BARS] = new IronBars();
			self::$list[self::GLASS_PANE] = new GlassPane();
			self::$list[self::MELON_BLOCK] = new Melon();
			self::$list[self::PUMPKIN_STEM] = new PumpkinStem();
			self::$list[self::MELON_STEM] = new MelonStem();
			self::$list[self::VINE] = new Vine();
			self::$list[self::FENCE_GATE] = new FenceGate();
			self::$list[self::BRICK_STAIRS] = new BrickStairs();
			self::$list[self::STONE_BRICK_STAIRS] = new StoneBrickStairs();

			self::$list[self::MYCELIUM] = new Mycelium();
			self::$list[self::WATER_LILY] = new WaterLily();
			self::$list[self::NETHER_BRICKS] = new NetherBrick();
			self::$list[self::NETHER_BRICK_FENCE] = new NetherBrickFence();
			self::$list[self::NETHER_BRICKS_STAIRS] = new NetherBrickStairs();

			self::$list[self::ENCHANTING_TABLE] = new EnchantingTable();
			self::$list[self::BREWING_STAND_BLOCK] = new BrewingStand();
			self::$list[self::END_PORTAL_FRAME] = new EndPortalFrame();
			self::$list[self::END_STONE] = new EndStone();
			self::$list[self::REDSTONE_LAMP] = new RedstoneLamp();
			self::$list[self::LIT_REDSTONE_LAMP] = new LitRedstoneLamp();
			self::$list[self::SANDSTONE_STAIRS] = new SandstoneStairs();
			self::$list[self::EMERALD_ORE] = new EmeraldOre();
			self::$list[self::TRIPWIRE_HOOK] = new TripwireHook();
			self::$list[self::TRIPWIRE] = new Tripwire();
			self::$list[self::EMERALD_BLOCK] = new Emerald();
			self::$list[self::SPRUCE_WOOD_STAIRS] = new SpruceWoodStairs();
			self::$list[self::BIRCH_WOOD_STAIRS] = new BirchWoodStairs();
			self::$list[self::JUNGLE_WOOD_STAIRS] = new JungleWoodStairs();
			self::$list[self::STONE_WALL] = new StoneWall();
			self::$list[self::FLOWER_POT_BLOCK] = new FlowerPot();
			self::$list[self::CARROT_BLOCK] = new Carrot();
			self::$list[self::POTATO_BLOCK] = new Potato();
			self::$list[self::WOODEN_BUTTON] = new WoodenButton();
			self::$list[self::MOB_HEAD_BLOCK] = new MobHead();
			self::$list[self::ANVIL] = new Anvil();
			self::$list[self::TRAPPED_CHEST] = new TrappedChest();
			self::$list[self::WEIGHTED_PRESSURE_PLATE_LIGHT] = new WeightedPressurePlateLight();
			self::$list[self::WEIGHTED_PRESSURE_PLATE_HEAVY] = new WeightedPressurePlateHeavy();

			self::$list[self::DAYLIGHT_SENSOR] = new DaylightSensor();
			self::$list[self::REDSTONE_BLOCK] = new Redstone();

			self::$list[self::QUARTZ_BLOCK] = new Quartz();
			self::$list[self::QUARTZ_STAIRS] = new QuartzStairs();
			self::$list[self::DOUBLE_WOOD_SLAB] = new DoubleWoodSlab();
			self::$list[self::WOOD_SLAB] = new WoodSlab();
			self::$list[self::STAINED_CLAY] = new StainedClay();

			self::$list[self::LEAVES2] = new Leaves2();
			self::$list[self::WOOD2] = new Wood2();
			self::$list[self::ACACIA_WOOD_STAIRS] = new AcaciaWoodStairs();
			self::$list[self::DARK_OAK_WOOD_STAIRS] = new DarkOakWoodStairs();
			self::$list[self::PRISMARINE] = new Prismarine();
			self::$list[self::SEA_LANTERN] = new SeaLantern();
			self::$list[self::IRON_TRAPDOOR] = new IronTrapdoor();
			self::$list[self::HAY_BALE] = new HayBale();
			self::$list[self::CARPET] = new Carpet();
			self::$list[self::HARDENED_CLAY] = new HardenedClay();
			self::$list[self::COAL_BLOCK] = new Coal();
			self::$list[self::PACKED_ICE] = new PackedIce();
			self::$list[self::DOUBLE_PLANT] = new DoublePlant();

			self::$list[self::FENCE_GATE_SPRUCE] = new FenceGateSpruce();
			self::$list[self::FENCE_GATE_BIRCH] = new FenceGateBirch();
			self::$list[self::FENCE_GATE_JUNGLE] = new FenceGateJungle();
			self::$list[self::FENCE_GATE_DARK_OAK] = new FenceGateDarkOak();
			self::$list[self::FENCE_GATE_ACACIA] = new FenceGateAcacia();

			self::$list[self::ITEM_FRAME_BLOCK] = new ItemFrame();

			self::$list[self::GRASS_PATH] = new GrassPath();

			self::$list[self::PODZOL] = new Podzol();
			self::$list[self::BEETROOT_BLOCK] = new Beetroot();
			self::$list[self::STONECUTTER] = new Stonecutter();
			self::$list[self::GLOWING_OBSIDIAN] = new GlowingObsidian();

			foreach(self::$list as $id => $block){
				if($block !== null){

					for($data = 0; $data < 16; ++$data){
						$b = clone $block;
						$b->meta = $data;
						self::$fullList[($id << 4) | $data] = $b;
					}

					self::$solid[$id] = $block->isSolid();
					self::$transparent[$id] = $block->isTransparent();
					self::$hardness[$id] = $block->getHardness();
					self::$light[$id] = $block->getLightLevel();

					if($block->isSolid()){
						if($block->isTransparent()){
							if($block instanceof Liquid or $block instanceof Ice){
								self::$lightFilter[$id] = 2;
							}else{
								self::$lightFilter[$id] = 1;
							}
						}else{
							self::$lightFilter[$id] = 15;
						}
					}else{
						self::$lightFilter[$id] = 1;
					}
				}else{
					self::$lightFilter[$id] = 1;
					for($data = 0; $data < 16; ++$data){
						self::$fullList[($id << 4) | $data] = new UnknownBlock($id, $data);
					}
				}
			}
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

	protected $id;
	protected $meta = 0;

	/** @var AxisAlignedBB */
	public $boundingBox = null;


	/**
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
		return $this->getLevel()->setBlock($this, new Air(), true, true);
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
	 * @return int
	 */
	public function getHardness(){
		return 10;
	}

	/**
	 * @return int
	 */
	public function getResistance(){
		return $this->getHardness() * 5;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_NONE;
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

	/**
	 * AKA: Block->isActivable
	 *
	 * @return bool
	 */
	public function canBeActivated(){
		return false;
	}

	public function hasEntityCollision(){
		return false;
	}

	public function canPassThrough(){
		return false;
	}

	/**
	 * @return string
	 */
	public function getName(){
		return "Unknown";
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
	 * @return array
	 */
	public function getDrops(Item $item){
		if(!isset(self::$list[$this->getId()])){ //Unknown blocks
			return [];
		}else{
			return [
				[$this->getId(), $this->getDamage(), 1],
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
					case Tool::TIER_WOODEN:
						$base /= 2;
						break;
					case Tool::TIER_STONE:
						$base /= 4;
						break;
					case Tool::TIER_IRON:
						$base /= 6;
						break;
					case Tool::TIER_DIAMOND:
						$base /= 8;
						break;
					case Tool::TIER_GOLD:
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
