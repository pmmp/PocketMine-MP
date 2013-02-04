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

require_once("classes/material/IDs.php");

class Item{
	public static $class = array(
		SUGARCANE => "SugarcaneItem",	
		WHEAT_SEEDS => "WheatSeedsItem",
		MELON_SEEDS => "MelonSeedsItem",
	);
	protected $block;
	protected $id;
	protected $meta;
	protected $count;
	protected $maxStackSize = 64;
	protected $durability = 0;
	protected $name;
	
	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
		$this->count = (int) $count;
		$this->name = $name;
		if(!isset($this->block) and isset(Block::$class[$this->id])){
			$this->block = BlockAPI::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function isPlaceable(){
		return (($this->block instanceof Block) and $this->block->isPlaceable === true);
	}
	
	public function getBlock(){
		if($this->block instanceof Block){
			return $this->block;
		}else{
			return BlockAPI::get(AIR);
		}
	}
	
	public function getID(){
		return $this->id;
	}
	
	public function getMetadata(){
		return $this->meta;
	}	
	
	public function getMaxStackSize(){
		return $this->maxStackSize;
	}
	
	public function isPickaxe(){ //Returns false or level of the pickaxe
		switch($this->id){
			case IRON_PICKAXE:
				return 3;
			case 270: //Wood
				return 1;
			case 274: //Stone
				return 2;
			case 278: //Diamond
				return 4;
			case 285: //Gold
				return 3;
			default:
				return false;
		}
	}
	
	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}
	
}