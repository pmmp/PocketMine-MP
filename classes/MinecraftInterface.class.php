<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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
	var $pstruct, $name, $server, $protocol, $client, $buffer;
	
	function __construct($server, $protocol = CURRENT_PROTOCOL, $port = 25565, $listen = false, $client = true){
		$this->server = new Socket($server, $port, (bool) $listen);
		$this->protocol = (int) $protocol;
		require("pstruct/RakNet.php");
		require("pstruct/packetName.php");
		$this->pstruct = $pstruct;
		$this->name = $packetName;
		$this->buffer = array();
		$this->client = (bool) $client;
		$this->start = microtime(true);
	}
	
	public function close(){
		return $this->server->close();
	}
	
	protected function getStruct($pid){
		if(isset($this->pstruct[$pid])){
			return $this->pstruct[$pid];
		}
		return false;
	}
	
	protected function writeDump($pid, $raw, $data, $origin = "client", $ip = "", $port = 0){
		if(LOG === true and DEBUG >= 2){
			$p = "[".(microtime(true) - $this->start)."] [".((($origin === "client" and $this->client === true) or ($origin === "server" and $this->client === false)) ? "CLIENT->SERVER":"SERVER->CLIENT")." ".$ip.":".$port."]: ".$this->name[$pid]." (0x".Utils::strTohex(chr($pid)).") [lenght ".strlen($raw)."]".PHP_EOL;
			$p .= Utils::hexdump($raw);
			if(is_array($data)){
				foreach($data as $i => $d){
					$p .= $i ." => ".(!is_array($d) ? $this->pstruct[$pid][$i]."(".(($this->pstruct[$pid][$i] === "magic" or substr($this->pstruct[$pid][$i], 0, 7) === "special" or is_int($this->pstruct[$pid][$i])) ? Utils::strToHex($d):$d).")":$this->pstruct[$pid][$i]."(\"".serialize($d)."\")").PHP_EOL;
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
		if($this->server->connected === false){
			//return array("pid" => "ff", "data" => array(0 => 'Connection error'));
		}
		$data = $this->server->read();
		if($data[3] === false){
			return false;
		}
		$pid = ord($data[0]);
		/*if($pid === 0x84){
			$data[0] = substr($data[0], 10);
			$pid = ord($data[0]);
		}*/
		$struct = $this->getStruct($pid);
		if($struct === false){
			console("[ERROR] Bad packet id 0x".Utils::strTohex(chr($pid)), true, true, 0);
			$p = "[".(microtime(true) - $this->start)."] [".((($origin === "client" and $this->client === true) or ($origin === "server" and $this->client === false)) ? "CLIENT->SERVER":"SERVER->CLIENT")." ".$ip.":".$port."]: Error, bad packet id 0x".Utils::strTohex(chr($pid))." [lenght ".strlen($raw)."]".PHP_EOL;
			$p .= Utils::hexdump($data[0]);
			$p .= PHP_EOL;
			logg($p, "packets", true, 2);
			
			$this->buffer = "";
			//$this->server->recieve("\xff".Utils::writeString('Bad packet id '.$pid.''));
			//$this->writePacket("ff", array(0 => 'Bad packet id '.$pid.''));
			//return array("pid" => "ff", "data" => array(0 => 'Bad packet id '.$pid.''));
			return false;
		}
		
		$packet = new Packet($pid, $struct, $data[0]);
		$packet->protocol = $this->protocol;
		$packet->parse();
		$this->data[] = array($pid, $packet->data, $data[0], $data[1], $data[2]);
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
			$packet->protocol = $this->protocol;
			$packet->data = $data;
			$packet->create();
			$write = $this->server->write($packet->raw, $dest, $port);
			$this->writeDump($pid, $packet->raw, $data, "client", $dest, $port);
		}else{
			$write = $this->server->write($data, $dest, $port);
			$this->writeDump($pid, $data, false, "client", $dest, $port);
		}		
		return true;
	}
	
}

?>