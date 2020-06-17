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
use function count;

class LevelChunkPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::LEVEL_CHUNK_PACKET;

	/** @var int */
	private $chunkX;
	/** @var int */
	private $chunkZ;
	/** @var int */
	private $subChunkCount;
	/** @var bool */
	private $cacheEnabled;
	/** @var int[] */
	private $usedBlobHashes = [];
	/** @var string */
	private $extraPayload;

	public static function withoutCache(int $chunkX, int $chunkZ, int $subChunkCount, string $payload) : self{
		$result = new self;
		$result->chunkX = $chunkX;
		$result->chunkZ = $chunkZ;
		$result->subChunkCount = $subChunkCount;
		$result->extraPayload = $payload;

		$result->cacheEnabled = false;

		return $result;
	}

	/**
	 * @param int[] $usedBlobHashes
	 */
	public static function withCache(int $chunkX, int $chunkZ, int $subChunkCount, array $usedBlobHashes, string $extraPayload) : self{
		(static function(int ...$hashes) : void{})(...$usedBlobHashes);
		$result = new self;
		$result->chunkX = $chunkX;
		$result->chunkZ = $chunkZ;
		$result->subChunkCount = $subChunkCount;
		$result->extraPayload = $extraPayload;

		$result->cacheEnabled = true;
		$result->usedBlobHashes = $usedBlobHashes;

		return $result;
	}

	public function getChunkX() : int{
		return $this->chunkX;
	}

	public function getChunkZ() : int{
		return $this->chunkZ;
	}

	public function getSubChunkCount() : int{
		return $this->subChunkCount;
	}

	public function isCacheEnabled() : bool{
		return $this->cacheEnabled;
	}

	/**
	 * @return int[]
	 */
	public function getUsedBlobHashes() : array{
		return $this->usedBlobHashes;
	}

	public function getExtraPayload() : string{
		return $this->extraPayload;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->chunkX = $in->getVarInt();
		$this->chunkZ = $in->getVarInt();
		$this->subChunkCount = $in->getUnsignedVarInt();
		$this->cacheEnabled = $in->getBool();
		if($this->cacheEnabled){
			for($i =  0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
				$this->usedBlobHashes[] = $in->getLLong();
			}
		}
		$this->extraPayload = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putVarInt($this->chunkX);
		$out->putVarInt($this->chunkZ);
		$out->putUnsignedVarInt($this->subChunkCount);
		$out->putBool($this->cacheEnabled);
		if($this->cacheEnabled){
			$out->putUnsignedVarInt(count($this->usedBlobHashes));
			foreach($this->usedBlobHashes as $hash){
				$out->putLLong($hash);
			}
		}
		$out->putString($this->extraPayload);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleLevelChunk($this);
	}
}
