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


class TreeObject{

	public static function growTree(LevelAPI $level, $block, $type){
		switch($type){
			default:
			case Sapling::OAK:
				$tree = new SmallTreeObject();
				break;
		}
		$tree->placeObject($level, $block[2][0], $block[2][1], $block[2][2], $type);
	}
}