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

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;

class GoldenAppleEnchanted extends GoldenApple{

	public function __construct(){
		Food::__construct(self::ENCHANTED_GOLDEN_APPLE, 0, "Enchanted Golden Apple"); //skip parent constructor
	}

	public function getAdditionalEffects() : array{
		return [
			new EffectInstance(Effect::REGENERATION(), 600, 4),
			new EffectInstance(Effect::ABSORPTION(), 2400, 3),
			new EffectInstance(Effect::RESISTANCE(), 6000),
			new EffectInstance(Effect::FIRE_RESISTANCE(), 6000)
		];
	}
}
