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
use pocketmine\network\mcpe\protocol\types\SubChunkPacketHeightMapInfo;
use pocketmine\network\mcpe\protocol\types\SubChunkPacketHeightMapType;

class SubChunkPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_PACKET;

	private int $dimension;
	private int $subChunkX;
	private int $subChunkY;
	private int $subChunkZ;
	private string $data;
	private int $requestResult;
	private ?SubChunkPacketHeightMapInfo $heightMapData = null;

	public static function create(int $dimension, int $subChunkX, int $subChunkY, int $subChunkZ, string $data, int $requestResult, ?SubChunkPacketHeightMapInfo $heightMapData) : self{
		$result = new self;
		$result->dimension = $dimension;
		$result->subChunkX = $subChunkX;
		$result->subChunkY = $subChunkY;
		$result->subChunkZ = $subChunkZ;
		$result->data = $data;
		$result->requestResult = $requestResult;
		$result->heightMapData = $heightMapData;
		return $result;
	}

	public function getDimension() : int{ return $this->dimension; }

	public function getSubChunkX() : int{ return $this->subChunkX; }

	public function getSubChunkY() : int{ return $this->subChunkY; }

	public function getSubChunkZ() : int{ return $this->subChunkZ; }

	public function getData() : string{ return $this->data; }

	public function getRequestResult() : int{ return $this->requestResult; }

	public function getHeightMapData() : ?SubChunkPacketHeightMapInfo{ return $this->heightMapData; }

	protected function decodePayload() : void{
		$this->dimension = $this->getVarInt();
		$this->subChunkX = $this->getVarInt();
		$this->subChunkY = $this->getVarInt();
		$this->subChunkZ = $this->getVarInt();
		$this->data = $this->getString();
		$this->requestResult = $this->getVarInt();
		$heightMapDataType = $this->getByte();
		$this->heightMapData = match($heightMapDataType){
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => SubChunkPacketHeightMapInfo::read($this),
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			default => throw new \UnexpectedValueException("Unknown heightmap data type $heightMapDataType")
		};
	}

	protected function encodePayload() : void{
		$this->putVarInt($this->dimension);
		$this->putVarInt($this->subChunkX);
		$this->putVarInt($this->subChunkY);
		$this->putVarInt($this->subChunkZ);
		$this->putString($this->data);
		$this->putVarInt($this->requestResult);
		if($this->heightMapData === null){
			$this->putByte(SubChunkPacketHeightMapType::NO_DATA);
		}elseif($this->heightMapData->isAllTooLow()){
			$this->putByte(SubChunkPacketHeightMapType::ALL_TOO_LOW);
		}elseif($this->heightMapData->isAllTooHigh()){
			$this->putByte(SubChunkPacketHeightMapType::ALL_TOO_HIGH);
		}else{
			$heightMapData = $this->heightMapData; //avoid PHPStan purity issue
			$this->putByte(SubChunkPacketHeightMapType::DATA);
			$heightMapData->write($this);
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleSubChunk($this);
	}
}
