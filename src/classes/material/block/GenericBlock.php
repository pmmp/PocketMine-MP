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


class GenericBlock extends Block{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}	
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$level->setBlock($block, $this->id, $this->getMetadata());
			return true;
		}
		return false;
	}
	
	public function isBreakable(Item $item, Player $player){
		return $this->breakable;
	}
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){
			$level->setBlock($this, AIR, 0);
			return true;
		}
		return false;
	}
	
	public function onUpdate(BlockAPI $level, $type){
		return false;
	}
	public function onActivate(BlockAPI $level, Item $item, Player $player){
		return false;
	}
}