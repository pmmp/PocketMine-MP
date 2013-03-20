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
		$this->server->api->console->register("gamemode", "Changes the player gamemode", array($this, "commandHandler"));
		$this->server->api->console->register("tppos", "Teleports a player to a position", array($this, "commandHandler"));
		$this->server->api->console->register("tp", "Teleports a player to another player", array($this, "commandHandler"));
		$this->server->api->console->alias("suicide", "kill");
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
					return true;
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
				return true;
				break;
		}
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "gamemode":
				$gm = -1;
				$player = false;
				if(!isset($params[1]) and isset($params[0]) and ($issuer instanceof Player)){
					$player = $issuer;
					$gm = (int) $params[1];
				}elseif(isset($params[1]) and isset($params[0])){
					$player = $this->server->api->player->get($params[0]);
					$gm = (int) $params[1];
				}
				if(!($player instanceof Player) or $gm < 0 or $gm > 2){
					$output .= "Usage: /gamemode [player] <0 | 1 | 2>\n";
					break;
				}				
				
				
				if($player->setGamemode($gm)){
					$output .= "Gamemode of ".$player->username." changed to ".$player->getGamemode()."\n";
				}
				break;
			case "tp":
				if(!isset($params[1]) and isset($params[0]) and ($issuer instanceof Player)){
					$name = $issuer->username;
					$target = $params[1];
				}elseif(isset($params[1]) and isset($params[0])){
					$name = $params[0];
					$target = $params[1];
				}else{
					$output .= "Usage: /tp [player] <target>\n";
					break;
				}
				if($this->teleport($name, $target)){
					$output .= "\"$name\" teleported to \"$target\"\n";
				}else{
					$output .= "Couldn't teleport\n";
				}
				break;
			case "tppos":
				if(!isset($params[3]) and isset($params[2]) and isset($params[1]) and isset($params[0]) and ($issuer instanceof Player)){
					$name = $issuer->username;
					$x = (float) $params[0];
					$y = (float) $params[1];
					$z = (float) $params[2];
				}elseif(isset($params[3]) and isset($params[2]) and isset($params[1]) and isset($params[0])){
					$name = $params[0];
					$x = (float) $params[1];
					$y = (float) $params[2];
					$z = (float) $params[3];
				}else{
					$output .= "Usage: /tp [player] <x> <y> <z>\n";
					break;
				}
				if($this->tppos($name, $x, $y, $z)){
					$output .= "\"$name\" teleported to ($x, $y, $z)\n";
				}else{
					$output .= "Couldn't teleport\n";
				}
				break;
			case "kill":
			case "suicide":
				if(!isset($params[0]) and ($issuer instanceof Player)){
					$player = $issuer;
				}else{
					$player = $this->get($params[0]);
				}
				if($player instanceof Player){
					$this->server->api->entity->harm($player->eid, 20, "console", true);
				}else{
					$output .= "Usage: /kill [player]\n";
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
			$player->teleport(new Vector3($x, $y, $z));
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
			$player->gamemode = $player->data->get("gamemode");
			$this->server->query("INSERT OR REPLACE INTO players (clientID, ip, port, name) VALUES (".$player->clientID.", '".$player->ip."', ".$player->port.", '".$player->username."');");
		}
	}

	public function remove($CID){
		if(isset($this->server->clients[$CID])){
			$player = $this->server->clients[$CID];
			$this->server->clients[$CID] = null;
			unset($this->server->clients[$CID]);
			$player->close();
			if($player->username != "" and ($player->data instanceof Config)){
				$this->saveOffline($player->data);
			}
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
		$iname = strtolower($name);
		$default = array(
			"position" => array(
				"x" => $this->server->spawn["x"],
				"y" => $this->server->spawn["y"],
				"z" => $this->server->spawn["z"],
			),
			"inventory" => array_fill(0, 36, array(AIR, 0, 0)),
			"armor" => array_fill(0, 4, array(AIR, 0, 0)),
			"gamemode" => $this->server->gamemode,
			"health" => 20,
			"lastIP" => "",
			"lastID" => 0,
		);
		$data = new Config(DATA_PATH."players/".$iname.".yml", CONFIG_YAML, $default);
		if(!file_exists(DATA_PATH."players/".$iname.".yml")){
			console("[NOTICE] Player data not found for \"".$iname."\", creating new profile");
			$data->save();
		}
		if($this->server->gamemode === CREATIVE){
			$data->set("health", 20);
		}
		$this->server->handle("player.offline.get", $data);
		return $data;
	}

	public function saveOffline(Config $data){
		$this->server->handle("player.offline.save", $data);
		$data->save();
	}
}