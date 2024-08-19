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

namespace pocketmine\block\utils;

use pocketmine\block\anvil\AnvilActionsFactory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function max;

final class AnvilHelper{
	private const COST_LIMIT = 39;

	/**
	 * Attempts to calculate the result of an anvil operation.
	 *
	 * Returns null if the operation can't do anything.
	 */
	public static function calculateResult(Player $player, Item $base, Item $material, ?string $customName = null) : ?AnvilResult {
		$xpCost = 0;
		$resultItem = clone $base;

		$additionnalRepairCost = 0;
		foreach(AnvilActionsFactory::getInstance()->getActions($base, $material, $customName) as $action){
			$action->process($resultItem);
			if(!$action->isFreeOfRepairCost() && $action->getXpCost() > 0){
				// Repair cost increment if the item has been processed
				// and any of the action is not free of repair cost
				$additionnalRepairCost = 1;
			}
			$xpCost += $action->getXpCost();
		}

		$xpCost += 2 ** $resultItem->getRepairCost() - 1;
		$xpCost += 2 ** $material->getRepairCost() - 1;
		$resultItem->setRepairCost(
			max($resultItem->getRepairCost(), $material->getRepairCost()) + $additionnalRepairCost
		);

		if($xpCost <= 0 || ($xpCost > self::COST_LIMIT && !$player->isCreative())){
			return null;
		}

		return new AnvilResult($xpCost, $resultItem);
	}
}
