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

class DeadBushBlock extends FlowableBlock{
	public function __construct(){
		parent::__construct(DEAD_BUSH, 0, "Dead Bush");
		$this->isReplaceable = true;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent === true){ //Replace wit common break method
				$this->level->setBlock($this, new AirBlock(), false);
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
	
}