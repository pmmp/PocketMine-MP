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
	 *
	 * @return self
	 */
	public static function create(array $hitHashes, array $missHashes) : self{
		//type checks
		(static function(int ...$hashes){})(...$hitHashes);
		(static function(int ...$hashes){})(...$missHashes);

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

	protected function decodePayload() : void{
		$hitCount = $this->getUnsignedVarInt();
		$missCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $hitCount; ++$i){
			$this->hitHashes[] = $this->getLLong();
		}
		for($i = 0; $i < $missCount; ++$i){
			$this->missHashes[] = $this->getLLong();
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->hitHashes));
		$this->putUnsignedVarInt(count($this->missHashes));
		foreach($this->hitHashes as $hash){
			$this->putLLong($hash);
		}
		foreach($this->missHashes as $hash){
			$this->putLLong($hash);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleClientCacheBlobStatus($this);
	}
}
