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

class OPEN_CONNECTION_REPLY_1 extends Packet{
	public static $ID = 0x06;

	public $serverID;
	public $mtuSize;

	public function encode(){
		parent::encode();
		$this->put(RakLib::MAGIC);
		$this->putLong($this->serverID);
		$this->putByte(0); //Server security
		$this->putShort($this->mtuSize);
	}

	public function decode(){
		parent::decode();
		$this->offset += 16; //Magic
		$this->serverID = $this->getLong();
		$this->getByte(); //security
		$this->mtuSize = $this->getShort();
	}
}