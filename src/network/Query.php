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

/*
Implementation of the UT3 Query Protocol (GameSpot)
Source: http://wiki.unrealadmin.org/UT3_query_protocol
*/

class Query{
	private $socket, $workers, $threads, $server, $token, $longData, $timeout;
	
	public function __construct(){
		$this->workers = array();
		$this->password = (string) $password;
		console("[INFO] Starting GS4 status listener");
		$this->threads = (int) max(1, $threads);
		$this->clientsPerThread = (int) max(1, $clientsPerThread);
		$this->server = ServerAPI::request();
		$addr = $this->server->api->getProperty("server-ip", "0.0.0.0");
		$port = $this->server->api->getProperty("server-port");
		console("[INFO] Setting query port to $port");
		$this->server->addHandler("server.unknownpacket", array($this, "packetHandler"), 50);
		$this->server->schedule(20 * 30, array($this, "regenerateToken"), array(), true);
		$this->regenerateToken();
		$this->regenerateInfo();
		console("[INFO] Query running on $addr:$port");
	}
	
	public function regenerateInfo(){
		$str = "";
		$plist = "PocketMine-MP ".MAJOR_VERSION;
		$pl = $this->server->api->plugin->getList();
		if(count($pl) > 0){
			$plist .= ":";
			foreach($pl as $p){
				$plist .= " ".str_replace(array(";", ":", " "), array("", "", "_"), $p["name"])." ".str_replace(array(";", ":", " "), array("", "", "_"), $p["version"]).";";
			}
			$plist = substr($plist, 0, -1);
		}
		$KVdata = array(
			"splitnum" => chr(128),
			"hostname" => $this->server->name,
			"gametype" => "SMP",
			"game_id" => "MINECRAFTPE",
			"version" => CURRENT_MINECRAFT_VERSION,
			"plugins" => $plist,
			"map" => $this->server->mapName,
			"numplayers" => count($this->server->clients),
			"maxplayers" => $this->server->maxClients,
			"hostport" => $this->server->api->getProperty("server-port"),
			//"hostip" => $this->server->api->getProperty("server-ip", "0.0.0.0")
		);
		foreach($KVdata as $key => $value){
			$str .= $key."\x00".$value."\x00";
		}
		$str .= "\x00\x01player_\x00\x00";
		foreach($this->server->clients as $player){
			if($player->username != ""){
				$str .= $player->username."\x00";
			}
		}
		$str .= "\x00";
		$this->longData = $str;
		$this->timeout = microtime(true) + 5;
	}
	
	public function regenerateToken(){
		$this->token = Utils::readInt("\x00".Utils::getRandomBytes(3, false));
	}
	
	public function packetHandler(&$packet, $event){
		if($event !== "server.unknownpacket"){
			return;
		}
		$magic = substr($packet["raw"], 0, 2);
		$offset = 2;
		if($magic !== "\xfe\xfd"){
			return;
		}
		$type = ord($packet["raw"]{2});
		++$offset;
		$sessionID = Utils::readInt(substr($packet["raw"], $offset, 4));
		$offset += 4;
		$payload = substr($packet["raw"], $offset);
		switch($type){
			case 9: //Handshake
				$this->server->send(9, chr(9).Utils::writeInt($sessionID).$this->token."\x00", true, $packet["ip"], $packet["port"]);
				break;
			case 0: //Stat
				$token = Utils::readInt(substr($payload, 0, 4));
				if($token !== $this->token){
					break;
				}
				if(strlen($payload) === 8){
					if($this->timeout < microtime(true)){
						$this->regenerateInfo();
					}
					$this->server->send(0, chr(0).Utils::writeInt($sessionID).$this->longData, true, $packet["ip"], $packet["port"]);				
				}else{
					$this->server->send(0, chr(0).Utils::writeInt($sessionID).$this->server->name."\x00SMP\x00".$this->server->mapName."\x00".count($this->server->clients)."\x00".$this->server->maxClients."\x00".Utils::writeLShort($this->server->api->getProperty("server-port")).$this->server->api->getProperty("server-ip", "0.0.0.0")."\x00", true, $packet["ip"], $packet["port"]);
				}
				break;
		}
		return true;
	}

}
