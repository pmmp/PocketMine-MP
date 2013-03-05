<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

/***REM_START***/
require_once(FILE_PATH."/src/math/Vector3.php");
/***REM_END***/

abstract class Block extends Vector3{
	public static $class = array(
			AIR => "AirBlock",
			STONE => "StoneBlock",
			GRASS => "GrassBlock",
			DIRT => "DirtBlock",
			COBBLESTONE => "CobblestoneBlock",
			PLANKS => "PlanksBlock",
			SAPLING => "SaplingBlock",
			BEDROCK => "BedrockBlock",
			WATER => "WaterBlock",
			STILL_WATER => "StillWaterBlock",
			LAVA => "LavaBlock",
			STILL_LAVA => "StillLavaBlock",
			SAND => "SandBlock",
			GRAVEL => "GravelBlock",
			GOLD_ORE => "GoldOreBlock",
			IRON_ORE => "IronOreBlock",
			COAL_ORE => "CoalOreBlock",
			WOOD => "WoodBlock",
			LEAVES => "LeavesBlock",
			GLASS => "GlassBlock",
			LAPIS_ORE => "LapisOreBlock",
			LAPIS_BLOCK => "LapisBlock",
			SANDSTONE => "SandstoneBlock",
			BED_BLOCK => "BedBlock",
			COBWEB => "CobwebBlock",
			TALL_GRASS => "TallGrassBlock",
			DEAD_BUSH => "DeadBushBlock",
			WOOL => "WoolBlock",
			DANDELION => "DandelionBlock",
			CYAN_FLOWER => "CyanFlowerBlock",
			BROWN_MUSHROOM => "BrownMushroomBlock",
			RED_MUSHROOM => "RedMushRoomBlock",
			GOLD_BLOCK => "GoldBlock",
			IRON_BLOCK => "IronBlock",
			DOUBLE_SLAB => "DoubleSlabBlock",
			SLAB => "SlabBlock",
			BRICKS_BLOCK => "BricksBlock",
			TNT => "TNTBlock",
			BOOKSHELF => "BookshelfBlock",
			MOSS_STONE => "MossStoneBlock",
			OBSIDIAN => "ObsidianBlock",
			TORCH => "TorchBlock",
			FIRE => "FireBlock",

			WOOD_STAIRS => "WoodStairsBlock",
			CHEST => "ChestBlock",

			DIAMOND_ORE => "DiamondOreBlock",
			DIAMOND_BLOCK => "DiamondBlock",
			WORKBENCH => "WorkbenchBlock",
			WHEAT_BLOCK => "WheatBlock",
			FARMLAND => "FarmlandBlock",
			FURNACE => "FurnaceBlock",
			BURNING_FURNACE => "BurningFurnaceBlock",
			SIGN_POST => "SignPostBlock",
			WOOD_DOOR_BLOCK => "WoodDoorBlock",
			LADDER => "LadderBlock",

			COBBLESTONE_STAIRS => "CobblestoneStairsBlock",
			WALL_SIGN => "WallSignBlock",

			IRON_DOOR_BLOCK => "IronDoorBlock",
			REDSTONE_ORE => "RedstoneOreBlock",
			GLOWING_REDSTONE_ORE => "GlowingRedstoneOreBlock",

			SNOW_LAYER => "SnowLayerBlock",
			ICE => "IceBlock",
			SNOW_BLOCK => "SnowBlock",
			CACTUS => "CactusBlock",
			CLAY_BLOCK => "ClayBlock",
			SUGARCANE_BLOCK => "SugarcaneBlock",

			FENCE => "FenceBlock",

			NETHERRACK => "NetherrackBlock",
			SOUL_SAND => "SoulSandBlock",
			GLOWSTONE_BLOCK => "GlowstoneBlock",

			TRAPDOOR => "TrapdoorBlock",

			STONE_BRICKS => "StoneBricksBlock",

			GLASS_PANE => "GlassPaneBlock",
			MELON_BLOCK => "MelonBlock",

			MELON_STEM => "MelonStemBlock",

			FENCE_GATE => "FenceGateBlock",
			BRICK_STAIRS => "BrickStairsBlock",
			STONE_BRICK_STAIRS => "StoneBrickStairsBlock",

			NETHER_BRICKS => "NetherBricksBlock",

			NETHER_BRICKS_STAIRS => "NetherBricksStairsBlock",

			SANDSTONE_STAIRS => "SandstoneStairsBlock",

			QUARTZ_BLOCK => "QuartzBlock",
			QUARTZ_STAIRS => "QuartzStairsBlock",

			STONECUTTER => "StonecutterBlock",
			GLOWING_OBSIDIAN => "GlowingObsidianBlock",
			NETHER_REACTOR => "NetherReactorBlock",
	);
	protected $id;
	protected $meta;
	protected $name;
	protected $breakTime;
	public $isActivable = false;
	public $breakable = true;
	public $isFlowable = false;
	public $isTransparent = false;
	public $isReplaceable = false;
	public $isPlaceable = true;
	public $inWorld = false;
	public $hasPhysics = false;
	public $isLiquid = false;
	public $x;
	public $y;
	public $z;
	
	public function __construct($id, $meta = 0, $name = "Unknown"){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
		$this->name = $name;
		$this->breakTime = 0.25;
	}
	
	public function getName(){
		return $this->name;
	}
	
	final public function getID(){
		return $this->id;
	}
	
	final public function getMetadata(){
		return $this->meta & 0x0F;
	}
	
	final public function position(Vector3 $v){
		$this->inWorld = true;
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
	}
	
	public function getDrops(Item $item, Player $player){
		if(!isset(Block::$class[$this->id])){ //Unknown blocks
			return array();
		}else{
			return array(
				array($this->id, $this->meta, 1),
			);
		}
	}
	
	public function getBreakTime(Item $item, Player $player){
		if($player->gamemode === CREATIVE){
			return 0.25;
		}
		return $this->breakTime;
	}
	
	final public function __toString(){
		return "Block ". $this->name ." (".$this->id.":".$this->meta.")";
	}
	
	abstract function isBreakable(Item $item, Player $player);
	
	abstract function onBreak(BlockAPI $level, Item $item, Player $player);
	
	abstract function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz);
	
	abstract function onActivate(BlockAPI $level, Item $item, Player $player);
	
	abstract function onUpdate(BlockAPI $level, $type);
}

/***REM_START***/
require_once("block/GenericBlock.php");
require_once("block/SolidBlock.php");
require_once("block/TransparentBlock.php");
require_once("block/FallableBlock.php");
require_once("block/LiquidBlock.php");
require_once("block/StairBlock.php");
require_once("block/DoorBlock.php");
/***REM_END***/
