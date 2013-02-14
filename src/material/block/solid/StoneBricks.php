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

class StoneBricksBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(STONE_BRICKS, $meta, "Stone Bricks");
		$names = array(
			0 => "Stone Bricks",
			1 => "Mossy Stone Bricks",
			2 => "Cracked Stone Bricks",
			3 => "Chiseled Stone Bricks",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
}