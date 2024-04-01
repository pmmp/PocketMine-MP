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

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\item\VanillaItems;
use function array_rand;

final class MasonProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::MASON, "entity.villager.mason", VanillaBlocks::STONECUTTER());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		$dyeColors = DyeColor::cases();
		$stoneBlocks = [
			VanillaBlocks::GRANITE(),
			VanillaBlocks::DIORITE(),
			VanillaBlocks::ANDESITE()
		];
		$dripstoneBlocks = [
			VanillaBlocks::POLISHED_GRANITE(),
			VanillaBlocks::POLISHED_ANDESITE()
			//TODO: Dripstone blocks
		];
		return [
			new TradeRecipe(
				buyA: VanillaItems::CLAY()->setCount(10),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::BRICK()->setCount(10),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaBlocks::STONE()->asItem()->setCount(20),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaBlocks::CHISELED_STONE_BRICKS()->asItem()->setCount(4),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: $stoneBlocks[array_rand($stoneBlocks)]->asItem()->setCount(4),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: $dripstoneBlocks[array_rand($dripstoneBlocks)]->asItem()->setCount(4),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::NETHER_QUARTZ()->setCount(12),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaBlocks::GLAZED_TERRACOTTA()->setColor($dyeColors[array_rand($dyeColors)])->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaBlocks::QUARTZ()->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
