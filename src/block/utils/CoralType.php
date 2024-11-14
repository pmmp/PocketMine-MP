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

use pocketmine\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static CoralType BRAIN()
 * @method static CoralType BUBBLE()
 * @method static CoralType FIRE()
 * @method static CoralType HORN()
 * @method static CoralType TUBE()
 */
enum CoralType{
	use LegacyEnumShimTrait;

	case TUBE;
	case BRAIN;
	case BUBBLE;
	case FIRE;
	case HORN;

	public function getDisplayName() : string{
		return match($this){
			self::TUBE => "Tube",
			self::BRAIN => "Brain",
			self::BUBBLE => "Bubble",
			self::FIRE => "Fire",
			self::HORN => "Horn",
		};
	}
}
