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
	var $clientID, $ip, $port, $counter, $username, $eid, $data, $entity, $auth, $CID, $MTU;
	function __construct($server, $clientID, $eid, $ip, $port, $MTU){
		$this->queue = array();
		$this->buffer = array();
		$this->MTU = $MTU;
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->eid = $eid;
		$this->data = array();
		$this->ip = $ip;
		$this->entity = false;
		$this->port = $port;
		$this->timeout = microtime(true) + 25;
		$this->evid = array();
		$this->evid[] = array("onTick", $this->server->event("onTick", array($this, "onTick")));
		$this->evid[] = array("onClose", $this->server->event("onClose", array($this, "close")));
		console("[DEBUG] New Session started with ".$ip.":".$port.". MTU ".$this->MTU.", Client ID ".$this->clientID, true, true, 2);
		$this->connected = true;
		$this->auth = false;
		$this->counter = array(0, 0);
	}
	
	public function onTick($time){
		if($time > $this->timeout){
			$this->close("timeout");
		}else{
			if(count($this->queue) > 0){
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
	}
	
	public function close($reason = "", $msg = true){
		$reason = $reason == "" ? "server stop":$reason;
		$this->save();
		foreach($this->evid as $ev){
			$this->server->deleteEvent($ev[0], $ev[1]);
		}
		$this->eventHandler("You have been kicked. Reason: ".$reason, "onChat");
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
			case "onDeath":
				if($data["eid"] === $this->eid){
					$this->server->trigger("onPlayerDeath", array("name" => $this->username, "cause" => $data["cause"]));
				}
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
					"x" => $entity->position["x"],
					"y" => $entity->position["y"],
					"z" => $entity->position["z"],
					"yaw" => $entity->position["yaw"],
					"pitch" => $entity->position["pitch"],
				));
				break;
			case "onHealthRegeneration":
				if($this->server->difficulty < 2){
					$this->server->trigger("onHealthChange", array("eid" => $this->eid, "health" => min(20, $this->data["health"] + $data), "cause" => "regeneration"));
				}
				break;
			case "onHealthChange":
				if($data["eid"] === $this->eid){
					$this->dataPacket(MC_SET_HEALTH, array(
						"health" => $data["health"],
					));
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
				$this->dataPacket(MC_ADD_PLAYER, array(
					"clientID" => $data["clientID"],
					"username" => $data["username"],
					"eid" => $data["eid"],
					"x" => $data["x"],
					"y" => $data["y"],
					"z" => $data["z"],
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
	
	public function handle($pid, &$data){
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
							foreach($this->server->clients as $c){
								if($c->eid !== $this->eid and $c->username === $this->username){
									$c->close("logged in from another location");
								}
							}
							if($this->server->whitelist !== false and !in_array($this->username, $this->server->whitelist)){
								$this->close("\"".$this->username."\" not being on white-list", false);
								break;
							}
							$this->server->api->player->add($this->CID);
							$this->auth = true;
							$this->data["lastIP"] = $this->ip;
							$this->data["lastID"] = $this->clientID;
							$this->evid[] = array("onTimeChange", $this->server->event("onTimeChange", array($this, "eventHandler")));
							$this->evid[] = array("onChat", $this->server->event("onChat", array($this, "eventHandler")));
							$this->evid[] = array("onDeath", $this->server->event("onDeath", array($this, "eventHandler")));
							$this->evid[] = array("onPlayerAdd", $this->server->event("onPlayerAdd", array($this, "eventHandler")));
							$this->evid[] = array("onEntityRemove", $this->server->event("onEntityRemove", array($this, "eventHandler")));
							$this->evid[] = array("onEntityMove", $this->server->event("onEntityMove", array($this, "eventHandler")));
							$this->evid[] = array("onHealthChange", $this->server->event("onHealthChange", array($this, "eventHandler")));
							$this->evid[] = array("onHealthRegeneration", $this->server->event("onHealthRegeneration", array($this, "eventHandler")));
							$this->evid[] = array("onAnimate", $this->server->event("onAnimate", array($this, "eventHandler")));
							$this->evid[] = array("onTeleport", $this->server->event("onTeleport", array($this, "eventHandler")));
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
								"eid" => $this->eid,
							));
							break;
						case MC_READY:
							if(is_object($this->entity)){
								break;
							}
							$this->server->trigger("onHealthChange", array("eid" => $this->eid, "health" => $this->data["health"], "cause" => "respawn"));
							console("[DEBUG] Player with EID ".$this->eid." \"".$this->username."\" spawned!", true, true, 2);
							$this->entity = new Entity($this->eid, ENTITY_PLAYER, 0, $this->server);
							$this->entity->setName($this->username);
							$this->entity->setHealth($this->data["health"]);
							$this->entity->data["clientID"] = $this->clientID;
							$this->server->entities[$this->eid] = &$this->entity;
							$this->server->trigger("onPlayerAdd", array(
								"clientID" => $this->clientID,
								"username" => $this->username,
								"eid" => $this->eid,
								"x" => $this->data["spawn"]["x"],
								"y" => $this->data["spawn"]["y"],
								"z" => $this->data["spawn"]["z"],
							));
							foreach($this->server->entities as $entity){
								if($entity->eid !== $this->eid){
									if($entity->class === ENTITY_PLAYER){
										$this->eventHandler(array(
											"clientID" => $entity->data["clientID"],
											"username" => $entity->name,
											"eid" => $entity->eid,
											"x" => $entity->position["x"],
											"y" => $entity->position["y"],
											"z" => $entity->position["z"],
										), "onPlayerAdd");
									}else{
										$this->dataPacket(MC_ADD_MOB, array(
											"eid" => $entity->eid,
											"type" => $entity->type,
											"x" => $entity->position["x"],
											"y" => $entity->position["y"],
											"z" => $entity->position["z"],
										));
									}
								}							
							}
							$this->eventHandler($this->server->motd, "onChat");
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
							console("[DEBUG] Chunk X ".$data["x"]." Z ".$data["z"]." requested", true, true, 2);
							break;
						case MC_REMOVE_BLOCK:
							console("[DEBUG] EID ".$this->eid." broke block at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
							$this->dataPacket(MC_ADD_ITEM_ENTITY, array(
								"eid" => $this->server->eidCnt++,
								"x" => $data["x"] + mt_rand(0, 100)/100,
								"y" => $data["y"],
								"z" => $data["z"] + mt_rand(0, 100)/100,
								"block" => 1,
								"meta" => 0,
								"stack" => 1,
							));
							/*$this->send(0x84, array(
								$this->counter[0],
								0x00,
								array(
									"id" => MC_UPDATE_BLOCK,
									"x" => $data["x"],
									"y" => $data["y"],
									"z" => $data["z"],
									"block" => 56,
									"meta" => 0,
								),
							));
							++$this->counter[0];*/
							break;
						case MC_INTERACT:
							if($this->server->gamemode !== 1 and $this->server->difficulty > 0 and isset($this->server->entities[$data["target"]]) and Utils::distance($this->entity->position, $this->server->entities[$data["target"]]->position) <= 8){
								console("[DEBUG] EID ".$this->eid." attacked EID ".$data["target"], true, true, 2);
								$this->server->trigger("onHealthChange", array("eid" => $data["target"], "health" => $this->server->entities[$data["target"]]->getHealth() - $this->server->difficulty, "cause" => $this->eid));
							}
							break;
						case MC_ANIMATE:
							$this->server->trigger("onAnimate", array("eid" => $this->eid, "action" => $data["action"]));
							break;
						case MC_RESPAWN:
							$this->server->trigger("onHealthChange", array("eid" => $this->eid, "health" => 20, "cause" => "respawn"));
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
			$this->send(0x84, array(
				$count,
				0x00,
				$data,
			));
		}
	}

}