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

class CobwebBlock extends FlowableBlock{
	public function __construct(){
		parent::__construct(COBWEB, 0, "Cobweb");
		$this->isFlowable = true;
	}
	public function getDrops(Item $item, Player $player){
		return array();
	}	
}