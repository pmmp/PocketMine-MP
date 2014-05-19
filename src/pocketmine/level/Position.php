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

namespace pocketmine\level;

use pocketmine\math\Vector3 as Vector3;

class Position extends Vector3{

	/** @var Level */
	public $level = null;

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param Level $level
	 */
	public function __construct($x = 0, $y = 0, $z = 0, Level $level){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->level = $level;
	}

	public static function fromObject(Vector3 $pos, Level $level){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}

	public function getLevel(){
		return $this->level;
	}

	/**
	 * Returns a side Vector
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Position
	 */
	public function getSide($side, $step = 1){
		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	/**
	 * Returns the distance between two points or objects
	 *
	 * @param Vector3 $pos
	 *
	 * @return float
	 */
	public function distance(Vector3 $pos){
		if(($pos instanceof Position) and $pos->level !== $this->level){
			return PHP_INT_MAX;
		}

		return parent::distance($pos);
	}

	public function __toString(){
		return "Position(level=" . $this->level->getName() . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

}