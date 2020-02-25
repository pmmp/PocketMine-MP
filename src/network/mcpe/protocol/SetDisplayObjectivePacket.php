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

use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

class SetDisplayObjectivePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_DISPLAY_OBJECTIVE_PACKET;

	/** @var string */
	public $displaySlot;
	/** @var string */
	public $objectiveName;
	/** @var string */
	public $displayName;
	/** @var string */
	public $criteriaName;
	/** @var int */
	public $sortOrder;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->displaySlot = $in->getString();
		$this->objectiveName = $in->getString();
		$this->displayName = $in->getString();
		$this->criteriaName = $in->getString();
		$this->sortOrder = $in->getVarInt();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putString($this->displaySlot);
		$out->putString($this->objectiveName);
		$out->putString($this->displayName);
		$out->putString($this->criteriaName);
		$out->putVarInt($this->sortOrder);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleSetDisplayObjective($this);
	}
}
