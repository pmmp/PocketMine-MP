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
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use function array_key_exists;

class CraftingTable extends Solid{

	protected $id = self::CRAFTING_TABLE;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 2.5;
	}

	public function getName() : string{
		return "Crafting Table";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$player->setCraftingGrid(new CraftingGrid($player, CraftingGrid::SIZE_BIG));

			if(!array_key_exists($windowId = Player::HARDCODED_CRAFTING_GRID_WINDOW_ID, $player->openHardcodedWindows)){
				//TODO: HACK! crafting grid doesn't fit very well into the current PM container system, so this hack allows
				//it to carry on working approximately the same way as it did in 1.14
				$pk = new ContainerOpenPacket();
				$pk->windowId = $windowId;
				$pk->type = WindowTypes::WORKBENCH;
				$pk->x = $this->getFloorX();
				$pk->y = $this->getFloorY();
				$pk->z = $this->getFloorZ();
				$player->sendDataPacket($pk);
				$player->openHardcodedWindows[$windowId] = true;
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
