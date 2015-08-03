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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\Network;
use pocketmine\network\protocol\TileEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Chest;

class AnvilInventory extends ContainerInventory implements InventoryHolder{
	public function __construct(Position $pos){
		parent::__construct($this, InventoryType::get(InventoryType::ANVIL));
	}

	public function getInventory(){
		return $this;
	}

	/**
	 * @return AnvilInventory
	 */
	public function getHolder(){
		return $this->holder;
	}
}