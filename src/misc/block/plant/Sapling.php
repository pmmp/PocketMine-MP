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

class Sapling{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const BURN_TIME = 5;

	public static function growTree(LevelAPI $level, $block, $type){
		$type = $type & 0x03;
		TreeObject::growTree($level, $block, $type);
	}
}