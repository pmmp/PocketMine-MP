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

class Item{
	public static $class = array(
		SUGARCANE => "SugarcaneItem",	
		WHEAT_SEEDS => "WheatSeedsItem",
		MELON_SEEDS => "MelonSeedsItem",
		SIGN => "SignItem",
		WOODEN_DOOR => "WoodenDoorItem",
		BUCKET => "BucketItem",
		WATER_BUCKET => "WaterBucketItem",
		LAVA_BUCKET => "LavaBucketItem",
		IRON_DOOR => "IronDoorItem",
		CAKE => "CakeItem",
		BED => "BedItem",
		PAINTING => "PaintingItem",
		COAL => "CoalItem",
		APPLE => "AppleItem",
		DIAMOND => "DiamondItem",
		STICK => "StickItem",
		BOWL => "BowlItem",
		FEATHER => "FeatherItem",
		BRICK => "BrickItem",
		IRON_INGOT => "IronIngotItem",
		GOLD_INGOT => "GoldIngotItem",
		IRON_SHOVEL => "IronShovelItem",
		IRON_PICKAXE => "IronPickaxeItem",
		IRON_AXE => "IronAxeItem",
		IRON_HOE => "IronHoeItem",
		WOODEN_SWORD => "WoodenSwordItem",
		WOODEN_SHOVEL => "WoodenShovelItem",
		WOODEN_PICKAXE => "WoodenPickaxeItem",
		WOODEN_AXE => "WoodenAxeItem",
	);
	protected $block;
	protected $id;
	protected $meta;
	public $count;
	protected $maxStackSize = 64;
	protected $durability = 0;
	protected $name;
	public $isActivable = false;
	
	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
		$this->count = (int) $count;
		$this->name = $name;
		if(!isset($this->block) and $this->id <= 0xff and isset(Block::$class[$this->id])){
			$this->block = BlockAPI::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}
	
	final public function getName(){
		return $this->name;
	}
	
	final public function isPlaceable(){
		return (($this->block instanceof Block) and $this->block->isPlaceable === true);
	}
	
	final public function getBlock(){
		if($this->block instanceof Block){
			return $this->block;
		}else{
			return BlockAPI::get(AIR);
		}
	}
	
	final public function getID(){
		return $this->id;
	}
	
	final public function getMetadata(){
		return $this->meta;
	}	
	
	final public function getMaxStackSize(){
		return $this->maxStackSize;
	}
	
	final public function isPickaxe(){ //Returns false or level of the pickaxe
		switch($this->id){
			case IRON_PICKAXE:
				return 3;
			case WOODEN_PICKAXE:
				return 1;
			case STONE_PICKAXE:
				return 2;
			case DIAMOND_PICKAXE:
				return 4;
			case GOLD_PICKAXE:
				return 3;
			default:
				return false;
		}
	}
	
	public function isHoe(){
		switch($this->id){
			case IRON_HOE:
			case WOODEN_HOE:
			case STONE_HOE:
			case DIAMOND_HOE:
			case GOLD_HOE:
				return true;
			default:
				return false;
		}
	}
	
	final public function __toString(){
		return "Item ". $this->name ." (".$this->id.":".$this->meta.")";
	}
	
	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return false;
	}
	
}
