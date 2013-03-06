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
require_once("BurningFurnace.php");
/***REM_END***/


class FurnaceBlock extends BurningFurnaceBlock{
	public function __construct($meta = 0){
		parent::__construct($meta);
		$this->id = FURNACE;
		$this->name = "Furnace";
		$this->isActivable = true;
	}
	
}