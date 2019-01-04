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


use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\NetworkSession;
use function assert;
use function get_class;
use function strlen;
use function zlib_decode;
use function zlib_encode;
#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

class BatchPacket extends DataPacket{
	public const NETWORK_ID = 0xfe;

	/** @var string */
	public $payload = "";
	/** @var int */
	protected $compressionLevel = 7;

	public function canBeBatched() : bool{
		return false;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	protected function decodeHeader(){
		$pid = $this->getByte();
		assert($pid === static::NETWORK_ID);
	}

	protected function decodePayload(){
		$data = $this->getRemaining();
		try{
			$this->payload = zlib_decode($data, 1024 * 1024 * 64); //Max 64MB
		}catch(\ErrorException $e){ //zlib decode error
			$this->payload = "";
		}
	}

	protected function encodeHeader(){
		$this->putByte(static::NETWORK_ID);
	}

	protected function encodePayload(){
		$this->put(zlib_encode($this->payload, ZLIB_ENCODING_DEFLATE, $this->compressionLevel));
	}

	/**
	 * @param DataPacket $packet
	 */
	public function addPacket(DataPacket $packet){
		if(!$packet->canBeBatched()){
			throw new \InvalidArgumentException(get_class($packet) . " cannot be put inside a BatchPacket");
		}
		if(!$packet->isEncoded){
			$packet->encode();
		}

		$this->payload .= Binary::writeUnsignedVarInt(strlen($packet->buffer)) . $packet->buffer;
	}

	/**
	 * @return \Generator
	 */
	public function getPackets(){
		$stream = new NetworkBinaryStream($this->payload);
		while(!$stream->feof()){
			yield $stream->getString();
		}
	}

	public function getCompressionLevel() : int{
		return $this->compressionLevel;
	}

	public function setCompressionLevel(int $level){
		$this->compressionLevel = $level;
	}

	public function handle(NetworkSession $session) : bool{
		if($this->payload === ""){
			return false;
		}

		foreach($this->getPackets() as $buf){
			$pk = PacketPool::getPacket($buf);

			if(!$pk->canBeBatched()){
				throw new \UnexpectedValueException("Received invalid " . get_class($pk) . " inside BatchPacket");
			}

			$session->handleDataPacket($pk);
		}

		return true;
	}
}
