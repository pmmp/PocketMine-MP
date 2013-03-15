<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class MinecraftInterface{
	public $client;
	private $socket;
	private $data;
	function __construct($server, $port = 25565, $listen = false, $client = false, $serverip = "0.0.0.0"){
		$this->socket = new UDPSocket($server, $port, (bool) $listen, $serverip);
		$this->client = (bool) $client;
		$this->start = microtime(true);
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

	protected function writeDump($pid, $raw, $data, $origin = "client", $ip = "", $port = 0){
		if(LOG === true and DEBUG >= 3){
			$p = "[".(microtime(true) - $this->start)."] [".((($origin === "client" and $this->client === true) or ($origin === "server" and $this->client === false)) ? "CLIENT->SERVER":"SERVER->CLIENT")." ".$ip.":".$port."]: ".(isset($data["id"]) ? "MC Packet ".Protocol::$dataName[$pid]:Protocol::$packetName[$pid])." (0x".Utils::strTohex(chr($pid)).") [length ".strlen($raw)."]".PHP_EOL;
			$p .= Utils::hexdump($raw);
			if(is_array($data)){
				foreach($data as $i => $d){
					$p .= $i ." => ".(!is_array($d) ? Protocol::$raknet[$pid][$i]."(".((Protocol::$raknet[$pid][$i] === "magic" or substr(Protocol::$raknet[$pid][$i], 0, 7) === "special" or is_int(Protocol::$raknet[$pid][$i])) ? Utils::strToHex($d):Utils::printable($d)).")":Protocol::$raknet[$pid][$i]."(\"".serialize(array_map("Utils::printable", $d))."\")").PHP_EOL;
				}
			}
			$p .= PHP_EOL;
			logg($p, "packets", false);
		}

	}

	public function readPacket(){
		$p = $this->popPacket();
		if($p !== false){
			return $p;
		}
		if($this->socket->connected === false){
			return false;
		}
		$buf = "";
		$source = false;
		$port = 1;
		$len = $this->socket->read($buf, $source, $port);
		if($len === false){
			return false;
		}
		$pid = ord($buf{0});
		$struct = $this->getStruct($pid);
		if($struct === false){
			console("[ERROR] Unknown Packet ID 0x".Utils::strToHex(chr($pid)), true, true, 0);
			$p = "[".(microtime(true) - $this->start)."] [".((($origin === "client" and $this->client === true) or ($origin === "server" and $this->client === false)) ? "CLIENT->SERVER":"SERVER->CLIENT")." ".$ip.":".$port."]: Error, bad packet id 0x".Utils::strToHex(chr($pid))." [length ".strlen($buf)."]".PHP_EOL;
			$p .= Utils::hexdump($buf);
			$p .= PHP_EOL;
			logg($p, "packets", true, 2);
			return false;
		}

		$packet = new Packet($pid, $struct, $buf);
		@$packet->parse();
		$this->data[] = array($pid, $packet->data, $buf, $source, $port);
		return $this->popPacket();
	}

	public function popPacket(){
		if(count($this->data) > 0){
			$p = array_shift($this->data);
			if(isset($p[1]["packets"]) and is_array($p[1]["packets"])){
				foreach($p[1]["packets"] as $d){
					$this->data[] = array($p[0], $d[1], $d[2], $p[3], $p[4]);
				}
			}
			$c = (isset($p[1]["id"]) ? true:false);
			$p[2] = $c ? chr($p[1]["id"]).$p[2]:$p[2];
			$this->writeDump(($c ? $p[1]["id"]:$p[0]), $p[2], $p[1], "server", $p[3], $p[4]);
			return array("pid" => $p[0], "data" => $p[1], "raw" => $p[2], "ip" => $p[3], "port" => $p[4]);
		}
		return false;
	}

	public function writePacket($pid, $data = array(), $raw = false, $dest = false, $port = false){
		$struct = $this->getStruct($pid);
		if($raw === false){
			$packet = new Packet($pid, $struct);
			$packet->data = $data;
			@$packet->create();
			$write = $this->socket->write($packet->raw, $dest, $port);
			$this->writeDump($pid, $packet->raw, $data, "client", $dest, $port);
		}else{
			$write = $this->socket->write($data, $dest, $port);
			$this->writeDump($pid, $data, false, "client", $dest, $port);
		}
		return true;
	}

}

?>