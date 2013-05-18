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

class SaplingBlock extends TransparentBlock{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const BURN_TIME = 5;
	
	public function __construct($meta = Sapling::OAK){
		parent::__construct(SAPLING, $meta, "Sapling");
		$this->isActivable = true;
		$this->isFlowable = true;
		$names = array(
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === GRASS or $down->getID() === DIRT or $down->getID() === FARMLAND){
			$this->level->setBlock($block, $this);
			return true;
		}
		return false;
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			TreeObject::growTree($this->level, $this, $this->meta);
			return true;
		}
		return false;
	}
	
	public function onUpdate($type){
		if($type === BLOCK_UPDATE_RANDOM and mt_rand(0,2) === 0){ //Growth
			if(($this->meta & 0x08) === 0x08){
				TreeObject::growTree($this->level, $this);
			}else{
				$this->meta |= 0x08;
				$this->level->setBlock($this, $this);
			}
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, $this->meta & 0x03, 1),
		);
	}
}