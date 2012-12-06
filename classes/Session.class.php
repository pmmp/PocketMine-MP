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


class Session{
	protected $server, $serverID, $timeout, $connected, $evid;
	var $clientID, $ip, $port, $counter, $username, $EID;
	function __construct($server, $clientID, $EID, $ip, $port){
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->EID = $EID;
		$this->ip = $ip;
		$this->port = $port;
		$this->serverID =& $this->server->serverID;
		$this->timeout = microtime(true) + 25;
		$this->evid = array();
		$this->evid[] = array("onTick", $this->server->event("onTick", array($this, "checkTimeout")));
		$this->evid[] = array("onClose", $this->server->event("onClose", array($this, "close")));
		console("[DEBUG] New Session started with ".$ip.":".$port.". Client GUID ".$this->clientID, true, true, 2);
		$this->connected = true;
		$this->counter = array(0, 0);
	}
	
	public function checkTimeout($time){
		if($time > $this->timeout){
			$this->close("timeout");
		}
	}
	
	public function close($reason = "server stop"){
		foreach($this->evid as $ev){
			$this->server->deleteEvent($ev[0], $ev[1]);
		}
		$this->connected = false;
		$this->server->trigger("onChat", $this->username." left the game");
		console("[DEBUG] Session with ".$this->ip.":".$this->port." closed due to ".$reason, true, true, 2);
		unset($this->server->clients[$this->CID]);
	}
	
	public function eventHandler($data, $event){		
		switch($event){
			case "onTimeChange":
				$this->send(0x84, array(
					$this->counter[0],
					0x00,
					array(
						"id" => MC_SET_TIME,
						"time" => $data,
					),
				));
				++$this->counter[0];				
				break;
			case "onChat":
				$this->send(0x84, array(
					$this->counter[0],
					0x00,
					array(
						"id" => MC_CHAT,
						"message" => $data,
					),
				));
				++$this->counter[0];				
				break;
		}
	}
	
	public function handle($pid, &$data){
		if($this->connected === true){
			$this->timeout = microtime(true) + 25;
			switch($pid){
				case 0x07:
					$this->send(0x08, array(
						MAGIC,
						$this->serverID,
						$this->port,
						$data[3],
						0,
					));
					break;					
				case 0x80:
				case 0x84:
				case 0x88:
				case 0x8c:
					if(isset($data[0])){
						$this->counter[1] = $data[0];
						$this->send(0xc0, array(1, true, $data[0]));
					}
					switch($data["id"]){
						case MC_CLIENT_DISCONNECT:
							$this->close("client disconnect");
							break;
						case MC_CLIENT_CONNECT:
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_SERVER_HANDSHAKE,
									"port" => $this->port,
									"session" => $data["session"],
									"session2" => Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9"),
								),
							));
							++$this->counter[0];
							break;
						case MC_CLIENT_HANDSHAKE:
						
							break;
						case MC_LOGIN:
							$this->username = $data["username"];
							console("[INFO] Player \"".$this->username."\" connected from ".$this->ip.":".$this->port);
							$this->evid[] = array("onTimeChange", $this->server->event("onTimeChange", array($this, "eventHandler")));
							$this->evid[] = array("onChat", $this->server->event("onChat", array($this, "eventHandler")));
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_LOGIN_STATUS,
									"status" => 0,
								),
							));
							++$this->counter[0];
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_START_GAME,
									"seed" => $this->server->seed,
									"x" => 128.5,
									"y" => 100,
									"z" => 128.5,
									"unknown1" => 0,
									"gamemode" => $this->server->gamemode,
									"eid" => $this->EID,
								),
							));
							++$this->counter[0];
							break;
						case MC_READY:
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_SET_TIME,
									"time" => $this->server->time,
								),
							));
							console("[DEBUG] Player with EID ".$this->EID." \"".$this->username."\" spawned!", true, true, 2);
							$this->server->trigger("onChat", $this->username." joined the game");
							$this->eventHandler($this->server->motd, "onChat");
							break;
						case MC_MOVE_PLAYER:
							console("[DEBUG] EID ".$this->EID." moved: X ".$data["x"].", Y ".$data["y"].", Z ".$data["z"].", Pitch ".$data["pitch"].", Yaw ".$data["yaw"], true, true, 2);
							break;
						case MC_PLAYER_EQUIPMENT:
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_PLAYER_EQUIPMENT,
									"eid" => 0,
									"block" => 323,
									"meta" => 0,
								),
							));
							console("[DEBUG] EID ".$this->EID." has now ".$data["block"]." with metadata ".$data["meta"]." in their hands!", true, true, 2);
							break;
							
					}
					break;
			}
		}
	}
	
	public function send($pid, $data = array(), $raw = false){
		if($this->connected === true){
			$this->server->send($pid, $data, $raw, $this->ip, $this->port);
		}
	}

}