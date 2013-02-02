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
	public function __construct($meta = 0){
		parent::__construct(LEAVES, $meta, "Leaves");
		$names = array(
			0 => "Oak Leaves",
			1 => "Spruce Leaves",
			2 => "Birch Leaves",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
}