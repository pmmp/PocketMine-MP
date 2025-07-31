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

use pocketmine\block\Hopper;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Hopper as TileHopper;

trait HopperInteractableContainerTrait{
	public function doHopperPush(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof Container){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		return HopperTransferHelper::transferOneItem(
			$tileHopper->getInventory(),
			$currentTile->getInventory()
		);
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof Container){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		return HopperTransferHelper::transferOneItem(
			$currentTile->getInventory(),
			$tileHopper->getInventory()
		);
	}

}
