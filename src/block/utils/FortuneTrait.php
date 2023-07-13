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
	 * If a random number between 0-1 is greater than 2/(level+2), this multiplies the max drop amount by level+1, and
	 * picks a random amount between the minimum and multiplied maximum. Each level of fortune increases the chance of
	 * fortune activation, and also increases the maximum drop limit when activated.
	 *
	 * Otherwise, returns a random amount of the item between the minimum and original maximum.
	 *
	 * @return Item[]
	 */
	protected function weightedDrops(Item $item, int $fortuneLevel, int $min, int $max) : array{
		if($max < $min){
			throw new \InvalidArgumentException("Maximum drop amount must be greater than or equal to minimum drop amount");
		}

		if($fortuneLevel > 0 && mt_rand() / mt_getrandmax() > 2 / ($fortuneLevel + 2)){
			$count = mt_rand($min, $max * ($fortuneLevel + 1));
		}else{
			$count = mt_rand($min, $max);
		}
		return [
			$item->setCount($count)
		];
	}

	/**
	 * Increases the drop amount according to a binomial distribution. The function will roll 3+level times, and add 1
	 * if a random number between 0-1 is less than the given probability. Each level of fortune adds one extra roll.
	 *
	 * @param float  $chance     The chance of adding 1 to the amount for each roll, must be in the range 0-1
	 * @param Item[] $extraDrops Extra drops to add to the result
	 *
	 * @return Item[]
	 */
	protected function binomialDrops(Item $item, int $fortuneLevel, int $min = 0, float $chance = 4 / 7, array $extraDrops = []) : array{
		$count = $min;
		for($i = 0; $i < 3 + $fortuneLevel; ++$i){
			if(mt_rand() / mt_getrandmax() < $chance){
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
	protected function grassDrops(int $fortuneLevel) : array{
		if(mt_rand(0, 7) === 0){
			$drop = mt_rand(1, 7);
			if($drop <= 1 + 2 * $fortuneLevel){
				return [
					VanillaItems::WHEAT_SEEDS()->setCount($drop)
				];
			}
		}

		return [];
	}

	/**
	 * Adds the fortune level to the base max and picks a random number between the minimim and adjusted maximum.
	 * Each amount in the range has an equal chance of being picked.
	 *
	 * @param int $maxBase  Maximum base amount, as if the fortune level was 0
	 * @param int $maxLimit Maximum amount to return, regardless of the other parameters
	 *
	 * @return Item[]
	 */
	protected function discreteDrops(Item $item, int $fortuneLevel, int $min, int $maxBase, int $maxLimit = PHP_INT_MAX) : array{
		if($maxBase < $min){
			throw new \InvalidArgumentException("Minimum base drop amount must be less than or equal to maximum base drop amount");
		}

		$max = min(
			$maxLimit,
			$maxBase + $fortuneLevel
		);
		$count = mt_rand($min, $max);
		return [
			$item->setCount($count)
		];
	}
}
