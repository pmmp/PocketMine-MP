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

use pocketmine\block\utils\BannerPatternLayer;
use pocketmine\block\utils\BannerPatternType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\item\VanillaItems;
use function array_rand;

final class CartographerProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::CARTOGRAPHER, "entity.villager.cartographer", VanillaBlocks::CARTOGRAPHY_TABLE());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		$dyeColors = DyeColor::cases();
		return [
			new TradeRecipe(
				buyA: VanillaItems::PAPER()->setCount(24),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 0,
				traderExp: 2
			),
			//new TradeRecipe(//TODO: Implement this after implementing empty map
			//	buyA: VanillaItems::EMERALD()->setCount(7),
			//	sell: VanillaItems::EMPTY_MAP()->setCount(1),
			//	maxUses: 12,
			//	priceMultiplier: 0.05,
			//	tier: 0,
			//	traderExp: 1
			//),
			new TradeRecipe(
				buyA: VanillaBlocks::GLASS_PANE()->asItem()->setCount(11),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 16,
				priceMultiplier: 0.05,
				tier: 1,
				traderExp: 10
			),
			//new TradeRecipe(//TODO: Implement this after implementing explorer map
			//	buyA: VanillaItems::EMERALD()->setCount(13),
			//	sell: VanillaItems::OCEAN_EXPLORER_MAP()->setCount(1),
			//	buyB: VanillaItems::COMPASS()->setCount(1),
			//	maxUses: 12,
			//	priceMultiplier: 0.2,
			//	tier: 1,
			//	traderExp: 5
			//),
			new TradeRecipe(
				buyA: VanillaItems::COMPASS()->setCount(1),
				sell: VanillaItems::EMERALD()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 2,
				traderExp: 20
			),
			//new TradeRecipe(//TODO: Implement this after implementing woodland explorer map
			//	buyA: VanillaItems::EMERALD()->setCount(14),
			//	sell: VanillaItems::WOODLAND_EXPLORER_MAP()->setCount(1),
			//	buyB: VanillaItems::COMPASS()->setCount(1),
			//	maxUses: 12,
			//	priceMultiplier: 0.2,
			//	tier: 2,
			//	traderExp: 10
			//),
			new TradeRecipe(
				buyA: VanillaItems::EMERALD()->setCount(7),
				sell: VanillaBlocks::ITEM_FRAME()->asItem()->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 3,
				traderExp: 15
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
				buyA: VanillaItems::EMERALD()->setCount(8),
				sell: VanillaItems::BANNER()->setPatterns([new BannerPatternLayer(BannerPatternType::GLOBE, DyeColor::WHITE)])->setCount(1),
				maxUses: 12,
				priceMultiplier: 0.05,
				tier: 4,
				traderExp: 30
			)
		];
	}
}
