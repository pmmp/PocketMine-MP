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

class Position extends Vector3{
	public $level;

	public function __construct($x = 0, $y = 0, $z = 0, Level $level){
		parent::__construct($x, $y, $z);
		$this->level = $level;
	}
	
	public function distance($x = 0, $y = 0, $z = 0){
		if($x instanceof Position and $x->level !== $this->level){
			return PHP_INT_MAX;
		}
		return parent::distance($x, $y, $z);
	}

	public function __toString(){
		return "Position(level=".$this->level->getName().",x=".$this->x.",y=".$this->y.",z=".$this->z.")";
	}

}