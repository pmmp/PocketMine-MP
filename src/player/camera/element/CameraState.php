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

namespace pocketmine\player\camera\element;

use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use function atan2;
use function sqrt;
use const M_PI;

class CameraState implements CameraElement{

	public function __construct(
		private ?Vector3 $position,
		private ?float $yaw = null,
		private ?float $pitch = null
	){
		if ($position === null && $yaw === null && $pitch === null) {
			throw new \InvalidArgumentException("All camera state properties cannot be null");
		}

		if ($yaw === null xor $pitch === null) {
			throw new \InvalidArgumentException("Invalid rotation, both yaw and pitch must be defined");
		}
	}

	public static function fromLocation(Location $location) : self{
		return new self($location->asVector3(), $location->yaw, $location->pitch);
	}

	public static function lookingAt(Vector3 $position, Vector3 $target) : self{
		$horizontal = sqrt(($target->x - $position->x) ** 2 + ($target->z - $position->z) ** 2);
		$vertical = $target->y - $position->y;
		$pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $position->x;
		$zDist = $target->z - $position->z;

		$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($yaw < 0){
			$yaw += 360.0;
		}

		return new self($position, $yaw, $pitch);
	}

	public function getPosition() : ?Vector3{
		return $this->position;
	}

	public function getYaw() : ?float{
		return $this->yaw;
	}

	public function getPitch() : ?float{
		return $this->pitch;
	}
}
