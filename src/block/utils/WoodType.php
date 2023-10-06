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
 * @method static WoodType ACACIA()
 * @method static WoodType BIRCH()
 * @method static WoodType CHERRY()
 * @method static WoodType CRIMSON()
 * @method static WoodType DARK_OAK()
 * @method static WoodType JUNGLE()
 * @method static WoodType MANGROVE()
 * @method static WoodType OAK()
 * @method static WoodType SPRUCE()
 * @method static WoodType WARPED()
 */
enum WoodType{
	use LegacyEnumShimTrait;

	case OAK;
	case SPRUCE;
	case BIRCH;
	case JUNGLE;
	case ACACIA;
	case DARK_OAK;
	case MANGROVE;
	case CRIMSON;
	case WARPED;
	case CHERRY;

	public function getDisplayName() : string{
		return match($this){
			self::OAK => "Oak",
			self::SPRUCE => "Spruce",
			self::BIRCH => "Birch",
			self::JUNGLE => "Jungle",
			self::ACACIA => "Acacia",
			self::DARK_OAK => "Dark Oak",
			self::MANGROVE => "Mangrove",
			self::CRIMSON => "Crimson",
			self::WARPED => "Warped",
			self::CHERRY => "Cherry",
		};
	}

	public function isFlammable() : bool{
		return $this !== self::CRIMSON && $this !== self::WARPED;
	}

	public function getStandardLogSuffix() : ?string{
		return $this === self::CRIMSON || $this === self::WARPED ? "Stem" : null;
	}

	public function getAllSidedLogSuffix() : ?string{
		return $this === self::CRIMSON || $this === self::WARPED ? "Hyphae" : null;
	}
}
