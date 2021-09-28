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

namespace pocketmine\block\utils;

use pocketmine\math\Facing;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static LeverFacing DOWN_AXIS_X()
 * @method static LeverFacing DOWN_AXIS_Z()
 * @method static LeverFacing EAST()
 * @method static LeverFacing NORTH()
 * @method static LeverFacing SOUTH()
 * @method static LeverFacing UP_AXIS_X()
 * @method static LeverFacing UP_AXIS_Z()
 * @method static LeverFacing WEST()
 */
final class LeverFacing{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("up_axis_x", Facing::UP),
			new self("up_axis_z", Facing::UP),
			new self("down_axis_x", Facing::DOWN),
			new self("down_axis_z", Facing::DOWN),
			new self("north", Facing::NORTH),
			new self("east", Facing::EAST),
			new self("south", Facing::SOUTH),
			new self("west", Facing::WEST),
		);
	}

	private function __construct(string $enumName, private int $facing){
		$this->Enum___construct($enumName);
	}

	public function getFacing() : int{ return $this->facing; }
}
