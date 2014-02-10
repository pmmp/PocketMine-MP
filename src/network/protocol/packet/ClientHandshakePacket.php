<?php

/**
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

class ClientHandshakePacket extends RakNetDataPacket{
	public $cookie;
	public $security;
	public $port;
	public $dataArray0;
	public $dataArray;
	public $timestamp;
	public $session2;
	public $session;

	public function pid(){
		return ProtocolInfo::CLIENT_HANDSHAKE_PACKET;
	}
	
	public function decode(){
		$this->cookie = $this->get(4);
		$this->security = $this->get(1);
		$this->port = $this->getShort(true);
		$this->dataArray0 = $this->get($this->getByte());
		$this->dataArray = $this->getDataArray(9);
		$this->timestamp = $this->get(2);
		$this->session2 = $this->getLong();
		$this->session = $this->getLong();
	}	
	
	public function encode(){
	
	}

}