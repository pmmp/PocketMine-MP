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

use pocketmine\math\Vector3;
use pocketmine\utils\LevelException;

class Position extends Vector3{

	/** @var Level */
	public $level = null;

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param Level $level
	 */
	public function __construct($x = 0, $y = 0, $z = 0, Level $level = null){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->level = $level;
	}

	public static function fromObject(Vector3 $pos, Level $level = null){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}

	/**
	 * @return Level
	 */
	public function getLevel(){
		return $this->level;
	}

	public function setLevel(Level $level){
		$this->level = $level;
		return $this;
	}

	/**
	 * Checks if this object has a valid reference to a Level
	 *
	 * @return bool
	 */
	public function isValid(){
		return $this->getLevel() instanceof Level;
	}

	/**
	 * Returns a side Vector
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Position
	 *
	 * @throws LevelException
	 */
	public function getSide($side, $step = 1){
		assert($this->isValid());

		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString(){
		return "Position(level=" . ($this->isValid() ? $this->getLevel()->getName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return Position
	 */
	public function setComponents($x, $y, $z){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		return $this;
	}

}
