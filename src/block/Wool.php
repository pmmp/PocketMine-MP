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

use pocketmine\block\utils\ColorInMetadataTrait;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;

class Wool extends Opaque{
	use ColorInMetadataTrait;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		$this->color = DyeColor::WHITE();
		parent::__construct($idInfo, $name, $breakInfo ?? new class(0.8, BlockToolType::SHEARS) extends BlockBreakInfo{
				public function getBreakTime(Item $item) : float{
					$time = parent::getBreakTime($item);
					if($item->getBlockToolType() === BlockToolType::SHEARS){
						$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
					}

					return $time;
				}
			}
		);
	}

	public function getFlameEncouragement() : int{
		return 30;
	}

	public function getFlammability() : int{
		return 60;
	}
}
