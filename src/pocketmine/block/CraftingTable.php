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

use pocketmine\inventory\CraftingGrid;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class CraftingTable extends Solid{

	public function getHardness() : float{
		return 2.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$player->setCraftingGrid(new CraftingGrid($player, CraftingGrid::SIZE_BIG));
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
