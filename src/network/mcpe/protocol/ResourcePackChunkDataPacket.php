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
use function strlen;

class ResourcePackChunkDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CHUNK_DATA_PACKET;

	/** @var string */
	public $packId;
	/** @var int */
	public $chunkIndex;
	/** @var int */
	public $progress;
	/** @var string */
	public $data;

	public static function create(string $packId, int $chunkIndex, int $chunkOffset, string $data) : self{
		$result = new self;
		$result->packId = $packId;
		$result->chunkIndex = $chunkIndex;
		$result->progress = $chunkOffset;
		$result->data = $data;
		return $result;
	}

	protected function decodePayload() : void{
		$this->packId = $this->buf->getString();
		$this->chunkIndex = $this->buf->getLInt();
		$this->progress = $this->buf->getLLong();
		$this->data = $this->buf->getString();
	}

	protected function encodePayload() : void{
		$this->buf->putString($this->packId);
		$this->buf->putLInt($this->chunkIndex);
		$this->buf->putLLong($this->progress);
		$this->buf->putString($this->data);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleResourcePackChunkData($this);
	}
}
