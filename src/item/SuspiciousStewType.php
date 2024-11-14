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

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static SuspiciousStewType ALLIUM()
 * @method static SuspiciousStewType AZURE_BLUET()
 * @method static SuspiciousStewType BLUE_ORCHID()
 * @method static SuspiciousStewType CORNFLOWER()
 * @method static SuspiciousStewType DANDELION()
 * @method static SuspiciousStewType LILY_OF_THE_VALLEY()
 * @method static SuspiciousStewType OXEYE_DAISY()
 * @method static SuspiciousStewType POPPY()
 * @method static SuspiciousStewType TULIP()
 * @method static SuspiciousStewType WITHER_ROSE()
 */
enum SuspiciousStewType{
	use LegacyEnumShimTrait;

	case POPPY;
	case CORNFLOWER;
	case TULIP;
	case AZURE_BLUET;
	case LILY_OF_THE_VALLEY;
	case DANDELION;
	case BLUE_ORCHID;
	case ALLIUM;
	case OXEYE_DAISY;
	case WITHER_ROSE;

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array{
		return match($this){
			self::POPPY => [new EffectInstance(VanillaEffects::NIGHT_VISION(), 80)],
			self::CORNFLOWER => [new EffectInstance(VanillaEffects::JUMP_BOOST(), 80)],
			self::TULIP => [new EffectInstance(VanillaEffects::WEAKNESS(), 140)],
			self::AZURE_BLUET => [new EffectInstance(VanillaEffects::BLINDNESS(), 120)],
			self::LILY_OF_THE_VALLEY => [new EffectInstance(VanillaEffects::POISON(), 200)],
			self::DANDELION,
			self::BLUE_ORCHID => [new EffectInstance(VanillaEffects::SATURATION(), 6)],
			self::ALLIUM => [new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 40)],
			self::OXEYE_DAISY => [new EffectInstance(VanillaEffects::REGENERATION(), 120)],
			self::WITHER_ROSE => [new EffectInstance(VanillaEffects::WITHER(), 120)]
		};
	}
}
