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
use pocketmine\network\mcpe\protocol\types\SubChunkPosition;
use pocketmine\network\mcpe\protocol\types\SubChunkPositionOffset;
use function count;

class SubChunkRequestPacket extends DataPacket/* implements ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_REQUEST_PACKET;

	private int $dimension;
	private SubChunkPosition $basePosition;
	/**
	 * @var SubChunkPositionOffset[]
	 * @phpstan-var list<SubChunkPositionOffset>
	 */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param SubChunkPositionOffset[] $entries
	 * @phpstan-param list<SubChunkPositionOffset> $entries
	 */
	public static function create(int $dimension, SubChunkPosition $basePosition, array $entries) : self{
		$result = new self;
		$result->dimension = $dimension;
		$result->basePosition = $basePosition;
		$result->entries = $entries;
		return $result;
	}

	public function getDimension() : int{ return $this->dimension; }

	public function getBasePosition() : SubChunkPosition{ return $this->basePosition; }

	/**
	 * @return SubChunkPositionOffset[]
	 * @phpstan-return list<SubChunkPositionOffset>
	 */
	public function getEntries() : array{ return $this->entries; }

	protected function decodePayload() : void{
		$this->dimension = $this->getVarInt();
		$this->basePosition = SubChunkPosition::read($this);

		$this->entries = [];
		for($i = 0, $count = $this->getLInt(); $i < $count; $i++){
			$this->entries[] = SubChunkPositionOffset::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putVarInt($this->dimension);
		$this->basePosition->write($this);

		$this->putLInt(count($this->entries));
		foreach($this->entries as $entry){
			$entry->write($this);
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleSubChunkRequest($this);
	}
}
