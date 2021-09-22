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

use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;

class AddVolumeEntityPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_VOLUME_ENTITY_PACKET;

	/** @var int */
	private $entityNetId;
	/** @var CompoundTag */
	private $data;
	/** @var string */
	private $engineVersion;

	public static function create(int $entityNetId, CompoundTag $data, string $engineVersion) : self{
		$result = new self;
		$result->entityNetId = $entityNetId;
		$result->data = $data;
		$result->engineVersion = $engineVersion;
		return $result;
	}

	public function getEntityNetId() : int{ return $this->entityNetId; }

	public function getData() : CompoundTag{ return $this->data; }

	public function getEngineVersion() : string{ return $this->engineVersion; }

	protected function decodePayload() : void{
		$this->entityNetId = $this->getUnsignedVarInt();
		$this->data = $this->getNbtCompoundRoot();
		$this->engineVersion = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt($this->entityNetId);
		$this->put((new NetworkLittleEndianNBTStream())->write($this->data));
		$this->putString($this->engineVersion);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleAddVolumeEntity($this);
	}
}
