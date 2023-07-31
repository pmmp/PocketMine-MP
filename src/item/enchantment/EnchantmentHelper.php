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
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Book;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds as ItemIds;
use pocketmine\item\TieredTool;
use pocketmine\item\ToolTier;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\network\mcpe\protocol\types\EnchantOption;
use pocketmine\utils\Random;
use pocketmine\world\Position;
use function abs;
use function array_filter;
use function array_map;
use function count;
use function implode;
use function max;
use function min;

final class EnchantmentHelper{
	public const MAX_BOOKSHELF_COUNT = 15;
	public const FIRST_OPTION = 0;
	public const SECOND_OPTION = 1;
	public const THIRD_OPTION = 2;

	/**
	 * @return EnchantOption[]
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
			self::createEnchantOption($random, $input, $topCost, self::FIRST_OPTION),
			self::createEnchantOption($random, $input, $middleCost, self::SECOND_OPTION),
			self::createEnchantOption($random, $input, $bottomCost, self::THIRD_OPTION),
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

	private static function createEnchantOption(Random $random, Item $inputItem, int $optionCost, int $slot) : EnchantOption{
		$cost = $optionCost;

		$enchantability = self::getEnchantability($inputItem);
		$cost = $cost + $random->nextRange(0, $enchantability >> 2) + $random->nextRange(0, $enchantability >> 2) + 1;
		// Random bonus for enchantment cost between 0.85 and 1.15
		$bonus = 1 + ($random->nextFloat() + $random->nextFloat() - 1) * 0.15;
		$cost = (int) ($cost * $bonus);

		$resultEnchantments = [];
		$availableEnchantments = self::getAvailableEnchantments($cost, $inputItem);

		if(count($availableEnchantments) !== 0){
			/** @var EnchantmentInstance $lastEnchantment */
			$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
			$resultEnchantments[] = $lastEnchantment;

			// With probability (cost + 1) / 50, continue adding enchantments
			while($random->nextFloat() <= ($cost + 1) / 50){
				// Remove from the list of available enchantments anything that conflicts
				// with previously-chosen enchantments
				$availableEnchantments = array_filter(
					$availableEnchantments,
					fn(EnchantmentInstance $e) => $e->getType() !== $lastEnchantment->getType() &&
						$e->getType()->isCompatibleWith($lastEnchantment->getType())
				);

				if(count($availableEnchantments) === 0){
					break;
				}

				/** @var EnchantmentInstance $lastEnchantment */
				$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
				$resultEnchantments[] = $lastEnchantment;

				$cost >>= 1;
			}
		}

		$protocolEnchantments = array_map(
			fn(EnchantmentInstance $e) => new Enchant(EnchantmentIdMap::getInstance()->toId($e->getType()), $e->getLevel()),
			$resultEnchantments
		);

		return new EnchantOption($optionCost, $slot, $protocolEnchantments, [], [], self::getRandomOptionName($random), $slot);
	}

	private static function getEnchantability(Item $item) : int{
		if($item instanceof TieredTool){
			return match ($item->getTier()) {
				ToolTier::WOOD() => 15,
				ToolTier::STONE() => 5,
				ToolTier::IRON() => 14,
				ToolTier::GOLD() => 22,
				ToolTier::DIAMOND() => 10,
				ToolTier::NETHERITE() => 15,
				default => 1
			};
		}

		return match ($item->getTypeId()) {
			ItemIds::LEATHER_CAP, ItemIds::LEATHER_TUNIC, ItemIds::LEATHER_PANTS, ItemIds::LEATHER_BOOTS => 15,
			ItemIds::CHAINMAIL_HELMET, ItemIds::CHAINMAIL_CHESTPLATE, ItemIds::CHAINMAIL_LEGGINGS, ItemIds::CHAINMAIL_BOOTS => 12,
			ItemIds::IRON_HELMET, ItemIds::IRON_CHESTPLATE, ItemIds::IRON_LEGGINGS, ItemIds::IRON_BOOTS, ItemIds::TURTLE_HELMET => 9,
			ItemIds::GOLDEN_HELMET, ItemIds::GOLDEN_CHESTPLATE, ItemIds::GOLDEN_LEGGINGS, ItemIds::GOLDEN_BOOTS => 25,
			ItemIds::DIAMOND_HELMET, ItemIds::DIAMOND_CHESTPLATE, ItemIds::DIAMOND_LEGGINGS, ItemIds::DIAMOND_BOOTS => 10,
			ItemIds::NETHERITE_HELMET, ItemIds::NETHERITE_CHESTPLATE, ItemIds::NETHERITE_LEGGINGS, ItemIds::NETHERITE_BOOTS => 15,
			default => 1
		};
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	private static function getAvailableEnchantments(int $cost, Item $item) : array{
		$list = [];

		foreach(VanillaEnchantments::getAll() as $enchantment){
			if($enchantment->isTreasure()){
				continue;
			}
			if(!$item instanceof Book && !$enchantment->hasPrimaryItemFlag($item->getEnchantmentFlag())){
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
	 * @param array<EnchantmentInstance> $enchantments
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
		$words = ['PocketMine', 'delights', 'players', 'enchanting', 'items', 'with', 'mystical', 'wonders'];
		$selectedWords = [];

		for($i = $random->nextRange(2, 3); $i > 0; $i--){
			$selectedWords[] = $words[$random->nextBoundedInt(count($words))];
		}

		return implode(' ', $selectedWords);
	}
}
