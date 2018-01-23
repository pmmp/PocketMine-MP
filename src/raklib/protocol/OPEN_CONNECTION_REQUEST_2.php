<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

namespace raklib\protocol;

#include <rules/RakLibPacket.h>


use raklib\RakLib;

class OPEN_CONNECTION_REQUEST_2 extends Packet{
	public static $ID = 0x07;

	public $clientID;
	public $serverAddress;
	public $serverPort;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putAddress($this->serverAddress, $this->serverPort, 4);
		$this->putShort($this->mtuSize);
		$this->putLong($this->clientID);
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; //Magic
		$this->getAddress($this->serverAddress, $this->serverPort);
		$this->mtuSize = $this->getShort();
		$this->clientID = $this->getLong();
	}
}
