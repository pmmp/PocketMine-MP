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

class DataPacket extends stdClass{
	public $id, $raw;
	private $encoded = false;
	private $decoded = false;	
	private $offset;
	
	public function encode(){

		$this->decoded = true;
		$this->encoded = true;
	}

	private function get($len){
		if($len <= 0){
			return "";
		}
		if($len === true){
			return substr($this->raw, $this->offset);
		}
		$this->offset += $len;
		return substr($this->raw, $this->offset - $len, $len);
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
		return Utils::readTriad($this->get(3));
	}
	
	private function getLTriad(){
		return Utils::readTriad(strrev($this->get(3)));
	}
	
	private function getByte(){
		return ord($this->get(1));
	}

	private function getDataArray($len = 10){
		$data = array();
		for($i = 1; $i <= $len and !$this->feof(); ++$i){
			$data[] = $this->get($this->getTriad());
		}
		return $data;
	}	
	
	private function feof(){
		return !isset($this->raw{$this->offset});
	}

	public function decode(){		
		if(!isset($this->raw{0})){
			return false;
		}
		$this->offset = 0;
		switch($this->id){
			case MC_PING:
				$this->timestamp = $this->getLong();
				break;
			case MC_PONG:
				$this->originalTimestamp = $this->getLong();
				$this->timestamp = $this->getLong();
				break;
			case MC_CLIENT_CONNECT:
				$this->clientID = $this->getLong();
				$this->session = $this->getLong();
				$this->unknown0 = $this->get(1);
				break;
			case MC_CLIENT_HANDSHAKE:
				$this->cookie = $this->get(4);
				$this->security = $this->get(1);
				$this->port = $this->getShort(true);
				$this->dataArray0 = $this->get($this->getByte());
				$this->dataArray = $this->getDataArray(9);
				$this->timestamp = $this->get(2);
				$this->session2 = $this->getLong();
				$this->session = $this->getLong();
				break;
			default:
				return false;
		}
		$this->encoded = true;
		$this->decoded = true;
	}
}