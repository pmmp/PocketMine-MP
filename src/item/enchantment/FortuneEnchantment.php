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
use pocketmine\item\VanillaItems;
use function min;
use function mt_getrandmax;
use function mt_rand;
use const PHP_INT_MAX;

class FortuneEnchantment extends Enchantment{
	/**
	 * Gives a weight of 2 to a normal drop chance and adds a weight of 1 for each extra drop multiplier.
	 *
	 * @return Item[]
	 */
	public function mineralDrops(Item $item, int $min, int $max, int $fortuneLevel) : array{
		$count = mt_rand($min, $max);
		$chanceForNoMoreDrop = 2 / ($fortuneLevel + 2);
		$rdm = mt_rand() / mt_getrandmax();
		if ($fortuneLevel > 0 && $rdm > $chanceForNoMoreDrop) {
			$count = mt_rand($min, $max * ($fortuneLevel + 1));
		}
		return [
			$item->setCount($count)
		];
	}

	/**
	 * Discreet drop, increases the maximum number of items that can be dropped by the fortune level.
	 *
	 * @param int $maximumDropLimitation As minecraft doc, this is the maximum number of drops that can be dropped by this enchantment.
	 *                                   If a drop higher than these maximums is rolled, it is rounded down to the capacity.
	 * @return Item[]
	 */
	public function discreteDrops(Item $item, int $min, int $max, int $fortuneLevel, int $maximumDropLimitation = PHP_INT_MAX) : array{
		$max = min(
			$maximumDropLimitation,
			$max + $fortuneLevel
		);
		$count = mt_rand($min, $max);
		return [
			$item->setCount($count)
		];
	}

	/**
	 * Grass have a fixed chance to drop wheat seed.
	 * Fortune level increases the maximum number of seeds that can be dropped.
	 * A discrete uniform distribution is used to determine the number of seeds dropped.
	 *
	 * @return Item[]
	 */
	public function grassDrops(int $fortuneLevel) : array{
		if(mt_rand(0, 7) === 0){
			$drop = mt_rand(1, 7);
			if ($drop <= 1 + 2 * $fortuneLevel) {
				return [
					VanillaItems::WHEAT_SEEDS()->setCount($drop)
				];
			}
		}

		return [];
	}

	/**
	 * Binomial drop, the fortune level increases the number of tests for the distribution.
	 *
	 * @param float $p The probability of the item being dropped.
	 * @return Item[]
	 */
	public function binomialDrops(Item $item, int $fortuneLevel = 0, int $baseCount = 0, float $p = 4 / 7) : array{
		$count = $baseCount;
		for($i = 0; $i < 3 + $fortuneLevel; ++$i){
			if(mt_rand() / mt_getrandmax() < $p){
				++$count;
			}
		}
		return [
			$item->setCount($count)
		];
	}
}
