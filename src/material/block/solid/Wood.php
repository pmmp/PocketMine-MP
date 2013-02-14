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

class WoodBlock extends SolidBlock{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	public function __construct($meta = 0){
		parent::__construct(WOOD, $meta, "Wood");
		$names = array(
			WoodBlock::OAK => "Oak Wood",
			WoodBlock::SPRUCE => "Spruce Wood",
			WoodBlock::BIRCH => "Birch Wood",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
}