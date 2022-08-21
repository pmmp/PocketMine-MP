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
use pocketmine\utils\AssumptionFailedError;
use function assert;

class Position extends Vector3{

	/** @var World|null */
	public $world = null;

	/**
	 * @param float|int $x
	 * @param float|int $y
	 * @param float|int $z
	 */
	public function __construct($x, $y, $z, ?World $world){
		parent::__construct($x, $y, $z);
		if($world !== null && !$world->isLoaded()){
			throw new \InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->world = $world;
	}

	/**
	 * @return Position
	 */
	public static function fromObject(Vector3 $pos, ?World $world){
		return new Position($pos->x, $pos->y, $pos->z, $world);
	}

	/**
	 * Return a Position instance
	 */
	public function asPosition() : Position{
		return new Position($this->x, $this->y, $this->z, $this->world);
	}

	/**
	 * Returns the position's world if valid. Throws an error if the world is unexpectedly invalid.
	 *
	 * @throws AssumptionFailedError
	 */
	public function getWorld() : World{
		if($this->world === null || !$this->world->isLoaded()){
			throw new AssumptionFailedError("Position world is null or has been unloaded");
		}

		return $this->world;
	}

	/**
	 * Checks if this object has a valid reference to a loaded world
	 */
	public function isValid() : bool{
		if($this->world !== null && !$this->world->isLoaded()){
			$this->world = null;

			return false;
		}

		return $this->world !== null;
	}

	/**
	 * Returns a side Vector
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
			return parent::equals($v) && $v->world === $this->world;
		}
		return parent::equals($v);
	}
}
