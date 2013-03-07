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

class PlayerAPI{
	private $server;
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}

	public function init(){
		$this->server->addHandler("server.regeneration", array($this, "handle"));
		$this->server->addHandler("player.death", array($this, "handle"), 1);
		$this->server->api->console->register("list", "Shows connected player list", array($this, "commandHandler"));
		$this->server->api->console->register("kill", "Kills a player", array($this, "commandHandler"));
		$this->server->api->console->register("harm", "Harms a player", array($this, "commandHandler"));
		$this->server->api->console->register("tppos", "Teleports a player to a position", array($this, "commandHandler"));
		$this->server->api->console->register("tp", "Teleports a player to another player", array($this, "commandHandler"));
	}

	public function handle($data, $event){
		switch($event){
			case "server.regeneration":
				$result = $this->server->query("SELECT EID FROM entities WHERE class = ".ENTITY_PLAYER." AND health < 20;");
				if($result !== true and $result !== false){
					while(($player = $result->fetchArray()) !== false){
						if(($player = $this->server->api->entity->get($player["EID"])) !== false){
							if($player->getHealth() <= 0){
								continue;
							}
							$player->setHealth(min(20, $player->getHealth() + $data), "regeneration");
						}
					}
				}
				break;
			case "player.death":
				$message = $data["name"];
				if(is_numeric($data["cause"]) and isset($this->entities[$data["cause"]])){
					$e = $this->api->entity->get($data["cause"]);
					switch($e->class){
						case ENTITY_PLAYER:
							$message .= " was killed by ".$e->name;
							break;
						default:
							$message .= " was killed";
							break;
					}
				}else{
					switch($data["cause"]){
						case "cactus":
							$message .= " was pricked to death";
							break;
						case "lava":
							$message .= " tried to swim in lava";
							break;
						case "fire":
							$message .= " went up in flames";
							break;
						case "burning":
							$message .= " burned to death";
							break;
						case "suffocation":
							$message .= " suffocated in a wall";
							break;
						case "water":
							$message .= " drowned";
							break;
						case "void":
							$message .= " fell out of the world";
							break;
						case "fall":
							$message .= " hit the ground too hard";
							break;
						case "flying":
							$message .= " tried to fly up to the sky";
							break;							
						default:
							$message .= " died";
							break;
					}
				}
				$this->server->chat(false, $message);
				break;
		}
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "tp":
				$name = array_shift($params);
				$target = array_shift($params);
				if($name == null or $target == null){
					$output .= "Usage: /tp <player> <target>\n";
					break;
				}
				if($this->teleport($name, $target)){
					$output .= "\"$name\" teleported to \"$target\"\n";
				}else{
					$output .= "Couldn't teleport\n";
				}
				break;
			case "tppos":
				$z = array_pop($params);
				$y = array_pop($params);
				$x = array_pop($params);
				$name = implode(" ", $params);
				if($name == null or $x === null or $y === null or $z === null){
					$output .= "Usage: /tp <player> <x> <y> <z>\n";
					break;
				}
				if($this->tppos($name, $x, $y, $z)){
					$output .= "\"$name\" teleported to ($x, $y, $z)\n";
				}else{
					$output .= "Couldn't teleport\n";
				}
				break;
			case "kill":
				$player = $this->get(implode(" ", $params));
				if($player !== false){
					$this->server->api->entity->harm($player->eid, 20, "console", true);
				}else{
					$output .= "Usage: /kill <player>\n";
				}
				break;
			case "harm":
				$dmg = (int) array_shift($params);
				$player = $this->get(implode(" ", $params));
				if($player !== false){
					$this->server->api->entity->harm($player->eid, $dmg, "console", true);
				}else{
					$output .= "Usage: /harm <damage> <player>\n";
				}
				break;
			case "list":
				$output .= "Player list:\n";
				foreach($this->server->clients as $c){
					$output .= $c->username." (".$c->ip.":".$c->port."), ClientID ".$c->clientID.", (".round($c->entity->x, 2).", ".round($c->entity->y, 2).", ".round($c->entity->z, 2).")\n";
				}
				break;
		}
		return $output;
	}

	public function teleport($name, $target){
		$target = $this->get($target);
		if(($target instanceof Player) and ($target->entity instanceof Entity)){
			return $this->tppos($name, $target->entity->x, $target->entity->y, $target->entity->z);
		}
		return false;
	}

	public function tppos($name, $x, $y, $z){
		$player = $this->get($name);
		if(($player instanceof Player) and ($player->entity instanceof Entity)){
			$player->entity->setPosition($x, $y, $z, 0, 0);
			$player->fallY = false;
			$player->fallStart = false;
			$player->dataPacket(MC_MOVE_PLAYER, array(
				"eid" => 0,
				"x" => $x,
				"y" => $y,
				"z" => $z,
				"yaw" => 0,
				"pitch" => 0,
			));
			$player->fallY = false;
			$player->fallStart = false;
			return true;
		}
		return false;
	}

	public function get($name){
		$CID = $this->server->query("SELECT ip,port FROM players WHERE name = '".$name."';", true);
		$CID = $this->server->clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
	}

	public function getAll(){
		return $this->server->clients;
	}

	public function getByEID($eid){
		$eid = (int) $eid;
		$CID = $this->server->query("SELECT ip,port FROM players WHERE EID = '".$eid."';", true);
		$CID = $this->server->clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
	}

	public function getByClientID($clientID){
		$clientID = (int) $clientID;
		$CID = $this->server->query("SELECT ip,port FROM players WHERE clientID = '".$clientID."';", true);
		$CID = $this->server->clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
	}

	public function online(){
		$o = array();
		foreach($this->server->clients as $p){
			if($p->auth === true){
				$o[] = $p->username;
			}
		}
		return $o;
	}

	public function add($CID){
		if(isset($this->server->clients[$CID])){
			$player = $this->server->clients[$CID];
			console("[INFO] Player \"\x1b[33m".$player->username."\x1b[0m\" connected from \x1b[36m".$player->ip.":".$player->port."\x1b[0m");
			$player->data = $this->getOffline($player->username);
			$this->server->query("INSERT OR REPLACE INTO players (clientID, ip, port, name) VALUES (".$player->clientID.", '".$player->ip."', ".$player->port.", '".$player->username."');");
		}
	}

	public function remove($CID){
		if(isset($this->server->clients[$CID])){
			$player = $this->server->clients[$CID];
			$this->server->clients[$CID] = null;
			unset($this->server->clients[$CID]);
			$player->close();
			$this->saveOffline($player->username, $player->data);
			$this->server->query("DELETE FROM players WHERE name = '".$player->username."';");
			if($player->entity instanceof Entity){
				$player->entity->player = null;
				$player->entity = null;
			}
			$this->server->api->entity->remove($player->eid);
			$player = null;
			unset($player);
		}
	}

	public function getOffline($name){
		if(!file_exists(DATA_PATH."players/".$name.".dat")){
			console("[NOTICE] Player data not found for \"".$name."\", creating new profile");
			$data = array(
				"spawn" => array(
					"x" => $this->server->spawn["x"],
					"y" => $this->server->spawn["y"],
					"z" => $this->server->spawn["z"],
				),
				"inventory" => array_fill(0, 36, array(AIR, 0, 0)),
				"armor" => array_fill(0, 4, array(AIR, 0, 0)),
				"health" => 20,
				"lastIP" => "",
				"lastID" => 0,
			);
			$this->saveOffline($name, $data);
		}else{
			$data = unserialize(file_get_contents(DATA_PATH."players/".$name.".dat"));
		}
		if($this->server->gamemode === 1){
			$data["health"] = 20;
		}
		$this->server->handle("api.player.offline.get", $data);
		return $data;
	}

	public function saveOffline($name, $data){
		$this->server->handle("api.player.offline.save", $data);
		file_put_contents(DATA_PATH."players/".str_replace("/", "", $name).".dat", serialize($data));
	}
}