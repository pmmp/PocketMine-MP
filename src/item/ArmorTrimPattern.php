<?php

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

	private const TEMPLATE_SUFFIX = "_armor_trim_smithing_template";

	public static function fromItem(Item $item) : ?ArmorTrimPattern{
		foreach(StringToItemParser::getInstance()->lookupAliases($item) as $alias){
			if (!str_ends_with($alias, self::TEMPLATE_SUFFIX)){
				continue;
			}
			return self::tryFrom(substr($alias, 0, strlen($alias) - strlen(self::TEMPLATE_SUFFIX)));
		}
		return null;
	}

	public function getItemId() : string{
		return "minecraft:" . $this->value . self::TEMPLATE_SUFFIX;
	}
}
