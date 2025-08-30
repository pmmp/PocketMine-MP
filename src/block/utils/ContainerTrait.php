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

use pocketmine\block\Block;
use pocketmine\block\inventory\window\BlockInventoryWindow;
use pocketmine\block\tile\ContainerTile;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\InventoryWindow;
use pocketmine\player\Player;
use pocketmine\world\Position;

trait ContainerTrait{
	/**
	 * @see Block::onInteract()
	 */
	public function onInteract(Item $item, Facing $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			$this->openTo($player, ignoreObstruction: false, ignoreLock: false);
		}

		return true;
	}

	protected function isOpeningObstructed() : bool{
		return false;
	}

	protected function newWindow(Player $player, Inventory $inventory, Position $position) : InventoryWindow{
		return new BlockInventoryWindow($player, $inventory, $position);
	}

	public function openTo(Player $player, bool $ignoreObstruction, bool $ignoreLock) : ContainerOpenResult{
		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof ContainerTile){
			return ContainerOpenResult::CONTAINER_NOT_FOUND;
		}
		if(!$ignoreLock && !$tile->canOpenWith($player->getHotbar()->getHeldItem()->getCustomName())){
			return ContainerOpenResult::INCORRECT_KEY;
		}
		if(!$ignoreObstruction && $this->isOpeningObstructed()){
			return ContainerOpenResult::OBSTRUCTED;
		}
		$window = $this->newWindow($player, $tile->getInventory(), $this->position);
		$player->setCurrentWindow($window);
		return ContainerOpenResult::SUCCESS;
	}
}
