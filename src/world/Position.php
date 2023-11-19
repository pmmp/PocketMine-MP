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
	public ?World $world = null;

	public function __construct(float|int $x, float|int $y, float|int $z, ?World $world){
		parent::__construct($x, $y, $z);
		if($world !== null && !$world->isLoaded()){
			throw new \InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->world = $world;
	}

	public function add(float|int $x, float|int $y, float|int $z) : Position{
		return Position::fromObject(parent::add($x, $y, $z), $this->world);
	}

	public function multiply(float $number) : Position{
		return Position::fromObject(parent::multiply($number), $this->world);
	}

	public function divide(float $number) : Position{
		return Position::fromObject(parent::divide($number), $this->world);
	}

	public function ceil() : Position{
		return Position::fromObject(parent::ceil(), $this->world);
	}

	public function floor() : Position{
		return Position::fromObject(parent::floor(), $this->world);
	}

	/**
	 * @phpstan-param 1|2|3|4 $mode
	 */
	public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP) : Position{
		return Position::fromObject(parent::round($precision, $mode), $this->world);
	}

	public function abs() : Position{
		return Position::fromObject(parent::abs(), $this->world);
	}

	public static function fromObject(Vector3 $pos, ?World $world) : Position{
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
	 */
	public function getSide(int $side, int $step = 1) : Position{
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
