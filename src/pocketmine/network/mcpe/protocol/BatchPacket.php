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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;

class BatchPacket extends DataPacket{
	const NETWORK_ID = 0xfe;

	public $payload;
	public $compressed = false;

	public function canBeBatched() : bool{
		return false;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	public function decode(){
		$this->payload = $this->get(true);
	}

	public function encode(){
		$this->reset();
		assert($this->compressed);
		$this->put($this->payload);
	}

	/**
	 * @param DataPacket|string $packet
	 */
	public function addPacket($packet){
		if($packet instanceof DataPacket){
			if(!$packet->isEncoded){
				$packet->encode();
			}
			$packet = $packet->buffer;
		}

		$this->payload .= Binary::writeUnsignedVarInt(strlen($packet)) . $packet;
	}

	public function compress(int $level = 7){
		assert(!$this->compressed);
		$this->payload = zlib_encode($this->payload, ZLIB_ENCODING_DEFLATE, $level);
		$this->compressed = true;
	}

	public function handle(NetworkSession $session) : bool{
		if(strlen($this->payload) < 2){
			throw new \InvalidStateException("Not enough bytes in payload, expected zlib header");
		}

		$str = zlib_decode($this->payload, 1024 * 1024 * 64); //Max 64MB
		$len = strlen($str);

		if($len === 0){
			throw new \InvalidStateException("Decoded BatchPacket payload is empty");
		}

		$this->setBuffer($str, 0);

		$network = $session->getServer()->getNetwork();
		while(!$this->feof()){
			$buf = $this->getString();
			$pk = $network->getPacket(ord($buf{0}));
			/*if(!$pk->canBeBatched()){
				throw new \InvalidArgumentException("Received invalid " . get_class($pk) . " inside BatchPacket");
			}*/

			$pk->setBuffer($buf, 1);
			$session->handleDataPacket($pk);
		}

		return true;
	}

}