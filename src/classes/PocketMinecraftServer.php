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

class PocketMinecraftServer{
	public $tCnt;
	var $version, $invisible, $api, $tickMeasure, $preparedSQL, $seed, $gamemode, $name, $maxClients, $clients, $eidCnt, $custom, $description, $motd, $timePerSecond, $spawn, $entities, $mapDir, $mapName, $map, $levelData, $tileEntities;
	private $database, $interface, $evCnt, $handCnt, $events, $handlers, $serverType, $lastTick, $ticker;
	
	private function load(){
		$this->version = new VersionString();
		console("[INFO] \x1b[33;1mPocketMine-MP ".MAJOR_VERSION." #".$this->version->getNumber()." by @shoghicp, LGPL License", true, true, 0);
		console("[INFO] Target Minecraft PE: \x1b[36;1m".CURRENT_MINECRAFT_VERSION."\x1b[0m, protocol #".CURRENT_PROTOCOL, true, true, 0);
		if($this->version->isDev()){
			console("[INFO] \x1b[31;1mThis is a Development version");
		}
		console("[INFO] Starting Minecraft PE Server at *:".$this->port);
		if($this->port < 19132 or $this->port > 19135){
			console("[WARNING] You've selected a not-standard port. Normal port range is from 19132 to 19135 included");
		}
		$this->serverID = $this->serverID === false ? Utils::readLong(Utils::getRandomBytes(8)):$this->serverID;
		$this->seed = $this->seed === false ? Utils::readInt(Utils::getRandomBytes(4)):$this->seed;
		console("[INFO] Loading database...");
		$this->startDatabase();
		$this->doTick = false;
		$this->api = false;	
		$this->tCnt = 1;
		$this->mapDir = false;
		$this->mapName = false;
		$this->events = array();
		$this->handlers = array();
		$this->map = false;
		$this->invisible = false;
		$this->levelData = false;
		$this->difficulty = 1;
		$this->tileEntities = array();
		$this->entities = array();
		$this->custom = array();
		$this->evCnt = 1;
		$this->handCnt = 1;
		$this->eidCnt = 1;
		$this->maxClients = 20;
		$this->schedule = array();
		$this->scheduleCnt = 1;
		$this->description = "";
		$this->whitelist = false;
		$this->clients = array();
		$this->spawn = array("x" => 128.5,"y" => 100,"z" =>  128.5);
		$this->time = 0;
		$this->timePerSecond = 10;
		$this->tickMeasure = array_fill(0, 40, 0);
		$this->setType("normal");
		$this->interface = new MinecraftInterface("255.255.255.255", $this->port, true, false);
		$this->reloadConfig();
		console("[INFO] Server Name: \x1b[36m".$this->name."\x1b[0m");
		console("[DEBUG] Server ID: ".$this->serverID, true, true, 2);
		$this->stop = false;	
	}
	
	function __construct($name, $gamemode = 1, $seed = false, $port = 19132, $serverID = false){
		$this->port = (int) $port; //19132 - 19135
		$this->gamemode = (int) $gamemode;
		$this->name = $name;
		$this->motd = "Welcome to ".$name;
		$this->serverID = $serverID;
		$this->seed = $seed;
		$this->load();
	}

	public function getTPS(){
		$v = array_values($this->tickMeasure);
		$tps = 40 / ($v[39] - $v[0]);
		return round($tps, 4);
	}

	public function loadEvents(){
		$this->action(500000, '$this->time += (int) ($this->timePerSecond / 2);$this->api->dhandle("server.time", $this->time);');
		$this->action(5000000, 'if($this->difficulty < 2){$this->api->dhandle("server.regeneration", 1);}');
		$this->action(1000000 * 60, '$this->reloadConfig();');
		$this->action(1000000 * 60 * 10, '$this->custom = array();');
		if($this->api !== false){
			$this->action(1000000 * 80, '$cnt = count($this->clients); if($cnt > 1){$this->api->chat->broadcast("Online (".$cnt."): ".implode(", ",$this->api->player->online()));}');
		}
		$this->action(1000000 * 120, '$this->debugInfo(true);');
	}

	public function startDatabase(){
		$this->preparedSQL = new stdClass();
		$this->database = new SQLite3(":memory:");
		//$this->query("PRAGMA journal_mode = OFF;");
		//$this->query("PRAGMA encoding = \"UTF-8\";");
		//$this->query("PRAGMA secure_delete = OFF;");
		$this->query("CREATE TABLE players (clientID INTEGER PRIMARY KEY, EID NUMERIC, ip TEXT, port NUMERIC, name TEXT UNIQUE);");
		$this->query("CREATE TABLE entities (EID INTEGER PRIMARY KEY, type NUMERIC, class NUMERIC, name TEXT, x NUMERIC, y NUMERIC, z NUMERIC, yaw NUMERIC, pitch NUMERIC, health NUMERIC);");
		$this->query("CREATE TABLE tileentities (ID INTEGER PRIMARY KEY, class NUMERIC, x NUMERIC, y NUMERIC, z NUMERIC, spawnable NUMERIC);");
		$this->query("CREATE TABLE actions (ID INTEGER PRIMARY KEY, interval NUMERIC, last NUMERIC, code TEXT, repeat NUMERIC);");
		$this->query("CREATE TABLE events (ID INTEGER PRIMARY KEY, name TEXT);");
		$this->query("CREATE TABLE handlers (ID INTEGER PRIMARY KEY, name TEXT, priority NUMERIC);");
		//$this->query("PRAGMA synchronous = OFF;");
		$this->preparedSQL->selectHandlers = $this->database->prepare("SELECT DISTINCT ID FROM handlers WHERE name = :name ORDER BY priority DESC;");
		$this->preparedSQL->selectEvents = $this->database->prepare("SELECT DISTINCT ID FROM events WHERE name = :name;");
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
		$info["handlers"] = $this->query("SELECT count(ID) as count FROM handlers;", true);
		$info["handlers"] = $info["handlers"]["count"];
		$info["actions"] = $this->query("SELECT count(ID) as count FROM actions;", true);
		$info["actions"] = $info["actions"]["count"];
		$info["garbage"] = gc_collect_cycles();
		$this->handle("server.debug", $info);
		if($console === true){
			console("[DEBUG] TPS: ".$info["tps"].", Memory usage: ".$info["memory_usage"]." (Peak ".$info["memory_peak_usage"]."), Entities: ".$info["entities"].", Events: ".$info["events"].", Handlers: ".$info["handlers"].", Actions: ".$info["actions"].", Garbage: ".$info["garbage"], true, true, 2);
		}
		return $info;
	}

	public function close($reason = "stop"){
		if($this->stop !== true){
			if(is_int($reason)){
				$reason = "signal stop";
			}
			if(($this->api instanceof ServerAPI) === true){
				if(($this->api->chat instanceof ChatAPI) === true){
					$this->api->chat->send(false, "Stopping server...");
				}
			}
			//$this->ticker->stop = true;
			$this->save(true);
			$this->stop = true;
			$this->trigger("server.close", $reason);
			$this->interface->close();
		}
	}

	public function chat($owner, $text, $target = false){
		$this->api->chat->send($owner, $text, $target);
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
		}elseif(isset(Deprecation::$events[$event])){
			$sub = "";
			if(Deprecation::$events[$event] !== false){
				$sub = " Substitute \"".Deprecation::$events[$event]."\" found.";
			}
			console("[ERROR] Event \"$event\" has been deprecated.$sub [Adding handle to ".(is_array($callable) ? get_class($callable[0])."::".$callable[1]:$callable)."]");
		}
		$priority = (int) $priority;
		$hnid = $this->handCnt++;
		$this->handlers[$hnid] = $callable;
		$this->query("INSERT INTO handlers (ID, name, priority) VALUES (".$hnid.", '".str_replace("'", "\\'", $event)."', ".$priority.");");
		console("[INTERNAL] New handler ".(is_array($callable) ? get_class($callable[0])."::".$callable[1]:$callable)." to special event ".$event." (ID ".$hnid.")", true, true, 3);
		return $hnid;
	}

	public function handle($event, &$data){
		$this->preparedSQL->selectHandlers->reset();
		$this->preparedSQL->selectHandlers->clear();
		$this->preparedSQL->selectHandlers->bindValue(":name", $event, SQLITE3_TEXT);
		$handlers = $this->preparedSQL->selectHandlers->execute();
		$result = true;
		if($handlers !== false and $handlers !== true){
			console("[INTERNAL] Handling ".$event, true, true, 3);
			$call = array();
			while(($hn = $handlers->fetchArray(SQLITE3_ASSOC)) !== false){
				$call[(int) $hn["ID"]] = true;
			}
			$handlers->finalize();
			foreach($call as $hnid => $boolean){
				if($result !== false){
					$called[$hnid] = true;
					$handler = $this->handlers[$hnid];
					if(is_array($handler)){
						$method = $handler[1];
						$result = $handler[0]->$method($data, $event);
					}else{
						$result = $handler($data, $event);
					}
				}else{
					break;
				}
			}
		}elseif(isset(Deprecation::$events[$event])){
			$sub = "";
			if(Deprecation::$events[$event] !== false){
				$sub = " Substitute \"".Deprecation::$events[$event]."\" found.";
			}
			console("[ERROR] Event \"$event\" has been deprecated.$sub [Handler]");
		}
		
		if($result !== false){
			$this->trigger($event, $data);
		}
		return $result;
	}

	public function eventHandler($data, $event){
		switch($event){

		}
	}

	public function loadMap(){
		if($this->mapName !== false and trim($this->mapName) !== ""){
			$this->levelData = unserialize(file_get_contents($this->mapDir."level.dat"));
			if($this->levelData === false){
				console("[ERROR] Invalid world data for \"".$this->mapDir."\. Please import the world correctly");
				$this->close("invalid world data");
			}
			console("[INFO] Map: ".$this->levelData["LevelName"]);
			$this->time = (int) $this->levelData["Time"];
			$this->seed = (int) $this->levelData["RandomSeed"];
			if(isset($this->levelData["SpawnX"])){
				$this->spawn = array("x" => $this->levelData["SpawnX"], "y" => $this->levelData["SpawnY"], "z" => $this->levelData["SpawnZ"]);
			}else{
				$this->levelData["SpawnX"] = $this->spawn["x"];
				$this->levelData["SpawnY"] = $this->spawn["y"];
				$this->levelData["SpawnZ"] = $this->spawn["z"];
			}
			$this->levelData["Time"] = $this->time;
			console("[INFO] Spawn: X \x1b[36m".$this->levelData["SpawnX"]."\x1b[0m Y \x1b[36m".$this->levelData["SpawnY"]."\x1b[0m Z \x1b[36m".$this->levelData["SpawnZ"]."\x1b[0m");
			console("[INFO] Time: \x1b[36m".$this->time."\x1b[0m");
			console("[INFO] Seed: \x1b[36m".$this->seed."\x1b[0m");
			console("[INFO] Gamemode: \x1b[36m".($this->gamemode === 0 ? "survival":"creative")."\x1b[0m");
			$d = array(0 => "peaceful", 1 => "easy", 2 => "normal", 3 => "hard");
			console("[INFO] Difficulty: \x1b[36m".$d[$this->difficulty]."\x1b[0m");
			console("[INFO] Loading map...");
			$this->map = new ChunkParser();
			if(!$this->map->loadFile($this->mapDir."chunks.dat")){
				console("[ERROR] Couldn't load the map \"\x1b[32m".$this->levelData["LevelName"]."\x1b[0m\"!", true, true, 0);
				$this->map = false;
			}else{
				$this->map->loadMap();
			}
		}else{
			console("[INFO] Time: \x1b[36m".$this->time."\x1b[0m");
			console("[INFO] Seed: \x1b[36m".$this->seed."\x1b[0m");
			console("[INFO] Gamemode: \x1b[36m".($this->gamemode === 0 ? "survival":"creative")."\x1b[0m");
		}
	}

	public function loadEntities(){
		if($this->map !== false){
			console("[INFO] Loading entities...");
			$entities = unserialize(file_get_contents($this->mapDir."entities.dat"));
			if($entities === false or !is_array($entities)){
				console("[ERROR] Invalid world data for \"".$this->mapDir."\. Please import the world correctly");
				$this->close("invalid world data");
			}
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
						$e = $this->api->entity->add(ENTITY_MOB, $entity["id"], $entity);
						$e->setPosition($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2], $entity["Rotation"][0], $entity["Rotation"][1]);
						$e->setHealth($entity["Health"]);

					}
				}
			}
			$tiles = unserialize(file_get_contents($this->mapDir."tileEntities.dat"));
			foreach($tiles as $tile){
				if(!isset($tile["id"])){
					break;
				}
				$class = false;
				switch($tile["id"]){
					case "Sign":
						$class = TILE_SIGN;
						break;
				}
				$t = $this->api->tileentity->add($class, $tile["x"], $tile["y"], $tile["z"], $tile);
			}
			$this->action(1000000 * 60 * 15, '$this->api->chat->broadcast("Forcing save...");$this->save();');
		}
	}

	public function save($final = false){
		if($this->mapName !== false){
			$this->levelData["Time"] = $this->time;
			file_put_contents($this->mapDir."level.dat", serialize($this->levelData));
			$this->map->saveMap($final);
			$this->trigger("server.save", $final);
			console("[INFO] Saving entities...");
			if(count($this->entities) > 0){
				$entities = array();
				foreach($this->entities as $entity){
					if($entity->class === ENTITY_MOB){
						$entities[] = array(
							"id" => $entity->type,
							"Color" => @$entity->data["Color"],
							"Sheared" => @$entity->data["Sheared"],
							"Health" => $entity->health,
							"Pos" => array(
								0 => $entity->x,
								1 => $entity->y,
								2 => $entity->z,
							),
							"Rotation" => array(
								0 => $entity->yaw,
								1 => $entity->pitch,
							),				
						);
					}elseif($entity->class === ENTITY_ITEM){
						$entities[] = array(
							"id" => 64,
							"Item" => array(
								"id" => $entity->type,
								"Damage" => $entity->meta,
								"Count" => $entity->stack,
							),
							"Health" => $entity->health,
							"Pos" => array(
								0 => $entity->x,
								1 => $entity->y,
								2 => $entity->z,
							),
							"Rotation" => array(
								0 => 0,
								1 => 0,
							),				
						);					
					}
				}
				file_put_contents($this->mapDir."entities.dat", serialize($entities));
			}
			if(count($this->tileEntities) > 0){
				$tiles = array();
				foreach($this->tileEntities as $tile){
					$tiles[] = $tile->data;
				}
				file_put_contents($this->mapDir."tileEntities.dat", serialize($tiles));
			}
		}
	}

	public function init(){
		if($this->mapName !== false and $this->map === false){
			$this->loadMap();
			$this->loadEntities();
		}
		console("[INFO] Loading events...");
		$this->loadEvents();
		//$this->ticker = new TickLoop($this);
		//$this->ticker->start();
		declare(ticks=15);
		register_tick_function(array($this, "tick"));
		register_shutdown_function(array($this, "dumpError"));
		register_shutdown_function(array($this, "close"));
		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, array($this, "close"));
			pcntl_signal(SIGINT, array($this, "close"));
			pcntl_signal(SIGHUP, array($this, "close"));
		}
		$this->trigger("server.start", microtime(true));
		console("[INFO] Server started!");
		$this->process();
	}
	
	public function dumpError(){
		console("[ERROR] An Unrecovereable has ocurred and the server has Crashed. Creating an Error Dump");
		$dump = "# PocketMine-MP Error Dump ".date("D M j H:i:s T Y")."\r\n";
		$dump .= "Error: ".var_export(error_get_last(), true)."\r\n\r\n";
		$version = new VersionString();
		$dump .= "PM Version: ".$version." #".$version->getNumber()." [Protocol ".CURRENT_PROTOCOL."]\r\n";
		$dump .= "uname -a: ".php_uname("a")."\r\n";
		$dump .= "PHP Version: " .phpversion()."\r\n";
		$dump .= "Zend version: ".zend_version()."\r\n";
		$dump .= "OS : " .PHP_OS.", ".Utils::getOS()."\r\n";
		$dump .= "Debug Info: ".var_export($this->debugInfo(false), true)."\r\n\r\n\r\n";
		global $arguments;
		$dump .= "Parameters: ".var_export($arguments, true)."\r\n\r\n\r\n";
		$dump .= "server.properties: ".var_export($this->api->getProperties(), true)."\r\n\r\n\r\n";
		global $lasttrace;
		$dump .= "Last Backtrace: ".$lasttrace."\r\n\r\n\r\n";
		$dump .= "Loaded Modules: ".var_export(get_loaded_extensions(), true)."\r\n\r\n";
		$name = "error_dump_".time();
		logg($dump, $name, true, 0, true);
		console("[ERROR] Please submit the \"logs/{$name}.log\" file to the Bug Reporting page. Give as much info as you can.", true, true, 0);
	}

	public function tick(){
		/*if($this->ticker->tick === true and $this->ticker->isWaiting() === true){
			$this->ticker->tick = false;
			$time = microtime(true);
			array_shift($this->tickMeasure);
			$this->tickMeasure[] = $this->lastTick = $time;
			$this->tickerFunction($time);
			$this->trigger("server.tick", $time);
			$this->ticker->notify();
		}*/
		$time = microtime(true);
		if($this->lastTick <= ($time - 0.05)){
			array_shift($this->tickMeasure);
			$this->tickMeasure[] = $this->lastTick = $time;
			$this->tickerFunction($time);
			$this->trigger("server.tick", $time);
		}
	}

	public function clientID($ip, $port){
		return md5($ip . $port, true);
	}

	public function packetHandler($packet){
		$data =& $packet["data"];
		$CID = $this->clientID($packet["ip"], $packet["port"]);
		if(isset($this->clients[$CID])){
			$this->clients[$CID]->handle($packet["pid"], $data);
		}else{
			if($this->handle("server.noauthpacket", $packet) === false){
				return;
			}
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
					if($this->api->ban->isIPBanned($packet["ip"])){
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
					$this->description .= " ";
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
					if($this->api->ban->isIPBanned($packet["ip"]) or count($this->clients) >= $this->maxClients){
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
					if($version !== CURRENT_PROTOCOL){
						$this->send(0x1a, array(
							CURRENT_PROTOCOL,
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
					if($this->api->ban->isIPBanned($packet["ip"]) or count($this->clients) >= $this->maxClients){
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
					$this->clients[$CID] = new Player($this, $clientID, $packet["ip"], $packet["port"], $MTU); //New Session!
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
			$packet = $this->interface->readPacket();
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
		$call = array();
		while(($evn = $events->fetchArray(SQLITE3_ASSOC)) !== false){
			$call[(int) $evn["ID"]] = true;
		}
		$events->finalize();
		if(count($call) > 0){
			foreach($call as $evid => $boolean){
				$ev = $this->events[$evid];
				if(!is_callable($ev)){
					$this->deleteEvent($evid);
					continue;
				}
				if(is_array($ev)){
					$method = $ev[1];
					$ev[0]->$method($data, $event);
				}else{
					$ev($data, $event);
				}
			}
		}elseif(isset(Deprecation::$events[$event])){
			$sub = "";
			if(Deprecation::$events[$event] !== false){
				$sub = " Substitute \"".Deprecation::$events[$event]."\" found.";
			}
			console("[ERROR] Event \"$event\" has been deprecated.$sub [Trigger]");
		}
		
		return true;
	}

	public function schedule($ticks, $callback, $data = array(), $repeat = false, $eventName = "server.schedule"){
		if(!is_callable($callback)){
			return false;
		}
		$add = "";
		$chcnt = $this->scheduleCnt++;
		if($repeat === false){
			$add = '$this->schedule['.$chcnt.']=null;unset($this->schedule['.$chcnt.']);';
		}
		$this->schedule[$chcnt] = array($callback, $data, $eventName);
		$this->action(50000 * $ticks, '$schedule=$this->schedule['.$chcnt.'];'.$add.'if(!is_callable($schedule[0])){$this->schedule['.$chcnt.']=null;unset($this->schedule['.$chcnt.']);return false;}return call_user_func($schedule[0],$schedule[1],$schedule[2]);', (bool) $repeat);
		return $chcnt;
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
		while(($action = $actions->fetchArray(SQLITE3_ASSOC)) !== false){
			$return = eval(base64_decode($action["code"]));
			if($action["repeat"] === 0 or $return === false){
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
		}elseif(isset(Deprecation::$events[$event])){
			$sub = "";
			if(Deprecation::$events[$event] !== false){
				$sub = " Substitute \"".Deprecation::$events[$event]."\" found.";
			}
			console("[ERROR] Event \"$event\" has been deprecated.$sub [Attach to ".(is_array($func) ? get_class($func[0])."::".$func[1]:$func)."]");
		}
		$evid = $this->evCnt++;
		$this->events[$evid] = $func;
		$this->query("INSERT INTO events (ID, name) VALUES (".$evid.", '".str_replace("'", "\\'", $event)."');");
		console("[INTERNAL] Attached ".(is_array($func) ? get_class($func[0])."::".$func[1]:$func)." to event ".$event." (ID ".$evid.")", true, true, 3);
		return $evid;
	}

	public function deleteEvent($id){
		$id = (int) $id;
		$this->events[$id] = null;
		unset($this->events[$id]);
		$this->query("DELETE FROM events WHERE ID = ".$id.";");
	}

}
