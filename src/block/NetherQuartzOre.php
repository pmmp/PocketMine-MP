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

use pocketmine\block\utils\FortuneTrait;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use function mt_rand;

class NetherQuartzOre extends Opaque{
	use FortuneTrait;

	public const MINIMUM_DROPS = 1;
	public const MAXIMUM_DROPS = 1;

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	protected function getXpDropAmount() : int{
		return mt_rand(2, 5);
	}

	/**
	 * @return Item[]
	 */
	protected function getFortuneDropsForLevel(int $level) : array{
		return $this->weightedDrops(
			VanillaItems::NETHER_QUARTZ(),
			self::MINIMUM_DROPS,
			self::MAXIMUM_DROPS,
			$level
		);
	}
}
