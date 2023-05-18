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

namespace pocketmine\block;

use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class Carrot extends Crops{

	public function getDropsForCompatibleTool(Item $item) : array{
		return $this->getDropsForFortuneLevel();
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaItems::CARROT();
	}

	public function isAffectedByFortune() : bool{
		return true;
	}

	public function getFortuneDrops(Item $item) : array{
		return $this->getDropsForFortuneLevel(
			$item->getEnchantmentLevel(VanillaEnchantments::FORTUNE())
		);
	}

	/** @return Item[] */
	private function getDropsForFortuneLevel(int $level = 0) : array{
		if ($this->age >= self::MAX_AGE) {
			return VanillaEnchantments::FORTUNE()->binomialDrops(
				VanillaItems::CARROT(),
				$level,
				1
			);
		} else {
			return [
				VanillaItems::CARROT()
			];
		}
	}
}
