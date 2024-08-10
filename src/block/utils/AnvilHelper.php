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

use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Durable;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function ceil;
use function floor;
use function max;
use function min;
use function strlen;

class AnvilHelper{
	private const COST_REPAIR_MATERIAL = 1;
	private const COST_REPAIR_SACRIFICE = 2;
	private const COST_RENAME = 1;
	private const COST_LIMIT = 39;

	/**
	 * Attempts to calculate the result of an anvil operation.
	 *
	 * Returns null if the operation can't do anything.
	 */
	public static function calculateResult(Player $player, Item $base, Item $material, ?string $customName = null) : ?AnvilResult {
		$resultCost = 0;
		$resultItem = clone $base;

		if($resultItem instanceof Durable && $resultItem->isValidRepairMaterial($material) && $resultItem->getDamage() > 0){
			$resultCost += self::repairWithMaterial($resultItem, $material);
		}else{
			if($resultItem->getTypeId() === $material->getTypeId() && $resultItem instanceof Durable && $material instanceof Durable){
				$resultCost += self::repairWithSacrifice($resultItem, $material);
			}
			if($material->hasEnchantments()){
				$resultCost += self::combineEnchantments($resultItem, $material);
			}
		}

		// Repair cost increment if the item has been processed, the rename is free of penalty
		$additionnalRepairCost = $resultCost > 0 ? 1 : 0;
		$resultCost += self::renameItem($resultItem, $customName);

		$resultCost += 2 ** $resultItem->getRepairCost() - 1;
		$resultCost += 2 ** $material->getRepairCost() - 1;
		$resultItem->setRepairCost(
			max($resultItem->getRepairCost(), $material->getRepairCost()) + $additionnalRepairCost
		);

		if($resultCost <= 0 || ($resultCost > self::COST_LIMIT && !$player->isCreative())){
			return null;
		}

		return new AnvilResult($resultCost, $resultItem);
	}

	/**
	 * @return int The XP cost of repairing the item
	 */
	private static function repairWithMaterial(Durable $result, Item $material) : int {
		$damage = $result->getDamage();
		$quarter = min($damage, (int) floor($result->getMaxDurability() / 4));
		$numberRepair = min($material->getCount(), (int) ceil($damage / $quarter));
		if($numberRepair > 0){
			$material->pop($numberRepair);
			$damage -= $quarter * $numberRepair;
		}
		$result->setDamage(max(0, $damage));

		return $numberRepair * self::COST_REPAIR_MATERIAL;
	}

	/**
	 * @return int The XP cost of repairing the item
	 */
	private static function repairWithSacrifice(Durable $result, Durable $sacrifice) : int{
		if($result->getDamage() === 0){
			return 0;
		}
		$baseDurability = $result->getMaxDurability() - $result->getDamage();
		$materialDurability = $sacrifice->getMaxDurability() - $sacrifice->getDamage();
		$addDurability = (int) ($result->getMaxDurability() * 12 / 100);

		$newDurability = min($result->getMaxDurability(), $baseDurability + $materialDurability + $addDurability);

		$result->setDamage($result->getMaxDurability() - $newDurability);

		return self::COST_REPAIR_SACRIFICE;
	}

	/**
	 * @return int The XP cost of combining the enchantments
	 */
	private static function combineEnchantments(Item $base, Item $sacrifice) : int{
		$cost = 0;
		foreach($sacrifice->getEnchantments() as $instance){
			$enchantment = $instance->getType();
			$level = $instance->getLevel();
			if(!AvailableEnchantmentRegistry::getInstance()->isAvailableForItem($enchantment, $base)){
				continue;
			}
			if(($targetEnchantment = $base->getEnchantment($enchantment)) !== null){
				// Enchant already present on the target item
				$targetLevel = $targetEnchantment->getLevel();
				$newLevel = ($targetLevel === $level ? $targetLevel + 1 : max($targetLevel, $level));
				$level = min($newLevel, $enchantment->getMaxLevel());
				$instance = new EnchantmentInstance($enchantment, $level);
			}else{
				// Check if the enchantment is compatible with the existing enchantments
				foreach($base->getEnchantments() as $testedInstance){
					$testedEnchantment = $testedInstance->getType();
					if(!$testedEnchantment->isCompatibleWith($enchantment)){
						$cost++;
						continue 2;
					}
				}
			}

			$costAddition = self::getCostAddition($enchantment);

			if($sacrifice instanceof EnchantedBook){
				// Enchanted books are half as expensive to combine
				$costAddition = max(1, $costAddition / 2);
			}
			$levelDifference = $instance->getLevel() - $base->getEnchantmentLevel($instance->getType());
			$cost += $costAddition * $levelDifference;
			$base->addEnchantment($instance);
		}

		return (int) $cost;
	}

	/**
	 * @return int The XP cost of renaming the item
	 */
	private static function renameItem(Item $item, ?string $customName) : int{
		$resultCost = 0;
		if($customName === null || strlen($customName) === 0){
			if($item->hasCustomName()){
				$resultCost += self::COST_RENAME;
				$item->clearCustomName();
			}
		}else{
			if($item->getCustomName() !== $customName){
				$resultCost += self::COST_RENAME;
				$item->setCustomName($customName);
			}
		}

		return $resultCost;
	}

	private static function getCostAddition(Enchantment $enchantment) : int {
		return match($enchantment->getRarity()){
			Rarity::COMMON => 1,
			Rarity::UNCOMMON => 2,
			Rarity::RARE => 4,
			Rarity::MYTHIC => 8,
			default => throw new TransactionValidationException("Invalid rarity " . $enchantment->getRarity() . " found")
		};
	}
}
