<?php

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\crafting\AnvilCraftResult;
use pocketmine\item\Item;
use pocketmine\Server;

final class AnvilHelper{
	private const COST_LIMIT = 39;

	/**
	 * Attempts to calculate the result of an anvil operation.
	 *
	 * Returns null if the operation can't do anything.
	 */
	public static function calculateResult(Item $base, Item $material, ?string $customName, bool $isCreative) : ?AnvilCraftResult{

		// Debug: log inputs
		Server::getInstance()->getLogger()->debug("[DEBUG-ANVIL] calculateResult called with base=" . $base->getName() . ", material=" . $material->getName() . ", customName=" . ($customName ?? 'null') . ", isCreative=" . ($isCreative ? '1' : '0'));

		$recipe = Server::getInstance()->getCraftingManager()->matchAnvilRecipe($base, $material);
		if($recipe === null){
			Server::getInstance()->getLogger()->debug("[DEBUG-ANVIL] No anvil recipe matched for base=" . $base->getName() . " material=" . $material->getName());
			// Allow rename-only operations even without a recipe
			if($customName !== null && $customName !== "" && !$base->isNull()){
				$resultItem = clone $base;
				if($resultItem->getCustomName() !== $customName){
					$resultItem->setCustomName($customName);
					Server::getInstance()->getLogger()->debug("[DEBUG-ANVIL] Rename-only operation: setting name to '" . $customName . "'");
					return new AnvilCraftResult(1, $resultItem, null);
				}
			}
			// Allow clearing custom name
			if(($customName === null || $customName === "") && $base->hasCustomName() && !$base->isNull()){
				$resultItem = clone $base;
				$resultItem->clearCustomName();
				Server::getInstance()->getLogger()->debug("[DEBUG-ANVIL] Clear name operation");
				return new AnvilCraftResult(1, $resultItem, null);
			}
			return null;
		}
		
		$result = $recipe->getResultFor($base, $material);
		Server::getInstance()->getLogger()->debug("[DEBUG-ANVIL] Recipe matched: " . get_class($recipe) . ", result present? " . ($result !== null ? 'yes' : 'no'));

		if($result !== null){
			$resultItem = $result->getOutput();
			$xpCost = $result->getXpCost();
			if(($customName === null || $customName === "") && $resultItem->hasCustomName()){
				$xpCost++;
				$resultItem->clearCustomName();
			}elseif($customName !== null && $resultItem->getCustomName() !== $customName){
				$xpCost++;
				$resultItem->setCustomName($customName);
			}

			$result = new AnvilCraftResult($xpCost, $resultItem, $result->getSacrificeResult());
		}

		if($result === null || $result->getXpCost() <= 0 || ($result->getXpCost() > self::COST_LIMIT && !$isCreative)){
			return null;
		}

		return $result;
	}
}