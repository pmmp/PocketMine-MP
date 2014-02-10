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
	public $packet;
	
	public function __construct(&$buffer){
		$this->buffer =& $buffer;
		$this->offset = 0;
		if(strlen($this->buffer) > 0){
			$this->parse();
		}else{
			$this->packet = false;
		}
	}
	
	private function get($len){
		if($len <= 0){
			$this->offset = strlen($this->buffer) - 1;
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
	
	private function parse(){
		$this->packet = new RakNetPacket(ord($this->get(1)));
		$this->packet->buffer =& $this->buffer;
		$this->packet->length = strlen($this->buffer);
		switch($this->packet->pid()){
			case RakNetInfo::UNCONNECTED_PING:
			case RakNetInfo::UNCONNECTED_PING_OPEN_CONNECTIONS:
				$this->packet->pingID = $this->getLong();
				$this->offset += 16; //Magic
				break;
			case RakNetInfo::OPEN_CONNECTION_REQUEST_1:
				$this->offset += 16; //Magic
				$this->packet->structure = $this->getByte();
				$this->packet->mtuSize = strlen($this->get(true));
				break;
			case RakNetInfo::OPEN_CONNECTION_REQUEST_2:
				$this->offset += 16; //Magic
				$this->packet->security = $this->get(5);
				$this->packet->port = $this->getShort(false);
				$this->packet->mtuSize = $this->getShort(false);
				$this->packet->clientID = $this->getLong();
				break;
			case RakNetInfo::DATA_PACKET_0:
			case RakNetInfo::DATA_PACKET_1:
			case RakNetInfo::DATA_PACKET_2:
			case RakNetInfo::DATA_PACKET_3:
			case RakNetInfo::DATA_PACKET_4:
			case RakNetInfo::DATA_PACKET_5:
			case RakNetInfo::DATA_PACKET_6:
			case RakNetInfo::DATA_PACKET_7:
			case RakNetInfo::DATA_PACKET_8:
			case RakNetInfo::DATA_PACKET_9:
			case RakNetInfo::DATA_PACKET_A:
			case RakNetInfo::DATA_PACKET_B:
			case RakNetInfo::DATA_PACKET_C:
			case RakNetInfo::DATA_PACKET_D:
			case RakNetInfo::DATA_PACKET_E:
			case RakNetInfo::DATA_PACKET_F:
				$this->packet->seqNumber = $this->getLTriad();
				$this->packet->data = array();
				while(!$this->feof() and ($pk = $this->parseDataPacket()) instanceof RakNetDataPacket){
					$this->packet->data[] = $pk;	
				}
				break;
			case RakNetInfo::NACK:
			case RakNetInfo::ACK:
				$count = $this->getShort();
				$this->packet->packets = array();
				for($i = 0; $i < $count and !$this->feof(); ++$i){
					if($this->getByte() === 0){
						$start = $this->getLTriad();
						$end = $this->getLTriad();
						if(($end - $start) > 4096){
							$end = $start + 4096;
						}
						for($c = $start; $c <= $end; ++$c){
							$this->packet->packets[] = $c;
						}
					}else{
						$this->packet->packets[] = $this->getLTriad();
					}
				}
				break;
			default:
				$this->packet = false;
				break;
		}
	}
	
	private function parseDataPacket(){
		$packetFlags = $this->getByte();
		$reliability = ($packetFlags & 0b11100000) >> 5;
		$hasSplit = ($packetFlags & 0b00010000) > 0;
		$length = (int) ceil($this->getShort() / 8);
		if($reliability === 2
		or $reliability === 3
		or $reliability === 4
		or $reliability === 6
		or $reliability === 7){
			$messageIndex = $this->getLTriad();
		}else{
			$messageIndex = false;
		}
		
		if($reliability === 1
		or $reliability === 3
		or $reliability === 4
		or $reliability === 7){
			$orderIndex = $this->getLTriad();
			$orderChannel = $this->getByte();
		}else{
			$orderIndex = false;
			$orderChannel = false;
		}
		
		if($hasSplit == true){
			$splitCount = $this->getInt();
			$splitID = $this->getShort();
			$splitIndex = $this->getInt();
		}else{
			$splitCount = false;
			$splitID = false;
			$splitIndex = false;
		}
		
		if($length <= 0
		or $orderChannel >= 32
		or ($hasSplit === true and $splitIndex >= $splitCount)){
			return false;
		}else{
			$pid = $this->getByte();
			$buffer = $this->get($length - 1);
			if(strlen($buffer) < ($length - 1)){
				return false;
			}
			if(isset(ProtocolInfo::$packets[$pid])){
				$data = new ProtocolInfo::$packets[$pid];
			}else{
				$data = new UnknownPacket();
				$data->packetID = $pid;
			}
			$data->reliability = $reliability;
			$data->hasSplit = $hasSplit;
			$data->messageIndex = $messageIndex;
			$data->orderIndex = $orderIndex;
			$data->orderChannel = $orderChannel;
			$data->splitCount = $splitCount;
			$data->splitID = $splitID;
			$data->splitIndex = $splitIndex;
			$data->setBuffer($buffer);
		}
		return $data;
	}

}