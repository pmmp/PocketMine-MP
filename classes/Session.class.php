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
	var $clientID, $ip, $port, $counter, $username, $eid, $data;
	function __construct($server, $clientID, $eid, $ip, $port){
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->eid = $eid;
		$this->data = array();
		$this->ip = $ip;
		$this->entity = false;
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
	
	function __destruct(){
		//$this->close("destruct");
	}
	
	public function save(){
		if(is_object($this->entity)){
			$this->data["spawn"] = array(
				"x" => $this->entity->position["x"],
				"y" => $this->entity->position["y"],
				"z" => $this->entity->position["z"],
			);
		}
		file_put_contents(FILE_PATH."data/players/".str_replace("/", "", $this->username).".dat", serialize($this->data));
	}
	
	public function close($reason = "server stop", $msg = true){
		foreach($this->evid as $ev){
			$this->server->deleteEvent($ev[0], $ev[1]);
		}
		$this->connected = false;
		if($msg === true){
			$this->server->trigger("onChat", $this->username." left the game");
		}
		$this->save();
		if(is_object($this->entity)){
			$this->entity->__destruct();
		}
		console("[INFO] Session with ".$this->ip.":".$this->port." closed due to ".$reason);
		unset($this->server->entities[$this->eid]);
		unset($this->server->clients[$this->CID]);
	}
	
	public function eventHandler($data, $event){		
		switch($event){
			case "onEntityMove":
				if($data === $this->eid){
					break;
				}
				$entity = $this->server->entities[$this->eid];
				$this->send(0x84, array(
					$this->counter[0],
					0x00,
					array(
						"id" => MC_MOVE_ENTITY,
						"eid" => $data,
						"x" => $entity->position["x"],
						"y" => $entity->position["y"],
						"z" => $entity->position["z"],
					),
				));
				++$this->counter[0];
				break;
			case "onHealthChange":
				if($data["eid"] === $this->eid){
					$this->send(0x84, array(
						$this->counter[0],
						0x00,
						array(
							"id" => MC_SET_HEALTH,
							"health" => $data["health"],
						),
					));
					++$this->counter[0];
					$this->data["health"] = $data["health"];
					if(is_object($this->entity)){
						$this->entity->setHealth($data["health"]);
					}
				}
				break;
			case "onPlayerAdd":
				if($data["eid"] === $this->eid){
					break;
				}
				/*$this->send(0x84, array(
					$this->counter[0],
					0x00,
					array(
						"id" => MC_ADD_PLAYER,
						"clientID" => $data["clientID"],
						"username" => $data["username"],
						"eid" => $data["eid"],
						"x" => $data["x"],
						"y" => $data["y"],
						"z" => $data["z"],
						"yaw" => $data["yaw"],
						"pitch" => $data["pitch"],
						"block" => $data["block"],
						"meta" => $data["meta"],
					),
				));
				++$this->counter[0];
				*/
				$this->send(0x84, array(
					$this->counter[0],
					0x00,
					array(
						"id" => MC_ADD_ITEM_ENTITY,
						"eid" => $data["eid"],
						"x" => $data["x"],
						"y" => $data["y"],
						"z" => $data["z"],
						"block" => 10,
						"meta" => 0,
						"stack" => 1,
					),
				));
				++$this->counter[0];
				break;
			case "onEntityRemove":
				if($data === $this->eid){
					$this->close("despawn");
				}else{
					$this->send(0x84, array(
						$this->counter[0],
						0x00,
						array(
							"id" => MC_ENTITY_REMOVE,
							"eid" => $data,
						),
					));
					++$this->counter[0];
				}
				break;
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
							$this->username = str_replace("/", "", $data["username"]);
							foreach($this->server->clients as $c){
								if($c->eid !== $this->eid and $c->username === $this->username){
									$c->close("logged in from another location");
								}
							}
							if($this->server->whitelist !== false and !in_array($this->username, $this->server->whitelist)){
								$this->close("\"".$this->username."\" not being on white-list", false);
								break;
							}
							console("[INFO] Player \"".$this->username."\" connected from ".$this->ip.":".$this->port);
							if(!file_exists(FILE_PATH."data/players/".$this->username.".dat")){
								console("[NOTICE] Player data not found for \"".$this->username."\", creating new");
								$this->data = array(
									"spawn" => array(
										"x" => $this->server->spawn["x"],
										"y" => $this->server->spawn["y"],
										"z" => $this->server->spawn["z"],
									),
									"health" => 20,
									"lastIP" => $this->ip,
									"lastID" => $this->clientID,
								);
							}else{
								$this->data = unserialize(file_get_contents(FILE_PATH."data/players/".str_replace("/", "", $this->username).".dat"));
								$this->data["lastIP"] = $this->ip;
								$this->data["lastID"] = $this->clientID;
							}
							$this->evid[] = array("onTimeChange", $this->server->event("onTimeChange", array($this, "eventHandler")));
							$this->evid[] = array("onChat", $this->server->event("onChat", array($this, "eventHandler")));
							$this->evid[] = array("onPlayerAdd", $this->server->event("onPlayerAdd", array($this, "eventHandler")));
							$this->evid[] = array("onEntityDespawn", $this->server->event("onEntityDespawn", array($this, "eventHandler")));
							$this->evid[] = array("onEntityMove", $this->server->event("onEntityMove", array($this, "eventHandler")));
							$this->evid[] = array("onHealthChange", $this->server->event("onHealthChange", array($this, "eventHandler")));
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
									"x" => $this->data["spawn"]["x"],
									"y" => $this->data["spawn"]["y"],
									"z" => $this->data["spawn"]["z"],
									"unknown1" => 0,
									"gamemode" => $this->server->gamemode,
									"eid" => $this->eid,
								),
							));
							++$this->counter[0];
							break;
						case MC_READY:
							if(is_object($this->entity)){
								break;
							}
							$this->server->trigger("onHealthChange", array("eid" => $this->eid, "health" => $this->data["health"]));
							console("[DEBUG] Player with EID ".$this->eid." \"".$this->username."\" spawned!", true, true, 2);
							$this->entity = new Entity($this->eid, ENTITY_PLAYER, 0, $this->server);
							$this->entity->setName($this->username);
							$this->server->entities[$this->eid] = &$this->entity;
							$this->server->trigger("onPlayerAdd", array(
								"clientID" => $this->clientID,
								"username" => $this->username,
								"eid" => $this->eid,
								"x" => $this->data["spawn"]["x"],
								"y" => $this->data["spawn"]["y"],
								"z" => $this->data["spawn"]["z"],
								"yaw" => $this->data["spawn"]["yaw"],
								"pitch" => $this->data["spawn"]["pitch"],
								"block" => 0,
								"meta" => 0,
							));
							foreach($this->server->entities as $entity){
								if($entity->eid !== $this->eid){
									$this->send(0x84, array(
										$this->counter[0],
										0x00,
										array(
											"id" => MC_ADD_ITEM_ENTITY,
											"eid" => $entity->eid,
											"x" => $entity->position["x"],
											"y" => $entity->position["y"],
											"z" => $entity->position["z"],
											"block" => $entity->type,
											"meta" => 0,
											"stack" => 1,
										),
									));
									++$this->counter[0];
								}							
							}
							$this->eventHandler($this->server->motd, "onChat");
							$this->server->trigger("onChat", $this->username." joined the game");
							break;
						case MC_MOVE_PLAYER:
							$this->entity->setPosition($data["x"], $data["y"], $data["z"], $data["yaw"], $data["pitch"]);
							$this->server->trigger("onEntityMove", $this->eid);
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_ADD_ITEM_ENTITY,
									"eid" => $this->server->eidCnt++,
									"x" => $data["x"],
									"y" => $data["y"],
									"z" => $data["z"],
									"block" => 7,
									"meta" => 0,
									"stack" => 1,
								),
							));
							++$this->counter[0];
							break;
						case MC_PLAYER_EQUIPMENT:
							console("[DEBUG] EID ".$this->eid." has now ".$data["block"].":".$data["meta"]." in their hands!", true, true, 2);
							break;
						case MC_REQUEST_CHUNK:
							console("[DEBUG] Chunk X ".$data["x"]." Z ".$data["z"]." requested", true, true, 2);						
							break;
						case MC_REMOVE_BLOCK:
							console("[DEBUG] EID ".$this->eid." broke block at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
							$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_ADD_ITEM_ENTITY,
									"eid" => $this->server->eidCnt++,
									"x" => $data["x"] + mt_rand(0, 100)/100,
									"y" => $data["y"],
									"z" => $data["z"] + mt_rand(0, 100)/100,
									"block" => 1,
									"meta" => 0,
									"stack" => 1,
								),
							));
							++$this->counter[0];
							break;
						case MC_RESPAWN:
							$this->server->trigger("onHealthChange", array("eid" => $this->eid, "health" => 20));
							$this->entity->setPosition($data["x"], $data["y"], $data["z"], $data["x"], 0, 0);
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