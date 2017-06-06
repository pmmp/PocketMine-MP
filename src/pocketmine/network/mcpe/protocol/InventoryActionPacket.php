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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;

class InventoryActionPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::INVENTORY_ACTION_PACKET;

	const ACTION_GIVE_ITEM = 0;
	const ACTION_ENCHANT_ITEM = 2;

	public $actionId;
	public $item;
	public $enchantmentId = 0;
	public $enchantmentLevel = 0;

	public function decode(){
		$this->actionId = $this->getUnsignedVarInt();
		$this->item = $this->getSlot();
		$this->enchantmentId = $this->getVarInt();
		$this->enchantmentLevel = $this->getVarInt();
	}

	public function encode(){
		$this->reset();
		$this->putUnsignedVarInt($this->actionId);
		$this->putSlot($this->item);
		$this->putVarInt($this->enchantmentId);
		$this->putVarInt($this->enchantmentLevel);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventoryAction($this);
	}
}