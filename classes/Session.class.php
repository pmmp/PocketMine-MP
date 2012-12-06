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
	var $clientID, $ip, $port, $counter, $username;
	function __construct($server, $clientID, $ip, $port){
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
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
		if($this->reason === "server stop"){
			$this->send(0x84, array(
				$this->counter[0],
				0x00,
				array(
					"id" => 0x15,
				),
			));
			++$this->counter[0];		
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
						"id" => 0x86,
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
						"id" => 0x85,
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
				case 0x84:
					if(isset($data[0])){
						$this->counter[1] = $data[0];
						$this->send(0xc0, array(1, true, $data[0]));
					}
					switch($data["id"]){
						case 0x15:
							$this->close("client disconnect");
							break;
						case 0x09:
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => 0x10,
									"port" => $this->port,
									"session" => $data["session"],
									"session2" => Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9"),
								),
							));
							++$this->counter[0];
							break;
							
						case 0x82:
							$this->username = $data["username"];
							console("[INFO] ".$this->username." connected from ".$this->ip.":".$this->port);
							$this->evid[] = array("onTimeChange", $this->server->event("onTimeChange", array($this, "eventHandler")));
							$this->evid[] = array("onChat", $this->server->event("onChat", array($this, "eventHandler")));
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => 0x83,
									"status" => 0,
								),
							));
							++$this->counter[0];
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => 0x87,
									"seed" => $this->server->seed,
									"x" => 128,
									"y" => 100,
									"z" => 128,
									"unknown1" => 0,
									"gamemode" => $this->server->gamemode,
									"unknwon2" => 0,
								),
							));
							++$this->counter[0];
							break;
						case 0x84:
							console("[DEBUG] ".$this->username." spawned!", true, true, 2);
							$this->server->trigger("onChat", $this->username." joined the game");
							$this->eventHandler("Welcome to ".$this->server->name, "onChat");
							break;
							
					}
					break;
				case 0x8c:
					$counter = $data[0];
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