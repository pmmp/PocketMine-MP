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

namespace pocketmine\item;

use pocketmine\block\utils\WoodType;

enum BoatType{
	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	case MANGROVE;

	public function getWoodType() : WoodType{
		return match($this){
			self::OAK => WoodType::OAK,
			self::SPRUCE => WoodType::SPRUCE,
			self::BIRCH => WoodType::BIRCH,
			self::JUNGLE => WoodType::JUNGLE,
			self::ACACIA => WoodType::ACACIA,
			self::DARK_OAK => WoodType::DARK_OAK,
			self::MANGROVE => WoodType::MANGROVE,
		};
	}

	public function getDisplayName() : string{
		return $this->getWoodType()->getDisplayName();
	}
}
