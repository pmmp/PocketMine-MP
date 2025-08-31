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
		if($player instanceof Player && !$this->isOpeningObstructed() && $this->canOpenWith($item->getCustomName())){
			$this->openToUnchecked($player);
		}

		return true;
	}

	protected function newMenu(Player $player, Inventory $inventory, Position $position) : InventoryWindow{
		return new BlockInventoryWindow($player, $inventory, $position);
	}

	public function isOpeningObstructed() : bool{
		return false;
	}

	abstract protected function getPosition() : Position;

	protected function getTile() : ?ContainerTile{
		$pos = $this->getPosition();
		$tile = $pos->getWorld()->getTile($pos);
		return $tile instanceof ContainerTile ? $tile : null;
	}

	public function canOpenWith(string $key) : bool{
		//TODO: maybe we can bring the key to the block in readStateFromWorld()?
		return $this->getTile()?->canOpenWith($key) ?? false;
	}

	public function openToUnchecked(Player $player) : bool{
		$tile = $this->getTile();
		return $tile !== null && $player->setCurrentWindow($this->newMenu($player, $tile->getInventory(), $this->getPosition()));
	}

	public function getInventory() : ?Inventory{
		return $this->getTile()?->getInventory();
	}
}
