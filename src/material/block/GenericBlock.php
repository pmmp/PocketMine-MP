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
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return $this->level->setBlock($block, $this);
	}
	
	public function isBreakable(Item $item, Player $player){
		return ($this->breakable);
	}
	
	public function onBreak(Item $item, Player $player){
		return $this->level->setBlock($this, new AirBlock());
	}
	
	public function onUpdate($type){
		return false;
	}

	public function onActivate(Item $item, Player $player){
		return ($this->isActivable);
	}
}