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
use pocketmine\network\mcpe\protocol\types\ChunkCacheBlob;
use function count;

class ClientCacheMissResponsePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_MISS_RESPONSE_PACKET;

	/** @var ChunkCacheBlob[] */
	private $blobs = [];

	/**
	 * @param ChunkCacheBlob[] $blobs
	 */
	public static function create(array $blobs) : self{
		//type check
		(static function(ChunkCacheBlob ...$blobs) : void{})(...$blobs);

		$result = new self;
		$result->blobs = $blobs;
		return $result;
	}

	/**
	 * @return ChunkCacheBlob[]
	 */
	public function getBlobs() : array{
		return $this->blobs;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$hash = $in->getLLong();
			$payload = $in->getString();
			$this->blobs[] = new ChunkCacheBlob($hash, $payload);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->blobs));
		foreach($this->blobs as $blob){
			$out->putLLong($blob->getHash());
			$out->putString($blob->getPayload());
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientCacheMissResponse($this);
	}
}
