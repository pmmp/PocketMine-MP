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

namespace pocketmine\inventory;

use pocketmine\entity\passive\Villager;
use pocketmine\item\enchantment\EnchantmentHelper;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Random;

class TradeItems{

	/** @var array */
	private static $items = [];
	/** @var Item */
	private static $emeraldItem;
	/** @var Random */
	private static $random;

	public static function init(){
		self::$emeraldItem = ItemFactory::get(Item::EMERALD);
		self::$random = new Random();

		self::$items[Villager::PROFESSION_FARMER][Villager::CAREER_FARMER] = [
			[
				self::getCompound(ItemFactory::get(Item::WHEAT, 0, mt_rand(18, 22)), null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::POTATO, 0, mt_rand(15, 19)), null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::CARROT, 0, mt_rand(15, 19)), null, self::$emeraldItem),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::BREAD, 0, mt_rand(2, 4))),
			],
			[
				self::getCompound(ItemFactory::get(Item::PUMPKIN, 0, mt_rand(8, 13)), null, self::$emeraldItem),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::PUMPKIN_PIE, 0, mt_rand(2, 3))),
			],
			[
				self::getCompound(ItemFactory::get(Item::MELON_BLOCK, 0, mt_rand(7, 12)), null, self::$emeraldItem),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::APPLE, 0, mt_rand(5, 7)))
			],
			[
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::COOKIE, 0, mt_rand(6, 10))),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::CAKE))
			]
		];

		self::$items[Villager::PROFESSION_FARMER][Villager::CAREER_FISHERMAN] = [
			[
				self::getCompound(ItemFactory::get(Item::RAW_FISH), self::$emeraldItem, ItemFactory::get(Item::COOKED_FISH)),
				self::getCompound(ItemFactory::get(Item::STRING, 0, mt_rand(15, 20)), null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::COAL, 0, mt_rand(16, 24)), null, self::$emeraldItem),
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(7, 8)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::FISHING_ROD)))
			]
		];

		self::$items[Villager::PROFESSION_FARMER][Villager::CAREER_FLETCHER] = [
			[
				self::getCompound(ItemFactory::get(Item::STRING, 0, mt_rand(15, 20)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(7, 8)), null, ItemFactory::get(Item::ARROW, 0, mt_rand(8, 12)))
			],
			[
				self::getCompound(ItemFactory::get(Item::GRAVEL, 0, 10), self::$emeraldItem, ItemFactory::get(Item::FLINT, 0, mt_rand(6, 10))),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(2, 3)), null, ItemFactory::get(Item::BOW)),
			]
		];

		self::$items[Villager::PROFESSION_FARMER][Villager::CAREER_STEPHERD] = [
			[
				self::getCompound(ItemFactory::get(Item::WOOL, 0, mt_rand(16, 22)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(3, 4)), null, ItemFactory::get(Item::SHEARS)),
			],
			[
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 0)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 1)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 2)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 3)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 4)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 5)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 6)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 7)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 8)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 9)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 10)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 11)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 12)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 13)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::WOOL, 14)),
			]
		];

		self::$items[Villager::PROFESSION_LIBRARIAN][Villager::CAREER_LIBRARIAN] = [
			[
				self::getCompound(ItemFactory::get(Item::PAPER, 0, mt_rand(24, 36)), null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::BOOK), (clone self::$emeraldItem)->setCount(mt_rand(5, 64)), EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::BOOK))),
			],
			[
				self::getCompound(ItemFactory::get(Item::BOOK, 0, mt_rand(8, 10)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(10, 12)), null, ItemFactory::get(Item::COMPASS)),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(3, 4)), null, ItemFactory::get(Item::BOOKSHELF))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(10, 12)), null, ItemFactory::get(Item::CLOCK)),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::GLASS, 0, mt_rand(3, 5))),
			],
			[
				self::getCompound(ItemFactory::get(Item::BOOK), (clone self::$emeraldItem)->setCount(mt_rand(5, 64)), EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::BOOK)))
			],
			[
				self::getCompound(ItemFactory::get(Item::BOOK), (clone self::$emeraldItem)->setCount(mt_rand(5, 64)), EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::BOOK)))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(20, 22)), null, ItemFactory::get(Item::NAME_TAG))
			]
		];

		self::$items[Villager::PROFESSION_LIBRARIAN][Villager::CAREER_CARTOGRAPHER] = [
			[
				self::getCompound(ItemFactory::get(Item::PAPER, 0, mt_rand(24, 36)), null, self::$emeraldItem),
			],
			[
				self::getCompound(ItemFactory::get(Item::COMPASS), null, self::$emeraldItem)
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(7, 11)), null, ItemFactory::get(Item::EMPTY_MAP))
			]
		];

		self::$items[Villager::PROFESSION_PRIEST][Villager::CAREER_CLERIC] = [
			[
				self::getCompound(ItemFactory::get(Item::ROTTEN_FLESH, 0, mt_rand(36, 40)),null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::GOLD_INGOT, 0, mt_rand(8, 10)),null, self::$emeraldItem)
			],
			[
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::REDSTONE, 0, mt_rand(1, 4))),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::DYE, 4, mt_rand(1, 2)))
			],
			[
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::DYE, 4, mt_rand(1, 3))),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(4, 7)), null, ItemFactory::get(Item::ENDER_PEARL))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(3, 11)), null, ItemFactory::get(Item::EXPERIENCE_BOTTLE))
			]
		];

		self::$items[Villager::PROFESSION_BLACKSMITH][Villager::CAREER_ARMOR] = [
			[
				self::getCompound(ItemFactory::get(Item::COAL, 0, mt_rand(16, 24)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(4, 6)), null, ItemFactory::get(Item::IRON_HELMET))
			],
			[
				self::getCompound(ItemFactory::get(Item::IRON_INGOT, 0, mt_rand(7, 9)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(10, 14)), null, ItemFactory::get(Item::IRON_CHESTPLATE))
			],
			[
				self::getCompound(ItemFactory::get(Item::DIAMOND, 0, mt_rand(3, 4)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(16, 19)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::DIAMOND_CHESTPLATE)))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(5, 7)), null, ItemFactory::get(Item::CHAIN_BOOTS)),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(9, 11)), null, ItemFactory::get(Item::CHAIN_LEGGINGS)),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(5, 7)), null, ItemFactory::get(Item::CHAIN_HELMET)),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(11, 15)), null, ItemFactory::get(Item::CHAIN_CHESTPLATE)),
			]
		];

		self::$items[Villager::PROFESSION_BLACKSMITH][Villager::CAREER_WEAPON] = [
			[
				self::getCompound(ItemFactory::get(Item::COAL, 0, mt_rand(16, 24)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(6, 8)), null, ItemFactory::get(Item::IRON_AXE))
			],
			[
				self::getCompound(ItemFactory::get(Item::IRON_INGOT, 0, mt_rand(7, 9)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(9, 10)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::IRON_SWORD)))
			],
			[
				self::getCompound(ItemFactory::get(Item::DIAMOND, 0, mt_rand(3, 4)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(12, 15)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::DIAMOND_SWORD))),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(9, 12)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::DIAMOND_AXE)))
			]
		];

		self::$items[Villager::PROFESSION_BLACKSMITH][Villager::CAREER_TOOL] = [
			[
				self::getCompound(ItemFactory::get(Item::COAL, 0, mt_rand(16, 24)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(5, 7)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::IRON_SHOVEL)))
			],
			[
				self::getCompound(ItemFactory::get(Item::IRON_INGOT, 0, mt_rand(7, 9)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(9, 11)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::IRON_PICKAXE)))
			],
			[
				self::getCompound(ItemFactory::get(Item::DIAMOND, 0, mt_rand(3, 4)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(12, 15)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::DIAMOND_PICKAXE)))
			]
		];

		self::$items[Villager::PROFESSION_BUTCHER][Villager::CAREER_BUTCHER] = [
			[
				self::getCompound(ItemFactory::get(Item::RAW_PORKCHOP, 0, mt_rand(14, 18)), null, self::$emeraldItem),
				self::getCompound(ItemFactory::get(Item::RAW_CHICKEN, 0, mt_rand(14, 18)), null, self::$emeraldItem)
			],
			[
				self::getCompound(ItemFactory::get(Item::COAL, 0, mt_rand(16, 24)), null, self::$emeraldItem),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::COOKED_PORKCHOP, 0, mt_rand(5, 7))),
				self::getCompound(self::$emeraldItem, null, ItemFactory::get(Item::COOKED_CHICKEN, 0, mt_rand(6, 8)))
			]
		];

		self::$items[Villager::PROFESSION_BUTCHER][Villager::CAREER_LEATHER] = [
			[
				self::getCompound(ItemFactory::get(Item::LEATHER, 0, mt_rand(9, 12)), null, self::$emeraldItem),
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(2, 4)), null, ItemFactory::get(Item::LEATHER_PANTS))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(7, 12)), null, EnchantmentHelper::addRandomEnchant(self::$random, ItemFactory::get(Item::LEATHER_TUNIC)))
			],
			[
				self::getCompound((clone self::$emeraldItem)->setCount(mt_rand(8, 10)), null, ItemFactory::get(Item::SADDLE))
			]
		];
	}

	public static function getCompound(Item $buyA, ?Item $buyB, Item $sell, int $rewardExp = 0, int $maxUses = 7){
		$tag = new CompoundTag("", [
			$buyA->nbtSerialize(-1, "buyA"),
			$sell->nbtSerialize(-1, "sell"),
			new IntTag("maxUses", $maxUses),
			new ByteTag("rewardExp", $rewardExp),
			new IntTag("uses", 0)
		]);
		if($buyB !== null) $tag->setTag($buyB->nbtSerialize(-1, "buyB"));

		return $tag;
	}

	public static function getItemsForVillager(Villager $villager) : ?array{
		if(empty(self::$items)) self::init();
		$pro = self::$items[$villager->getProfession()] ?? [];
		$arr = $pro[$villager->getCareer()] ?? null;
		while($arr !== null && count($arr) !== ($villager->getTradeTier() + 1)){
			array_pop($arr);
		}

		$new = [];
		foreach($arr as $a){
			foreach($a as $value){
				$new[] = $value;
			}
		}

		return $new;
	}

	public static function getItems() : array{
		return self::$items;
	}

}