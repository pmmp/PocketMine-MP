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

class CLIENT_CONNECT_DataPacket extends Packet{
	public static $ID = 0x09;

	public $clientID;
	public $sendPing;
	public $useSecurity = false;

	public function encode(){
		parent::encode();
		$this->putLong($this->clientID);
		$this->putLong($this->sendPing);
		$this->putByte($this->useSecurity ? 1 : 0);
	}

	public function decode(){
		parent::decode();
		$this->clientID = $this->getLong();
		$this->sendPing = $this->getLong();
		$this->useSecurity = $this->getByte() > 0;
	}
}