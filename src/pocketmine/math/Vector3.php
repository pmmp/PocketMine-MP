<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\math;

/**
 * WARNING: This class is available on the PocketMine-MP Zephir project.
 * If this class is modified, remember to modify the PHP C extension.
 */
class Vector3{
	public $x;
	public $y;
	public $z;

	public function __construct($x = 0, $y = 0, $z = 0){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
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
		return $this->x;
	}

	public function getUp(){
		return $this->y;
	}

	public function getForward(){
		return $this->z;
	}

	public function getSouth(){
		return $this->x;
	}

	public function getWest(){
		return $this->z;
	}

	/**
	 * @param Vector3|int $x
	 * @param int         $y
	 * @param int         $z
	 *
	 * @return Vector3
	 */
	public function add($x, $y = 0, $z = 0){
		if($x instanceof Vector3){
			return $this->add($x->x, $x->y, $x->z);
		}else{
			return new Vector3($this->x + $x, $this->y + $y, $this->z + $z);
		}
	}

	/**
	 * @param Vector3|int $x
	 * @param int         $y
	 * @param int         $z
	 *
	 * @return Vector3
	 */
	public function subtract($x = 0, $y = 0, $z = 0){
		if($x instanceof Vector3){
			return $this->add(-$x->x, -$x->y, -$x->z);
		}else{
			return $this->add(-$x, -$y, -$z);
		}
	}

	public function multiply($number){
		return new Vector3($this->x * $number, $this->y * $number, $this->z * $number);
	}

	public function divide($number){
		return new Vector3($this->x / $number, $this->y / $number, $this->z / $number);
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

	public function getSide($side, $step = 1){
		switch((int) $side){
			case 0:
				return new Vector3($this->x, $this->y - $step, $this->z);
			case 1:
				return new Vector3($this->x, $this->y + $step, $this->z);
			case 2:
				return new Vector3($this->x, $this->y, $this->z - $step);
			case 3:
				return new Vector3($this->x, $this->y, $this->z + $step);
			case 4:
				return new Vector3($this->x - $step, $this->y, $this->z);
			case 5:
				return new Vector3($this->x + $step, $this->y, $this->z);
			default:
				return $this;
		}
	}

	public function distance(Vector3 $pos){
		return sqrt($this->distanceSquared($pos));
	}

	public function distanceSquared(Vector3 $pos){
		return pow($this->x - $pos->x, 2) + pow($this->y - $pos->y, 2) + pow($this->z - $pos->z, 2);
	}

	public function maxPlainDistance($x = 0, $z = 0){
		if($x instanceof Vector3){
			return $this->maxPlainDistance($x->x, $x->z);
		}elseif($x instanceof Vector2){
			return $this->maxPlainDistance($x->x, $x->y);
		}else{
			return max(abs($this->x - $x), abs($this->z - $z));
		}
	}

	public function length(){
		return sqrt($this->lengthSquared());
	}

	public function lengthSquared(){
		return $this->x * $this->x + $this->y * $this->y + $this->z * $this->z;
	}

	public function normalize(){
		$len = $this->length();
		if($len != 0){
			return $this->divide($len);
		}

		return new Vector3(0, 0, 0);
	}

	public function dot(Vector3 $v){
		return $this->x * $v->x + $this->y * $v->y + $this->z * $v->z;
	}

	public function cross(Vector3 $v){
		return new Vector3(
			$this->y * $v->z - $this->z * $v->y,
			$this->z * $v->x - $this->x * $v->z,
			$this->x * $v->y - $this->y * $v->x
		);
	}

	public function __toString(){
		return "Vector3(x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

}
