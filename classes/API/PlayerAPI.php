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

class PlayerAPI{
	private $server;
	function __construct($server){
		$this->server = $server;
	}
	
	public function init(){
		$this->server->api->console->register("list", "Shows connected player list", array($this, "commandHandler"));
		$this->server->api->console->register("kill", "Kills a player", array($this, "commandHandler"));
	}
	
	public function commandHandler($cmd, $params){
		switch($cmd){
			case "kill":
				$player = $this->get(implode(" ", $params));
				if($player !== false){
					$this->server->trigger("onHealthChange", array("eid" => $player->eid, "health" => -1, "cause" => "console"));
				}else{
					console("[INFO] Usage: /kill <player>");
				}
				break;
			case "list":
				console("[INFO] Player list:");
				foreach($this->server->clients as $c){
					console("[INFO] ".$c->username." (".$c->ip.":".$c->port."), ClientID ".$c->clientID);
				}
				break;
		}
	}
	
	public function get($name){
		$CID = $this->server->query("SELECT ip,port FROM players WHERE name = '".str_replace("'", "", $name)."';", true);
		$CID = $this->server->clientID($CID["ip"], $CID["port"]);
		if(isset($this->server->clients[$CID])){
			return $this->server->clients[$CID];
		}
		return false;
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
			console("[INFO] Player \"".$player->username."\" connected from ".$player->ip.":".$player->port);
			$player->data = $this->getOffline($player->username);
			$this->server->query("INSERT OR REPLACE INTO players (clientID, EID, ip, port, name) VALUES (".$player->clientID.", ".$player->eid.", '".$player->ip."', ".$player->port.", '".$player->username."');");
		}
	}
	
	public function remove($CID){
		if(isset($this->server->clients[$CID])){
			$player = $this->server->clients[$CID];
			if(is_object($player->entity)){
				$player->entity->close();
			}
			$this->saveOffline($player->username, $player->data);
			$this->server->query("DELETE FROM players WHERE name = '".$player->username."';");
			unset($this->server->entities[$player->eid]);
			unset($this->server->clients[$player->CID]);
		}
	}
	
	public function getOffline($name){
		if(!file_exists(FILE_PATH."data/players/".$name.".dat")){
			console("[NOTICE] Player data not found for \"".$name."\", creating new");
			$data = array(
				"spawn" => array(
					"x" => $this->server->spawn["x"],
					"y" => $this->server->spawn["y"],
					"z" => $this->server->spawn["z"],
				),
				"health" => 20,
				"lastIP" => "",
				"lastID" => "",
			);
			$this->saveOffline($name, $data);
		}else{
			$data = unserialize(file_get_contents(FILE_PATH."data/players/".$name.".dat"));
		}
		return $data;
	}
	
	public function saveOffline($name, $data){
		file_put_contents(FILE_PATH."data/players/".str_replace("/", "", $name).".dat", serialize($data));
	}	
}