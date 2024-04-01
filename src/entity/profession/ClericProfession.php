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
use pocketmine\item\VanillaItems;

final class ClericProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::CLERIC, "entity.villager.cleric", VanillaBlocks::BREWING_STAND());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		return [
			new TradeRecipe(
				buyA: VanillaItems::ROTTEN_FLESH()->setCount(32),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::REDSTONE_DUST()->setCount(2),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::GOLD_INGOT()->setCount(3),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::LAPIS_LAZULI()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::RABBIT_FOOT()->setCount(2),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(4),
				sell: VanillaBlocks::GLOWSTONE()->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::SCUTE()->setCount(4),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::GLASS_BOTTLE()->setCount(9),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(5),
				sell: VanillaItems::ENDER_PEARL()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaBlocks::NETHER_WART()->asItem()->setCount(22),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaItems::EXPERIENCE_BOTTLE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
