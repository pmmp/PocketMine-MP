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

class MinecraftInterface{
	public $client;
	public $bandwidth;
	private $socket;
	private $data;
	private $chunked;
	private $toChunk;
	private $needCheck;
	function __construct($object, $server, $port = 25565, $listen = false, $client = false, $serverip = "0.0.0.0"){
		$this->socket = new UDPSocket($server, $port, (bool) $listen, $serverip);
		if($this->socket->connected === false){
			console("[ERROR] Couldn't bind to $serverip:".$port, true, true, 0);
			exit(1);
		}
		$this->bandwidth = array(0, 0, microtime(true));
		$this->client = (bool) $client;
		$this->start = microtime(true);
		$this->chunked = array();
		$this->toChunk = array();
		$this->needCheck = array();
		$object->schedule(1, array($this, "checkChunked"), array(), true);
	}

	public function close(){
		return $this->socket->close(false);
	}

	protected function getStruct($pid){
		if(isset(Protocol::$raknet[$pid])){
			return Protocol::$raknet[$pid];
		}
		return false;
	}

	public function readPacket(){
		$pk = $this->popPacket();
		if($this->socket->connected === false){
			return $pk;
		}
		$buf = "";
		$source = false;
		$port = 1;
		$len = $this->socket->read($buf, $source, $port);
		if($len === false or $len === 0){
			return $pk;
		}
		$this->bandwidth[0] += $len;
		$this->parsePacket($buf, $source, $port);
		return ($pk !== false ? $pk : $this->popPacket());
	}
	
	private function parsePacket($buf, $source, $port){
		$pid = ord($buf{0});
		$struct = $this->getStruct($pid);
		if($struct === false){
			if(ServerAPI::request()->api->dhandle("server.unknownpacket", array(
				"pid" => $pid,
				"data" => array(),
				"raw" => $buf,
				"ip" => $source,
				"port" => $port
			)) !== true){
				console("[ERROR] Unknown Packet ID 0x".Utils::strToHex(chr($pid)), true, true, 2);
			}
			return false;
		}

		$packet = new Packet($pid, $struct, $buf);
		@$packet->parse();
		if($pid === 0x99){
			$CID = PocketMinecraftServer::clientID($source, $port);
			if(!isset($this->chunked[$CID]) and $packet->data[0] !== 0){ //Drop packet
				return false;
			}
			switch($packet->data[0]){
				case 0:
					$this->initChunked($CID, $source, $port);
					return false;
				case 1:
					$this->stopChunked($CID);
					return false;
				case 3:
					$this->ackChunked($CID, $data[1]["id"], $data[1]["index"]);
					return false;
				case 4:
					$this->receiveChunked($CID, $data[1]["id"], $data[1]["index"], $data[1]["count"], $data[1]["data"]);
					return true;
			}
		}
		$this->data[] = array($pid, $packet->data, $buf, $source, $port);
		return true;
	}
	
	public function checkChunked($CID){
		$time = microtime(true);
		foreach($this->needCheck as $CID => $packets){
			if($packets[-1] < $time){
				$d = $this->chunked[$CID];
				unset($packets[-1]);
				foreach($packets as $packet){
					$this->writePacket(0x99, $packet, true, $d[1], $d[2], true);
				}
				$this->needCheck[$CID][-1] = $time + 5;
			}
		}
		foreach($this->toChunk as $CID => $packets){
			$d = $this->chunked[$CID];
			$raw = "";
			$MTU = 512;
			foreach($packets as $packet){
				$raw .= $packet;
				if(($len = strlen($packet)) > $MTU){
					$MTU = $len;
				}
			}
			if($MTU > $d[0][2]){
				$this->chunked[$CID][0][2] = $MTU;
			}else{
				$MTU = $d[0][2];
			}
			$raw = str_split(gzdeflate($raw, DEFLATEPACKET_LEVEL), $MTU - 9); // - 1 - 2 - 2 - 2 - 2
			$count = count($raw);
			$messageID = $this->chunked[$CID][0][0]++;
			$this->chunked[$CID][0][0] &= 0xFFFF;
			if(!isset($this->needCheck[$CID])){
				$this->needCheck[$CID] = array();
			}
			$this->needCheck[$CID][$messageID] = array(-1 => $time + 1);
			foreach($raw as $index => $r){
				$p = "\x99\x02".Utils::writeShort($messageID).Utils::writeShort($index).Utils::writeShort($count).Utils::writeShort(strlen($r)).$r;
				$this->needCheck[$CID][$messageID][$index] = $p;
				$this->writePacket(0x99, $p, true, $d[1], $d[2], true);
			}			
			unset($this->toChunk[$CID]);
		}
	}
	
	public function isChunked($CID){
		return isset($this->chunked[$CID]);
	}
	
	private function initChunked($CID, $source, $port){
		console("[DEBUG] Starting DEFLATEPacket for $source:$port", true, true, 2);
		$this->chunked[$CID] = array(
			0 => array(0, 0, 0), //index, sent/received; MTU
			1 => $source,
			2 => $port,
			3 => array(), //Received packets
		);
		$this->writePacket(0x99, array(
			0 => 0, //start
		), false, $source, $port, true);
	}
	
	public function stopChunked($CID){
		if(!isset($this->chunked[$CID])){
			return false;
		}
		$this->writePacket(0x99, array(
			0 => 1, //stop
		), false, $this->chunked[$CID][1], $this->chunked[$CID][2], true);
		console("[DEBUG] Stopping DEFLATEPacket for ".$this->chunked[$CID][1].":".$this->chunked[$CID][2], true, true, 2);
		$this->chunked[$CID][3] = null;
		$this->chunked[$CID][4] = null;
		unset($this->chunked[$CID]);
		unset($this->toChunk[$CID]);
		unset($this->needCheck[$CID]);
	}
	
	private function ackChunked($CID, $ID, $index){
		unset($this->needCheck[$CID][$ID][$index]);
		if(count($this->needCheck[$CID][$ID]) <= 1){
			unset($this->needCheck[$CID][$ID]);
		}
	}
	
	private function receiveChunked($CID, $ID, $index, $count, $data){
		if(!isset($this->chunked[$CID][3][$ID])){
			$this->chunked[$CID][3][$ID] = array();
		}
		$this->chunked[$CID][3][$ID][$index] = $data;
		
		if(count($this->chunked[$CID][3][$ID]) === $count){
			ksort($this->chunked[$CID][3][$ID]);
			$data = gzinflate(implode($this->chunked[$CID][3][$ID]), 524280);
			unset($this->chunked[$CID][3][$ID]);
			if($data === false or strlen($data) === 0){
				console("[ERROR] Invalid DEFLATEPacket for ".$this->chunked[$CID][1].":".$this->chunked[$CID][2], true, true, 2);
			}
			$offset = 0;
			while(($plen = Utils::readShort(substr($data, $offset, 2), false)) !== 0xFFFF or $offset >= $len){
				$offset += 2;
				$packet = substr($data, $offset, $plen);
				$this->parsePacket($packet, $this->chunked[$CID][1], $this->chunked[$CID][2]);
			}
		}
	}

	public function popPacket(){
		if(count($this->data) > 0){
			$p = each($this->data);
			unset($this->data[$p[0]]);
			$p = $p[1];
			return array("pid" => $p[0], "data" => $p[1], "raw" => $p[2], "ip" => $p[3], "port" => $p[4]);
		}
		return false;
	}

	public function writePacket($pid, $data = array(), $raw = false, $dest = false, $port = false, $force = false){
		$CID = PocketMinecraftServer::clientID($dest, $port);
		if($raw === false){
			$packet = new Packet($pid, $this->getStruct($pid));
			$packet->data = $data;
			@$packet->create();			
			if($force === false and $this->isChunked($CID)){
				if(!isset($this->toChunk[$CID])){
					$this->toChunk[$CID] = array();
				}
				$this->toChunk[$CID][] = $packet->raw;
				$write = strlen($packet->raw);
			}else{
				$write = $this->socket->write($packet->raw, $dest, $port);
				$this->bandwidth[1] += $write;
			}
		}else{
			if($force === false and $this->isChunked($CID)){
				if(!isset($this->toChunk[$CID])){
					$this->toChunk[$CID] = array();
				}
				$this->toChunk[$CID][] = $data;
				$write = strlen($data);
			}else{
				$write = $this->socket->write($data, $dest, $port);
				$this->bandwidth[1] += $write;
			}
		}
		return $write;
	}

}

?>