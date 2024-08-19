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

namespace pocketmine\block\anvil;

use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use function max;
use function min;

final class CombineEnchantmentsAction extends AnvilAction{
	public function canBeApplied() : bool{
		return $this->material->hasEnchantments();
	}

	public function process(Item $resultItem) : void{
		foreach($this->material->getEnchantments() as $instance){
			$enchantment = $instance->getType();
			$level = $instance->getLevel();
			if(!AvailableEnchantmentRegistry::getInstance()->isAvailableForItem($enchantment, $this->base)){
				continue;
			}
			if(($targetEnchantment = $this->base->getEnchantment($enchantment)) !== null){
				// Enchant already present on the target item
				$targetLevel = $targetEnchantment->getLevel();
				$newLevel = ($targetLevel === $level ? $targetLevel + 1 : max($targetLevel, $level));
				$level = min($newLevel, $enchantment->getMaxLevel());
				$instance = new EnchantmentInstance($enchantment, $level);
			}else{
				// Check if the enchantment is compatible with the existing enchantments
				foreach($this->base->getEnchantments() as $testedInstance){
					$testedEnchantment = $testedInstance->getType();
					if(!$testedEnchantment->isCompatibleWith($enchantment)){
						$this->xpCost++;
						continue 2;
					}
				}
			}

			$costAddition = match($enchantment->getRarity()){
				Rarity::COMMON => 1,
				Rarity::UNCOMMON => 2,
				Rarity::RARE => 4,
				Rarity::MYTHIC => 8,
				default => throw new TransactionValidationException("Invalid rarity " . $enchantment->getRarity() . " found")
			};

			if($this->material instanceof EnchantedBook){
				// Enchanted books are half as expensive to combine
				$costAddition = max(1, $costAddition / 2);
			}
			$levelDifference = $instance->getLevel() - $this->base->getEnchantmentLevel($instance->getType());
			$this->xpCost += $costAddition * $levelDifference;
			$resultItem->addEnchantment($instance);
		}
	}
}
