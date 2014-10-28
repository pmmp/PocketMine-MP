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

	/** @var Position[] */
	private static $positionList = [];
	private static $nextPosition = 0;
	
	/** @var Level */
	public $level = null;

	public static function clearPositions(){
		self::$nextPosition = 0;
		self::$positionList = [];
	}

	public static function clearPositionList(){
		if(self::$nextPosition > 65536){
			self::clearVectors();
		}else{
			self::$nextPosition = 0;
		}
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 * @param Level $level
	 *
	 * @return Position
	 */
	public static function createPosition($x, $y, $z, Level $level){
		if(self::$nextPosition >= count(self::$positionList)){
			self::$positionList[] = new Position(0, 0, 0, null);
		}

		return self::$positionList[self::$nextPosition++]->setLevel($level)->setComponents($x, $y, $z);
	}

	public static function clonePosition(Position $pos){
		if(self::$nextPosition >= count(self::$positionList)){
			self::$positionList[] = new Position(0, 0, 0, null);
		}

		return self::$positionList[self::$nextPosition++]->setLevel($pos->level)->setComponents($pos->x, $pos->y, $pos->z);
	}

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
		return self::createPosition($pos->x, $pos->y, $pos->z, $level);
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
		return isset($this->level) and $this->level instanceof Level;
	}

	/**
	 * Marks the level reference as strong so it won't be collected
	 * by the garbage collector.
	 *
	 * @deprecated
	 *
	 * @return bool
	 */
	public function setStrong(){
		return false;
	}

	/**
	 * Marks the level reference as weak so it won't have effect against
	 * the garbage collector decision.
	 *
	 * @deprecated
	 *
	 * @return bool
	 */
	public function setWeak(){
		return false;
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
		if(!$this->isValid()){
			throw new LevelException("Undefined Level reference");
		}

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
		if(($pos instanceof Position) and $pos->getLevel() !== $this->getLevel()){
			return PHP_INT_MAX;
		}

		return parent::distance($pos);
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
