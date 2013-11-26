<?php

/**
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

class Item{
	public static $class = array(
		SUGARCANE => "SugarcaneItem",	
		WHEAT_SEEDS => "WheatSeedsItem",
		PUMPKIN_SEEDS => "PumpkinSeedsItem",
		MELON_SEEDS => "MelonSeedsItem",
		MUSHROOM_STEW => "MushroomStewItem",
		BEETROOT_SOUP => "BeetrootSoupItem",
		CARROT => "CarrotItem",
		POTATO => "PotatoItem",
		BEETROOT_SEEDS => "BeetrootSeedsItem",
		SIGN => "SignItem",
		WOODEN_DOOR => "WoodenDoorItem",
		BUCKET => "BucketItem",
		IRON_DOOR => "IronDoorItem",
		CAKE => "CakeItem",
		BED => "BedItem",
		PAINTING => "PaintingItem",
		COAL => "CoalItem",
		APPLE => "AppleItem",
		SPAWN_EGG => "SpawnEggItem",
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
		FLINT_STEEL => "FlintSteelItem",
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
		if($this->isTool() !== false){
			$this->maxStackSize = 1;
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
	
	final public function getFuelTime(){
		if(!isset(FuelData::$duration[$this->id])){
			return false;
		}
		if($this->id !== BUCKET or $this->meta === 10){
			return FuelData::$duration[$this->id];
		}
		return false;
	}
	
	final public function getSmeltItem(){
		if(!isset(SmeltingData::$product[$this->id])){
			return false;
		}
		
		if(isset(SmeltingData::$product[$this->id][0]) and !is_array(SmeltingData::$product[$this->id][0])){
			return BlockAPI::getItem(SmeltingData::$product[$this->id][0], SmeltingData::$product[$this->id][1]);
		}
		
		if(!isset(SmeltingData::$product[$this->id][$this->meta])){
			return false;
		}
		
		return BlockAPI::getItem(SmeltingData::$product[$this->id][$this->meta][0], SmeltingData::$product[$this->id][$this->meta][1]);
		
	}
	
	public function useOn($object, $force = false){
		if($this->isTool() or $force === true){
			if(($object instanceof Entity) and !$this->isSword()){
				$this->meta += 2;
			}else{
				$this->meta++;
			}
			return true;
		}elseif($this->isHoe()){
			if(($object instanceof Block) and ($object->getID() === GRASS or $object->getID() === DIRT)){
				$this->meta++;
			}
		}
		return false;
	}
	
	final public function isTool(){
		return ($this->id === FLINT_STEEL or $this->id === SHEARS or $this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false or $this->isSword() !== false);
	}
	
	final public function getMaxDurability(){
		if(!$this->isTool() and $this->isHoe() === false and $this->id !== BOW){
			return false;
		}
		
		$levels = array(
			2 => 33,
			1 => 60,
			3 => 132,
			4 => 251,
			5 => 1562,
			FLINT_STEEL => 65,
			SHEARS => 239,
			BOW => 385,
		);

		if(($type = $this->isPickaxe()) === false){			
			if(($type = $this->isAxe()) === false){			
				if(($type = $this->isSword()) === false){				
					if(($type = $this->isShovel()) === false){					
						if(($type = $this->isHoe()) === false){
							$type = $this->id;
						}
					}	
				}
			}
		}
		return $levels[$type];
	}
	
	final public function isPickaxe(){ //Returns false or level of the pickaxe
		switch($this->id){
			case IRON_PICKAXE:
				return 4;
			case WOODEN_PICKAXE:
				return 1;
			case STONE_PICKAXE:
				return 3;
			case DIAMOND_PICKAXE:
				return 5;
			case GOLD_PICKAXE:
				return 2;
			default:
				return false;
		}
	}
	
	final public function isAxe(){
		switch($this->id){
			case IRON_AXE:
				return 4;
			case WOODEN_AXE:
				return 1;
			case STONE_AXE:
				return 3;
			case DIAMOND_AXE:
				return 5;
			case GOLD_AXE:
				return 2;
			default:
				return false;
		}
	}

	final public function isSword(){
		switch($this->id){
			case IRON_SWORD:
				return 4;
			case WOODEN_SWORD:
				return 1;
			case STONE_SWORD:
				return 3;
			case DIAMOND_SWORD:
				return 5;
			case GOLD_SWORD:
				return 2;
			default:
				return false;
		}
	}
	
	final public function isShovel(){
		switch($this->id){
			case IRON_SHOVEL:
				return 4;
			case WOODEN_SHOVEL:
				return 1;
			case STONE_SHOVEL:
				return 3;
			case DIAMOND_SHOVEL:
				return 5;
			case GOLD_SHOVEL:
				return 2;
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

	public function isShears(){
		return ($this->id === SHEARS);
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
