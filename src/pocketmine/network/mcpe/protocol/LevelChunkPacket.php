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

use pocketmine\network\mcpe\NetworkSession;
use function count;
use const PHP_INT_MAX;

class LevelChunkPacket extends DataPacket/* implements ClientboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::LEVEL_CHUNK_PACKET;

	/**
	 * Client will request all subchunks as needed up to the top of the world
	 */
	private const CLIENT_REQUEST_FULL_COLUMN_FAKE_COUNT = 0xff_ff_ff_ff;
	/**
	 * Client will request subchunks as needed up to the height written in the packet, and assume that anything above
	 * that height is air (wtf mojang ...)
	 */
	private const CLIENT_REQUEST_TRUNCATED_COLUMN_FAKE_COUNT = 0xff_ff_ff_fe;

	private int $chunkX;
	private int $chunkZ;
	private int $subChunkCount;
	private bool $clientSubChunkRequestsEnabled;
	/** @var int[]|null */
	private ?array $usedBlobHashes = null;
	private string $extraPayload;

	/**
	 * @generate-create-func
	 * @param int[] $usedBlobHashes
	 */
	public static function create(int $chunkX, int $chunkZ, int $subChunkCount, bool $clientSubChunkRequestsEnabled, ?array $usedBlobHashes, string $extraPayload) : self{
		$result = new self;
		$result->chunkX = $chunkX;
		$result->chunkZ = $chunkZ;
		$result->subChunkCount = $subChunkCount;
		$result->clientSubChunkRequestsEnabled = $clientSubChunkRequestsEnabled;
		$result->usedBlobHashes = $usedBlobHashes;
		$result->extraPayload = $extraPayload;
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

	public function isClientSubChunkRequestEnabled() : bool{
		return $this->clientSubChunkRequestsEnabled;
	}

	public function isCacheEnabled() : bool{
		return $this->usedBlobHashes !== null;
	}

	/**
	 * @return int[]|null
	 */
	public function getUsedBlobHashes() : ?array{
		return $this->usedBlobHashes;
	}

	public function getExtraPayload() : string{
		return $this->extraPayload;
	}

	protected function decodePayload() : void{
		$this->chunkX = $this->getVarInt();
		$this->chunkZ = $this->getVarInt();

		$subChunkCountButNotReally = $this->getUnsignedVarInt();
		if($subChunkCountButNotReally === self::CLIENT_REQUEST_FULL_COLUMN_FAKE_COUNT){
			$this->clientSubChunkRequestsEnabled = true;
			$this->subChunkCount = PHP_INT_MAX;
		}elseif($subChunkCountButNotReally === self::CLIENT_REQUEST_TRUNCATED_COLUMN_FAKE_COUNT){
			$this->clientSubChunkRequestsEnabled = true;
			$this->subChunkCount = $this->getLShort();
		}else{
			$this->clientSubChunkRequestsEnabled = false;
			$this->subChunkCount = $subChunkCountButNotReally;
		}

		$cacheEnabled = $this->getBool();
		if($cacheEnabled){
			$this->usedBlobHashes = [];
			for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
				$this->usedBlobHashes[] = $this->getLLong();
			}
		}
		$this->extraPayload = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putVarInt($this->chunkX);
		$this->putVarInt($this->chunkZ);

		if($this->clientSubChunkRequestsEnabled){
			if($this->subChunkCount === PHP_INT_MAX){
				$this->putUnsignedVarInt(self::CLIENT_REQUEST_FULL_COLUMN_FAKE_COUNT);
			}else{
				$this->putUnsignedVarInt(self::CLIENT_REQUEST_TRUNCATED_COLUMN_FAKE_COUNT);
				$this->putLShort($this->subChunkCount);
			}
		}else{
			$this->putUnsignedVarInt($this->subChunkCount);
		}

		$this->putBool($this->usedBlobHashes !== null);
		if($this->usedBlobHashes !== null){
			$usedBlobHashes = $this->usedBlobHashes;
			$this->putUnsignedVarInt(count($usedBlobHashes));
			foreach($usedBlobHashes as $hash){
				$this->putLLong($hash);
			}
		}
		$this->putString($this->extraPayload);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleLevelChunk($this);
	}
}
