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

class SubChunkRequestPacket extends DataPacket/* implements ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_REQUEST_PACKET;

	private int $dimension;
	private int $subChunkX;
	private int $subChunkY;
	private int $subChunkZ;

	public static function create(int $dimension, int $subChunkX, int $subChunkY, int $subChunkZ) : self{
		$result = new self;
		$result->dimension = $dimension;
		$result->subChunkX = $subChunkX;
		$result->subChunkY = $subChunkY;
		$result->subChunkZ = $subChunkZ;
		return $result;
	}

	public function getDimension() : int{ return $this->dimension; }

	public function getSubChunkX() : int{ return $this->subChunkX; }

	public function getSubChunkY() : int{ return $this->subChunkY; }

	public function getSubChunkZ() : int{ return $this->subChunkZ; }

	protected function decodePayload() : void{
		$this->dimension = $this->getVarInt();
		$this->subChunkX = $this->getVarInt();
		$this->subChunkY = $this->getVarInt();
		$this->subChunkZ = $this->getVarInt();
	}

	protected function encodePayload() : void{
		$this->putVarInt($this->dimension);
		$this->putVarInt($this->subChunkX);
		$this->putVarInt($this->subChunkY);
		$this->putVarInt($this->subChunkZ);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleSubChunkRequest($this);
	}
}
