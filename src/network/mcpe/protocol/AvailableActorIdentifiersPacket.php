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
use function base64_decode;
use function file_get_contents;

class AvailableActorIdentifiersPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_ACTOR_IDENTIFIERS_PACKET;

	/** @var string|null */
	private static $DEFAULT_NBT_CACHE = null;

	/** @var string */
	public $namedtag;

	protected function decodePayload() : void{
		$this->namedtag = $this->getRemaining();
	}

	protected function encodePayload() : void{
		$this->put(
			$this->namedtag ??
			self::$DEFAULT_NBT_CACHE ??
			(self::$DEFAULT_NBT_CACHE = file_get_contents(\pocketmine\RESOURCE_PATH . '/vanilla/entity_identifiers.nbt'))
		);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleAvailableActorIdentifiers($this);
	}
}
