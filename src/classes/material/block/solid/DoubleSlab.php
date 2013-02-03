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

class DoubleSlabBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(DOUBLE_SLAB, $meta, "Double Slab");
		$names = array(
			0 => "Stone",
			1 => "Sandstone",
			2 => "Wooden",
			3 => "Cobblestone",
			4 => "Brick",
			5 => "Stone Brick",
			6 => "Nether Brick",
			7 => "Quartz",
		);
		$this->name = "Double " . $names[$this->meta & 0x07] . " Slab";
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array(SLAB, $this->meta & 0x07, 2),
		);
	}
	
}