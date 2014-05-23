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

namespace pocketmine\inventory;

use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerOpenPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Furnace;

abstract class ContainerInventory extends BaseInventory{

	/**
	 * @return Chest|Furnace
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function onOpen(Player $who){
		$pk = new ContainerOpenPacket;
		$pk->windowid = $who->getWindowId($this);
		$pk->type = 0;
		$pk->slots = $this->getSize();
		$pk->x = $this->getHolder()->getX();
		$pk->y = $this->getHolder()->getY();
		$pk->z = $this->getHolder()->getZ();
		$who->dataPacket($pk);
	}

	public function onClose(Player $who){
		$pk = new ContainerClosePacket;
		$pk->windowid = $who->getWindowId($this);
		$who->dataPacket($pk);
	}
}