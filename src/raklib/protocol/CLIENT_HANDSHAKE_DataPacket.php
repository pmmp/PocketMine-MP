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
