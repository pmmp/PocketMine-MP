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

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use function mt_rand;

class Beetroot extends Crops{

	public function getDropsForCompatibleTool(Item $item) : array{
		if($this->age >= 7){
			return [
				VanillaItems::BEETROOT(),
				VanillaItems::BEETROOT_SEEDS()->setCount(mt_rand(0, 3))
			];
		}

		return [
			VanillaItems::BEETROOT_SEEDS()
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaItems::BEETROOT_SEEDS();
	}
}
