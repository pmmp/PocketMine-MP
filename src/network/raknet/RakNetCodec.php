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

class RakNetCodec{
	public $packet;
	public function __construct(RakNetPacket $packet){
		$this->packet = $packet;
		$this->buffer =& $this->packet->buffer;
		$this->encode();
	}
	
	private function encode(){
		if(strlen($this->packet->buffer) > 0){
			return;
		}
		$this->buffer .= chr($this->packet->pid());

		switch($this->packet->pid()){
			case RakNetInfo::OPEN_CONNECTION_REPLY_1:
				$this->buffer .= RakNetInfo::MAGIC;
				$this->putLong($this->packet->serverID);
				$this->putByte(0); //server security
				$this->putShort($this->packet->mtuSize);
				break;
			case RakNetInfo::OPEN_CONNECTION_REPLY_2:
				$this->buffer .= RakNetInfo::MAGIC;
				$this->putLong($this->packet->serverID);
				$this->putShort($this->packet->port);
				$this->putShort($this->packet->mtuSize);
				$this->putByte(0); //Server security
				break;
			case RakNetInfo::INCOMPATIBLE_PROTOCOL_VERSION:
				$this->putByte(RakNetInfo::STRUCTURE);
				$this->buffer .= RakNetInfo::MAGIC;
				$this->putLong($this->packet->serverID);
				break;
			case RakNetInfo::UNCONNECTED_PONG:
			case RakNetInfo::ADVERTISE_SYSTEM:
				$this->putLong($this->packet->pingID);
				$this->putLong($this->packet->serverID);
				$this->buffer .= RakNetInfo::MAGIC;
				$this->putString($this->packet->serverType);
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
				$this->putLTriad($this->seqNumber);
				foreach($this->data as $pk){
					$this->buffer .= $this->encodeDataPacket($pk);
				}
				break;
			case RakNetInfo::NACK:
			case RakNetInfo::ACK:
				$payload = b"";
				$records = 0;
				$pointer = 0;
				sort($this->packet->packets, SORT_NUMERIC);
				$max = count($this->packet->packets);
				
				while($pointer < $max){
					$type = true;
					$curr = $start = $this->packet->packets[$pointer];
					for($i = $start + 1; $i < $max; ++$i){
						$n = $this->packet->packets[$i];
						if(($n - $curr) === 1){
							$curr = $end = $n;
							$type = false;
							$pointer = $i + 1;
						}else{
							break;
						}
					}
					++$pointer;
					if($type === false){
						$payload .= "\x00";
						$payload .= strrev(Utils::writeTriad($start));
						$payload .= strrev(Utils::writeTriad($end));
					}else{
						$payload .= Utils::writeBool(true);
						$payload .= strrev(Utils::writeTriad($start));
					}
					++$records;
				}
				$this->putShort($records);
				$this->buffer .= $payload;
				break;
			default:
				
		}
	
	}

	protected function put($str){
		$this->buffer .= $str;
	}

	protected function putLong($v){
		$this->buffer .= Utils::writeLong($v);
	}
	
	protected function putInt($v){
		$this->buffer .= Utils::writeInt($v);
	}
	
	protected function putShort($v){
		$this->buffer .= Utils::writeShort($v);
	}

	protected function putTriad($v){
		$this->buffer .= Utils::putTriad($v);
	}
	
	protected function putLTriad($v){
		$this->buffer .= strrev(Utils::putTriad($v));
	}
	
	protected function putByte($v){
		$this->buffer .= chr($v);
	}
	
	protected function putString($v){
		$this->putShort(strlen($v));
		$this->put($v);
	}
}