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
	protected $server, $serverID, $timeout, $eventID, $connected;
	var $clientID, $ip, $port, $counter, $username;
	function __construct($server, $clientID, $ip, $port){
		$this->server = $server;
		$this->clientID = $clientID;
		$this->ip = $ip;
		$this->port = $port;
		$this->serverID =& $this->server->serverID;
		$this->eventID = $this->server->event("onTick", array($this, "checkTimeout"));
		console("[DEBUG] New Session started with ".$ip.":".$port, true, true, 2);
		$this->connected = true;
		$this->counter = array(0, 0);
	}
	
	public function checkTimeout($time){
		if($time > $this->timeout){
			$this->close();
		}
	}
	
	public function close($reason = "timeout"){
		$this->server->deleteEvent("onTick", $this->eventID);
		$this->connected = false;
		console("[DEBUG] Session with ".$this->ip.":".$this->port." closed due to ".$reason, true, true, 2);
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
						/*case 0x00:
							$this->send(0x84, array(
								$this->counter[0],
								0x40,
								array(
									"payload" => $data["payload"],
								),
							));
							++$this->counter[0];						
							break;*/
						case 0x15:
							$this->close("client disconnect");
							break;
						case 0x09:
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => 0x10,
									"count" => 0,
									"port" => $this->port,
									"session" => $data[2]["session"],
								),
							));
							++$this->counter[0];
							break;
							
						case 0x82:
							$this->username = $data["username"];
							console("[INFO] User ".$this->username." connected from ".$this->ip.":".$this->port);
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => 0x87,
									"seed" => $this->server->seed,
									"spawnX" => 0,
									"spawnY" => 100,
									"spawnZ" => 0,
								),
							));
							++$this->counter[0];
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
		$this->server->send($pid, $data, $raw, $this->ip, $this->port);
	}

}