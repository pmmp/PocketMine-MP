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

namespace pocketmine\command\overload;

use pocketmine\math\Vector3;
use pocketmine\utils\Limits;
use pocketmine\world\World;

final class RelativeXYZ{

	public function __construct(
		private RelativeFloat $x,
		private RelativeFloat $y,
		private RelativeFloat $z
	){}

	public function resolve(Vector3 $base) : Vector3{
		return new Vector3(
			//TODO: these bounds should be parameterised somehow
			$this->x->resolve($base->x, Limits::INT32_MIN, Limits::INT32_MAX),
			$this->y->resolve($base->y, World::Y_MIN, World::Y_MAX),
			$this->z->resolve($base->z, Limits::INT32_MIN, Limits::INT32_MAX)
		);
	}
}
