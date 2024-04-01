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

final class ArmorerProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::ARMORER, "entity.villager.armor", VanillaBlocks::BLAST_FURNACE());
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
				buyA: VanillaItems::EMERALD()->setCount(5),
				sell: VanillaItems::IRON_HELMET()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(9),
				sell: VanillaItems::IRON_CHESTPLATE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(7),
				sell: VanillaItems::IRON_LEGGINGS()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 0,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(4),
				sell: VanillaItems::IRON_BOOTS()->setCount(1),
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
				buyA: VanillaItems::EMERALD()->setCount(3),
				sell: VanillaItems::CHAINMAIL_LEGGINGS()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::CHAINMAIL_BOOTS()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 1,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::LAVA_BUCKET()->setCount(1),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::DIAMOND()->setCount(1),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::CHAINMAIL_HELMET()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(4),
				sell: VanillaItems::CHAINMAIL_CHESTPLATE()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.2,
				tier: 2,
				traderExp: 10
			),
			//new TradeRecipe(//TODO: Implement this after implementing shield
			//	buyA: VanillaItems::EMERALD()->setCount(5),
			//	sell: VanillaItems::SHIELD()->setCount(1),
			//	maxUses: 12,
			//	priceMultiplier: 0.2,
			//	tier: 2,
			//	traderExp: 10
			//),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(19, 33)),
				sell: VanillaItems::DIAMOND_LEGGINGS()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(13, 27)),
				sell: VanillaItems::DIAMOND_BOOTS()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 15
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(13, 27)),
				sell: VanillaItems::DIAMOND_HELMET()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 4,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(random_int(21, 35)),
				sell: VanillaItems::DIAMOND_CHESTPLATE()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
