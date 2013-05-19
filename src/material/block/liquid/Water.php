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

class WaterBlock extends LiquidBlock{
	public function __construct($meta = 0){
		parent::__construct(WATER, $meta, "Water");
	}
	
	public function onUpdate($type){
		return false;
		$level = $this->meta & 0x03;
		if($type !== BLOCK_UPDATE_NORMAL or $level === 0){
			return false;
		}
		
		$falling = $this->meta >> 3;
		$down = $this->getSide(0);
		if($down->isFlowable){
			$this->level->setBlock($down, new WaterBlock(9), true); //1001
			return;
		}elseif($down instanceof WaterBlock and $down->getMetadata() === 9){
			$level = 1;
		}
		
		$up = $this->getSide(1);
		if($up instanceof WaterBlock){
			
		}
	}
	
}