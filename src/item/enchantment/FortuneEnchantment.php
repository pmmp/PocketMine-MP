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

namespace pocketmine\item\enchantment;

use pocketmine\item\Item;
use function mt_getrandmax;
use function mt_rand;

class FortuneEnchantment extends Enchantment{
	/**
	 * @param ?int $step Defines the step that will be added to the maximum at each wealth level.
	 *                   Default: $max * ($fortuneLevel + 1), if set :  $max + $step * $fortuneLevel
	 * @return Item[]
	 */
	public function mineralDrops(Item $item, int $min, int $max, int $fortuneLevel, ?int $step = null) : array{
		$count = mt_rand($min, $max);
		$chanceForNoMoreDrop = 2 / ($fortuneLevel + 2);
		$rdm = mt_rand() / mt_getrandmax();
		if ($fortuneLevel > 0 && $rdm > $chanceForNoMoreDrop) {
			if ($step !== null) {
				$maxBonus = $max + $step * $fortuneLevel;
			} else {
				$maxBonus = $max * ($fortuneLevel + 1);
			}
			$count = mt_rand($min, $maxBonus);
		}
		return [
			$item->setCount($count)
		];
	}
}
