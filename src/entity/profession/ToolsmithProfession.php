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
use function random_int;

final class ToolsmithProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::TOOLSMITH, "entity.villager.tool", VanillaBlocks::SMITHING_TABLE());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		return [
			new TradeRecipe(
				buyA: VanillaItems::COAL()->setCount(15),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::STONE_AXE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::STONE_PICKAXE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::STONE_SHOVEL()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::STONE_HOE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::IRON_INGOT()->setCount(4),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(36),
				sell: VanillaBlocks::BELL()->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::FLINT()->setCount(30),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(6, 20)),
				sell: VanillaItems::IRON_AXE()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(7, 21)),
				sell: VanillaItems::IRON_PICKAXE()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(8, 22)),
				sell: VanillaItems::IRON_SHOVEL()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(4),
				sell: VanillaItems::DIAMOND_HOE()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::DIAMOND()->setCount(1),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(17, 31)),
				sell: VanillaItems::DIAMOND_AXE()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(10, 24)),
				sell: VanillaItems::DIAMOND_SHOVEL()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(18, 32)),
				sell: VanillaItems::DIAMOND_PICKAXE()->setCount(1), //TODO: enchantment
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 30
			)
		];
	}
}
