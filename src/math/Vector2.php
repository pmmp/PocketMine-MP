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

class Vector2{
	public $x, $y;

	public function __construct($x = 0, $y = 0){
		if(($x instanceof Vector2) === true){
			$this->__construct($x->x, $x->y);
		}else{
			$this->x = $x;
			$this->y = $y;
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getY(){
		return $this->y;
	}

	public function getFloorX(){
		return (int) $this->x;
	}

	public function getFloorY(){
		return (int) $this->y;
	}

	public function add($x = 0, $y = 0){
		if(($x instanceof Vector2) === true){
			return $this->add($x->x, $x->y);
		}else{
			$this->x += $x;
			$this->y += $y;
			return new Vector3($this->x + $x, $this->y + $y);
		}
	}

	public function subtract($x = 0, $y = 0){
		if(($x instanceof Vector2) === true){
			return $this->add(-$x->x, -$x->y);
		}else{
			return $this->add(-$x, -$y);
		}
	}

	public function ceil(){
		return new Vector2((int) ($this->x + 1), (int) ($this->y + 1));
	}

	public function floor(){
		return new Vector2((int) $this->x, (int) $this->y);
	}

	public function round(){
		return new Vector2(round($this->x), round($this->y));
	}

	public function abs(){
		return new Vector2(abs($this->x), abs($this->y));
	}

	public function distance($x = 0, $y = 0){
		if(($x instanceof Vector2) === true){
			return sqrt($this->distanceSquared($x->x, $x->y));
		}else{
			return sqrt($this->distanceSquared($x, $y));
		}
	}

	public function distanceSquared($x = 0, $y = 0){
		if(($x instanceof Vector2) === true){
			return $this->distanceSquared($x->x, $x->y);
		}else{
			return pow($this->x - $x, 2) + pow($this->y - $y, 2);
		}
	}

	public function __toString(){
		return "Vector2(x=".$this->x.",y=".$this->y.")";
	}

}