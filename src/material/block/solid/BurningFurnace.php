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

/***REM_START***/
require_once("Furnace.php");
/***REM_END***/

class BurningFurnaceBlock extends FurnaceBlock{
	public function __construct($meta = 0){
		parent::__construct($meta);
		$this->id = BURNING_FURNACE;
		$this->name = "Burning Furnace";
		$this->isActivable = true;
	}
	
}