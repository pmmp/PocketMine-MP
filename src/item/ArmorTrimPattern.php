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

enum ArmorTrimPattern : string{

	case COAST = "coast";
	case DUNE = "dune";
	case EYE = "eye";
	case HOST = "host";
	case RAISER = "raiser";
	case RIB = "rib";
	case SENTRY = "sentry";
	case SHAPER = "shaper";
	case SILENCE = "silence";
	case SNOUT = "snout";
	case SPIRE = "spire";
	case TIDE = "tide";
	case VEX = "vex";
	case WARD = "ward";
	case WAYFINDER = "wayfinder";
	case WILD = "wild";

	public const TEMPLATE_SUFFIX = "_armor_trim_smithing_template";

	public static function fromItem(Item $item) : ?ArmorTrimPattern{
		return match($item->getTypeId()){
			ItemTypeIds::COAST_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::COAST,
			ItemTypeIds::DUNE_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::DUNE,
			ItemTypeIds::EYE_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::EYE,
			ItemTypeIds::HOST_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::HOST,
			ItemTypeIds::RAISER_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::RAISER,
			ItemTypeIds::RIB_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::RIB,
			ItemTypeIds::SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::SENTRY,
			ItemTypeIds::SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::SHAPER,
			ItemTypeIds::SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::SILENCE,
			ItemTypeIds::SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::SNOUT,
			ItemTypeIds::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::SPIRE,
			ItemTypeIds::TIDE_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::TIDE,
			ItemTypeIds::VEX_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::VEX,
			ItemTypeIds::WARD_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::WARD,
			ItemTypeIds::WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::WAYFINDER,
			ItemTypeIds::WILD_ARMOR_TRIM_SMITHING_TEMPLATE => ArmorTrimPattern::WILD,
			default => null
		};
	}
}
