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
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems as Items;
use pocketmine\utils\Random;
use pocketmine\world\Position;
use function abs;
use function array_filter;
use function chr;
use function count;
use function floor;
use function max;
use function min;
use function ord;
use function round;

final class EnchantmentHelper{
	private const MAX_BOOKSHELF_COUNT = 15;

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	public static function enchantItem(Item $item, array $enchantments) : Item{
		$resultItem = $item->getTypeId() === ItemTypeIds::BOOK ? Items::ENCHANTED_BOOK() : clone $item;

		foreach($enchantments as $enchantment){
			$resultItem->addEnchantment($enchantment);
		}

		return $resultItem;
	}

	/**
	 * @return EnchantmentOption[]
	 */
	public static function getEnchantOptions(Position $tablePos, Item $input, int $seed) : array{
		if($input->isNull() || $input->hasEnchantments()){
			return [];
		}

		$random = new Random($seed);

		$bookshelfCount = self::countBookshelves($tablePos);
		$baseRequiredLevel = $random->nextRange(1, 8) + ($bookshelfCount >> 1) + $random->nextRange(0, $bookshelfCount);
		$topRequiredLevel = (int) floor(max($baseRequiredLevel / 3, 1));
		$middleRequiredLevel = (int) floor($baseRequiredLevel * 2 / 3 + 1);
		$bottomRequiredLevel = max($baseRequiredLevel, $bookshelfCount * 2);

		return [
			self::createEnchantOption($random, $input, $topRequiredLevel),
			self::createEnchantOption($random, $input, $middleRequiredLevel),
			self::createEnchantOption($random, $input, $bottomRequiredLevel),
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

	private static function createEnchantOption(Random $random, Item $inputItem, int $requiredXpLevel) : EnchantmentOption{
		$enchantingPower = $requiredXpLevel;

		$enchantability = $inputItem->getEnchantability();
		$enchantingPower = $enchantingPower + $random->nextRange(0, $enchantability >> 2) + $random->nextRange(0, $enchantability >> 2) + 1;
		// Random bonus for enchanting power between 0.85 and 1.15
		$bonus = 1 + ($random->nextFloat() + $random->nextFloat() - 1) * 0.15;
		$enchantingPower = (int) round($enchantingPower * $bonus);

		$resultEnchantments = [];
		$availableEnchantments = self::getAvailableEnchantments($enchantingPower, $inputItem);

		$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
		if($lastEnchantment !== null){
			$resultEnchantments[] = $lastEnchantment;

			// With probability (power + 1) / 50, continue adding enchantments
			while($random->nextFloat() <= ($enchantingPower + 1) / 50){
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
				$enchantingPower >>= 1;
			}
		}

		return new EnchantmentOption($requiredXpLevel, self::getRandomOptionName($random), $resultEnchantments);
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	private static function getAvailableEnchantments(int $enchantingPower, Item $item) : array{
		$list = [];

		foreach(EnchantingTableOptionRegistry::getInstance()->getAvailableEnchantments($item) as $enchantment){
			for($lvl = $enchantment->getMaxLevel(); $lvl > 0; $lvl--){
				if($enchantingPower >= $enchantment->getMinEnchantingPower($lvl) &&
					$enchantingPower <= $enchantment->getMaxEnchantingPower($lvl)
				){
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
		$name = "";
		for($i = $random->nextRange(5, 15); $i > 0; $i--){
			$name .= chr($random->nextRange(ord("a"), ord("z")));
		}

		return $name;
	}
}
