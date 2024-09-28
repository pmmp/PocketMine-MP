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

use pocketmine\block\Planks;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use function ceil;
use function max;
use function min;
use function strlen;
use function var_dump;

final class AnvilHelper{

	public const COST_RENAME = 1;

	public const COST_REPAIR_MATERIAL = 1;

	public const COST_REPAIR_SACRIFICE = 2;

	public const COST_LIMIT = 39;

	public static function createResult(Player $source, Item $base, Item $sacrifice, ?int &$cost, ?string $customName = null) : ?Item{
		$cost = 0;

		if(($base instanceof Armor || $base instanceof TieredTool) && self::isValidRepairMaterial($base, $sacrifice) && $base->getDamage() > 0){
			$damage = $base->getDamage();
			$l = min($damage, (int) ceil($base->getMaxDurability() / 4));
			for($i = 0; $i < 4; $i++) {
				$sacrifice->pop();
				$damage = max(0, $damage - $l);
				$cost += self::COST_REPAIR_MATERIAL;

				if($sacrifice->isNull() || $damage === 0){
					break;
				}
			}
			$base->setDamage($damage);
		}else{
			if($base instanceof Durable && $sacrifice instanceof Durable && $base->getTypeId() === $sacrifice->getTypeId()){
				$baseDurability = $base->getMaxDurability() - $base->getDamage();
				$sacrificeDurability = $sacrifice->getMaxDurability() - $sacrifice->getDamage();
				$additionalDurability = (int) ($sacrificeDurability + $base->getMaxDurability() * 12 / 100);

				$cost += self::COST_REPAIR_SACRIFICE;
				$base->setDamage(max(0, $base->getMaxDurability() - $baseDurability - $additionalDurability));
			}
			if($sacrifice->hasEnchantments()){
				$cost += self::combineEnchants($base, $sacrifice);
			}
		}

		$rename = false;
		if($customName === null || strlen($customName) === 0){
			if($base->hasCustomName()){
				$cost += self::COST_RENAME;
				$base->clearCustomName();
				$rename = true;
			}
		}else{
			$base->setCustomName($customName);
			$cost += self::COST_RENAME;
			$rename = true;
		}
		if($base instanceof Durable){
			$repairCost = max(($base->getRepairCost()), ($sacrifice instanceof Durable ? $sacrifice->getRepairCost() : 0));
			if(!$rename || $cost !== self::COST_RENAME){
				$repairCost = 2 * $repairCost + 1;
			}
			$cost += $base->getRepairCost() + ($sacrifice instanceof Durable ? $sacrifice->getRepairCost() : 0);
			$base->setRepairCost($repairCost);
		}
		if($cost <= 0 || ($cost > self::COST_LIMIT && !$source->isCreative())){
			return null;
		}
		var_dump("debug: calculated cost of $cost xp level, " . ($rename ? "renamed the item" : "no rename") . ", new repair cost " . ($repairCost ?? 0));
		return $base;
	}

	private static function combineEnchants(Item $a, Item $b) : int{
		$cost = 0;
		foreach($b->getEnchantments() as $instance){
			$level = $instance->getLevel();
			$enchantment = $instance->getType();
			if(!AvailableEnchantmentRegistry::getInstance()->isAvailableForItem($enchantment, $a)){
				continue;
			}
			if(($targetEnchantment = $a->getEnchantment($enchantment)) !== null){
				$targetLevel = $targetEnchantment->getLevel();
				$newLevel = ($targetLevel === $level ? $targetLevel + 1 : max($targetLevel, $level));
				$a->addEnchantment(new EnchantmentInstance($enchantment, min($newLevel, $enchantment->getMaxLevel())));
				continue;
			}
			foreach($a->getEnchantments() as $testedInstance){
				$testedEnchantment = $testedInstance->getType();
				if(!$testedEnchantment->isCompatibleWith($enchantment)){
					$cost++;
					continue 2;
				}
			}
			$costAddition = match($enchantment->getRarity()){
				Rarity::COMMON => 1,
				Rarity::UNCOMMON => 2,
				Rarity::RARE => 4,
				Rarity::MYTHIC => 8,
				default => throw new TransactionValidationException("Invalid rarity " . $enchantment->getRarity() . " found")
			};
			if($b instanceof EnchantedBook){
				$costAddition = max(1, $costAddition / 2);
			}
			$cost += $costAddition * $level;
			$a->addEnchantment($instance);
		}
		return (int) $cost;
	}

	public static function isValidRepairMaterial(Armor|TieredTool $equipment, Item $material) : bool{
		$equals = fn(Item $item) => $material->equals($item, checkCompound: false);
		if($equipment instanceof Armor){
			return match($equipment->getMaterial()){
				VanillaArmorMaterials::LEATHER() => $equals(VanillaItems::LEATHER()),
				VanillaArmorMaterials::CHAINMAIL(), VanillaArmorMaterials::IRON() => $equals(VanillaItems::IRON_INGOT()),
				VanillaArmorMaterials::GOLD() => $equals(VanillaItems::GOLD_INGOT()),
				VanillaArmorMaterials::DIAMOND() => $equals(VanillaItems::DIAMOND()),
				VanillaArmorMaterials::NETHERITE() => $equals(VanillaItems::NETHERITE_INGOT()),
				VanillaArmorMaterials::TURTLE() => $equals(VanillaItems::SCUTE()),
				default => false
			};
		}
		return match($equipment->getTier()){
			ToolTier::WOOD => $material->getBlock() instanceof Planks,
			ToolTier::STONE => $equals(VanillaBlocks::COBBLESTONE()->asItem()),
			ToolTier::IRON => $equals(VanillaItems::IRON_INGOT()),
			ToolTier::GOLD => $equals(VanillaItems::GOLD_INGOT()),
			ToolTier::DIAMOND => $equals(VanillaItems::DIAMOND()),
			ToolTier::NETHERITE => $equals(VanillaItems::NETHERITE_INGOT())
		};
	}
}
