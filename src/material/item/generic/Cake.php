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

class CakeItem extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockAPI::get(CAKE_BLOCK);
		$this->maxStackSize = 1;
		parent::__construct(CAKE, 0, $count, "Cake");
	}
}