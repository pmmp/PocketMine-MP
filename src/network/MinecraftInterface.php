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
	private $packets;
	function __construct($object, $server, $port = 25565, $listen = false, $client = false, $serverip = "0.0.0.0"){
		$this->socket = new UDPSocket($server, $port, (bool) $listen, $serverip);
		if($this->socket->connected === false){
			console("[SEVERE] Couldn't bind to $serverip:".$port, true, true, 0);
			exit(1);
		}
		$this->bandwidth = array(0, 0, microtime(true));
		$this->client = (bool) $client;
		$this->start = microtime(true);
		$this->packets = array();
	}

	public function close(){
		return $this->socket->close(false);
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
	
	private function parsePacket($buffer, $source, $port){
		$pid = ord($buffer{0});
		if(RakNetInfo::isValid($pid)){
			$parser = new RakNetParser($buffer);
			if($parser->packet !== false){
				$parser->packet->ip = $source;
				$parser->packet->port = $port;
				$this->packets[] = $parser->packet;
			}
		}else{
			$packet = new Packet();
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer = $buffer;
			if(ServerAPI::request()->api->dhandle("server.unknownpacket.$pid", $packet) !== true){
				console("[ERROR] Unknown Packet ID 0x".Utils::strToHex(chr($pid)), true, true, 2);
			}
			return false;
		}
		return true;
	}

	public function popPacket(){
		if(count($this->packets) > 0){
			$p = each($this->packets);
			unset($this->packets[$p[0]]);
			return $p[1];
		}
		return false;
	}
	
	public function writePacket(Packet $packet){
		if($packet instanceof RakNetPacket){
			$codec = new RakNetCodec($packet);		
		}
		$write = $this->socket->write($packet->buffer, $packet->ip, $packet->port);
		$this->bandwidth[1] += $write;
		return $write;
	}

}

?>