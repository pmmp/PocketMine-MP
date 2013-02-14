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

class GrassBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(GRASS, 0, "Grass");
		$this->isActivable = true;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array(DIRT, 0, 1),
		);
	}

	public function onActivate(BlockAPI $level, Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){
			for($c = 0; $c < 15; ++$c){
				$x = mt_rand($this->x - 2, $this->x + 2);
				$z = mt_rand($this->z - 2, $this->z + 2);
				$b = $level->getBlock(new Vector3($x, $this->y + 1, $z));
				$d = $level->getBlock(new Vector3($x, $this->y, $z));
				if($b->getID() === AIR and $d->getID() === GRASS){
					$arr = array(
						array(DANDELION, 0),
						array(CYAN_FLOWER, 0),
						array(TALL_GRASS, 1),
						array(TALL_GRASS, 1),
						array(TALL_GRASS, 1),
						array(TALL_GRASS, 1),
						array(AIR, 0),
					);
					$t = $arr[mt_rand(0, count($arr) - 1)];
					$level->setBlock($b, $t[0], $t[1]);
				}
			}
			return true;
		}elseif($item->isHoe()){
			$level->setBlock($this, FARMLAND, 0);
			return true;
		}
		return false;
	}
}