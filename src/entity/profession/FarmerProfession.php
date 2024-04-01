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
use pocketmine\item\SuspiciousStewType;
use pocketmine\item\VanillaItems;
use function random_int;

final class FarmerProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::FARMER, "entity.villager.farmer", VanillaBlocks::AIR()); //TODO: Change this to Composter when it's implemented.
	}

	public function getRecipes(int $biomeId) : array{
		return [
			new TradeRecipe(
				buyA: VanillaItems::WHEAT()->setCount(20),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::POTATO()->setCount(26),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::CARROT()->setCount(22),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::BEETROOT()->setCount(15),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::BREAD()->setCount(6),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaBlocks::PUMPKIN()->asItem()->setCount(6),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaBlocks::EMERALD()->asItem()->setCount(1),
				sell: VanillaItems::PUMPKIN_PIE()->setCount(4),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::APPLE()->setCount(4),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaBlocks::MELON()->asItem()->setCount(4),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaItems::COOKIE()->setCount(18),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::SUSPICIOUS_STEW()->setType([
					SuspiciousStewType::AZURE_BLUET,
					SuspiciousStewType::CORNFLOWER,
					SuspiciousStewType::POPPY,
					SuspiciousStewType::LILY_OF_THE_VALLEY
				][random_int(0, 3)])->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaBlocks::CAKE()->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaItems::GOLDEN_CARROT()->setCount(3),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(4),
				sell: VanillaItems::GLISTERING_MELON()->setCount(3),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
