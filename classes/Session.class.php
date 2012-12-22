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
	private $server, $timeout, $connected, $evid, $queue, $buffer;
	var $clientID, $ip, $port, $counter, $username, $eid, $data, $entity, $auth, $CID, $MTU, $spawned;
	function __construct($server, $clientID, $ip, $port, $MTU){
		$this->queue = array();
		$this->buffer = array();
		$this->MTU = $MTU;
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->eid = false;
		$this->data = array();
		$this->ip = $ip;
		$this->entity = false;
		$this->port = $port;
		$this->timeout = microtime(true) + 25;
		$this->evid = array();
		$this->spawned = false;
		$this->evid[] = $this->server->event("onTick", array($this, "onTick"));
		$this->evid[] = $this->server->event("onClose", array($this, "close"));
		console("[DEBUG] New Session started with ".$ip.":".$port.". MTU ".$this->MTU.", Client ID ".$this->clientID, true, true, 2);
		$this->connected = true;
		$this->auth = false;
		$this->counter = array(0, 0);
	}
	
	public function onTick($time, $event){
		if($event !== "onTick"){
			return;
		}
		if($time > $this->timeout){
			$this->close("timeout");
		}else{
			if(!empty($this->queue)){
				$cnt = 0;
				while($cnt < 4){
					$p = array_shift($this->queue);
					if($p === null){
						break;
					}
					switch($p[0]){
						case 0:
							$this->dataPacket($p[1], $p[2], false, $p[3]);
							break;
						case 1:
							eval($p[1]);
							break;
					}
					++$cnt;
				}
			}
		}
	}
	
	public function save(){
		if(is_object($this->entity)){
			$this->data["spawn"] = array(
				"x" => $this->entity->x,
				"y" => $this->entity->y,
				"z" => $this->entity->z,
			);
		}
	}
	
	public function close($reason = "", $msg = true){
		$reason = $reason == "" ? "server stop":$reason;
		$this->save();
		foreach($this->evid as $ev){
			$this->server->deleteEvent($ev);
		}
		$this->eventHandler("You have been kicked. Reason: ".$reason, "onChat");
		$this->dataPacket(MC_LOGIN_STATUS, array(
			"status" => 1,
		));
		$this->dataPacket(MC_DISCONNECT);

		$this->connected = false;
		if($msg === true){
			$this->server->trigger("onChat", $this->username." left the game");
		}
		console("[INFO] Session with ".$this->ip.":".$this->port." Client ID ".$this->clientID." closed due to ".$reason);
		$this->server->api->player->remove($this->CID);
	}
	
	public function eventHandler($data, $event){		
		switch($event){
			case "onBlockUpdate":
				$this->dataPacket(MC_UPDATE_BLOCK, $data);
				break;
			case "onTeleport":
				if($data["eid"] !== $this->eid){
					break;
				}
				$this->dataPacket(MC_MOVE_PLAYER, array(
					"eid" => $data["eid"],
					"x" => $data["x"],
					"y" => $data["y"],
					"z" => $data["z"],
					"yaw" => 0,
					"pitch" => 0,
				));
				break;
			case "onEntityMove":
				if($data === $this->eid){
					break;
				}
				$entity = $this->server->entities[$data];
				$this->dataPacket(MC_MOVE_ENTITY_POSROT, array(
					"eid" => $data,
					"x" => $entity->x,
					"y" => $entity->y,
					"z" => $entity->z,
					"yaw" => $entity->yaw,
					"pitch" => $entity->pitch,
				));
				break;
			case "onEntityRemove":
				if($data === $this->eid){
					break;
				}
				$this->dataPacket(MC_ENTITY_REMOVE, array(
					"eid" => $data,
				));
				break;
			case "onTimeChange":
				$this->dataPacket(MC_SET_TIME, array(
					"time" => $data,
				));
				break;
			case "onAnimate":
				if($data["eid"] === $this->eid){
					break;
				}
				$this->dataPacket(MC_ANIMATE, array(
						"eid" => $data["eid"],
						"action" => $data["action"],
					));
				break;
			case "onChat":
				$this->dataPacket(MC_CHAT, array(
					"message" => $data,
				));				
				break;
		}
	}
	
	public function handle($pid, $data){
		if($this->connected === true){
			$this->timeout = microtime(true) + 25;
			switch($pid){
				case 0xa0: //NACK
					if(isset($this->buffer[$data[2]])){
						array_unshift($this->queue, array(0, $this->buffer[$data[2]][0], $this->buffer[$data[2]][1], $data[2]));
					}
					if(isset($data[3])){
						if(isset($this->buffer[$data[3]])){
							array_unshift($this->queue, array(0, $this->buffer[$data[3]][0], $this->buffer[$data[3]][1], $data[3]));
						}
					}
					break;
				case 0xc0: //ACK
					unset($this->buffer[$data[2]]);
					if(isset($data[3])){
						unset($this->buffer[$data[3]]);
					}
					break;
				case 0x07:
					$this->send(0x08, array(
						MAGIC,
						$this->server->serverID,
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
						$diff = $data[0] - $this->counter[1];
						if($diff > 1){ //Packet recovery
							for($i = $this->counter[1]; $i < $data[0]; ++$i){
								$this->send(0xa0, array(1, true, $i));
							}
							$this->counter[1] = $data[0];
						}elseif($diff === 1){
							$this->counter[1] = $data[0];
						}
						$this->send(0xc0, array(1, true, $data[0]));
					}
					switch($data["id"]){
						case MC_DISCONNECT:
							$this->connected = false;
							$this->close("client disconnect");
							break;
						case MC_CLIENT_CONNECT:
							$this->dataPacket(MC_SERVER_HANDSHAKE, array(
								"port" => $this->port,
								"session" => $data["session"],
								"session2" => Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9"),
							));
							break;
						case MC_CLIENT_HANDSHAKE:
						
							break;
						case MC_LOGIN:
							$this->username = str_replace("/", "", $data["username"]);
							$u = $this->server->api->player->get($this->username);
							$c = $this->server->api->player->getByClientID($this->clientID);
							if($u !== false){
								$u->close("logged in from another location");
							}
							if($c !== false){
								$c->close("logged in from another location");
							}
							if($this->server->whitelist !== false and !in_array($this->username, $this->server->whitelist)){
								$this->close("\"".$this->username."\" not being on white-list", false);
								break;
							}
							$this->server->api->player->add($this->CID);
							$this->auth = true;
							$this->data["lastIP"] = $this->ip;
							$this->data["lastID"] = $this->clientID;
							$this->dataPacket(MC_LOGIN_STATUS, array(
								"status" => 0,
							));
							$this->dataPacket(MC_START_GAME, array(
								"seed" => $this->server->seed,
								"x" => $this->data["spawn"]["x"],
								"y" => $this->data["spawn"]["y"],
								"z" => $this->data["spawn"]["z"],
								"unknown1" => 0,
								"gamemode" => $this->server->gamemode,
								"eid" => 0,
							));
							break;
						case MC_READY:
							if($this->spawned !== false){
								break;
							}
							$this->spawned = true;
							$this->entity = $this->server->api->entity->add(ENTITY_PLAYER, 0, array("player" => $this));
							$this->eid = $this->entity->eid;
							$this->server->query("UPDATE players SET EID = ".$this->eid." WHERE clientID = ".$this->clientID.";");
							$this->entity->setName($this->username);
							$this->entity->data["clientID"] = $this->clientID;
							$this->server->api->entity->spawnAll($this);
							$this->server->api->entity->spawnToAll($this->eid);
							$this->evid[] = $this->server->event("onTimeChange", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onChat", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onEntityRemove", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onEntityMove", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onAnimate", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onTeleport", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("onBlockUpdate", array($this, "eventHandler"));
							console("[DEBUG] Player with EID ".$this->eid." \"".$this->username."\" spawned!", true, true, 2);
							
							$this->eventHandler($this->server->motd, "onChat");
							if($this->MTU <= 548){
								$this->eventHandler("Your connection is bad, you may experience lag and slow map loading.", "onChat");
							}
							break;
						case MC_MOVE_PLAYER:
							if(is_object($this->entity)){
								$this->entity->setPosition($data["x"], $data["y"], $data["z"], $data["yaw"], $data["pitch"]);
								$this->server->trigger("onEntityMove", $this->eid);
							}
							break;
						case MC_PLAYER_EQUIPMENT:
							console("[DEBUG] EID ".$this->eid." has now ".$data["block"].":".$data["meta"]." in their hands!", true, true, 2);
							break;
						case MC_REQUEST_CHUNK:
							$this->actionQueue('
							$max = floor(($this->MTU - 16 - 255) / 192);
							$chunk = $this->server->api->level->getOrderedChunk('.$data["x"].', '.$data["z"].', $max);
							foreach($chunk as $d){
								$this->dataPacket(MC_CHUNK_DATA, array(
									"x" => '.$data["x"].',
									"z" => '.$data["z"].',
									"data" => $d,								
								), true);
							}
							');
							console("[INTERNAL] Chunk X ".$data["x"]." Z ".$data["z"]." requested", true, true, 3);
							break;
						case MC_USE_ITEM:
							if($data["face"] >= 0 and $data["face"] <= 5 and $data["block"] !== 0){
								switch($data["face"]){
									case 0:
										--$data["y"];
										break;
									case 1:
										++$data["y"];
										break;
									case 2:
										--$data["z"];
										break;
									case 3:
										++$data["z"];
										break;
									case 4:
										--$data["x"];
										break;
									case 5:
										++$data["x"];
										break;
								}
								if($data["block"] === 65){
									$data["block"] = 63;
								}
								$data["eid"] = $this->eid;
								$this->server->handle("onBlockPlace", $data);
								if($data["block"] === 63){
									$data["line0"] = "WHOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
									$data["line1"] = "this is a test";
									$data["line2"] = "I'm ".$this->username;
									$data["line3"] = "TPS: ".$this->server->getTPS();
									$this->dataPacket(MC_SIGN_UPDATE, $data);
								}
							}
							break;
						case MC_PLACE_BLOCK:
							var_dump($data);
							break;
						case MC_REMOVE_BLOCK:
							$data["eid"] = $this->eid;
							$this->server->handle("onBlockBreak", $data);
							break;
						case MC_INTERACT:
							if(isset($this->server->entities[$data["target"]]) and Utils::distance($this->entity->position, $this->server->entities[$data["target"]]->position) <= 8){
								console("[DEBUG] EID ".$this->eid." attacked EID ".$data["target"], true, true, 2);
								if($this->server->gamemode !== 1 and $this->server->difficulty > 0){								
									$this->server->api->entity->harm($data["target"], $this->server->difficulty, $this->eid);
								}
							}
							break;
						case MC_ANIMATE:
							$this->server->trigger("onAnimate", array("eid" => $this->eid, "action" => $data["action"]));
							break;
						case MC_RESPAWN:
							$this->entity->setHealth(20, "respawn");
							$this->entity->setPosition($data["x"], $data["y"], $data["z"], 0, 0);
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
	
	public function actionQueue($code){
		$this->queue[] = array(1, $code);
	}
	
	public function dataPacket($id, $data = array(), $queue = false, $count = false){
		if($queue === true){
			$this->queue[] = array(0, $id, $data, $count);
		}else{
			if($count === false){
				$count = $this->counter[0];
				++$this->counter[0];
				if(count($this->buffer) >= 512){
					array_shift($this->buffer);
				}
				$this->buffer[$count] = array($id, $data);
			}
			$data["id"] = $id;
			$this->send(0x80, array(
				$count,
				0x00,
				$data,
			));
		}
	}

}