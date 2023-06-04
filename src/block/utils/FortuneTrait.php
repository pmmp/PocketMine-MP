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

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use function array_merge;
use function assert;
use function min;
use function mt_getrandmax;
use function mt_rand;
use const PHP_INT_MAX;

trait FortuneTrait{
	/**
	 * @return Item[]
	 */
	public function getDropsForCompatibleTool(Item $item) : array{
		return $this->getFortuneDrops($item);
	}

	/**
	 * @return Item[]
	 */
	public function getFortuneDrops(Item $item) : array{
		$fortuneEnchantment = VanillaEnchantments::FORTUNE();
		return $this->getFortuneDropsForLevel($item->getEnchantmentLevel($fortuneEnchantment));
	}

	/**
	 * @return Item[]
	 */
	abstract protected function getFortuneDropsForLevel(int $level) : array;

	/**
	 * Gives a weight of 2 to a normal drop chance and adds a weight of 1 for each extra drop multiplier.
	 *
	 * @return Item[]
	 */
	protected function weightedDrops(Item $item, int $min, int $max, int $fortuneLevel) : array{
		assert($min <= $max, "Minimum drop amount must be less than or equal to maximum drop amount");

		if ($fortuneLevel > 0 && mt_rand() / mt_getrandmax() > 2 / ($fortuneLevel + 2)) {
			$count = mt_rand($min, $max * ($fortuneLevel + 1));
		} else {
			$count = mt_rand($min, $max);
		}
		return [
			$item->setCount($count)
		];
	}

	/**
	 * Binomial drop, the fortune level increases the number of tests for the distribution.
	 *
	 * @param float  $p          The probability of the item being dropped.
	 * @param Item[] $extraDrops Extra drops to add to the result.
	 * @return Item[]
	 */
	protected function binomialDrops(Item $item, int $fortuneLevel = 0, int $baseCount = 0, float $p = 4 / 7, array $extraDrops = []) : array{
		$count = $baseCount;
		for($i = 0; $i < 3 + $fortuneLevel; ++$i){
			if(mt_rand() / mt_getrandmax() < $p){
				++$count;
			}
		}
		return array_merge(
			$extraDrops, [
				$item->setCount($count)
			]
		);
	}

	/**
	 * Grass have a fixed chance to drop wheat seed.
	 * Fortune level increases the maximum number of seeds that can be dropped.
	 * A discrete uniform distribution is used to determine the number of seeds dropped.
	 *
	 * @return Item[]
	 */
	protected function grassDrops(int $fortuneLevel) : array {
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
	 * Discrete drop increases the maximum number of items that can be dropped by the fortune level.
	 *
	 * @param int $maximum As minecraft doc, this is the maximum number of drops that can be dropped by this enchantment.
	 *                     If a drop higher than these maximums is rolled, it is rounded down to the capacity.
	 * @return Item[]
	 */
	protected function discreteDrops(Item $item, int $minBaseAmount, int $maxBaseAmount, int $fortuneLevel, int $maximum = PHP_INT_MAX) : array{
		assert($minBaseAmount <= $maxBaseAmount, "Minimum base drop amount must be less than or equal to maximum base drop amount");

		$max = min(
			$maximum,
			$maxBaseAmount + $fortuneLevel
		);
		$count = mt_rand($minBaseAmount, $max);
		return [
			$item->setCount($count)
		];
	}
}
