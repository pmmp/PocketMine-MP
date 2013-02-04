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

class WheatBlock extends FlowableBlock{
	public function __construct($meta = 0){
		parent::__construct(WHEAT_BLOCK, $meta, "Wheat");
	}

	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($this->meta >= 0x07){
			$drops[] = array(296, 0, 1);
			$drops[] = array(295, 0, mt_rand(0, 3));
		}else{
			$drops[] = array(295, 0, 1);
		}
		return $drops;
	}
}