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

class SandstoneBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(SANDSTONE, $meta, "Sandstone");
		$names = array(
			0 => "Sandstone",
			1 => "Chiseled Sandstone",
			2 => "Smooth Sandstone",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
}