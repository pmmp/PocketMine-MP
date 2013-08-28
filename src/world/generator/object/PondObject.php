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

class PondObject{
	private $random;
	public $type;
	
	public function __construct(Random $random, Block $type){
		$this->type = $type;
		$this->random = $random;
	}
	
	public function canPlaceObject(Level $level, Vector3 $pos){
	}
	
	public function placeObject(Level $level, Vector3 $pos){
	}

}