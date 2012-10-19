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
	var $pstruct, $name, $server, $protocol;
	
	function __construct($server, $protocol = CURRENT_PROTOCOL, $port = 25565, $listen = false){
		$this->server = new Socket($server, $port, (bool) $listen);
		$this->protocol = (int) $protocol;
		require("pstruct/RakNet.php");
		require("pstruct/packetName.php");
		$this->pstruct = $pstruct;
		$this->name = $packetName;
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
			$p = "[".microtime(true)."] [".($origin === "client" ? "CLIENT->SERVER":"SERVER->CLIENT")." ".$ip.":".$port."]: ".$this->name[$pid]." (0x".Utils::strTohex(chr($pid)).") [lenght ".strlen($raw)."]".PHP_EOL;
			$p .= hexdump($raw, false, false, true);
			if(is_array($data)){
				foreach($data as $i => $d){
					$p .= $i ." => ".(!is_array($d) ? $this->pstruct[$pid][$i]."(".(($this->pstruct[$pid][$i] === "magic" or substr($this->pstruct[$pid][$i], 0, 7) === "special") ? Utils::strToHex($d):$d).")":$this->pstruct[$pid][$i]."(***)").PHP_EOL;
				}
			}
			$p .= PHP_EOL;
			logg($p, "packets", false);
		}
	
	}
	
	public function readPacket($port = false){
		if($this->server->connected === false){
			//return array("pid" => "ff", "data" => array(0 => 'Connection error'));
		}
		$data = $this->server->read();
		if($data[3] === false){
			return false;
		}
		$pid = $data[0]{0};
		$pid = ord($pid);
		$struct = $this->getStruct($pid);
		if($struct === false){
			$p = "[".microtime(true)."] [SERVER->CLIENT]: Error, bad packet id 0x".Utils::strToHex(chr($pid)).PHP_EOL;
			$p .= hexdump($data[0], false, false, true);
			$p .= PHP_EOL . "--------------- (1024 byte max extract) ----------" .PHP_EOL;
			logg($p, "packets", true, 3);
			
			$this->buffer = "";
			//$this->server->recieve("\xff".Utils::writeString('Bad packet id '.$pid.''));
			//$this->writePacket("ff", array(0 => 'Bad packet id '.$pid.''));
			//return array("pid" => "ff", "data" => array(0 => 'Bad packet id '.$pid.''));
			return false;
		}
		
		$packet = new Packet($pid, $struct, $data[0]);
		$packet->protocol = $this->protocol;
		$packet->parse();		
		$this->writeDump($pid, $data[0], $packet->data, "server", $data[1], $data[2]);
		return array("pid" => $pid, "data" => $packet->data, "raw" => $data[0], "ip" => $data[1], "port" => $data[2]);
	}
	
	public function writePacket($pid, $data = array(), $raw = false, $dest = false, $port = false){
		$struct = $this->getStruct($pid);
		if($raw === false){
			$packet = new Packet($pid, $struct);
			$packet->protocol = $this->protocol;
			$packet->data = $data;
			$packet->create();
			$write = $this->server->write($packet->raw, $dest, $port);
			$this->writeDump($pid, $packet->raw, $data, "client");
		}else{
			$write = $this->server->write($data, $dest, $port);
			$this->writeDump($pid, $data, false, "client", $dest, $port);
		}		
		return true;
	}
	
}

?>