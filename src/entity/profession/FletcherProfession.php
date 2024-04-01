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

namespace pocketmine\entity\profession;

use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;

final class FletcherProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::FLETCHER, "entity.villager.fletcher", VanillaBlocks::FLETCHING_TABLE());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		return [
			new TradeRecipe(
				buyA: VanillaItems::STICK()->setCount(32),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::ARROW()->setCount(16),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::FLINT()->setCount(26),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::BOW()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::STRING()->setCount(14),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
//			new TradeRecipe(//TODO: Implement this after implementing crossbow
//				buyA: VanillaItems::EMERALD()->setCount(3),
//				sell: VanillaItems::CROSSBOW()->setCount(1),
//				maxUses: 12,
//				priceMultiplier: 0.05,
//				tier: 2,
//				traderExp: 10
//			),
			new TradeRecipe(
				buyA: VanillaItems::FEATHER()->setCount(24),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(7),
				sell: VanillaItems::BOW()->setCount(1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 1)),
				maxUses: 3,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaBlocks::TRIPWIRE_HOOK()->asItem()->setCount(8),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			),
//			new TradeRecipe(//TODO: Implement this after implementing crossbow
//				buyA: VanillaItems::EMERALD()->setCount(8),
//				sell: VanillaItems::CROSSBOW()->setCount(1)->setEnchantments([
//					EnchantmentInstance::get(Enchantment::get(Enchantment::MULTISHOT), 1)
//				]),
//				maxUses: 3,
//				priceMultiplier: 0.05,
//				tier: 4,
//				traderExp: 15
//			),
//			new TradeRecipe(//TODO: Implement this after implementing tipped arrows
//				buyA: VanillaItems::ARROW()->setCount(2),
//				buyB: VanillaItems::TIPPED_ARROW()->setCount(1),
//				sell: VanillaItems::EMERALD()->setCount(1),
//				maxUses: 12,
//				priceMultiplier: 0.05,
//				tier: 4,
//				traderExp: 30
//			)
		];
	}
}
