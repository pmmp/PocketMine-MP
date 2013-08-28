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

class MelonBlock extends TransparentBlock{
	public function __construct(){
		parent::__construct(MELON_BLOCK, 0, "Melon Block");
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array(MELON_SLICE, 0, mt_rand(3, 7)),
		);
	}
}