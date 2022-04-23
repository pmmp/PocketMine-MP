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

class CreatePhotoPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::CREATE_PHOTO_PACKET;

	private int $entityUniqueId;
	private string $photoName;
	private string $photoItemName;

	public static function create(int $actorUniqueId, string $photoName, string $photoItemName) : self{
		$result = new self;
		$result->entityUniqueId = $actorUniqueId;
		$result->photoName = $photoName;
		$result->photoItemName = $photoItemName;
		return $result;
	}

	/**
	 * TODO: rename this to getEntityUniqueId() on PM4 (shit architecture, thanks shoghi)
	 */
	public function getEntityUniqueIdField() : int{ return $this->entityUniqueId; }

	public function getPhotoName() : string{ return $this->photoName; }

	public function getPhotoItemName() : string{ return $this->photoItemName; }

	protected function decodePayload() : void{
		$this->entityUniqueId = $this->getLLong(); //why be consistent mojang ?????
		$this->photoName = $this->getString();
		$this->photoItemName = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putLLong($this->entityUniqueId);
		$this->putString($this->photoName);
		$this->putString($this->photoItemName);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleCreatePhoto($this);
	}
}
