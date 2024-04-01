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
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\item\VanillaItems;

final class FishermanProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::FISHERMAN, "entity.villager.fisherman", VanillaBlocks::BARREL());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		return [
			new TradeRecipe(
				buyA: VanillaItems::STRING()->setCount(20),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			new TradeRecipe(
				buyA: VanillaItems::COAL()->setCount(10),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
//				new TradeRecipe( //TODO: Implement this after implementing cod bucket
//					buyA: VanillaItems::EMERALD()->setCount(3),
//					sell: VanillaItems::COD_BUCKET()->setCount(1),
//					maxUses: 16,
//					priceMultiplier: 0.05,
//					tier: 1,
//					traderExp: 1
//				),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::COOKED_FISH()->setCount(6),
				buyB: VanillaItems::RAW_FISH()->setCount(6),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 1
			),
			new TradeRecipe(
				buyA: VanillaItems::RAW_FISH()->setCount(15),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 10
			),
//				new TradeRecipe( //TODO: Implement this after implementing campfire
//					buyA: VanillaItems::EMERALD()->setCount(2),
//					sell: VanillaBlocks::CAMPFIRE()->setCount(1),
//					maxUses: 12,
//					priceMultiplier: 0.05,
//					tier: 2,
//					traderExp: 5
//				),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(1),
				sell: VanillaItems::COOKED_SALMON()->setCount(6),
				buyB: VanillaItems::RAW_SALMON()->setCount(6),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 5
			),
			new TradeRecipe(
				buyA: VanillaItems::RAW_SALMON()->setCount(13),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 20
			),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(8),
				sell: VanillaItems::FISHING_ROD()->setCount(1),
				maxUses: 3,
				priceMultiplier: 0.2,
				tier: 3,
				traderExp: 10
			),
			new TradeRecipe(
				buyA: VanillaItems::CLOWNFISH()->setCount(6),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: VanillaItems::PUFFERFISH()->setCount(4),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			),
			new TradeRecipe(
				buyA: (match($biomeId){
					BiomeIds::TAIGA, BiomeIds::COLD_TAIGA => VanillaItems::SPRUCE_BOAT(),
					BiomeIds::DESERT, BiomeIds::JUNGLE => VanillaItems::JUNGLE_BOAT(),
					BiomeIds::SAVANNA => VanillaItems::ACACIA_BOAT(),
					BiomeIds::SWAMPLAND => VanillaItems::DARK_OAK_BOAT(),
					default => VanillaItems::OAK_BOAT()
				})->setCount(1), //TODO: check if this is correct
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
