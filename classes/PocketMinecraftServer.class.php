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

require_once("classes/Session.class.php");

class PocketMinecraftServer{
	var $seed, $protocol, $gamemode, $name, $maxClients, $clients, $eidCnt, $custom, $description, $motd, $timePerSecond, $responses, $spawn, $entities, $mapDir, $mapName, $map, $level, $tileEntities;
	private $database, $interface, $cnt, $events, $version, $serverType, $lastTick;
	function __construct($name, $gamemode = 1, $seed = false, $protocol = CURRENT_PROTOCOL, $port = 19132, $serverID = false, $version = CURRENT_VERSION){
		$this->port = (int) $port;
		console("[INFO] Starting Minecraft PE Server at *:".$this->port);
		console("[INFO] Loading database...");
		$this->startDatabase();
		$this->gamemode = (int) $gamemode;
		$this->version = (int) $version;
		$this->name = $name;
		$this->mapDir = false;
		$this->mapName = false;
		$this->map = false;
		$this->level = false;
		$this->difficulty = 1;
		$this->tileEntities = array();
		$this->entities = array();
		$this->custom = array();
		$this->cnt = 1;
		$this->eidCnt = 1;
		$this->maxClients = 20;
		$this->description = "";
		$this->whitelist = false;
		$this->bannedIPs = array();
		$this->motd = "Welcome to ".$name;
		$this->serverID = $serverID === false ? Utils::readLong(Utils::getRandomBytes(8)):$serverID;
		$this->seed = $seed === false ? Utils::readInt(Utils::getRandomBytes(4)):$seed;
		$this->clients = array();
		$this->protocol = (int) $protocol;
		$this->spawn = array("x" => 128.5,"y" => 100,"z" =>  128.5);
		$this->time = 0;
		$this->timePerSecond = 10;
		console("[INFO] Loading events...");
		$this->loadEvents();
		$this->setType("normal");
		$this->interface = new MinecraftInterface("255.255.255.255", $this->protocol, $this->port, true, false);		
		$this->reloadConfig();
		console("[INFO] Server Name: ".$this->name);
		console("[INFO] Server GUID: ".$this->serverID);
		console("[INFO] Protocol Version: ".$this->protocol);
		console("[INFO] Max Clients: ".$this->maxClients);
		$this->stop = false;
	}
	
	public function loadEvents(){
		$this->events = array("disabled" => array());
		
		$this->event("onChat", "eventHandler", true);
		$this->event("onPlayerDeath", "eventHandler", true);
		$this->event("onPlayerAdd", "eventHandler", true);
		$this->event("onHealthChange", "eventHandler", true);
		
		$this->action(100000, '$this->time += ceil($this->timePerSecond / 10);$this->trigger("onTimeChange", $this->time);');
		$this->action(5000000, '$this->trigger("onHealthRegeneration", 1);');
		$this->action(1000000 * 60, '$this->reloadConfig();');
		$this->action(1000000 * 60 * 10, '$this->custom = array();');
		$this->action(1000000 * 80, '$list = ""; foreach($this->clients as $c){$list .= ", ".$c->username;}$this->chat(false, count($this->clients)."/".$this->maxClients." online: ".substr($list, 2));');
		$this->action(1000000 * 75, '$this->debugInfo(true);');
	}

	public function startDatabase(){
		$this->database = new SQLite3(":memory:");
		$this->query("CREATE TABLE clients (clientID INTEGER PRIMARY KEY, EID NUMERIC, ip TEXT, port NUMERIC, name TEXT);");
		$this->query("CREATE TABLE entities (EID INTEGER PRIMARY KEY, type NUMERIC, class NUMERIC, name TEXT, x NUMERIC, y NUMERIC, z NUMERIC, yaw NUMERIC, pitch NUMERIC, health NUMERIC);");
		$this->query("CREATE TABLE metadata (EID INTEGER PRIMARY KEY, name TEXT, value TEXT);");
		$this->query("CREATE TABLE actions (ID INTEGER PRIMARY KEY, interval NUMERIC, last NUMERIC, code TEXT, repeat NUMERIC);");
		$this->query("CREATE TABLE events (ID INTEGER PRIMARY KEY, eventName TEXT, disabled INTEGER);");
	}
	
	public function query($sql, $fetch = false){
		$result = $this->database->query($sql) or console("[ERROR] [SQL Error] ".$this->database->lastErrorMsg().". Query: ".$sql, true, true, 0);
		if($fetch === true and ($result !== false and $result !== true)){
			$result = $result->fetchArray(SQLITE3_ASSOC);
		}
		return $result;
	}
	
	public function reloadConfig(){
		if($this->whitelist === true or is_array($this->whitelist)){
			$this->whitelist = explode("\n", str_replace(array("\t","\r"), "", file_get_contents(FILE_PATH."white-list.txt")));
		}
		$this->bannedIPs = explode("\n", str_replace(array(" ","\t","\r"), "", file_get_contents(FILE_PATH."banned-ips.txt")));
	}
	
	public function debugInfo($console = false){
		$info = array();
		$info["memory_usage"] = round((memory_get_usage(true) / 1024) / 1024, 2)."MB";
		$info["memory_peak_usage"] = round((memory_get_peak_usage(true) / 1024) / 1024, 2)."MB";
		$info["entities"] = $this->query("SELECT count(EID) as count FROM entities;", true);
		$info["entities"] = $info["entities"]["count"];
		$info["events"] = $this->query("SELECT count(ID) as count FROM events;", true);
		$info["events"] = $info["events"]["count"];
		$info["actions"] = $this->query("SELECT count(ID) as count FROM actions;", true);
		$info["actions"] = $info["actions"]["count"];
		$info["garbage"] = gc_collect_cycles();
		if($console === true){
			console("[DEBUG] Memory usage: ".$info["memory_usage"]." (Peak ".$info["memory_peak_usage"]."), Entities: ".$info["entities"].", Events: ".$info["events"].", Actions: ".$info["actions"].", Garbage: ".$info["garbage"], true, true, 2);
		}
		return $info;
	}
	
	public function close($reason = "stop"){	
		$this->chat(false, "Stopping server...");
		$this->save();
		$this->stop = true;
		$this->trigger("onClose");
	}
	
	public function chat($owner, $text, $target = true){
		$message = "";
		if($owner !== false){
			$message = "<".$owner."> ";
		}
		$message .= $text;
		$this->trigger("onChat", $text);
	}
	
	public function setType($type = "normal"){
		switch($type){
			case "normal":
				$this->serverType = "MCCPP;Demo;";
				break;
			case "minecon":
				$this->serverType = "MCCPP;MINECON;";
				break;
		}
		
	}
	
	public function eventHandler($data, $event){
		switch($event){
			case "onPlayerDeath":
				$message = $data["name"];
				if(is_numeric($data["cause"]) and isset($this->entities[$data["cause"]])){
					switch($this->entities[$data["cause"]]->class){
						case ENTITY_PLAYER:
							$message .= " was killed by ".$this->entities[$data["cause"]]->name;
							break;
						default:
							$message .= " was killed";
							break;
					}
				}else{
					switch($data["cause"]){
						default:
							$message .= " was killed";
							break;
					}
				}
				$this->chat(false, $message);
				break;
			case "onHealthChange":
				if($data["health"] <= 0){
					$this->trigger("onDeath", array("eid" => $data["eid"], "cause" => $data["cause"]));
				}
				break;
			case "onPlayerAdd":
				console("[DEBUG] Player \"".$data["username"]."\" EID ".$data["eid"]." spawned at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
				break;
			case "onChat":
				console("[CHAT] $data");
				break;
		}
	}
	
	private function loadMap(){
		if($this->mapName !== false and trim($this->mapName) !== ""){			
			$this->level = unserialize(file_get_contents($this->mapDir."level.dat"));
			console("[INFO] Map: ".$this->level["LevelName"]);
			$this->time = (int) $this->level["Time"];
			$this->level["Time"] = &$this->time;
			console("[INFO] Time: ".$this->time);
			console("[INFO] Seed: ".$this->seed);
			console("[INFO] Gamemode: ".($this->gamemode === 0 ? "survival":"creative"));
			$d = array(0 => "peaceful", 1 => "easy", 2 => "normal", 3 => "hard");
			console("[INFO] Difficulty: ".$d[$this->difficulty]);
			console("[INFO] Loading map...");
			$this->map = new ChunkParser();
			if(!$this->map->loadFile($this->mapDir."chunks.dat")){
				console("[ERROR] Couldn't load the map \"".$this->level["LevelName"]."\"!", true, true, 0);
				$this->map = false;
			}else{
				$this->map->loadMap();
			}
			console("[INFO] Loading entities...");
			$entities = unserialize(file_get_contents($this->mapDir."entities.dat"));
			foreach($entities as $entity){
				$this->entities[$this->eidCnt] = new Entity($this->eidCnt, ENTITY_MOB, $entity["id"], $this);
				$this->entities[$this->eidCnt]->setPosition($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2], $entity["Rotation"][0], $entity["Rotation"][1]);
				$this->entities[$this->eidCnt]->setHealth($entity["Health"]);
				++$this->eidCnt;
			}
			console("[DEBUG] Loaded ".count($this->entities)." Entities", true, true, 2);
			$this->action(1000000 * 60 * 15, '$this->chat(false, "Forcing save...");$this->save();$this->chat(false, "Done");');
		}else{
			console("[INFO] Time: ".$this->time);
			console("[INFO] Seed: ".$this->seed);
			console("[INFO] Gamemode: ".($this->gamemode === 0 ? "survival":"creative"));
		}
	}
	
	public function save(){
		if($this->mapName !== false){	
			file_put_contents($this->mapDir."level.dat", serialize($this->level));
		}
	}
	
	public function start(){
		declare(ticks=15);
		register_tick_function(array($this, "tick"));
		$this->event("onTick", "tickerFunction", true);
		$this->event("onReceivedPacket", "packetHandler", true);
		register_shutdown_function(array($this, "close"));
		$this->loadMap();
		console("[INFO] Server started!");
		$this->process();
	}
	
	public function tick(){
		$time = microtime(true);
		if($this->lastTick <= ($time - 0.05)){
			$this->lastTick = $time;
			$this->trigger("onTick", $time);
		}
	}
	
	public function clientID($ip, $port){
		return md5($pi . $port, true);
	}
	
	public function packetHandler($packet, $event){
		$data =& $packet["data"];
		$CID = $this->clientID($packet["ip"], $packet["port"]);
		if(isset($this->clients[$CID])){
			$this->clients[$CID]->handle($packet["pid"], $data);
		}else{
			switch($packet["pid"]){
				case 0x02:
					if(in_array($packet["ip"], $this->bannedIPs)){
						$this->send(0x1c, array(
							$data[0],
							$this->serverID,
							MAGIC,
							$this->serverType. $this->name . " [You're banned]",
						), false, $packet["ip"], $packet["port"]);
						break;
					}
					if(!isset($this->custom["times_".$CID])){
						$this->custom["times_".$CID] = 0;
					}
					$ln = 15;
					$txt = substr($this->description, $this->custom["times_".$CID], $ln);
					$txt .= substr($this->description, 0, $ln - strlen($txt));
					$this->send(0x1c, array(
						$data[0],
						$this->serverID,
						MAGIC,
						$this->serverType. $this->name . " [".($this->gamemode === 1 ? "C":"S").($this->whitelist !== false ? "W":"")." ".count($this->clients)."/".$this->maxClients."] ".$txt,
					), false, $packet["ip"], $packet["port"]);
					$this->custom["times_".$CID] = ($this->custom["times_".$CID] + 1) % strlen($this->description);
					break;
				case 0x05:
					if(in_array($packet["ip"], $this->bannedIPs)){
						break;
					}
					if(count($this->clients) >= $this->maxClients){
						break;
					}
					$version = $data[1];
					$size = strlen($data[2]);
					if($version !== $this->protocol){
						$this->send(0x1a, array(
							5,
							MAGIC,
							$this->serverID,
						), false, $packet["ip"], $packet["port"]);
					}else{
						$this->send(0x06, array(
							MAGIC,
							$this->serverID,
							0,
							strlen($packet["raw"]),
						), false, $packet["ip"], $packet["port"]);
					}
					break;
				case 0x07:
					if(in_array($packet["ip"], $this->bannedIPs)){
						break;
					}
					if(count($this->clients) >= $this->maxClients){
						break;
					}
					$port = $data[2];
					$MTU = $data[3];
					$clientID = $data[4];
					$eid = $this->eidCnt++;
					$this->clients[$CID] = new Session($this, $clientID, $eid, $packet["ip"], $packet["port"]);
					$this->clients[$CID]->handle(0x07, $data);
					break;
			}
		}
	}
	
	public function send($pid, $data = array(), $raw = false, $dest = false, $port = false){
		$this->trigger($pid, $data);
		$this->trigger("onSentPacket", $data);
		$this->interface->writePacket($pid, $data, $raw, $dest, $port);
	}
	
	public function process(){
		while($this->stop === false){
			$packet = $this->interface->readPacket();
			if($packet !== false){
				$this->trigger("onReceivedPacket", $packet);
				$this->trigger($packet["pid"], $packet);
			}else{
				usleep(10000);
			}			
		}
	}
	
	public function trigger($event, $data = ""){
		$events = $this->query("SELECT ID FROM events WHERE eventName = '".$event."' AND disabled = 0;");
		if($events === false or $events === true){
			return;
		}
		console("[INTERNAL] Event ". $event, true, true, 3);
		while($evn = $events->fetchArray(SQLITE3_ASSOC)){
			$ev = $this->events[$event][$evn["ID"]];
			if(isset($ev[1]) and ($ev[1] === true or is_object($ev[1]))){
				$this->responses[$evn["ID"]] = call_user_func(array(($ev[1] === true ? $this:$ev[1]), $ev[0]), $data, $event, $this);
			}else{
				$this->responses[$evn["ID"]] = call_user_func($ev[0], $data, $event, $this);
			}
		}
	}

	public function response($eid){
		if(isset($this->responses[$eid])){
			$res = $this->responses[$eid];
			unset($this->responses[$eid]);
			return $res;
		}
		return false;
	}	
	
	public function action($microseconds, $code, $repeat = true){
		$this->query("INSERT INTO actions (interval, last, code, repeat) VALUES(".($microseconds / 1000000).", ".microtime(true).", '".str_replace("'", "\\'", $code)."', ".($repeat === true ? 1:0).");");
		console("[INTERNAL] Attached to action ".$microseconds, true, true, 3);
	}

	public function tickerFunction(){
		//actions that repeat every x time will go here
		$time = microtime(true);
		$actions = $this->query("SELECT ID,code,repeat FROM actions WHERE last <= (".$time." - interval);");
		if($actions === false or $actions === true){
			return;
		}
		while($action = $actions->fetchArray(SQLITE3_ASSOC)){
			eval($action["code"]);
			if($action["repeat"] === 0){
				$this->query("DELETE FROM actions WHERE ID = ".$action["ID"].";");
			}
		}
		$this->query("UPDATE actions SET last = ".$time." WHERE last <= (".$time." - interval);");
	}	
	
	public function toggleEvent($event){
		if(isset($this->events["disabled"][$event])){
			unset($this->events["disabled"][$event]);
			$this->query("UPDATE events SET disabled = 0 WHERE eventName = '".$event."';");
			console("[INTERNAL] Enabled event ".$event, true, true, 3);
		}else{
			$this->events["disabled"][$event] = false;
			$this->query("UPDATE events SET disabled = 1 WHERE eventName = '".$event."';");
			console("[INTERNAL] Disabled event ".$event, true, true, 3);
		}
	}
	
	public function event($event, $func, $in = false){
		++$this->cnt;
		if(!isset($this->events[$event])){
			$this->events[$event] = array();
		}
		$this->query("INSERT INTO events (ID, eventName, disabled) VALUES (".$this->cnt.", '".str_replace("'", "\\'", $event)."', 0);");
		$this->events[$event][$this->cnt] = array($func, $in);
		console("[INTERNAL] Attached to event ".$event, true, true, 3);
		return $this->cnt;
	}
	
	public function deleteEvent($event, $id = -1){
		$id = (int) $id;
		if($id === -1){
			unset($this->events[$event]);
			$this->query("DELETE FROM events WHERE eventName = '".str_replace("'", "\\'", $event)."';");
		}else{
			unset($this->events[$event][$id]);
			$this->query("DELETE FROM events WHERE ID = ".$id.";");
			if(isset($this->events[$event]) and count($this->events[$event]) === 0){
				unset($this->events[$event]);
				$this->query("DELETE FROM events WHERE eventName = '".str_replace("'", "\\'", $event)."';");
			}
		}
	}
	
}