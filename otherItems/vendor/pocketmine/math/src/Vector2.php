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

declare(strict_types=1);

namespace pocketmine\math;

use function abs;
use function ceil;
use function floor;
use function round;
use function sqrt;

class Vector2{
	/** @var float */
	public $x;
	/** @var float */
	public $y;

	public function __construct(float $x = 0, float $y = 0){
		$this->x = $x;
		$this->y = $y;
	}

	public function getX() : float{
		return $this->x;
	}

	public function getY() : float{
		return $this->y;
	}

	public function getFloorX() : int{
		return (int) floor($this->x);
	}

	public function getFloorY() : int{
		return (int) floor($this->y);
	}

	/**
	 * @param Vector2|float $x
	 * @param float         $y
	 *
	 * @return Vector2
	 */
	public function add($x, float $y = 0) : Vector2{
		if($x instanceof Vector2){
			return $this->add($x->x, $x->y);
		}else{
			return new Vector2($this->x + $x, $this->y + $y);
		}
	}

	/**
	 * @param Vector2|float $x
	 * @param float         $y
	 *
	 * @return Vector2
	 */
	public function subtract($x, float $y = 0) : Vector2{
		if($x instanceof Vector2){
			return $this->add(-$x->x, -$x->y);
		}else{
			return $this->add(-$x, -$y);
		}
	}

	public function ceil() : Vector2{
		return new Vector2((int) ceil($this->x), (int) ceil($this->y));
	}

	public function floor() : Vector2{
		return new Vector2((int) floor($this->x), (int) floor($this->y));
	}

	public function round() : Vector2{
		return new Vector2(round($this->x), round($this->y));
	}

	public function abs() : Vector2{
		return new Vector2(abs($this->x), abs($this->y));
	}

	public function multiply(float $number) : Vector2{
		return new Vector2($this->x * $number, $this->y * $number);
	}

	public function divide(float $number) : Vector2{
		return new Vector2($this->x / $number, $this->y / $number);
	}

	/**
	 * @param Vector2|float $x
	 * @param float         $y
	 *
	 * @return float
	 */
	public function distance($x, float $y = 0) : float{
		if($x instanceof Vector2){
			return sqrt($this->distanceSquared($x->x, $x->y));
		}else{
			return sqrt($this->distanceSquared($x, $y));
		}
	}

	/**
	 * @param Vector2|float $x
	 * @param float         $y
	 *
	 * @return float
	 */
	public function distanceSquared($x, float $y = 0) : float{
		if($x instanceof Vector2){
			return $this->distanceSquared($x->x, $x->y);
		}else{
			return (($this->x - $x) ** 2) + (($this->y - $y) ** 2);
		}
	}

	public function length() : float{
		return sqrt($this->lengthSquared());
	}

	public function lengthSquared() : float{
		return $this->x * $this->x + $this->y * $this->y;
	}

	public function normalize() : Vector2{
		$len = $this->lengthSquared();
		if($len > 0){
			return $this->divide(sqrt($len));
		}

		return new Vector2(0, 0);
	}

	public function dot(Vector2 $v) : float{
		return $this->x * $v->x + $this->y * $v->y;
	}

	public function __toString(){
		return "Vector2(x=" . $this->x . ",y=" . $this->y . ")";
	}

}
