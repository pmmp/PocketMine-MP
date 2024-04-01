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
use function random_int;

final class ShepherdProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::SHEPHERD, "entity.villager.shepherd", VanillaBlocks::LOOM());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		$dyeColors = DyeColor::cases();
		return [
			new TradeRecipe(
				buyA: VanillaBlocks::WOOL()->setColor([
					DyeColor::WHITE,
					DyeColor::BLACK,
					DyeColor::GRAY,
					DyeColor::BROWN
				][random_int(0, 3)])->asItem()->setCount(18),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(2),
				sell: VanillaItems::SHEARS()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::DYE()->setColor($dyeColors[array_rand($dyeColors)])->setCount(12),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaBlocks::WOOL()->setColor($dyeColors[array_rand($dyeColors)])->asItem()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(2),
				sell: VanillaBlocks::CARPET()->setColor($dyeColors[array_rand($dyeColors)])->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::DYE()->setColor([
					DyeColor::RED,
					DyeColor::LIGHT_GRAY,
					DyeColor::PINK,
					DyeColor::YELLOW,
					DyeColor::ORANGE
				][random_int(0, 3)])->setCount(12),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaBlocks::BED()->setColor($dyeColors[array_rand($dyeColors)])->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::DYE()->setColor([
					DyeColor::RED,
					DyeColor::LIGHT_GRAY,
					DyeColor::PINK,
					DyeColor::YELLOW,
					DyeColor::ORANGE
				][random_int(0, 4)])->setCount(12),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaItems::BANNER()->setColor($dyeColors[array_rand($dyeColors)])->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(2),
				sell: VanillaItems::PAINTING()->setCount(3),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
