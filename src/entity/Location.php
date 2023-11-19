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

namespace pocketmine\entity;

use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;

class Location extends Position{

	public float $yaw;
	public float $pitch;

	public function __construct(float $x, float $y, float $z, ?World $world, float $yaw, float $pitch){
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		parent::__construct($x, $y, $z, $world);
	}

	public static function fromObject(Vector3 $pos, ?World $world, float $yaw = 0.0, float $pitch = 0.0) : Location{
		return new Location($pos->x, $pos->y, $pos->z, $world ?? (($pos instanceof Position) ? $pos->world : null), $yaw, $pitch);
	}

	/**
	 * Return a Location instance
	 */
	public function asLocation() : Location{
		return new Location($this->x, $this->y, $this->z, $this->world, $this->yaw, $this->pitch);
	}

	public function getYaw() : float{
		return $this->yaw;
	}

	public function getPitch() : float{
		return $this->pitch;
	}

	public function __toString(){
		return "Location (world=" . ($this->isValid() ? $this->getWorld()->getDisplayName() : "null") . ", x=$this->x, y=$this->y, z=$this->z, yaw=$this->yaw, pitch=$this->pitch)";
	}

	public function equals(Vector3 $v) : bool{
		if($v instanceof Location){
			return parent::equals($v) && $v->yaw == $this->yaw && $v->pitch == $this->pitch;
		}
		return parent::equals($v);
	}

	public function add(float|int $x, float|int $y, float|int $z) : Location{
		return Location::fromObject(parent::add($x, $y, $z), $this->world, $this->yaw, $this->pitch);
	}

	public function multiply(float $number) : Location{
		return Location::fromObject(parent::multiply($number), $this->world, $this->yaw, $this->pitch);
	}

	public function divide(float $number) : Location{
		return Location::fromObject(parent::divide($number), $this->world, $this->yaw, $this->pitch);
	}

	public function ceil() : Location{
		return Location::fromObject(parent::ceil(), $this->world, $this->yaw, $this->pitch);
	}

	public function floor() : Location{
		return Location::fromObject(parent::floor(), $this->world, $this->yaw, $this->pitch);
	}

	/**
	 * @phpstan-param 1|2|3|4 $mode
	 */
	public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP) : Location{
		return Location::fromObject(parent::round($precision, $mode), $this->world, $this->yaw, $this->pitch);
	}

	public function abs() : Location{
		return Location::fromObject(parent::abs(), $this->world, $this->yaw, $this->pitch);
	}
}
