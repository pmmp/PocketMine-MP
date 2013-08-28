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
require_once("Water.php");
/***REM_END***/

class StillWaterBlock extends WaterBlock{
	public function __construct($meta = 0){
		LiquidBlock::__construct(STILL_WATER, $meta, "Still Water");
	}
	
}