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

class LeavesBlock extends TransparentBlock{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	public function __construct($meta = 0){
		parent::__construct(LEAVES, $meta, "Leaves");
		$names = array(
			LeavesBlock::OAK => "Oak Leaves",
			LeavesBlock::SPRUCE => "Spruce Leaves",
			LeavesBlock::BIRCH => "Birch Leaves",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if(mt_rand(1,20) === 1){ //Saplings
			$drops[] = array(SAPLING, $this->meta & 0x03, 1);
		}
		if(($this->meta & 0x03) === LeavesBlock::OAK and mt_rand(1,200) === 1){ //Apples
			$drops[] = array(260, 0, 1);
		}
		return $drops;
	}
}