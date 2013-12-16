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

class RakNetParser{
	
	private $buffer;
	private $offset;
	private $packet;
	
	public function __construct($buffer){
		$this->buffer = $buffer;
		$this->offset = 0;
		if(strlen($this->buffer) > 0){
			$this->parse(ord($this->get(1)));
		}else{
			$this->packet = false;
		}
	}
	
	private function get($len){
		if($len === true){
			return substr($this->buffer, $this->offset);
		}
		$this->offset += $len;
		return substr($this->buffer, $this->offset - $len, $len);
	}
	
	private function getLong($unsigned = false){
		return Utils::readLong($this->get(8), $unsigned);
	}
	
	private function getShort($unsigned = false){
		return Utils::readShort($this->get(2), $unsigned);
	}
	
	private function getByte(){
		return ord($this->get(1));
	}
	
	
	private function feof(){
		return !isset($this->buffer{$this->offset});
	}
	
	private function parse($packetID){
		$this->packet = new RakNetPacket($packetID);
		$this->packet->length = strlen($this->buffer);
		switch($packetID){
			case RAKNET_UNCONNECTED_PING:
				$this->packet->pingID = $this->getLong();
				$this->offset += 16; //Magic
				break;
			case RAKNET_UNCONNECTED_PING_OPEN_CONNECTIONS:
				$this->packet->pingID = $this->getLong();
				$this->offset += 16; //Magic
				break;
			case RAKNET_OPEN_CONNECTION_REQUEST_1:
				$this->offset += 16; //Magic
				$this->packet->structure = $this->getByte();
				$this->packet->MTU = strlen($this->get(true));
				break;
			case RAKNET_OPEN_CONNECTION_REQUEST_2:
				$this->offset += 16; //Magic
				$this->packet->securoty = $this->get(5);
				$this->packet->port = $this->getShort(false);
				$this->packet->MTU = $this->getShort(false);
				$this->packet->clientGUID = $this->getLong();
				break;
			default:
				$this->packet = false;
				break;
		}
	}

}