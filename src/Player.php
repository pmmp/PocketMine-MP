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
	private $buffer = "";
	private $nextBuffer = 0;
	private $recovery = array();
	private $evid = array();
	private $lastMovement = 0;
	private $timeout;
	private $connected = true;
	private $clientID;
	private $ip;
	private $port;
	private $counter = array(0, 0, 0, 0);
	private $username;
	private $iusername;
	private $eid = false;
	public $data;
	public $entity = false;
	public $auth = false;
	public $CID;
	public $MTU;
	public $spawned = false;
	public $inventory;
	public $equipment;
	public $armor;
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;
	public $windowCnt = 0;
	public $windows = array();
	public $blocked = true;
	public $chunksLoaded = array();
	private $chunksOrder = array();
	private $lag = array(0, 0);
	private $spawnPosition;
	private $freedChunks = true;
	public $itemEnforcement;
	public $lastCorrect;
	private $bigCnt;
	
	public function __get($name){
		if(isset($this->{$name})){
			return ($this->{$name});
		}
		return null;
	}
	
	public function __construct($clientID, $ip, $port, $MTU){
		$this->bigCnt = 0;
		$this->MTU = $MTU;
		$this->server = ServerAPI::request();
		$this->lastBreak = microtime(true);
		$this->clientID = $clientID;
		$this->CID = $this->server->clientID($ip, $port);
		$this->ip = $ip;
		$this->port = $port;
		$this->itemEnforcement = $this->server->api->getProperty("item-enforcement");
		$this->spawnPosition = $this->server->spawn;
		$this->timeout = microtime(true) + 20;
		$this->inventory = array_fill(0, 36, array(AIR, 0, 0));
		$this->armor = array_fill(0, 4, array(AIR, 0, 0));
		$this->gamemode = $this->server->gamemode;
		$this->level = $this->server->api->level->getDefault();
		$this->equipment = BlockAPI::getItem(AIR);
		$this->evid[] = $this->server->event("server.tick", array($this, "onTick"));
		$this->evid[] = $this->server->event("server.close", array($this, "close"));
		console("[DEBUG] New Session started with ".$ip.":".$port.". MTU ".$this->MTU.", Client ID ".$this->clientID, true, true, 2);
	}
	
	public function getSpawn(){
		return $this->spawnPosition;
	}
	
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Level)){
			$level = $this->level;
		}else{
			$level = $pos->level;
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$this->dataPacket(MC_SET_SPAWN_POSITION, array(
			"x" => (int) $this->spawnPosition->x,
			"y" => (int) $this->spawnPosition->y,
			"z" => (int) $this->spawnPosition->z,
		));
	}
	
	public function orderChunks(){
		if(!($this->entity instanceof Entity) or $this->connected === false){
			return false;
		}
		$X = $this->entity->x / 16;
		$Z = $this->entity->z / 16;
		$Y = $this->entity->y / 16;
		$v = new Vector3($X, $Y / 4, $Z);
		$this->chunksOrder = array();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				for($y = 0; $y < 8; ++$y){
					$d = $x.":".$y.":".$z;
					if(!isset($this->chunksLoaded[$d])){
						$this->chunksOrder[$d] = $v->distance(new Vector3($x + 0.5, $y / 4, $z + 0.5));
					}
				}
			}
		}
		asort($this->chunksOrder);
	}
	
	public function getNextChunk($repeat = false){
		if($this->connected === false){
			return false;
		}
		$c = key($this->chunksOrder);
		$d = @$this->chunksOrder[$c];
		if($c === null or $d > $this->server->api->getProperty("view-distance")){
			if($this->freedChunks === false){
				foreach($this->chunksOrder as $c => $d){
					$id = explode(":", $c);
					$X = $id[0];
					$Z = $id[2];
					$this->level->freeChunk($X, $Z, $this);
				}
				$this->freedChunks = true;
			}
			$this->server->schedule(50, array($this, "getNextChunk"));
			return false;
		}
		$this->freedChunks = false;
		unset($this->chunksOrder[$c]);
		$this->chunksLoaded[$c] = true;
		$id = explode(":", $c);
		$X = $id[0];
		$Z = $id[2];
		$Y = $id[1];
		$x = $X << 4;
		$z = $Z << 4;
		$y = $Y << 4;
		$this->level->useChunk($X, $Z, $this);
		$this->dataPacket(MC_CHUNK_DATA, array(
			"x" => $X,
			"z" => $Z,
			"data" => $this->level->getOrderedMiniChunk($X, $Z, $Y),
		));
		
		$tiles = $this->server->query("SELECT ID FROM tileentities WHERE spawnable = 1 AND level = '".$this->level->getName()."' AND x >= ".($x - 1)." AND x < ".($x + 17)." AND z >= ".($z - 1)." AND z < ".($z + 17)." AND y >= ".($y - 1)." AND y < ".($y + 17).";");
		if($tiles !== false and $tiles !== true){
			while(($tile = $tiles->fetchArray(SQLITE3_ASSOC)) !== false){
				$this->server->api->tileentity->spawnTo($tile["ID"], $this);
			}
		}
		if($repeat === false){
			$this->getNextChunk(true);
		}
		
		$this->server->schedule(1, array($this, "getNextChunk"));
	}

	public function onTick($time, $event){
		if($event !== "server.tick" or $this->connected === false){
			return;
		}
		if($time > $this->timeout){
			$this->close("timeout");
		}else{
			if(!empty($this->queue)){
				$maxtime = $time + 0.0025;
				while(microtime(true) < $maxtime and ($p = each($this->queue)) !== false){
					unset($this->queue[$p[0]]);
					switch($p[1][0]){
						case 0:
							$this->dataPacket($p[1][1]["id"], $p[1][1], false);
							break;
						case 1:
							eval($p[1][1]);
							break;
					}
				}
			}

			if($this->nextBuffer <= $time and strlen($this->buffer) > 0){
				$this->sendBuffer();
			}
		}
	}

	public function save(){
		if($this->entity instanceof Entity){
			$this->data->set("position", array(
				"level" => $this->entity->level->getName(),
				"x" => $this->entity->x,
				"y" => $this->entity->y,
				"z" => $this->entity->z,
			));
			$this->data->set("spawn", array(
				"level" => $this->spawnPosition->level->getName(),
				"x" => $this->spawnPosition->x,
				"y" => $this->spawnPosition->y,
				"z" => $this->spawnPosition->z,
			));
			$this->data->set("inventory", $this->inventory);
			$this->data->set("armor", $this->armor);
			$this->data->set("gamemode", $this->gamemode);
		}
	}

	public function close($reason = "", $msg = true){
		if($this->connected === true){
			foreach($this->evid as $ev){
				$this->server->deleteEvent($ev);
			}
			if($this->username != ""){
				$this->server->api->handle("player.quit", $this);
				$this->save();
			}
			$reason = $reason == "" ? "server stop":$reason;			
			$this->eventHandler(new Container("You have been kicked. Reason: ".$reason), "server.chat");
			$this->directDataPacket(MC_DISCONNECT);
			$this->sendBuffer();
			$this->level->freeAllChunks($this);
			$this->buffer = null;
			unset($this->buffer);
			$this->recovery = null;
			unset($this->recovery);
			$this->queue = null;
			unset($this->queue);
			$this->connected = false;
			if($msg === true and $this->username != ""){
				$this->server->api->chat->broadcast($this->username." left the game");
			}
			console("[INFO] \x1b[33m".$this->username."\x1b[0m[/".$this->ip.":".$this->port."] logged out due to ".$reason);
			$this->server->api->player->remove($this->CID);
		}
	}

	public function addItem($type, $damage, $count){
		while($count > 0){
			$add = 0;
			foreach($this->inventory as $s => $data){
				if($data[0] === AIR){
					$add = min(64, $count);
					$this->inventory[$s] = array($type, $damage, $add);
					$this->sendInventorySlot($s);
					break;
				}elseif($data[0] === $type and $data[1] === $damage){
					$add = min(64 - $data[2], $count);
					if($add <= 0){
						continue;
					}
					$this->inventory[$s] = array($type, $damage, $data[2] + $add);
					$this->sendInventorySlot($s);
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
						$this->inventory[$s] = array(AIR, 0, 0);
					}
					$this->sendInventorySlot($s);
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
	
	public function setSlot($slot, Item $item){
		$this->inventory[(int) $slot] = array($item->getID(), $item->getMetadata(), $item->count);
		$this->sendInventorySlot((int) $slot);
		return true;
	}
	
	public function getSlot($slot){
		$slot = $this->inventory[(int) $slot];
		return BlockAPI::getItem($slot[0], $slot[1], $slot[2]);
		return true;
	}
	
	public function sendInventorySlot($s){
		$s = (int) $s;
		if(!isset($this->inventory[$s]) or $this->itemEnforcement === false){
			return false;
		}
		
		
		$this->sendInventory(); //Fallback
		return true;
		
		//Can't do this :(
		
		/*$slot = BlockAPI::getItem($this->inventory[$s][0], $this->inventory[$s][1], $this->inventory[$s][2]);
		$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
			"windowid" => 0,
			"slot" => (int) $s,
			"block" => $slot->getID(),
			"stack" => $slot->count,
			"meta" => $slot->getMetadata(),
		));
		return true;*/
	}
	
	public function hasItem($type, $damage = false){
		if($type === AIR){
			return true;
		}
		foreach($this->inventory as $s => $data){
			if($data[0] === $type and ($data[1] === $damage or $damage === false) and $data[2] > 0){
				return true;
			}
		}
		return false;
	}
	
	public function eventHandler($data, $event){
		switch($event){
			case "tile.container.slot":
				foreach($this->windows as $id => $w){
					if($w === $data["tile"]){
						$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
							"windowid" => $id,
							"slot" => $data["slot"],
							"block" => $data["slotdata"]->getID(),
							"stack" => $data["slotdata"]->count,
							"meta" => $data["slotdata"]->getMetadata(),
						));
					}
				}
				break;
			case "player.armor":
				if($data["eid"] === $this->eid){
					$data["eid"] = 0;
					$this->armor = array();
					for($i = 0; $i < 4; ++$i){
						if($data["slot".$i] > 0){
							$this->armor[$i] = array($data["slot".$i] + 256, 0, 1);
						}else{
							$this->armor[$i] = array(AIR, 0, 0);
						}
					}
					$this->dataPacket(MC_PLAYER_ARMOR_EQUIPMENT, $data);
				}elseif($data["player"]->level === $this->level){
					$this->dataPacket(MC_PLAYER_ARMOR_EQUIPMENT, $data);
				}
				break;
			case "player.pickup":
				if($data["eid"] === $this->eid){
					$data["eid"] = 0;
					$this->dataPacket(MC_TAKE_ITEM_ENTITY, $data);
					if(($this->gamemode & 0x01) === 0x00){
						$this->addItem($data["entity"]->type, $data["entity"]->meta, $data["entity"]->stack);
					}
				}elseif($data["entity"]->level === $this->level){
					$this->dataPacket(MC_TAKE_ITEM_ENTITY, $data);
				}
				break;
			case "player.equipment.change":
				if($data["eid"] === $this->eid or $data["player"]->level !== $this->level){
					break;
				}
				$this->dataPacket(MC_PLAYER_EQUIPMENT, $data);

				break;
			case "entity.move":
				if($data->eid === $this->eid or $data->level !== $this->level){
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
			case "entity.motion":
				/*if($data->eid === $this->eid or $data->level !== $this->level){
					break;
				}
				$this->dataPacket(MC_SET_ENTITY_MOTION, array(
					"eid" => $data->eid,
					"speedX" => (int) ($data->speedX * 32000),
					"speedY" => (int) ($data->speedY * 32000),
					"speedZ" => (int) ($data->speedZ * 32000),
				));
				break;*/
			case "entity.remove":
				if($data->eid === $this->eid or $data->level !== $this->level){
					break;
				}
				$this->dataPacket(MC_REMOVE_ENTITY, array(
					"eid" => $data->eid,
				));
				break;
			case "entity.animate":
				if($data["eid"] === $this->eid or $data["entity"]->level !== $this->level){
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
				if($data->level === $this->level){
					$this->dataPacket(MC_SET_ENTITY_DATA, array(
						"eid" => $eid,
						"metadata" => $data->getMetadata(),
					));
				}
				break;
			case "entity.event":
				if($data["entity"]->eid === $this->eid){
					$eid = 0;
				}else{
					$eid = $data["entity"]->eid;
				}
				if($data["entity"]->level === $this->level){
					$this->dataPacket(MC_ENTITY_EVENT, array(
						"eid" => $eid,
						"event" => $data["event"],
					));
				}
				break;
			case "server.chat":
				if(($data instanceof Container) === true){
					if(!$data->check($this->username) and !$data->check($this->iusername)){
						return;
					}else{
						$message = $data->get();
					}
				}else{
					$message = (string) $data;
				}
				$this->sendChat(preg_replace('/\x1b\[[0-9;]*m/', "", $message)); //Remove ANSI codes from chat
				break;
		}
	}
	
	public function sendChat($message){
		$mes = explode("\n", $message);
		foreach($mes as $m){
			if(preg_match_all('#@([@A-Za-z_]{1,})#', $m, $matches, PREG_OFFSET_CAPTURE) > 0){
				$offsetshift = 0;
				foreach($matches[1] as $selector){
					if($selector[0]{0} === "@"){ //Escape!
						$m = substr_replace($m, $selector[0], $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
						--$offsetshift;
						continue;
					}
					switch(strtolower($selector[0])){
						case "player":
						case "username":
							$m = substr_replace($m, $this->username, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
							$offsetshift += strlen($selector[0]) - strlen($this->username) + 1;
							break;
					}
				}
			}
			$this->dataPacket(MC_CHAT, array(				
				"message" => $m,
			));	
		}
	}
	
	public function sendSettings($nametags = true){
		/*
		 bit mask | flag name
		0x00000001 world_inmutable
		0x00000002 -
		0x00000004 -
		0x00000008 - (autojump)
		0x00000010 -
		0x00000020 nametags_visible
		0x00000040 ?
		0x00000080 ?
		0x00000100 ?
		0x00000200 ?
		0x00000400 ?
		0x00000800 ?
		0x00001000 ?
		0x00002000 ?
		0x00004000 ?
		0x00008000 ?
		0x00010000 ?
		0x00020000 ?
		0x00040000 ?
		0x00080000 ?
		0x00100000 ?
		0x00200000 ?
		0x00400000 ?
		0x00800000 ?
		0x01000000 ?
		0x02000000 ?
		0x04000000 ?
		0x08000000 ?
		0x10000000 ?
		0x20000000 ?
		0x40000000 ?
		0x80000000 ?
		*/
		$flags = 0;
		if(($this->gamemode & 0x02) === 0x02){
			$flags |= 0x01; //Not allow placing/breaking blocks, adventure mode
		}
		
		if($nametags !== false){
			$flags |= 0x20; //Show Nametags
		}

		$this->dataPacket(MC_ADVENTURE_SETTINGS, array(
			"flags" => $flags,
		));
	}
	
	public function teleport(Vector3 $pos, $yaw = false, $pitch = false, $terrain = true){
		if($this->entity instanceof Entity){
			$this->entity->check = false;
			if($yaw === false){
				$yaw = $this->entity->yaw;
			}
			if($pitch === false){
				$pitch = $this->entity->pitch;
			}
			if($this->server->api->dhandle("player.teleport", array("player" => $this, "target" => $pos)) === false){
				return false;
			}
			
			$this->lastCorrect = $pos;
			$this->entity->fallY = false;
			$this->entity->fallStart = false;
			$this->entity->setPosition($pos, $yaw, $pitch);
			$this->entity->resetSpeed();
			$this->entity->updateLast();
			$this->entity->calculateVelocity();
			if($pos instanceof Position and $pos->level !== $this->level){
				foreach($this->server->api->entity->getAll($this->level) as $e){
					if($e !== $this->entity){
						if($e->player instanceof Player){
							$e->player->dataPacket(MC_REMOVE_ENTITY, array(
								"eid" => $this->eid,
							));
						}
						$this->dataPacket(MC_REMOVE_ENTITY, array(
							"eid" => $e->eid,
						));
					}
				}
				$this->level->freeAllChunks($this);
				$this->level = $pos->level;
				$this->chunksLoaded = array();
				$this->server->api->entity->spawnToAll($this->level, $this->eid);
				$this->server->api->entity->spawnAll($this);
				$terrain = true;
			}
			if($terrain === true){
				$this->orderChunks();
				$this->getNextChunk();
			}
			$this->entity->check = true;
		}
		$this->dataPacket(MC_MOVE_PLAYER, array(
			"eid" => 0,
			"x" => $pos->x,
			"y" => $pos->y,
			"z" => $pos->z,
			"yaw" => $yaw,
			"pitch" => $pitch,
		));
	}
	
	public function getGamemode(){
		switch($this->gamemode){
			case SURVIVAL:
				return "survival";
			case CREATIVE:
				return "creative";
			case ADVENTURE:
				return "adventure";
			case VIEW:
				return "view";
		}
	}
	
	public function setGamemode($gm){
		if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
			return false;
		}
		
		if($this->server->api->dhandle("player.gamemode.change", array("player" => $this, "gamemode" => $gm)) === false){
			return false;
		}
		
		$inv =& $this->inventory;
		if(($this->gamemode & 0x01) === ($gm & 0x01)){			
			if(($gm & 0x01) === 0x01 and ($gm & 0x02) === 0x02){
				$inv = array();
				foreach(BlockAPI::$creative as $item){
					$inv[] = array(DANDELION, 0, 1);
				}
			}elseif(($gm & 0x01) === 0x01){
				$inv = array();
				foreach(BlockAPI::$creative as $item){
					$inv[] = array($item[0], $item[1], 1);
				}
			}
			$this->gamemode = $gm;
			$this->eventHandler("Your gamemode has been changed to ".$this->getGamemode().".", "server.chat");
		}else{
			$inv = array_fill(0, 36, array(AIR, 0, 0));
			$this->blocked = true;
			$this->gamemode = $gm;
			$this->eventHandler("Your gamemode has been changed to ".$this->getGamemode().", you've to do a forced reconnect.", "server.chat");
			$this->server->schedule(30, array($this, "close"), "gamemode change"); //Forces a kick
			if(($gm & 0x01) === 0x01){
				$this->itemEnforcement = true;
			}
		}
		$this->inventory = $inv;
		$this->sendSettings();
		if($this->itemEnforcement === true){
			$this->sendInventory();
		}
		return true;
	}
	
	public function measureLag(){
		if($this->connected === false){
			return false;
		}
		$this->lag[0] = microtime(true) * 1000;
		$this->dataPacket(MC_PING, array(
			"time" => (int) $this->lag[0],
		));
		$this->sendBuffer();
	}
	
	public function getLag(){
		return $this->lag[1] - $this->lag[0];
	}

	public function handle($pid, $data){
		if($this->connected === true){
			$this->timeout = microtime(true) + 20;
			switch($pid){
				case 0xa0: //NACK
					foreach($data[0] as $count){
						if(isset($this->recovery[$count])){
							$this->directDataPacket($this->recovery[$count]["id"], $this->recovery[$count], $count);
						}
					}
					break;
				case 0xc0: //ACK
					foreach($data[0] as $count){
						if($count > $this->counter[2]){
							$this->counter[2] = $count;
						}
						$this->recovery[$count] = null;
						unset($this->recovery[$count]);
					}
					$limit = microtime(true) - 2; //max lag
					foreach($this->recovery as $count => $d){
						$diff = $this->counter[2] - $count;
						if($diff > 16 and $d["sendtime"] < $limit){
							$this->directDataPacket($d["id"], $d, $count);
						}
					}
					break;
				case 0x07:
					if($this->loggedIn === true){
						break;
					}
					$this->send(0x08, array(
						RAKNET_MAGIC,
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
							$arr = array();
							for($i = $this->counter[1]; $i < $data[0]; ++$i){
								$arr[] = $i;
							}
							$this->send(0xa0, array($arr));
							$this->counter[1] = $data[0];
						}elseif($diff === 1){
							$this->counter[1] = $data[0];
						}
						$this->send(0xc0, array(array($data[0])));
					}
					
					if(!isset($data["id"])){
						break;
					}
					switch($data["id"]){
						case 0x01:
							break;
						case MC_PONG:
							$this->lag[1] = microtime(true) * 1000;
							break;
						case MC_PING:
							$t = (int) (microtime(true) * 1000);
							$this->dataPacket(MC_PONG, array(
								"ptime" => $data["time"],
								"time" => (int) (microtime(true) * 1000),
							));
							$this->sendBuffer();
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
							if(count($this->server->clients) >= $this->server->maxClients){
								$this->close("server is full!", false);
								return;
							}					
							if($data["protocol1"] !== CURRENT_PROTOCOL){
								if($data["protocol1"] < CURRENT_PROTOCOL){
									$this->directDataPacket(MC_LOGIN_STATUS, array(
										"status" => 1,
									));
								}else{
									$this->directDataPacket(MC_LOGIN_STATUS, array(
										"status" => 2,
									));
								}
								$this->close("Incorrect protocol", false);
								break;
							}
							if(preg_match('#^[a-zA-Z0-9_]{2,16}$#', $data["username"])){
								$this->username = $data["username"];
								$this->iusername = strtolower($this->username);
							}else{
								$this->close("Bad username", false);
								break;
							}
							if($this->server->api->handle("player.connect", $this) === false){
								$this->close("Unknown reason", false);
								return;
							}
							
							if($this->server->whitelist === true and !$this->server->api->ban->inWhitelist($this->iusername)){
								$this->close("Server is white-listed", false);
								return;
							}elseif($this->server->api->ban->isBanned($this->iusername) or $this->server->api->ban->isIPBanned($this->ip)){
								$this->close("You are banned!", false);
								return;
							}
							$this->loggedIn = true;
							
							$u = $this->server->api->player->get($this->iusername);
							if($u !== false){
								$u->close("logged in from another location");
							}
							
							$this->server->api->player->add($this->CID);
							if($this->server->api->handle("player.join", $this) === false){
								$this->close("join cancelled", false);
								return;
							}
							
							if(!($this->data instanceof Config)){
								$u->close("no config created", false);
								return;
							}
							
							$this->auth = true;
							if(!$this->data->exists("inventory") or ($this->gamemode & 0x01) === 0x01){
								if(($this->gamemode & 0x01) === 0x01){
									$this->itemEnforcement = true;
									$this->inventory = array();
									if(($this->gamemode & 0x02) === 0x02){
										foreach(BlockAPI::$creative as $item){
											$this->inventory[] = array(DANDELION, 0, 1);
										}
									}else{
										foreach(BlockAPI::$creative as $item){
											$this->inventory[] = array($item[0], $item[1], 1);
										}
									}
								}
								$this->data->set("inventory", $this->inventory);
							}
							$this->data->set("caseusername", $this->username);
							$this->inventory = $this->data->get("inventory");
							$this->armor = $this->data->get("armor");
							
							$this->data->set("lastIP", $this->ip);
							$this->data->set("lastID", $this->clientID);

							if($this->data instanceof Config){
								$this->server->api->player->saveOffline($this->data);
							}
							$this->dataPacket(MC_LOGIN_STATUS, array(
								"status" => 0,
							));
							$this->dataPacket(MC_START_GAME, array(
								"seed" => $this->level->getSeed(),
								"x" => $this->data->get("position")["x"],
								"y" => $this->data->get("position")["y"],
								"z" => $this->data->get("position")["z"],
								"unknown1" => 0,
								"gamemode" => ($this->gamemode & 0x01),
								"eid" => 0,
							));
							if(($this->gamemode & 0x01) === 0x01){
								$this->equipment = BlockAPI::getItem($this->inventory[7][0], $this->inventory[7][1], $this->inventory[7][2]);
							}
							$this->entity = $this->server->api->entity->add($this->level, ENTITY_PLAYER, 0, array("player" => $this));
							$this->eid = $this->entity->eid;
							$this->server->query("UPDATE players SET EID = ".$this->eid." WHERE clientID = ".$this->clientID.";");
							$this->entity->x = $this->data->get("position")["x"];
							$this->entity->y = $this->data->get("position")["y"];
							$this->entity->z = $this->data->get("position")["z"];
							$this->entity->setName($this->username);
							$this->entity->data["clientID"] = $this->clientID;
							$this->evid[] = $this->server->event("server.chat", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.remove", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.move", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.motion", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.animate", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.event", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("entity.metadata", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("player.equipment.change", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("player.armor", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("player.pickup", array($this, "eventHandler"));
							$this->evid[] = $this->server->event("tile.container.slot", array($this, "eventHandler"));
							$this->server->schedule(40, array($this, "measureLag"), array(), true);
							console("[INFO] \x1b[33m".$this->username."\x1b[0m[/".$this->ip.":".$this->port."] logged in with entity id ".$this->eid." at (".$this->entity->level->getName().", ".round($this->entity->x, 2).", ".round($this->entity->y, 2).", ".round($this->entity->z, 2).")");
							break;
						case MC_READY:
							if($this->loggedIn === false){
								break;
							}
							switch($data["status"]){
								case 1: //Spawn!!
									if($this->spawned !== false){
										break;
									}
									$this->spawned = true;						
									$this->server->api->entity->spawnAll($this);
									$this->server->api->entity->spawnToAll($this->level, $this->eid);
									$this->server->schedule(5, array($this->entity, "update"), array(), true);
									$this->sendArmor();
									$this->eventHandler(new Container($this->server->motd), "server.chat");
									if($this->MTU <= 548){
										$this->eventHandler("Your connection is bad, you may experience lag and slow map loading.", "server.chat");
									}
									
									if($this->iusername === "steve" or $this->iusername === "stevie"){
										$this->eventHandler("You're using the default username. Please change it on the Minecraft PE settings.", "server.chat");
									}
									$this->sendInventory();
									$this->sendSettings();
									$this->server->schedule(50, array($this, "orderChunks"), array(), true);
									$this->blocked = false;
									$this->teleport(new Position($this->data->get("position")["x"], $this->data->get("position")["y"], $this->data->get("position")["z"], $this->level));
									break;
								case 2://Chunk loaded?
									break;
							}
							break;
						case MC_MOVE_PLAYER:
							if($this->spawned === false){
								break;
							}
							if(($this->entity instanceof Entity) and $data["counter"] > $this->lastMovement){
								$this->lastMovement = $data["counter"];
								$speed = $this->entity->getSpeed();
								if($this->blocked === true or ($speed > 6 and ($this->gamemode & 0x01) === 0x00) or $speed > 15 or $this->server->api->handle("player.move", $this->entity) === false){
									if($this->lastCorrect instanceof Vector3){
										$this->teleport($this->lastCorrect, $this->entity->yaw, $this->entity->pitch, false);
									}
									console("[WARNING] ".$this->username." moved too quickly!");
								}else{
									$this->entity->setPosition(new Vector3($data["x"], $data["y"], $data["z"]), $data["yaw"], $data["pitch"]);
								}
							}
							break;
						case MC_PLAYER_EQUIPMENT:
							if($this->spawned === false){
								break;
							}
							$data["eid"] = $this->eid;
							$data["player"] = $this;
							$data["item"] = BlockAPI::getItem($data["block"], $data["meta"]);
							if(($this->hasItem($data["block"], $data["meta"]) or $this->itemEnforcement !== true) and $this->server->handle("player.equipment.change", $data) !== false){
								$this->equipment = $data["item"];
							}elseif($this->itemEnforcement === true){
								$this->sendInventory();
							}
							break;
						case MC_REQUEST_CHUNK:
							if($this->spawned === false){
								break;
							}
							break;
						case MC_USE_ITEM:
							if($this->spawned === false){
								break;
							}
							$data["eid"] = $this->eid;
							if($this->blocked === true or Utils::distance($this->entity->position, $data) > 10){
								break;
							}elseif(!$this->hasItem($data["block"], $data["meta"]) and $this->itemEnforcement === true){
								$this->sendInventory();
								break;
							}
							$this->server->api->block->playerBlockAction($this, new Vector3($data["x"], $data["y"], $data["z"]), $data["face"], $data["fx"], $data["fy"], $data["fz"]);
							break;
						case MC_REMOVE_BLOCK:
							if($this->spawned === false){
								break;
							}
							if($this->blocked === true or $this->entity->distance(new Vector3($data["x"], $data["y"], $data["z"])) > 8){
								break;
							}
							$this->server->api->block->playerBlockBreak($this, new Vector3($data["x"], $data["y"], $data["z"]));
							break;
						case MC_PLAYER_ARMOR_EQUIPMENT:
							if($this->spawned === false){
								break;
							}
							$data["eid"] = $this->eid;
							$this->server->handle("player.armor", $data);
							break;
						case MC_INTERACT:
							if($this->spawned === false){
								break;
							}
							$target = $this->server->api->entity->get($data["target"]);
							if($this->gamemode !== VIEW and $this->blocked === false and ($target instanceof Entity) and $this->entity->distance($target) <= 8){
								$data["targetentity"] = $target;
								$data["entity"] = $this->entity;
								if(!($target instanceof Entity)){
									break;
								}elseif($target->class === ENTITY_PLAYER and ($this->server->api->getProperty("pvp") == false or $this->server->difficulty <= 0 or ($target->player->gamemode & 0x01) === 0x01)){
									break;
								}elseif($this->server->handle("player.interact", $data) !== false){
									switch($this->equipment->getID()){
										case WOODEN_SWORD:
										case GOLD_SWORD:
											$damage = 4;
											break;
										case STONE_SWORD:
											$damage = 5;
											break;
										case IRON_SWORD:
											$damage = 6;
											break;
										case DIAMOND_SWORD:
											$damage = 7;
											break;
											
										case WOODEN_AXE:
										case GOLD_AXE:
											$damage = 3;
											break;
										case STONE_AXE:
											$damage = 4;
											break;
										case IRON_AXE:
											$damage = 5;
											break;
										case DIAMOND_AXE:
											$damage = 6;
											break;

										case WOODEN_PICKAXE:
										case GOLD_PICKAXE:
											$damage = 2;
											break;
										case STONE_PICKAXE:
											$damage = 3;
											break;
										case IRON_PICKAXE:
											$damage = 4;
											break;
										case DIAMOND_PICKAXE:
											$damage = 5;
											break;

										case WOODEN_SHOVEL:
										case GOLD_SHOVEL:
											$damage = 1;
											break;
										case STONE_SHOVEL:
											$damage = 2;
											break;
										case IRON_SHOVEL:
											$damage = 3;
											break;
										case DIAMOND_SHOVEL:
											$damage = 4;
											break;

										default:
											$damage = 1;//$this->server->difficulty;
									}
									$this->server->api->entity->harm($data["target"], $damage, $this->eid);
								}
							}
							break;
						case MC_ANIMATE:
							if($this->spawned === false){
								break;
							}
							$this->server->api->dhandle("entity.animate", array("eid" => $this->eid, "entity" => $this->entity, "action" => $data["action"]));
							break;
						case MC_RESPAWN:
							if($this->spawned === false){
								break;
							}
							if($this->entity->dead === false){
								break;
							}
							$this->blocked = false;
							$this->entity->fire = 0;
							$this->entity->air = 300;
							$this->entity->setHealth(20, "respawn");
							$this->entity->updateMetadata();
							$this->sendInventory();
							$this->teleport($this->spawnPosition);
							break;
						case MC_SET_HEALTH:
							if($this->spawned === false){
								break;
							}
							if(($this->gamemode & 0x01) === 0x01){
								break;
							}
							//$this->entity->setHealth($data["health"], "client");
							break;
						case MC_ENTITY_EVENT:
							if($this->spawned === false){
								break;
							}
							$data["eid"] = $this->eid;
							switch($data["event"]){
								case 9: //Eating
									$items = array(
										APPLE => 2, //Apples
										282 => 10, //Stew
										BREAD => 5, //Bread
										319 => 3,
										320 => 8,
										363 => 3,
										364 => 8,
									);
									if(isset($items[$this->equipment->getID()])){
										if($this->removeItem($this->equipment->getID(), $this->equipment->getMetadata(), 1) === true or $this->itemEnforcement !== true){
											$this->dataPacket(MC_ENTITY_EVENT, array(
												"eid" => 0,
												"event" => 9,
											));
											$this->entity->heal($items[$this->equipment->getID()], "eating");
										}
									}
									break;
							}
							break;
						case MC_DROP_ITEM:
							if($this->spawned === false){
								break;
							}
							$item = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"]);
							$data["item"] = $item;
							if($this->blocked === false and $this->server->handle("player.drop", $data) !== false){
								if($this->removeItem($item->getID(), $item->getMetadata(), $item->count) === true or $this->itemEnforcement !== true){
									$this->server->api->entity->drop(new Position($this->entity->x - 0.5, $this->entity->y, $this->entity->z - 0.5, $this->level), $item);
								}
							}
							break;
						case MC_SIGN_UPDATE:
							if($this->spawned === false){
								break;
							}
							$t = $this->server->api->tileentity->get(new Position($data["x"], $data["y"], $data["z"], $this->level));
							if(($t[0] instanceof TileEntity) and $t[0]->class === TILE_SIGN){
								$t = $t[0];
								if($t->data["creator"] !== $this->username){
									$t->spawn($this);
								}else{
									$t->data["Text1"] = $data["line0"];
									$t->data["Text2"] = $data["line1"];
									$t->data["Text3"] = $data["line2"];
									$t->data["Text4"] = $data["line3"];
									$this->server->handle("tile.update", $t);
									$this->server->api->tileentity->spawnToAll($this->level, $t);
								}
							}
							break;
						case MC_CHAT:
							if($this->spawned === false){
								break;
							}
							$message = $data["message"];
							if($message{0} === "/"){ //Command
								$this->server->api->console->run(substr($message, 1), $this);
							}else{
								if($this->server->api->dhandle("player.chat", array("player" => $this, "message" => $message)) !== false){
									$this->server->api->send($this, $message);
								}
							}
							break;
						case MC_CONTAINER_CLOSE:
							if($this->spawned === false){
								break;
							}
							unset($this->windows[$data["windowid"]]);
							$this->dataPacket(MC_CONTAINER_CLOSE, array(
								"windowid" => $data["windowid"],
							));
							break;
						case MC_CONTAINER_SET_SLOT:
							if($this->spawned === false){
								break;
							}
							if(!isset($this->windows[$data["windowid"]])){
								break;
							}
							$tile = $this->windows[$data["windowid"]];
							if(($tile->class !== TILE_CHEST and $tile->class !== TILE_FURNACE) or $data["slot"] < 0 or ($tile->class === TILE_CHEST and $data["slot"] >= CHEST_SLOTS) or ($tile->class === TILE_FURNACE and $data["slot"] >= FURNACE_SLOTS)){
								break;
							}
							$done = false;
							$item = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"]);
							
							$slot = $tile->getSlot($data["slot"]);
							$done = true;
							if($this->server->api->dhandle("player.container.slot", array(
								"tile" => $tile,
								"slot" => $data["slot"],
								"slotdata" => $slot,
								"itemdata" => $item,
								"player" => $this,
							)) === false){
								$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
									"windowid" => $data["windowid"],
									"slot" => $data["slot"],
									"block" => $slot->getID(),
									"stack" => $slot->count,
									"meta" => $slot->getMetadata(),
								));
								break;
							}
							if($item->getID() !== AIR and $slot->getID() == $item->getID()){
								if($slot->count < $item->count){
									if($this->removeItem($item->getID(), $item->getMetadata(), $item->count - $slot->count) === false and $this->itemEnforcement === true){
										break;
									}
								}elseif($slot->count > $item->count){
									$this->addItem($item->getID(), $item->getMetadata(), $slot->count - $item->count);
								}
							}else{
								if($this->removeItem($item->getID(), $item->getMetadata(), $item->count) === false and $this->itemEnforcement === true){
									break;
								}
								$this->addItem($slot->getID(), $slot->getMetadata(), $slot->count);
							}
							$tile->setSlot($data["slot"], $item);
							break;
						case MC_SEND_INVENTORY: //TODO, Mojang, enable this ´^_^`
							if($this->spawned === false){
								break;
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
	
	public function sendArmor(){
		$this->server->api->dhandle("player.armor", array("player" => $this, "eid" => $this->eid, "slot0" => ($this->armor[0][0] > 0 ? ($this->armor[0][0] - 256):AIR), "slot1" => ($this->armor[1][0] > 0 ? ($this->armor[1][0] - 256):AIR), "slot2" => ($this->armor[2][0] > 0 ? ($this->armor[2][0] - 256):AIR), "slot3" => ($this->armor[3][0] > 0 ? ($this->armor[3][0] - 256):AIR)));
	}
	
	public function sendInventory(){
		/*
		//OLD WAY
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
			$this->inventory[$s] = array(AIR, 0, 0);
		}*/
		$inv = array();
		foreach($this->inventory as $s => $data){
			if($data[0] > AIR and $data[2] >= 0){
				$inv[] = BlockAPI::getItem($data[0], $data[1], $data[2]);
			}else{
				$inv[] = BlockAPI::getItem(AIR, 0, 0);
				$this->inventory[$s] = array(AIR, 0, 0);
			}
		}
		$this->dataPacket(MC_CONTAINER_SET_CONTENT, array(
			"windowid" => 0,
			"count" => count($inv),
			"slots" => $inv
		));
		/*
		//Future
		$inv = array();
		foreach($this->inventory as $s => $data){
			if($data[0] > 0 and $data[2] >= 0){
				$inv[] = BlockAPI::getItem($data[0], $data[1], $data[2]);
			}else{
				$inv[] = BlockAPI::getItem(AIR, 0, 0);
				$this->inventory[$s] = array(AIR, 0, 0);
			}
		}
		$this->dataPacket(MC_SEND_INVENTORY, array(
			"eid" => 0,
			"windowid" => 0,
			"slots" => $inv,
			"armor" => array(
				0 => BlockAPI::getItem($this->armor[0][0], $this->armor[0][1], $this->armor[0][2], $this->armor[0][3]),
				1 => BlockAPI::getItem($this->armor[1][0], $this->armor[1][1], $this->armor[1][2], $this->armor[1][3]),
				2 => BlockAPI::getItem($this->armor[2][0], $this->armor[2][1], $this->armor[2][2], $this->armor[2][3]),
				3 => BlockAPI::getItem($this->armor[3][0], $this->armor[3][1], $this->armor[3][2], $this->armor[3][3]),
			),	
		));
		*/
	}

	public function send($pid, $data = array(), $raw = false){
		if($this->connected === true){
			$this->server->send($pid, $data, $raw, $this->ip, $this->port);
		}
	}

	public function actionQueue($code){
		$this->queue[] = array(1, $code);
	}
	
	public function sendBuffer(){
		if(strlen($this->buffer) > 0){
			$this->directDataPacket(false, array("raw" => $this->buffer));
		}
		$this->buffer = "";
		$this->nextBuffer = microtime(true) + 0.1;
	}
	
	public function directBigRawPacket($id, $buffer){
		if($this->connected === false){
			return false;
		}
		$data = array(
			"id" => false,
			"sendtime" => microtime(true),
			"raw" => "",
		);
		$size = $this->MTU - 31;
		$buffer = str_split(($id === false ? "":chr($id)).$buffer, $size);
		$h = Utils::writeInt(count($buffer)).Utils::writeShort($this->bigCnt);
		$this->bigCnt = ($this->bigCnt + 1) % 0x10000;
		foreach($buffer as $i => $buf){
			$data["raw"] = Utils::writeShort(strlen($buf) << 3).$h.Utils::writeInt($i).$buf;
			$count = $this->counter[0]++;
			if(count($this->recovery) >= PLAYER_RECOVERY_BUFFER){
				reset($this->recovery);
				$k = key($this->recovery);
				$this->recovery[$k] = null;
				unset($this->recovery[$k]);
				end($this->recovery);
			}
			$this->recovery[$count] = $data;
			$this->send(0x80, array(
				$count,
				0x10, //0b01010000
				$data,
			));
		}
	}
	
	public function directDataPacket($id, $data = array(), $count = false){
		if($this->connected === false){
			return false;
		}
		$data["id"] = $id;
		$data["sendtime"] = microtime(true);
		if($count === false){
			$count = $this->counter[0]++;
			if(count($this->recovery) >= PLAYER_RECOVERY_BUFFER){
				reset($this->recovery);
				$k = key($this->recovery);
				$this->recovery[$k] = null;
				unset($this->recovery[$k]);
				end($this->recovery);
			}
		}
		$this->recovery[$count] = $data;
		$this->send(0x80, array(
			$count,
			0x00,
			$data,
		));
	}

	public function dataPacket($id, $data = array(), $queue = false){
		$data["id"] = $id;
		if($queue === true){
			$this->queue[] = array(0, $data);
		}else{
			if($id === false){
				$raw = $data["raw"];
			}else{
				$data = new CustomPacketHandler($id, "", $data, true);
				$raw = chr($id).$data->raw;
			}
			$len = strlen($raw);
			$MTU = $this->MTU - 21;
			if($len > $MTU){
				$this->directBigRawPacket(false, $raw);
				return;
			}
			
			if((strlen($this->buffer) + $len) >= $MTU){
				$this->sendBuffer();
			}

			$this->buffer .= ($this->buffer === "" ? "":"\x00").Utils::writeShort($len << 3).$raw;
			
		}
	}
	
	function __toString(){
		if($this->username != ""){
			return $this->username;
		}
		return $this->clientID;
	}

}
