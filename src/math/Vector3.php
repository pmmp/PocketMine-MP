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

class Vector3{
	public $x, $y, $z;

	public function __construct($x = 0, $y = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			$this->__construct($x->x, $x->y, $x->z);
		}else{
			$this->x = $x;
			$this->y = $y;
			$this->z = $z;
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getY(){
		return $this->y;
	}

	public function getZ(){
		return $this->z;
	}

	public function getFloorX(){
		return (int) $this->x;
	}

	public function getFloorY(){
		return (int) $this->y;
	}

	public function getFloorZ(){
		return (int) $this->z;
	}

	public function getRight(){
		return $this->getX();
	}

	public function getUp(){
		return $this->getY();
	}

	public function getForward(){
		return $this->getZ();
	}

	public function getSouth(){
		return $this->getX();
	}

	public function getWest(){
		return $this->getZ();
	}

	public function add($x = 0, $y = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			return $this->add($x->x, $x->y, $x->z);
		}else{
			return new Vector3($this->x + $x, $this->y + $y, $this->z + $z);
		}
	}

	public function subtract($x = 0, $y = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			return $this->add(-$x->x, -$x->y, -$x->z);
		}else{
			return $this->add(-$x, -$y, -$z);
		}
	}

	public function ceil(){
		return new Vector3((int) ($this->x + 1), (int) ($this->y + 1), (int) ($this->z + 1));
	}

	public function floor(){
		return new Vector3((int) $this->x, (int) $this->y, (int) $this->z);
	}

	public function round(){
		return new Vector3(round($this->x), round($this->y), round($this->z));
	}

	public function abs(){
		return new Vector3(abs($this->x), abs($this->y), abs($this->z));
	}
	
	public function getSide($side){
		switch((int) $side){
			case 0:
				return new Vector3($this->x, $this->y - 1, $this->z);
			case 1:
				return new Vector3($this->x, $this->y + 1, $this->z);
			case 2:
				return new Vector3($this->x, $this->y, $this->z - 1);
			case 3:
				return new Vector3($this->x, $this->y, $this->z + 1);
			case 4:
				return new Vector3($this->x - 1, $this->y, $this->z);
			case 5:
				return new Vector3($this->x + 1, $this->y, $this->z);	
			default:
				return $this;
		}
	}

	public function distance($x = 0, $y = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			return sqrt($this->distanceSquared($x->x, $x->y, $x->z));
		}else{
			return sqrt($this->distanceSquared($x, $y, $z));
		}
	}

	public function distanceSquared($x = 0, $y = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			return $this->distanceSquared($x->x, $x->y, $x->z);
		}else{
			return pow($this->x - $x, 2) + pow($this->y - $y, 2) + pow($this->z - $z, 2);
		}
	}
	
	public function maxPlainDistance($x = 0, $z = 0){
		if(($x instanceof Vector3) === true){
			return $this->maxPlainDistance($x->x, $x->z);
		}else{
			return max(abs($this->x - $x), abs($this->z - $z));
		}	
	}

	public function __toString(){
		return "Vector3(x=".$this->x.",y=".$this->y.",z=".$this->z.")";
	}

}