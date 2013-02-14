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

class BedrockBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(BEDROCK, 0, "Bedrock");
		$this->breakable = false;
	}
	
	public function isBreakable(Item $item, Player $player){
		if($player->gamemode === CREATIVE){
			return true;
		}
		return false;
	}
	
}