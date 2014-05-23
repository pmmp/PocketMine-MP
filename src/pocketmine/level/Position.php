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

	/** @var \WeakRef<Level> */
	public $level = null;

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param Level $level
	 * @param bool  $strong
	 */
	public function __construct($x = 0, $y = 0, $z = 0, Level $level, $strong = false){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->level = new \WeakRef($level);
		if($strong === true){
			$this->level->acquire();
		}
	}

	public static function fromObject(Vector3 $pos, Level $level, $strong = false){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}

	/**
	 * @return Level
	 */
	public function getLevel(){
		return $this->level->get();
	}

	public function setLevel(Level $level, $strong = false){
		$this->level = new \WeakRef($level);
		if($strong === true){
			$this->level->acquire();
		}
	}

	/**
	 * Checks if this object has a valid reference to a Level
	 *
	 * @return bool
	 */
	public function isValid(){
		return isset($this->level) and $this->level->valid();
	}

	/**
	 * Marks the level reference as strong so it won't be collected
	 * by the garbage collector.
	 *
	 * @return bool
	 */
	public function setStrong(){
		return $this->level->acquire();
	}

	/**
	 * Marks the level reference as weak so it won't have effect against
	 * the garbage collector decision.
	 *
	 * @return bool
	 */
	public function setWeak(){
		return $this->level->release();
	}

	/**
	 * Returns a side Vector
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Position
	 *
	 * @throws \RuntimeException
	 */
	public function getSide($side, $step = 1){
		if(!$this->isValid()){
			throw new \RuntimeException("Undefined Level reference");
		}

		return Position::fromObject(parent::getSide($side, $step), $this->getLevel());
	}

	/**
	 * Returns the distance between two points or objects
	 *
	 * @param Vector3 $pos
	 *
	 * @return float
	 */
	public function distance(Vector3 $pos){
		if(($pos instanceof Position) and $pos->getLevel() !== $this->getLevel()){
			return PHP_INT_MAX;
		}

		return parent::distance($pos);
	}

	public function __toString(){
		return "Position(level=" . ($this->isValid() ? $this->getLevel()->getID() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

}