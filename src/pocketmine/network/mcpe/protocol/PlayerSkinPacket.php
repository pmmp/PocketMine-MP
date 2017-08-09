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
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	/** @var UUID */
	public $uuid;
	/** @var string */
	public $skinId;
	/** @var string */
	public $skinName;
	/** @var string */
	public $serializeName;
	/** @var string */
	public $skinData;
	/** @var string */
	public $capeData;
	/** @var string */
	public $geometryModel;
	/** @var string */
	public $geometryData;


	protected function decodePayload(){
		$this->uuid = $this->getUUID();
		$this->skinId = $this->getString();
		$this->skinName = $this->getString();
		$this->serializeName = $this->getString();
		$this->skinData = $this->getString();
		$this->capeData = $this->getString();
		$this->geometryModel = $this->getString();
		$this->geometryData = $this->getString();
	}

	protected function encodePayload(){
		$this->putUUID($this->uuid);
		$this->putString($this->skinId);
		$this->putString($this->skinName);
		$this->putString($this->serializeName);
		$this->putString($this->skinData);
		$this->putString($this->capeData);
		$this->putString($this->geometryModel);
		$this->putString($this->geometryData);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerSkin($this);
	}
}