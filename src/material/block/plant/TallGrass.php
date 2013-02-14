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

class TallGrassBlock extends FlowableBlock{
	public function __construct($meta = 1){
		parent::__construct(TALL_GRASS, $meta, "Tall Grass");
		$this->isFlowable = true;
		$this->isReplaceable = true;
		$names = array(
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern",
		);
		$this->name = $names[$this->meta & 0x03];
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if(mt_rand(1,10) === 1){//Seeds
			$drops[] = array(295, 0, 1);
		}
		return $drops;
	}

}