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


class Player{
	private $server;
	private $queue = array();
	private $buffer = array();
	private $evid = array();
	var $timeout;
	var $connected = true;
	var $clientID;
	var $ip;
	var $port;
	var $counter = array(0, 0, 0);
	var $username;
	var $eid = false;
	var $data = array();
	var $entity = false;
	var $auth = false;
	var $CID;
	var $MTU;
	var $spawned = false;
	var $inventory;
	var $equipment = array(1, 0);
	var $loggedIn = false;
	function __construct(PocketMinecraftServer $server, $clientID, $ip, $port, $MTU){
		$this->MTU = $MTU;
		$this->server = $server;
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->ip = $ip;
		$this->port = $port;
		$this->timeout = microtime(true) + 25;
		$this->inventory = array_fill(0, 36, array(0, 0, 0));
		$this->evid[] = $this->server->event("server.tick", array($this, "onTick"));
		$this->evid[] = $this->server->event("server.close", array($this, "close"));
		console("[DEBUG] New Session started with ".$ip.":".$port.". MTU ".$this->MTU.", Client ID ".$this->clientID, true, true, 2);
	}

	public function onTick($time, $event){
		if($event !== "server.tick"){ //WTF??
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
		if($this->connected === true){
			foreach($this->evid as $ev){
				$this->server->deleteEvent($ev);
			}
			$this->server->api->dhandle("player.quit", $this);
			$reason = $reason == "" ? "server stop":$reason;
			$this->save();
			$this->eventHandler(new Container("You have been kicked. Reason: ".$reason), "server.chat");
			$this->dataPacket(MC_LOGIN_STATUS, array(
				"status" => 1,
			));
			$this->dataPacket(MC_DISCONNECT);
			$this->buffer = null;
			unset($this->buffer);
			$this->queue = null;
			unset($this->queue);
			$this->connected = false;
			if($msg === true){
				$this->server->api->chat->broadcast($this->username." left the game");
			}
			console("[INFO] Session with \x1b[36m".$this->ip.":".$this->port."\x1b[0m Client ID ".$this->clientID." closed due to ".$reason);
			$this->server->api->player->remove($this->CID);
		}
	}

	public function addItem($type, $damage, $count){
		while($count > 0){
			$add = 0;
			foreach($this->inventory as $s => $data){
				if($data[0] === 0){
					$add = min(64, $count);
					$this->inventory[$s] = array($type, $damage, $add);
					break;
				}elseif($data[0] === $type and $data[1] === $damage){
					$add = min(64 - $data[2], $count);
					if($add <= 0){
						continue;
					}
					$this->inventory[$s] = array($type, $damage, $data[2] + $add);
					break;
				}
			}
			if($add === 0){
				return false;
			}
			$count -= $add;
		}
		return true;
	}

	public function removeItem($type, $damage, $count){
		while($count > 0){
			$remove = 0;
			foreach($this->inventory as $s => $data){
				if($data[0] === $type and $data[1] === $damage){
					$remove = min($count, $data[2]);
					if($remove < $data[2]){
						$this->inventory[$s][2] -= $remove;
					}else{
						$this->inventory[$s] = array(0, 0, 0);
					}
					break;
				}
			}
			if($remove === 0){
				return false;
			}
			$count -= $remove;
		}
		return true;
	}
	
	public function hasItem($type, $damage = false){
		foreach($this->inventory as $s => $data){
			if($data[0] === $type and ($data[1] === $damage or $damage === false) and $data[2] > 0){
				return true;
			}
		}
		return false;
	}
	
	public function eventHandler($data, $event){
		switch($event){
			case "player.block.place":
				if($data["eid"] === $this->eid and $this->server->gamemode === 0){
					$this->removeItem($data["original"][0], $data["original"][1], 1);
				}
				break;
			case "player.pickup":
				if($data["eid"] === $this->eid){
					$data["eid"] = 0;
					if($this->server->gamemode === 0){
						$this->addItem($data["entity"]->type, $data["entity"]->meta, $data["entity"]->stack);
					}
				}
				$this->dataPacket(MC_TAKE_ITEM_ENTITY, $data);
				break;
			case "player.equipment.change":
				if($data["eid"] === $this->eid){
					break;
				}
				$this->dataPacket(MC_PLAYER_EQUIPMENT, $data);
				break;
			case "block.change":
				$this->dataPacket(MC_UPDATE_BLOCK, $data);
				break;
			case "entity.move":
				if($data->eid === $this->eid){
					break;
				}
				$this->dataPacket(MC_MOVE_ENTITY_POSROT, array(
					"eid" => $data->eid,
					"x" => $data->x,
					"y" => $data->y,
					"z" => $data->z,
					"yaw" => $data->yaw,
					"pitch" => $data->pitch,
				));
				break;
			case "entity.remove":
				if($data->eid === $this->eid){
					break;
				}
				$this->dataPacket(MC_REMOVE_ENTITY, array(
					"eid" => $data->eid,
				));
				break;
			case "server.time":
				$this->dataPacket(MC_SET_TIME, array(
					"time" => $data,
				));
				break;
			case "entity.animate":
				if($data["eid"] === $this->eid){
					break;
				}
				$this->dataPacket(MC_ANIMATE, array(
					"eid" => $data["eid"],
					"action" => $data["action"],
				));
				break;
			case "entity.metadata":
				if($data->eid === $this->eid){
					$eid = 0;
				}else{
					$eid = $data->eid;
				}
				$this->dataPacket(MC_SET_ENTITY_DATA, array(
					"eid" => $eid,
					"metadata" => $data->getMetadata(),
				));
				break;
			case "entity.event":
				if($data["entity"]->eid === $this->eid){
					$eid = 0;
				}else{
					$eid = $data["entity"]->eid;
				}
				$this->dataPacket(MC_ENTITY_EVENT, array(
					"eid" => $eid,
					"event" => $data["event"],
				));
				break;
			case "server.chat":
				if(($data instanceof Container) === true){
					if(!$data->check($this->username)){
						return;
					}else{
						$message = $data->get();
					}
				}else{
					$message = (string) $data;
				}
				$this->dataPacket(MC_CHAT, array(
					"message" => str_replace("@username", $this->username, $message),
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
					$diff = $data[2] - $this->counter[2];
					if($diff > 8){ //Packet recovery
						array_unshift($this->queue, array(0, $this->buffer[$data[2]][0], $this->buffer[$data[2]][1], $data[2]));
					}
					$this->counter[2] = $data[2];
					$this->buffer[$data[2]] = null;
					unset($this->buffer[$data[2]]);

					if(isset($data[3])){
						$diff = $data[3] - $this->counter[2];
						if($diff > 8){ //Packet recovery
							array_unshift($this->queue, array(0, $this->buffer[$data[3]][0], $this->buffer[$data[3]][1], $data[3]));
						}
						$this->counter[2] = $data[3];
						$this->buffer[$data[3]] = null;
						unset($this->buffer[$data[3]]);
					}
					break;
				case 0x07:
					if($this->loggedIn === true){
						break;
					}
					$this->send(0x08, array(
						MAGIC,
						$this->server->serverID,
						$this->port,
						$data[3],
						0,
					));
					break;
				case 0x80:
				case 0x81:
				case 0x82:
				case 0x83:
				case 0x84:
				case 0x85:
				case 0x86:
				case 0x87:
				case 0x88:
				case 0x89:
				case 0x8a:
				case 0x8b:
				case 0x8c:
				case 0x8d:
				case 0x8e:
				case 0x8f:
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

						case MC_KEEP_ALIVE:

							break;
						case 0x03:
						case 0xa9:

							break;
						case MC_DISCONNECT:
							$this->close("client disconnect");
							break;
						case MC_CLIENT_CONNECT:
							if($this->loggedIn === true){
								break;
							}
							$this->dataPacket(MC_SERVER_HANDSHAKE, array(
								"port" => $this->port,
								"session" => $data["session"],
								"session2" => Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9"),
							));
							break;
						case MC_CLIENT_HANDSHAKE:
							if($this->loggedIn === true){
								break;
							}
							break;
						case MC_LOGIN:
							if($this->loggedIn === true){
								break;
							}
							$this->loggedIn = true;
							$this->username = str_replace(array("\x00", "/", " ", "\r", "\n", '"', "'"), array("", "-", "_", "", "", "", ""), $data["username"]);
							if($this->username == ""){
								$this->close("bad username", false);
								break;
							}
							$o = $this->server->api->player->getOffline($this->username);
							if($this->server->whitelist === true and !$this->server->api->ban->inWhitelist($this->username)){
								$this->close("\"\x1b[33m".$this->username."\x1b[0m\" not being on white-list", false);
								break;
							}elseif($this->server->api->ban->isBanned($this->username) or $this->server->api->ban->isIPBanned($this->ip)){
								$this->close("\"\x1b[33m".$this->username."\x1b[0m\" is banned!", false);
							}
							$u = $this->server->api->player->get($this->username);
							$c = $this->server->api->player->getByClientID($this->clientID);
							if($u !== false){
								$u->close("logged in from another location");
							}
							if($c !== false){
								$c->close("logged in from another location");
							}
							if($this->server->api->dhandle("player.join", $this) === false){
								$this->close();
								return;
							}
							$this->server->api->player->add($this->CID);
							$this->auth = true;
							if(!isset($this->data["inventory"]) or $this->server->gamemode === 1){
								$this->data["inventory"] = $this->inventory;
							}
							$this->inventory = &$this->data["inventory"];
							
							$this->data["lastIP"] = $this->ip;
							$this->data["lastID"] = $this->clientID;
							$this->server->api->player->saveOffline($this->username, $this->data);
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
							switch($data["status"]){
								case 1: //Spawn!!
									if($this->spawned !== false){
										break;
									}
									$this->spawned = true;
									$this->entity = $this->server->api->entity->add(ENTITY_PLAYER, 0, array("player" => $this));
									$this->eid = $this->entity->eid;
									$this->server->query("UPDATE players SET EID = ".$this->eid." WHERE clientID = ".$this->clientID.";");
									$this->entity->x = $this->data["spawn"]["x"];
									$this->entity->y = $this->data["spawn"]["y"];
									$this->entity->z = $this->data["spawn"]["z"];
									$this->entity->setName($this->username);
									$this->entity->data["clientID"] = $this->clientID;
									$this->server->api->entity->spawnAll($this);
									$this->server->api->entity->spawnToAll($this->eid);
									$this->evid[] = $this->server->event("server.time", array($this, "eventHandler"));  
									$this->evid[] = $this->server->event("server.chat", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("entity.remove", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("entity.move", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("entity.animate", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("entity.event", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("entity.metadata", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("player.equipment.change", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("player.pickup", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("block.change", array($this, "eventHandler"));
									$this->evid[] = $this->server->event("player.block.place", array($this, "eventHandler"));
									console("[DEBUG] Player \"".$this->username."\" EID ".$this->eid." spawned at X ".$this->entity->x." Y ".$this->entity->y." Z ".$this->entity->z, true, true, 2);
									$this->eventHandler(new Container($this->server->motd), "server.chat");
									if($this->MTU <= 548){
										$this->eventHandler("Your connection is bad, you may experience lag and slow map loading.", "server.chat");
									}
									foreach($this->inventory as $s => $data){
										if($data[0] > 0 and $data[2] >= 0){
											$e = $this->server->api->entity->add(ENTITY_ITEM, $data[0], array(
												"x" => $this->entity->x + 0.5,
												"y" => $this->entity->y + 0.19,
												"z" => $this->entity->z + 0.5,
												"meta" => $data[1],
												"stack" => $data[2],
											));
											$this->server->api->entity->spawnTo($e->eid, $this);
										}
										$this->inventory[$s] = array(0, 0, 0);
									}
									break;
								case 2://Chunk loaded?
									break;
							}
							break;
						case MC_MOVE_PLAYER:
							if(is_object($this->entity)){
								$this->entity->setPosition($data["x"], $data["y"], $data["z"], $data["yaw"], $data["pitch"]);
								$this->server->api->dhandle("player.move", $this->entity);
							}
							break;
						case MC_PLAYER_EQUIPMENT:
							$data["eid"] = $this->eid;
							if($this->server->handle("player.equipment.change", $data) !== false){
								$this->equipment[0] = $data["block"];
								$this->equipment[1] = $data["meta"];
								console("[DEBUG] EID ".$this->eid." has now ".$data["block"].":".$data["meta"]." in their hands!", true, true, 2);
							}
							break;
						case MC_REQUEST_CHUNK:
							$x = $data["x"] * 16;
							$z = $data["z"] * 16;
							$this->actionQueue('
							$max = max(1, floor(($this->MTU - 16 - 255) / 192));
							$chunk = $this->server->api->level->getOrderedChunk('.$data["x"].', '.$data["z"].', $max);
							foreach($chunk as $d){
								$this->dataPacket(MC_CHUNK_DATA, array(
									"x" => '.$data["x"].',
									"z" => '.$data["z"].',
									"data" => $d,
								), true);
							}
							$tiles = $this->server->query("SELECT * FROM tileentities WHERE spawnable = 1 AND x >= '.$x.' AND x < '.($x + 16).' AND z >= '.$z.' AND z < '.($z + 16).';");
							if($tiles !== false and $tiles !== true){
								while(($tile = $tiles->fetchArray(SQLITE3_ASSOC)) !== false){
									$this->server->api->tileentity->spawnTo($tile["ID"], "'.$this->username.'");
								}
							}
							');
							console("[INTERNAL] Chunk X ".$data["x"]." Z ".$data["z"]." requested", true, true, 3);
							break;
						case MC_USE_ITEM:
							$data["eid"] = $this->eid;
							if(Utils::distance($this->entity->position, $data) > 10){
								break;
							}elseif($this->server->gamemode === 0 and !$this->hasItem($data["block"], $data["meta"])){
								console("[DEBUG] Player \"".$this->username."\" tried to place not got block (or crafted block)", true, true, 2);
								//break;
							}
							$this->server->handle("player.block.action", $data);
							break;
						case MC_REMOVE_BLOCK:
							$data["eid"] = $this->eid;
							if(Utils::distance($this->entity->position, $data) > 8){
								break;
							}
							$this->server->handle("player.block.break", $data);
							break;
						case MC_INTERACT:
							if(isset($this->server->entities[$data["target"]]) and Utils::distance($this->entity->position, $this->server->entities[$data["target"]]->position) <= 8){
								if($this->handle("player.interact", $data) !== false){
									console("[DEBUG] EID ".$this->eid." attacked EID ".$data["target"], true, true, 2);
									if($this->server->gamemode !== 1 and $this->server->difficulty > 0){
										$this->server->api->entity->harm($data["target"], $this->server->difficulty, $this->eid);
									}
								}
							}
							break;
						case MC_ANIMATE:
							$this->server->api->dhandle("entity.animate", array("eid" => $this->eid, "action" => $data["action"]));
							break;
						case MC_RESPAWN:
							if($this->entity->dead === false){
								break;
							}
							$this->entity->fire = 0;
							$this->entity->air = 300;
							$this->entity->setPosition($data["x"], $data["y"], $data["z"], 0, 0);
							$this->entity->setHealth(20, "respawn");
							$this->entity->updateMetadata();
							break;
						case MC_SET_HEALTH:
							if($this->server->gamemode === 1){
								break;
							}
							//$this->entity->setHealth($data["health"], "client");
							break;
						case MC_DROP_ITEM:
							if($this->server->handle("player.drop", $data) !== false){
								$this->server->api->block->drop($this->entity->x, $this->entity->y, $this->entity->z, $data["block"], $data["meta"], $data["stack"]);
							}
							break;
						default:
							console("[DEBUG] Unhandled 0x".dechex($data["id"])." Data Packet for Client ID ".$this->clientID.": ".print_r($data, true), true, true, 2);
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