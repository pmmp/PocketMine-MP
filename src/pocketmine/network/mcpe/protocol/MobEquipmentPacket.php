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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\item\Item;
use pocketmine\network\mcpe\handler\SessionHandler;

class MobEquipmentPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOB_EQUIPMENT_PACKET;

	/** @var int */
	public $entityRuntimeId;
	/** @var Item */
	public $item;
	/** @var int */
	public $inventorySlot;
	/** @var int */
	public $hotbarSlot;
	/** @var int */
	public $windowId = 0;

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->item = $this->getSlot();
		$this->inventorySlot = $this->getByte();
		$this->hotbarSlot = $this->getByte();
		$this->windowId = $this->getByte();
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putSlot($this->item);
		$this->putByte($this->inventorySlot);
		$this->putByte($this->hotbarSlot);
		$this->putByte($this->windowId);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleMobEquipment($this);
	}
}
