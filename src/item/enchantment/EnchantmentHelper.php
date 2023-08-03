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

use pocketmine\block\BlockTypeIds;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\utils\Random;
use pocketmine\world\Position;
use function abs;
use function array_filter;
use function count;
use function max;
use function min;
use function range;
use function round;

final class EnchantmentHelper{
	private const MAX_BOOKSHELF_COUNT = 15;

	/**
	 * @return EnchantmentOption[]
	 */
	public static function getEnchantOptions(Position $tablePos, Item $input, int $seed) : array{
		if($input->isNull() || $input->hasEnchantments()){
			return [];
		}

		$random = new Random($seed);

		$bookshelfCount = self::countBookshelves($tablePos);
		$baseCost = $random->nextRange(1, 8) + ($bookshelfCount >> 1) + $random->nextRange(0, $bookshelfCount);
		$topCost = (int) max($baseCost / 3, 1);
		$middleCost = (int) ($baseCost * 2 / 3 + 1);
		$bottomCost = max($baseCost, $bookshelfCount * 2);

		return [
			self::createEnchantOption($random, $input, $topCost, 0),
			self::createEnchantOption($random, $input, $middleCost, 1),
			self::createEnchantOption($random, $input, $bottomCost, 2),
		];
	}

	private static function countBookshelves(Position $tablePos) : int{
		$bookshelfCount = 0;
		$world = $tablePos->getWorld();

		for($x = -2; $x <= 2; $x++){
			for($z = -2; $z <= 2; $z++){
				// We only check blocks at a distance of 2 blocks from the enchanting table
				if(abs($x) !== 2 && abs($z) !== 2){
					continue;
				}
				for($y = 0; $y <= 1; $y++){
					$block = $world->getBlock($tablePos->add($x, $y, $z));
					if($block->getTypeId() !== BlockTypeIds::BOOKSHELF){
						continue;
					}

					// Calculate the coordinates of the space between the bookshelf and the enchanting table
					$spaceX = max(min($x, 1), -1);
					$spaceZ = max(min($z, 1), -1);

					$lowerSpaceBlock = $world->getBlock($tablePos->add($spaceX, 0, $spaceZ));
					if($lowerSpaceBlock->getTypeId() !== BlockTypeIds::AIR){
						break;
					}
					$upperSpaceBlock = $world->getBlock($tablePos->add($spaceX, 1, $spaceZ));
					if($upperSpaceBlock->getTypeId() !== BlockTypeIds::AIR){
						break;
					}

					$bookshelfCount++;
					if($bookshelfCount === self::MAX_BOOKSHELF_COUNT){
						return $bookshelfCount;
					}
				}
			}
		}

		return $bookshelfCount;
	}

	private static function createEnchantOption(Random $random, Item $inputItem, int $optionCost, int $slot) : EnchantmentOption{
		$cost = $optionCost;

		$enchantability = self::getEnchantability($inputItem);
		$cost = $cost + $random->nextRange(0, $enchantability >> 2) + $random->nextRange(0, $enchantability >> 2) + 1;
		// Random bonus for enchantment cost between 0.85 and 1.15
		$bonus = 1 + ($random->nextFloat() + $random->nextFloat() - 1) * 0.15;
		$cost = (int) round($cost * $bonus);

		$resultEnchantments = [];
		$availableEnchantments = self::getAvailableEnchantments($cost, $inputItem);

		$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
		if($lastEnchantment !== null){
			$resultEnchantments[] = $lastEnchantment;

			// With probability (cost + 1) / 50, continue adding enchantments
			while($random->nextFloat() <= ($cost + 1) / 50){
				// Remove from the list of available enchantments anything that conflicts
				// with previously-chosen enchantments
				$availableEnchantments = array_filter(
					$availableEnchantments,
					function(EnchantmentInstance $e) use ($lastEnchantment){
						return $e->getType() !== $lastEnchantment->getType() &&
							$e->getType()->isCompatibleWith($lastEnchantment->getType());
					}
				);

				$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
				if($lastEnchantment === null){
					break;
				}

				$resultEnchantments[] = $lastEnchantment;
				$cost >>= 1;
			}
		}

		return new EnchantmentOption($optionCost, $slot, $resultEnchantments, self::getRandomOptionName($random));
	}

	private static function getEnchantability(Item $item) : int{
		if($item instanceof TieredTool){
			return $item->getTier()->getEnchantability();
		}
		if($item instanceof Armor){
			return $item->getMaterial()->getEnchantability();
		}
		return 1;
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	private static function getAvailableEnchantments(int $cost, Item $item) : array{
		$list = [];

		foreach(VanillaEnchantments::getAll() as $enchantment){
			if($enchantment->isTreasure() || !$enchantment->hasPrimaryItemType($item->getEnchantmentFlag())){
				continue;
			}

			for($lvl = $enchantment->getMaxLevel(); $lvl > 0; $lvl--){
				if($cost >= $enchantment->getMinCost($lvl) && $cost <= $enchantment->getMaxCost($lvl)){
					$list[] = new EnchantmentInstance($enchantment, $lvl);
					break;
				}
			}
		}

		return $list;
	}

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	private static function getRandomWeightedEnchantment(Random $random, array $enchantments) : ?EnchantmentInstance{
		if(count($enchantments) === 0){
			return null;
		}

		$totalWeight = 0;
		foreach($enchantments as $enchantment){
			$totalWeight += $enchantment->getType()->getRarity();
		}

		$result = null;
		$randomWeight = $random->nextRange(1, $totalWeight);

		foreach($enchantments as $enchantment){
			$randomWeight -= $enchantment->getType()->getRarity();

			if($randomWeight <= 0){
				$result = $enchantment;
				break;
			}
		}

		return $result;
	}

	private static function getRandomOptionName(Random $random) : string{
		$symbols = range('a', 'z');
		$name = '';

		for($i = $random->nextRange(5, 15); $i > 0; $i--){
			$name .= $symbols[$random->nextBoundedInt(count($symbols))];
		}

		return $name;
	}
}
