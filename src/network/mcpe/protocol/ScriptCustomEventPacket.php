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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class ScriptCustomEventPacket extends DataPacket{ //TODO: this doesn't have handlers in either client or server in the game as of 1.8
	public const NETWORK_ID = ProtocolInfo::SCRIPT_CUSTOM_EVENT_PACKET;

	/** @var string */
	public $eventName;
	/** @var string json data */
	public $eventData;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->eventName = $in->getString();
		$this->eventData = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putString($this->eventName);
		$out->putString($this->eventData);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleScriptCustomEvent($this);
	}
}
