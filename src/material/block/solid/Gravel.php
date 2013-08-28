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

class GravelBlock extends FallableBlock{
	public function __construct(){
		parent::__construct(GRAVEL, 0, "Gravel");
	}
	
	public function getDrops(Item $item, Player $player){
		if(mt_rand(1,10) === 1){
			return array(
				array(FLINT, 0, 1),
			);
		}
		return array(
			array(GRAVEL, 0, 1),
		);
	}
	
}