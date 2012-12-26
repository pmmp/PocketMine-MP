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

class PocketMinecraftServer extends stdClass{
	var $invisible, $tickMeasure, $preparedSQL, $seed, $protocol, $gamemode, $name, $maxClients, $clients, $eidCnt, $custom, $description, $motd, $timePerSecond, $responses, $spawn, $entities, $mapDir, $mapName, $map, $level, $tileEntities;
	private $database, $interface, $evCnt, $handCnt, $events, $handlers, $version, $serverType, $lastTick;
	function __construct($name, $gamemode = 1, $seed = false, $protocol = CURRENT_PROTOCOL, $port = 19132, $serverID = false, $version = CURRENT_VERSION){
		$this->port = (int) $port; //19132 - 19135
		console("[INFO] PocketMine-MP ".MAJOR_VERSION." by @shoghicp, LGPL License. http://bit.ly/TbrimG", true, true, 0);
		console("[INFO] Starting Minecraft PE Server at *:".$this->port);
		if($this->port < 19132 or $this->port > 19135){
			console("[WARNING] You've selected a not-standard port. Normal port range is from 19132 to 19135 included");
		}
		console("[INFO] Loading database...");
		$this->startDatabase();
		$this->gamemode = (int) $gamemode;
		$this->version = (int) $version;
		$this->name = $name;
		$this->mapDir = false;
		$this->mapName = false;
		$this->events = array();
		$this->handlers = array();
		$this->map = false;
		$this->invisible = false;
		$this->level = false;
		$this->difficulty = 1;
		$this->tileEntities = array();
		$this->entities = array();
		$this->custom = array();
		$this->evCnt = 0;
		$this->handCnt = 0;
		$this->eidCnt = 1;
		$this->maxClients = 20;
		$this->schedule = array();
		$this->scheduleCnt = 0;
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
		$this->tickMeasure = array_fill(0, 40, 0);
		$this->setType("normal");
		$this->interface = new MinecraftInterface("255.255.255.255", $this->protocol, $this->port, true, false);		
		$this->reloadConfig();
		console("[INFO] Server Name: ".$this->name);
		console("[INFO] Server GUID: ".$this->serverID);
		console("[INFO] Protocol Version: ".$this->protocol);
		console("[INFO] Max Clients: ".$this->maxClients);
		$this->stop = false;
	}
	
	public function getTPS(){
		$v = array_values($this->tickMeasure);
		$tps = 40 / ($v[39] - $v[0]);
		return round($tps, 4);
	}
	
	public function loadEvents(){		
		$this->event("server.chat", array($this, "eventHandler"));
		$this->event("player.new", array($this, "eventHandler"));
		
		$this->action(500000, '$this->time += (int) ($this->timePerSecond / 2);$this->trigger("server.time.change", $this->time);');
		$this->action(5000000, 'if($this->difficulty < 2){$this->trigger("server.regeneration", 1);}');
		$this->action(1000000 * 60, '$this->reloadConfig();');
		$this->action(1000000 * 60 * 10, '$this->custom = array();');
		if($this->api !== false){
			$this->action(1000000 * 80, '$this->chat(false, count($this->clients)."/".$this->maxClients." online: ".implode(", ",$this->api->player->online()));');
		}
		$this->action(1000000 * 75, '$this->debugInfo(true);');
	}

	public function startDatabase(){
		$this->preparedSQL = new stdClass();
		$this->database = new SQLite3(":memory:");
		//$this->query("PRAGMA journal_mode = OFF;");		
		//$this->query("PRAGMA encoding = \"UTF-8\";");
		//$this->query("PRAGMA secure_delete = OFF;");
		$this->query("CREATE TABLE players (clientID INTEGER PRIMARY KEY, EID NUMERIC, ip TEXT, port NUMERIC, name TEXT UNIQUE);");
		$this->query("CREATE TABLE entities (EID INTEGER PRIMARY KEY, type NUMERIC, class NUMERIC, name TEXT, x NUMERIC, y NUMERIC, z NUMERIC, yaw NUMERIC, pitch NUMERIC, health NUMERIC);");
		$this->query("CREATE TABLE metadata (EID INTEGER PRIMARY KEY, name TEXT, value TEXT);");
		$this->query("CREATE TABLE actions (ID INTEGER PRIMARY KEY, interval NUMERIC, last NUMERIC, code TEXT, repeat NUMERIC);");
		$this->query("CREATE TABLE events (ID INTEGER PRIMARY KEY, name TEXT);");
		$this->query("CREATE TABLE handlers (ID INTEGER PRIMARY KEY, name TEXT, priority NUMERIC);");
		//$this->query("PRAGMA synchronous = OFF;");
		$this->preparedSQL->selectHandlers = $this->database->prepare("SELECT ID FROM handlers WHERE name = :name ORDER BY priority DESC;");
		$this->preparedSQL->selectEvents = $this->database->prepare("SELECT ID FROM events WHERE name = :name;");
		$this->preparedSQL->selectActions = $this->database->prepare("SELECT ID,code,repeat FROM actions WHERE last <= (:time - interval);");
		$this->preparedSQL->updateActions = $this->database->prepare("UPDATE actions SET last = :time WHERE last <= (:time - interval);");
	}
	
	public function query($sql, $fetch = false){
		console("[INTERNAL] [SQL] ".$sql, true, true, 3);
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
		$info["tps"] = $this->getTPS();
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
			console("[DEBUG] TPS: ".$info["tps"].", Memory usage: ".$info["memory_usage"]." (Peak ".$info["memory_peak_usage"]."), Entities: ".$info["entities"].", Events: ".$info["events"].", Actions: ".$info["actions"].", Garbage: ".$info["garbage"], true, true, 2);
		}
		return $info;
	}
	
	public function close($reason = "stop"){
		if($this->stop !== true){
			$this->chat(false, "Stopping server...");
			$this->save();
			$this->stop = true;
			$this->trigger("server.close");
			$this->interface->close();
		}
	}
	
	public function chat($owner, $text, $target = true){
		$message = "";
		if($owner !== false){
			$message = "<".$owner."> ";
		}
		$message .= $text;
		$this->trigger("server.chat", $message);
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
	
	public function addHandler($event, $callable, $priority = 5){
		if(!is_callable($callable)){
			return false;
		}
		$priority = (int) $priority;
		$this->handlers[$this->handCnt] = $callable;
		$this->query("INSERT INTO handlers (ID, name, priority) VALUES (".$this->handCnt.", '".str_replace("'", "\\'", $event)."', ".$priority.");");
		console("[INTERNAL] New handler ".(is_array($callable) ? get_class($callable[0])."::".$callable[1]:$callable)." to special event ".$event." (ID ".$this->handCnt.")", true, true, 3);
		return $this->handCnt++;
	}
	
	public function handle($event, &$data){
		$this->preparedSQL->selectHandlers->reset();
		$this->preparedSQL->selectHandlers->clear();
		$this->preparedSQL->selectHandlers->bindValue(":name", $event, SQLITE3_TEXT);
		$handlers = $this->preparedSQL->selectHandlers->execute();
		$result = true;
		if($handlers !== false and $handlers !== true){			
			while(false !== ($hn = $handlers->fetchArray(SQLITE3_ASSOC)) and $result !== false){
				$handler = $this->handlers[(int) $hn["ID"]];
				if(is_array($handler)){
					$method = $handler[1];
					$result = $handler[0]->$method($data, $event);
				}else{
					$result = $handler($data, $event);
				}
			}					
		}
		$handlers->finalize();
		if($result !== false){
			$this->trigger($event, $data);
		}
		return $result;
	}
	
	public function eventHandler($data, $event){
		switch($event){
			case "player.new":
				console("[DEBUG] Player \"".$data["username"]."\" EID ".$data["eid"]." spawned at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
				break;
			case "server.chat":
				console("[CHAT] $data");
				break;
		}
	}
	
	public function loadMap(){
		if($this->mapName !== false and trim($this->mapName) !== ""){			
			$this->level = unserialize(file_get_contents($this->mapDir."level.dat"));
			console("[INFO] Map: ".$this->level["LevelName"]);
			$this->time = (int) $this->level["Time"];
			$this->seed = (int) $this->level["RandomSeed"];
			if(isset($this->level["SpawnX"])){			
				$this->spawn = array("x" => $this->level["SpawnX"], "y" => $this->level["SpawnY"], "z" => $this->level["SpawnZ"]);
			}else{
				$this->level["SpawnX"] = $this->spawn["x"];
				$this->level["SpawnY"] = $this->spawn["y"];
				$this->level["SpawnZ"] = $this->spawn["z"];
			}
			$this->level["Time"] = &$this->time;
			console("[INFO] Spawn: X ".$this->level["SpawnX"]." Y ".$this->level["SpawnY"]." Z ".$this->level["SpawnZ"]);
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
		}else{
			console("[INFO] Time: ".$this->time);
			console("[INFO] Seed: ".$this->seed);
			console("[INFO] Gamemode: ".($this->gamemode === 0 ? "survival":"creative"));
		}
	}
	
	public function loadEntities(){
		if($this->map !== false){
			console("[INFO] Loading entities...");
			$entities = unserialize(file_get_contents($this->mapDir."entities.dat"));
			foreach($entities as $entity){
				if(!isset($entity["id"])){
					break;
				}
				if(isset($this->api) and $this->api !== false){
					if($entity["id"] === 64){ //Item Drop
						$e = $this->api->entity->add(ENTITY_ITEM, $entity["Item"]["id"], array(
							"meta" => $entity["Item"]["Damage"],
							"stack" => $entity["Item"]["Count"],
							"x" => $entity["Pos"][0],
							"y" => $entity["Pos"][1],
							"z" => $entity["Pos"][2],
							"yaw" => $entity["Rotation"][0],
							"pitch" => $entity["Rotation"][1],
						));
					}else{
						$e = $this->api->entity->add(ENTITY_MOB, $entity["id"]);
						$e->setPosition($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2], $entity["Rotation"][0], $entity["Rotation"][1]);
						$e->setHealth($entity["Health"]);
					
					}
				}
			}
			console("[DEBUG] Loaded ".count($this->entities)." Entities", true, true, 2);
			$this->action(1000000 * 60 * 15, '$this->chat(false, "Forcing save...");$this->save();$this->chat(false, "Done");');
		}	
	}
	
	public function save(){
		if($this->mapName !== false){	
			file_put_contents($this->mapDir."level.dat", serialize($this->level));
			$this->map->saveMap();
			console("[INFO] Saving entities...");
			foreach($this->entities as $entity){
				
			}
		}
	}
	
	public function start(){
		if($this->mapName !== false and $this->map === false){
			$this->loadMap();
			$this->loadEntities();
		}
		console("[INFO] Loading events...");
		$this->loadEvents();
		declare(ticks=15);
		register_tick_function(array($this, "tick"));
		register_shutdown_function(array($this, "close"));
		$this->trigger("server.start", microtime(true));
		console("[INFO] Server started!");
		$this->process();
	}
	
	public function tick(){
		$time = microtime(true);
		if($this->lastTick <= ($time - 0.05)){
			array_shift($this->tickMeasure);
			$this->tickMeasure[] = $this->lastTick = $time;			
			$this->tickerFunction($time);
			$this->trigger("server.tick", $time);
		}
	}
	
	public function clientID($ip, $port){
		return md5($pi . $port, true);
	}
	
	public function packetHandler($packet){
		$data =& $packet["data"];
		$CID = $this->clientID($packet["ip"], $packet["port"]);
		if(isset($this->clients[$CID])){
			$this->clients[$CID]->handle($packet["pid"], $data);
		}else{
			switch($packet["pid"]){
				case 0x02:
					if($this->invisible === true){
						$this->send(0x1c, array(
							$data[0],
							$this->serverID,
							MAGIC,
							$this->serverType,
						), false, $packet["ip"], $packet["port"]);					
						break;
					}
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
					if(in_array($packet["ip"], $this->bannedIPs) or count($this->clients) >= $this->maxClients){
						$this->send(0x80, array(
							0,
							0x00,
							array(
								"id" => MC_LOGIN_STATUS,
								"status" => 1,
							),
						), false, $packet["ip"], $packet["port"]);
						$this->send(0x80, array(
							1,
							0x00,
							array(
								"id" => MC_DISCONNECT,
							),
						), false, $packet["ip"], $packet["port"]);
						break;
					}
					$version = $data[1];
					$size = strlen($data[2]);
					if($version !== $this->protocol){
						$this->send(0x1a, array(
							$this->protocol,
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
					if(in_array($packet["ip"], $this->bannedIPs) or count($this->clients) >= $this->maxClients){
						$this->send(0x80, array(
							0,
							0x00,
							array(
								"id" => MC_LOGIN_STATUS,
								"status" => 1,
							),
						), false, $packet["ip"], $packet["port"]);
						$this->send(0x80, array(
							1,
							0x00,
							array(
								"id" => MC_DISCONNECT,
							),
						), false, $packet["ip"], $packet["port"]);
						break;
					}
					$port = $data[2];
					$MTU = $data[3];
					$clientID = $data[4];
					$this->clients[$CID] = new Player($this, $clientID, $packet["ip"], $packet["port"], $MTU);
					$this->clients[$CID]->handle(0x07, $data);
					break;
			}
		}
	}
	
	public function send($pid, $data = array(), $raw = false, $dest = false, $port = false){
		$this->interface->writePacket($pid, $data, $raw, $dest, $port);
	}
	
	public function process(){
		while($this->stop === false){
			$packet = @$this->interface->readPacket();
			if($packet !== false){
				$this->packetHandler($packet);
			}else{
				usleep(1);
			}			
		}
	}
	
	public function trigger($event, $data = ""){
		$this->preparedSQL->selectEvents->reset();
		$this->preparedSQL->selectEvents->clear();
		$this->preparedSQL->selectEvents->bindValue(":name", $event, SQLITE3_TEXT);
		$events = $this->preparedSQL->selectEvents->execute();
		if($events === false or $events === true){
			return;
		}
		while(false !== ($evn = $events->fetchArray(SQLITE3_ASSOC))){
			$evid = (int) $evn["ID"];
			$this->responses[$evid] = call_user_func($this->events[$evid], $data, $event);		
		}
		$events->finalize();
		return true;
	}

	public function response($eid){
		if(isset($this->responses[$eid])){
			$res = $this->responses[$eid];
			unset($this->responses[$eid]);
			return $res;
		}
		return false;
	}
	
	public function schedule($ticks, $callback, $data = array(), $repeat = false, $eventName = "server.schedule"){
		if(!is_callable($callback)){
			return false;
		}
		$add = "";
		if($repeat === false){
			$add = ' unset($this->schedule['.$this->scheduleCnt.']);';
		}
		$this->schedule[$this->scheduleCnt] = array($callback, $data, $eventName);
		$this->action(50000 * $ticks, '$schedule = $this->schedule['.$this->scheduleCnt.'];'.$add.' call_user_func($schedule[0], $schedule[1], $schedule[2]);', (bool) $repeat);
		return $this->scheduleCnt++;
	}
	
	public function action($microseconds, $code, $repeat = true){
		$this->query("INSERT INTO actions (interval, last, code, repeat) VALUES(".($microseconds / 1000000).", ".microtime(true).", '".base64_encode($code)."', ".($repeat === true ? 1:0).");");
		console("[INTERNAL] Attached to action ".$microseconds, true, true, 3);
	}

	public function tickerFunction($time){
		//actions that repeat every x time will go here
		$this->preparedSQL->selectActions->reset();
		$this->preparedSQL->selectActions->clear();
		$this->preparedSQL->selectActions->bindValue(":time", $time, SQLITE3_FLOAT);
		$actions = $this->preparedSQL->selectActions->execute();
		
		if($actions === false or $actions === true){
			return;
		}
		while(false !== ($action = $actions->fetchArray(SQLITE3_ASSOC))){
			eval(base64_decode($action["code"]));
			if($action["repeat"] === 0){
				$this->query("DELETE FROM actions WHERE ID = ".$action["ID"].";");
			}
		}
		$actions->finalize();
		$this->preparedSQL->updateActions->reset();
		$this->preparedSQL->updateActions->clear();
		$this->preparedSQL->updateActions->bindValue(":time", $time, SQLITE3_FLOAT);
		$this->preparedSQL->updateActions->execute();
	}	
	
	public function event($event, $func){
		if(!is_callable($func)){
			return false;
		}
		$this->events[$this->evCnt] = $func;
		$this->query("INSERT INTO events (ID, name) VALUES (".$this->evCnt.", '".str_replace("'", "\\'", $event)."');");
		console("[INTERNAL] Attached ".(is_array($func) ? get_class($func[0])."::".$func[1]:$func)." to event ".$event." (ID ".$this->evCnt.")", true, true, 3);
		return $this->evCnt++;
	}
	
	public function deleteEvent($id){
		$id = (int) $id;
		unset($this->events[$id]);
		$this->query("DELETE FROM events WHERE ID = ".$id.";");
	}
	
}