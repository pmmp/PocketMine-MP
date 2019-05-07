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

namespace pocketmine\world;

use pocketmine\math\Vector3;
use function assert;

class Position extends Vector3{

	/** @var World */
	public $world = null;

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param World $world
	 */
	public function __construct($x = 0, $y = 0, $z = 0, ?World $world = null){
		parent::__construct($x, $y, $z);
		$this->setWorld($world);
	}

	public static function fromObject(Vector3 $pos, ?World $world = null){
		return new Position($pos->x, $pos->y, $pos->z, $world);
	}

	/**
	 * Return a Position instance
	 *
	 * @return Position
	 */
	public function asPosition() : Position{
		return new Position($this->x, $this->y, $this->z, $this->world);
	}

	/**
	 * Returns the target world, or null if the target is not valid.
	 * If a reference exists to a world which is closed, the reference will be destroyed and null will be returned.
	 *
	 * @return World|null
	 */
	public function getWorld(){
		if($this->world !== null and $this->world->isClosed()){
			\GlobalLogger::get()->debug("Position was holding a reference to an unloaded world");
			$this->world = null;
		}

		return $this->world;
	}

	/**
	 * Sets the target world of the position.
	 *
	 * @param World|null $world
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException if the specified World has been closed
	 */
	public function setWorld(?World $world){
		if($world !== null and $world->isClosed()){
			throw new \InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->world = $world;
		return $this;
	}

	/**
	 * Checks if this object has a valid reference to a loaded world
	 *
	 * @return bool
	 */
	public function isValid() : bool{
		if($this->world !== null and $this->world->isClosed()){
			$this->world = null;

			return false;
		}

		return $this->world !== null;
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

		return Position::fromObject(parent::getSide($side, $step), $this->world);
	}

	public function __toString(){
		return "Position(world=" . ($this->isValid() ? $this->getWorld()->getDisplayName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

	public function equals(Vector3 $v) : bool{
		if($v instanceof Position){
			return parent::equals($v) and $v->getWorld() === $this->getWorld();
		}
		return parent::equals($v);
	}
}
