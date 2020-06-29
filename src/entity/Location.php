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

	/** @var float */
	public $yaw;
	/** @var float */
	public $pitch;

	public function __construct(float $x, float $y, float $z, float $yaw = 0.0, float $pitch = 0.0, ?World $world = null){
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		parent::__construct($x, $y, $z, $world);
	}

	/**
	 * @return Location
	 */
	public static function fromObject(Vector3 $pos, ?World $world = null, float $yaw = 0.0, float $pitch = 0.0){
		return new Location($pos->x, $pos->y, $pos->z, $yaw, $pitch, $world ?? (($pos instanceof Position) ? $pos->world : null));
	}

	/**
	 * Return a Location instance
	 */
	public function asLocation() : Location{
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->world);
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
			return parent::equals($v) and $v->yaw == $this->yaw and $v->pitch == $this->pitch;
		}
		return parent::equals($v);
	}
}
