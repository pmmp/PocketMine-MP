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

namespace pocketmine\level;

use pocketmine\math\Vector3;
use pocketmine\utils\MainLogger;
use function assert;

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
		parent::__construct($x, $y, $z);
		$this->setLevel($level);
	}

	public static function fromObject(Vector3 $pos, Level $level = null){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}

	/**
	 * Return a Position instance
	 *
	 * @return Position
	 */
	public function asPosition() : Position{
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	/**
	 * Returns the target Level, or null if the target is not valid.
	 * If a reference exists to a Level which is closed, the reference will be destroyed and null will be returned.
	 *
	 * @return Level|null
	 */
	public function getLevel(){
		if($this->level !== null and $this->level->isClosed()){
			MainLogger::getLogger()->debug("Position was holding a reference to an unloaded world");
			$this->level = null;
		}

		return $this->level;
	}

	/**
	 * Sets the target Level of the position.
	 *
	 * @param Level|null $level
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException if the specified Level has been closed
	 */
	public function setLevel(Level $level = null){
		if($level !== null and $level->isClosed()){
			throw new \InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->level = $level;
		return $this;
	}

	/**
	 * @param Position|int $x
	 * @param int         $y
	 * @param int         $z
	 *
	 * @return Vector3
	 */
	public function add($x, $y = 0, $z = 0) : Position{
		if($x instanceof Position){
			return new Position($this->x + $x->x, $this->y + $x->y, $this->z + $x->z);
		}else{
			return new Position($this->x + $x, $this->y + $y, $this->z + $z);
		}
	}
	
	/**
	 * @param Position|int $x
	 * @param int         $y
	 * @param int         $z
	 *
	 * @return Vector3
	 */
	public function subtract($x = 0, $y = 0, $z = 0) : Position{
		if($x instanceof Position){
			return $this->add(-$x->x, -$x->y, -$x->z);
		}else{
			return $this->add(-$x, -$y, -$z);
		}
	}
	
	public function multiply(float $number) : Position{
		return new Position($this->x * $number, $this->y * $number, $this->z * $number, $this->level);
	}
	
	public function divide(float $number) : Position{
		return new Position($this->x / $number, $this->y / $number, $this->z / $number, $this->level);
	}
	
	public function ceil() : Position{
		return new Position((int) ceil($this->x), (int) ceil($this->y), (int) ceil($this->z), $this->level);
	}
	
	public function floor() : Position{
		return new Position((int) floor($this->x), (int) floor($this->y), (int) floor($this->z), $this->level);
	}
	
	public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP) : Position{
		return $precision > 0 ?
			new Position(round($this->x, $precision, $mode), round($this->y, $precision, $mode), round($this->z, $precision, $mode), $this->level) :
			new Position((int) round($this->x, $precision, $mode), (int) round($this->y, $precision, $mode), (int) round($this->z, $precision, $mode), $this->level);
	}
	
	public function abs() : Position{
		return new Position(abs($this->x), abs($this->y), abs($this->z), $this->level);
	}

	/**
	 * Checks if this object has a valid reference to a loaded Level
	 *
	 * @return bool
	 */
	public function isValid() : bool{
		if($this->level !== null and $this->level->isClosed()){
			$this->level = null;

			return false;
		}

		return $this->level !== null;
	}

	/**
	 * Returns a side Vector
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Position
	 */
	public function getSide(int $side, int $step = 1){
		assert($this->isValid());

		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString(){
		return "Position(level=" . ($this->isValid() ? $this->getLevel()->getName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

	public function equals(Vector3 $v) : bool{
		if($v instanceof Position){
			return parent::equals($v) and $v->getLevel() === $this->getLevel();
		}
		return parent::equals($v);
	}
}
