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


use pocketmine\network\mcpe\handler\SessionHandler;

class StopSoundPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::STOP_SOUND_PACKET;

	/** @var string */
	public $soundName;
	/** @var bool */
	public $stopAll;

	protected function decodePayload() : void{
		$this->soundName = $this->getString();
		$this->stopAll = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->soundName);
		$this->putBool($this->stopAll);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleStopSound($this);
	}
}
