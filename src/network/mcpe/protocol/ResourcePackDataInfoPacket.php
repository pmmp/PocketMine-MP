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
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackType;

class ResourcePackDataInfoPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_DATA_INFO_PACKET;

	/** @var string */
	public $packId;
	/** @var int */
	public $maxChunkSize;
	/** @var int */
	public $chunkCount;
	/** @var int */
	public $compressedPackSize;
	/** @var string */
	public $sha256;
	/** @var bool */
	public $isPremium = false;
	/** @var int */
	public $packType = ResourcePackType::RESOURCES; //TODO: check the values for this

	public static function create(string $packId, int $maxChunkSize, int $chunkCount, int $compressedPackSize, string $sha256sum) : self{
		$result = new self;
		$result->packId = $packId;
		$result->maxChunkSize = $maxChunkSize;
		$result->chunkCount = $chunkCount;
		$result->compressedPackSize = $compressedPackSize;
		$result->sha256 = $sha256sum;
		return $result;
	}

	protected function decodePayload() : void{
		$this->packId = $this->buf->getString();
		$this->maxChunkSize = $this->buf->getLInt();
		$this->chunkCount = $this->buf->getLInt();
		$this->compressedPackSize = $this->buf->getLLong();
		$this->sha256 = $this->buf->getString();
		$this->isPremium = $this->buf->getBool();
		$this->packType = $this->buf->getByte();
	}

	protected function encodePayload() : void{
		$this->buf->putString($this->packId);
		$this->buf->putLInt($this->maxChunkSize);
		$this->buf->putLInt($this->chunkCount);
		$this->buf->putLLong($this->compressedPackSize);
		$this->buf->putString($this->sha256);
		$this->buf->putBool($this->isPremium);
		$this->buf->putByte($this->packType);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleResourcePackDataInfo($this);
	}
}
