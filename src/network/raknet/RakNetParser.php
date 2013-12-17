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
		if($len <= 0){
			return "";
		}
		if($len === true){
			return substr($this->buffer, $this->offset);
		}
		$this->offset += $len;
		return substr($this->buffer, $this->offset - $len, $len);
	}
	
	private function getLong($unsigned = false){
		return Utils::readLong($this->get(8), $unsigned);
	}
	
	private function getInt($unsigned = false){
		return Utils::readInt($this->get(4), $unsigned);
	}
	
	private function getShort($unsigned = false){
		return Utils::readShort($this->get(2), $unsigned);
	}
	
	private function getLTriad(){
		return Utils::readTriad(strrev($this->get(3)));
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
				$this->packet->security = $this->get(5);
				$this->packet->port = $this->getShort(false);
				$this->packet->MTU = $this->getShort(false);
				$this->packet->clientGUID = $this->getLong();
				break;
			case RAKNET_DATA_PACKET_0:
			case RAKNET_DATA_PACKET_1:
			case RAKNET_DATA_PACKET_2:
			case RAKNET_DATA_PACKET_3:
			case RAKNET_DATA_PACKET_4:
			case RAKNET_DATA_PACKET_5:
			case RAKNET_DATA_PACKET_6:
			case RAKNET_DATA_PACKET_7:
			case RAKNET_DATA_PACKET_8:
			case RAKNET_DATA_PACKET_9:
			case RAKNET_DATA_PACKET_A:
			case RAKNET_DATA_PACKET_B:
			case RAKNET_DATA_PACKET_C:
			case RAKNET_DATA_PACKET_D:
			case RAKNET_DATA_PACKET_E:
			case RAKNET_DATA_PACKET_F:
				$this->seqNumber = $this->getLTriad();
				$this->data = array();
				while(!$this->feof()){
					$this->data[] = $this->parseDataPacket();				
				}
				break;
			default:
				$this->packet = false;
				break;
		}
	}
	
	private function parseDataPacket(){
		$data = new DataPacket;
		$data->pid = $this->getByte();
		$data->reliability = ($data->pid & 0b11100000) >> 5;
		$data->hasSplit = ($data->pid & 0b00010000) > 0;
		$data->length = (int) ceil($this->getShort() / 8);
		if($data->reliability === 2
		or $data->reliability === 3
		or $data->reliability === 4
		or $data->reliability === 6
		or $data->reliability === 7){
			$data->messageIndex = $this->getLTriad();
		}else{
			$data->messageIndex = 0;
		}
		
		if($reliability === 1
		or $reliability === 3
		or $reliability === 4
		or $reliability === 7){
			$data->orderIndex = $this->getLTriad();
			$data->orderChannel = $this->getByte();
		}else{
			$data->orderIndex = 0;
			$data->orderChannel = 0;
		}
		
		if($data->hasSplit == true){
			$data->splitCount = $this->getInt();
			$data->splitID = $this->getShort();
			$data->splitIndex = $this->getInt();
			//error! no split packets allowed!
			return false;
		}else{
			$data->splitCount = 0;
			$data->splitID = 0;
			$data->splitIndex = 0;
		}
		
		if($data->length <= 0
		or $this->orderChannel >= 32
		or ($hasSplit === 1 and $splitIndex >= $splitCount)){
			return false;
		}
		
		$data->id = $this->getByte();
		$data->raw = $this->get($len - 1);
		return $data;
	}

}