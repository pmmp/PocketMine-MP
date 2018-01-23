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

class CLIENT_HANDSHAKE_DataPacket extends Packet{
	public static $ID = 0x13;

	public $address;
	public $port;

	public $systemAddresses = [];

	public $sendPing;
	public $sendPong;

	public function encode(){

	}

	public function decode(){
		parent::decode();
		$this->getAddress($this->address, $this->port);
		for($i = 0; $i < 10; ++$i){
			$this->getAddress($addr, $port, $version);
			$this->systemAddresses[$i] = [$addr, $port, $version];
		}

		$this->sendPing = $this->getLong();
		$this->sendPong = $this->getLong();
	}
}
