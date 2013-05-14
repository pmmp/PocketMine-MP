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
	function __construct(){
		$this->server = ServerAPI::request();
	}

	public function init(){
		$this->server->addHandler("server.regeneration", array($this, "handle"));
		$this->server->addHandler("player.death", array($this, "handle"), 1);
		$this->server->api->console->register("list", "", array($this, "commandHandler"));
		$this->server->api->console->register("kill", "<player>", array($this, "commandHandler"));
		$this->server->api->console->register("gamemode", "<mode> [player]", array($this, "commandHandler"));
		$this->server->api->console->register("tppos", "[target player] <x> <y> <z>", array($this, "commandHandler"));
		$this->server->api->console->register("tp", "[target player] <destination player>", array($this, "commandHandler"));
		$this->server->api->console->register("lag", "", array($this, "commandHandler"));
		$this->server->api->console->alias("suicide", "kill");
		$this->server->api->ban->cmdWhitelist("list");
		$this->server->api->ban->cmdWhitelist("lag");
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
				if(is_numeric($data["cause"])){
					$e = $this->server->api->entity->get($data["cause"]);
					if($e instanceof Entity){
						switch($e->class){
							case ENTITY_PLAYER:
								$message .= " was killed by ".$e->name;
								break;
							default:
								$message = " was killed";
								break;
						}
					}
				}else{
					switch($data["cause"]){
						case "cactus":
							$message = " was pricked to death";
							break;
						case "lava":
							$message = " tried to swim in lava";
							break;
						case "fire":
							$message = " went up in flames";
							break;
						case "burning":
							$message = " burned to death";
							break;
						case "suffocation":
							$message = " suffocated in a wall";
							break;
						case "water":
							$message = " drowned";
							break;
						case "void":
							$message = " fell out of the world";
							break;
						case "fall":
							$message = " hit the ground too hard";
							break;
						default:
							$message = " died";
							break;
					}
				}
				$this->server->api->chat->broadcast($data["player"]->username . $message);
				return true;
				break;
		}
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "lag":
				if(!($issuer instanceof Player)){					
					$output .= "Please run this command in-game.\n";
					break;
				}
				$output .= "Lag: ".round($issuer->getLag(), 2)."\n";
				break;
			case "gamemode":
				$player = false;
				$gms = array(
					"0" => SURVIVAL,
					"survival" => SURVIVAL,
					"s" => SURVIVAL,
					"1" => CREATIVE,
					"creative" => CREATIVE,
					"c" => CREATIVE,
					"2" => ADVENTURE,
					"adventure" => ADVENTURE,
					"a" => ADVENTURE,
					"3" => VIEW,
					"view" => VIEW,
					"viewer" => VIEW,
					"spectator" => VIEW,
					"v" => VIEW,
				);
				if($issuer instanceof Player){
					$player = $issuer;
				}
				if(isset($params[1])){
					$player = $this->server->api->player->get($params[1]);
				}
				if(!($player instanceof Player) or !isset($gms[strtolower($params[0])])){
					$output .= "Usage: /$cmd <mode> [player]\n";
					break;
				}
				if($player->setGamemode($gms[strtolower($params[0])])){
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
					$output .= "Usage: /$cmd [player] <target>\n";
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
					$output .= "Usage: /$cmd [player] <x> <y> <z>\n";
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
					$output .= "Usage: /$cmd [player]\n";
				}
				break;
			case "list":
				$output .= "There are ".count($this->server->clients)."/".$this->server->maxClients." players online:\n";
				if(count($this->server->clients) == 0){
					break;
				}
				foreach($this->server->clients as $c){
					$output .= $c->username.", ";
				}
				$output = substr($output, 0, -2)."\n";
				break;
		}
		return $output;
	}

	public function teleport(&$name, &$target){
		$player = $this->get($target);
		if(($player instanceof Player) and ($player->entity instanceof Entity)){
			$target = $player->username;
			return $this->tppos($name, $player->entity->x, $player->entity->y, $player->entity->z);
		}
		return false;
	}

	public function tppos(&$name, $x, $y, $z){
		$player = $this->get($name);
		if(($player instanceof Player) and ($player->entity instanceof Entity)){
			$name = $player->username;
			$player->teleport(new Vector3($x, $y, $z));
			return true;
		}
		return false;
	}

	public function get($name, $alike = true){
		$name = trim(strtolower($name));
		if($name === ""){
			return false;
		}
		$CID = $this->server->query("SELECT ip,port FROM players WHERE name ".($alike === true ? "LIKE '%".$name."%'":"= '".$name."'").";", true);
		$CID = PocketMinecraftServer::clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
	}

	public function getAll($level = null){
		if($level instanceof Level){
			$clients = array();
			$l = $this->server->query("SELECT EID FROM entities WHERE level = '".$this->level->getName()."' AND class = '".ENTITY_PLAYER."';");
			if($l !== false and $l !== true){
				while(($e = $l->fetchArray(SQLITE3_ASSOC)) !== false){
					$e = $this->getByEID($e["EID"]);
					if($e instanceof Player){
						$clients[$e->clientID] = $e->player;
					}
				}
			}
			return $clients;
		}
		return $this->server->clients;
	}

	public function getByEID($eid){
		$eid = (int) $eid;
		$CID = $this->server->query("SELECT ip,port FROM players WHERE EID = '".$eid."';", true);
		$CID = PocketMinecraftServer::clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
	}

	public function getByClientID($clientID){
		$clientID = (int) $clientID;
		$CID = $this->server->query("SELECT ip,port FROM players WHERE clientID = '".$clientID."';", true);
		$CID = PocketMinecraftServer::clientID($CID["ip"], $CID["port"]);
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
			$player->data = $this->getOffline($player->username);
			$player->gamemode = $player->data->get("gamemode");
			if(($player->level = $this->server->api->level->get($player->data->get("position")["level"])) === false){
				$player->level = $this->server->api->level->getDefault();
			}
			$this->server->query("INSERT OR REPLACE INTO players (clientID, ip, port, name) VALUES (".$player->clientID.", '".$player->ip."', ".$player->port.", '".strtolower($player->username)."');");
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
			"caseusername" => $name,
			"position" => array(
				"level" => $this->server->spawn->level->getName(),
				"x" => $this->server->spawn->x,
				"y" => $this->server->spawn->y,
				"z" => $this->server->spawn->z,
			),
			"spawn" => array(
				"level" => $this->server->spawn->level->getName(),
				"x" => $this->server->spawn->x,
				"y" => $this->server->spawn->y,
				"z" => $this->server->spawn->z,
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
		if(($this->server->gamemode & 0x01) === 0x01){
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