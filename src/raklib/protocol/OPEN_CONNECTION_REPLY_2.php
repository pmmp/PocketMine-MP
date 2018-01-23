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

class OPEN_CONNECTION_REPLY_2 extends Packet{
	public static $ID = 0x08;

	public $serverID;
	public $clientAddress;
	public $clientPort;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putLong($this->serverID);
		$this->putAddress($this->clientAddress, $this->clientPort, 4);
		$this->putShort($this->mtuSize);
		$this->putByte(0); //server security
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; //Magic
		$this->serverID = $this->getLong();
		$this->getAddress($this->clientAddress, $this->clientPort);
		$this->mtuSize = $this->getShort();
		//server security
	}
}
