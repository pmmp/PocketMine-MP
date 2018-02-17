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

use pocketmine\network\mcpe\NetworkSession;

class ServerSettingsResponsePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SERVER_SETTINGS_RESPONSE_PACKET;

	/** @var int */
	public $formId;
	/** @var string */
	public $formData; //json

	protected function decodePayload(){
		$this->formId = $this->getUnsignedVarInt();
		$this->formData = $this->getString();
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->formId);
		$this->putString($this->formData);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleServerSettingsResponse($this);
	}
}
