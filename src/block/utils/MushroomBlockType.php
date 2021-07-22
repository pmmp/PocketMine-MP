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

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static MushroomBlockType ALL_CAP()
 * @method static MushroomBlockType CAP_EAST()
 * @method static MushroomBlockType CAP_MIDDLE()
 * @method static MushroomBlockType CAP_NORTH()
 * @method static MushroomBlockType CAP_NORTHEAST()
 * @method static MushroomBlockType CAP_NORTHWEST()
 * @method static MushroomBlockType CAP_SOUTH()
 * @method static MushroomBlockType CAP_SOUTHEAST()
 * @method static MushroomBlockType CAP_SOUTHWEST()
 * @method static MushroomBlockType CAP_WEST()
 * @method static MushroomBlockType PORES()
 */
final class MushroomBlockType{
	use EnumTrait;

	protected static function setup() : void{
		self::registerAll(
			new self("PORES"),
			new self("CAP_NORTHWEST"),
			new self("CAP_NORTH"),
			new self("CAP_NORTHEAST"),
			new self("CAP_WEST"),
			new self("CAP_MIDDLE"),
			new self("CAP_EAST"),
			new self("CAP_SOUTHWEST"),
			new self("CAP_SOUTH"),
			new self("CAP_SOUTHEAST"),
			new self("ALL_CAP")
		);
	}
}
