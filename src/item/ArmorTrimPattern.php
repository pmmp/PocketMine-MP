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

use function str_ends_with;
use function strlen;
use function substr;

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
	case SNOUT = "snouth";
	case SPIRE = "spire";
	case TIDE = "tide";
	case VEX = "vex";
	case WARD = "ward";
	case WAYFINDER = "wayfinder";
	case WILD = "wild";

	public const TEMPLATE_SUFFIX = "_armor_trim_smithing_template";

	public static function fromItem(Item $item) : ?ArmorTrimPattern{
		foreach(StringToItemParser::getInstance()->lookupAliases($item) as $alias){
			if (!str_ends_with($alias, self::TEMPLATE_SUFFIX)){
				continue;
			}
			return self::tryFrom(substr($alias, 0, strlen($alias) - strlen(self::TEMPLATE_SUFFIX)));
		}
		return null;
	}
}
