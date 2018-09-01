<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\item\enchantment;

use pocketmine\item\Armor;
use pocketmine\item\Book;
use pocketmine\item\Bow;
use pocketmine\item\Elytra;
use pocketmine\item\FishingRod;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Random;

class EnchantmentHelper{

	public static function canEnchantItem(Item $item, Enchantment $enchantment) : bool{
		// TODO : Update
		$slot = $enchantment->getPrimaryItemFlags();
		switch($slot){
			case Enchantment::SLOT_ALL:
				return true;
			case Enchantment::SLOT_SWORD:
				return $item instanceof Sword;
			case Enchantment::SLOT_NONE:
				return false;
			case Enchantment::SLOT_TOOL:
			case Enchantment::SLOT_DIG:
				return $item instanceof Tool;
			case Enchantment::SLOT_BOW:
				return $item instanceof Bow;
			case Enchantment::SLOT_FISHING_ROD:
				return $item instanceof FishingRod;
			case Enchantment::SLOT_ELYTRA:
				return $item instanceof Elytra;
			default:
				if($item instanceof Armor){
					$slots = [
						Armor::SLOT_HELMET => Enchantment::SLOT_HEAD,
						Armor::SLOT_CHESTPLATE => Enchantment::SLOT_TORSO,
						Armor::SLOT_LEGGINGS => Enchantment::SLOT_LEGS,
						Armor::SLOT_BOOTS => Enchantment::SLOT_FEET
					];
					return ($slot === Enchantment::SLOT_ARMOR) ? true : ($slots[$item->getArmorSlot()] === $slot) ?? false;
				}elseif($item instanceof Book){
					return true;
				}
				return false;
		}
	}

	/**
	 * @param Random $random
	 * @param EnchantmentInstance[] $enchs
	 * @return EnchantmentInstance
	 */
	public static function getRandomEnchantment(Random $random, array $enchs): EnchantmentInstance{
		$i = 0;
		foreach ($enchs as $ench){
			$i += $ench->getEnchantment()->getRarity();
		}
		$i = $random->nextBoundedInt($i);

		foreach($enchs as $ench){
			$i -= $ench->getEnchantment()->getRarity();
			if ($i < 0) return $ench;
		}

		return null;
	}


	public static function addRandomEnchant(Random $random, Item $item, int $enchantabilityLevel = null) : Item{
		if($enchantabilityLevel === null) $enchantabilityLevel = 5 + $random->nextBoundedInt(15);
		$list = self::buildEnchantmentList($random, $item, $enchantabilityLevel);
		$flag = $item->getId() == Item::BOOK;

		if($flag){
			$item = ItemFactory::get(Item::ENCHANTED_BOOK, $item->getDamage(), $item->getCount());
		}

		if($list != null){
			foreach($list as $enchInstance){
				$item->addEnchantment($enchInstance);
			}
		}

		return $item;
	}

	public static function buildEnchantmentList(Random $random, Item $item, int $enchantabilityLevel) : array{
		$itemEnchantability = $item->getEnchantability();

		if($itemEnchantability <= 0){
			return [];
		}else{
			$itemEnchantability /= 2;
			$itemEnchantability = 1 + $random->nextBoundedInt(($itemEnchantability >> 1) + 1) + $random->nextBoundedInt(($itemEnchantability >> 1) + 1);
			$newEnchantability = $itemEnchantability + $enchantabilityLevel;
			$f = ($random->nextFloat() + $random->nextFloat() - 1.0) * 0.15;
			$k = (int)($newEnchantability * (1.0 + $f) + 0.5);

			if($k < 1)
				$k = 1;

			$list = [];
			$map = self::mapEnchantmentData($k, $item);

			if(!empty($map)){
				$enchInstance = self::getRandomEnchantment($random, $map);

				if($enchInstance != null){
					$list[] = $enchInstance;

					for($l = $k; $random->nextBoundedInt(50) <= $l; $l >>= 1){
						foreach($map as $index => $ench){
							$flag = true;

							foreach($list as $einsta1){
								if($einsta1 === Enchantment::getEnchantment($index)){
									$flag = false;
									break;
								}
							}

							if(!$flag)
								unset($map[$index]);
						}

						if(!empty($map))
							$list[] = self::getRandomEnchantment($random, $map);
					}
				}
			}
		}

		return $list;
	}

	public static function mapEnchantmentData(int $enchantability, Item $item) : array{
		$map = [];

		foreach(Enchantment::getEnchantments() as $enchantment){
			if($enchantment != null && self::canEnchantItem($item, $enchantment)){
				for($i = 0; $i <= $enchantment->getMaxLevel(); ++$i){

					if($enchantability >= $enchantment->getMinEnchantability($i) && $enchantability <= $enchantment->getMaxEnchantability($i)){
						$map[$enchantment->getId()] = new EnchantmentInstance($enchantment, $i);
					}
				}
			}
		}

		return $map;
	}

}