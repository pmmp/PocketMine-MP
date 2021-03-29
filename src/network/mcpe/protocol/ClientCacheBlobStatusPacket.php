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

class ClientCacheBlobStatusPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_BLOB_STATUS_PACKET;

	/** @var int[] xxHash64 subchunk data hashes */
	private $hitHashes = [];
	/** @var int[] xxHash64 subchunk data hashes */
	private $missHashes = [];

	/**
	 * @param int[] $hitHashes
	 * @param int[] $missHashes
	 */
	public static function create(array $hitHashes, array $missHashes) : self{
		//type checks
		(static function(int ...$hashes) : void{})(...$hitHashes);
		(static function(int ...$hashes) : void{})(...$missHashes);

		$result = new self;
		$result->hitHashes = $hitHashes;
		$result->missHashes = $missHashes;
		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getHitHashes() : array{
		return $this->hitHashes;
	}

	/**
	 * @return int[]
	 */
	public function getMissHashes() : array{
		return $this->missHashes;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$missCount = $in->getUnsignedVarInt();
		$hitCount = $in->getUnsignedVarInt();
		for($i = 0; $i < $missCount; ++$i){
			$this->missHashes[] = $in->getLLong();
		}
		for($i = 0; $i < $hitCount; ++$i){
			$this->hitHashes[] = $in->getLLong();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->missHashes));
		$out->putUnsignedVarInt(count($this->hitHashes));
		foreach($this->missHashes as $hash){
			$out->putLLong($hash);
		}
		foreach($this->hitHashes as $hash){
			$out->putLLong($hash);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientCacheBlobStatus($this);
	}
}
