<?php

/**
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

class PocketMinecraftServer{
	public $tCnt;
	public $serverID, $interface, $database, $version, $invisible, $api, $tickMeasure, $preparedSQL, $seed, $gamemode, $name, $maxClients, $clients, $eidCnt, $custom, $description, $motd, $port, $saveEnabled;
	private $serverip, $evCnt, $handCnt, $events, $eventsID, $handlers, $serverType, $lastTick, $ticks, $memoryStats, $async = array(), $asyncID = 0;
	
	private function load(){
		$this->version = new VersionString();
		/*if(defined("DEBUG") and DEBUG >= 0){
			@cli_set_process_title("PocketMine-MP ".MAJOR_VERSION);
		}*/
		console("[INFO] Starting Minecraft PE server on ".($this->serverip === "0.0.0.0" ? "*":$this->serverip).":".$this->port);
		define("BOOTUP_RANDOM", Utils::getRandomBytes(16));
		$this->serverID = $this->serverID === false ? Utils::readLong(Utils::getRandomBytes(8, false)):$this->serverID;
		$this->seed = $this->seed === false ? Utils::readInt(Utils::getRandomBytes(4, false)):$this->seed;
		$this->startDatabase();
		$this->api = false;
		$this->tCnt = 1;
		$this->events = array();
		$this->eventsID = array();
		$this->handlers = array();
		$this->invisible = false;
		$this->levelData = false;
		$this->difficulty = 1;
		$this->tiles = array();
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
		$this->memoryStats = array();
		$this->clients = array();
		$this->spawn = false;
		$this->saveEnabled = true;
		$this->tickMeasure = array_fill(0, 40, 0);
		$this->setType("normal");
		$this->interface = new MinecraftInterface($this, "255.255.255.255", $this->port, true, false, $this->serverip);
		$this->reloadConfig();
		$this->stop = false;
		$this->ticks = 0;
		if(!defined("NO_THREADS")){
			$this->asyncThread = new AsyncMultipleQueue();
		}
	}

	function __construct($name, $gamemode = SURVIVAL, $seed = false, $port = 19132, $serverip = "0.0.0.0"){
		$this->port = (int) $port;
		$this->doTick = true;
		$this->gamemode = (int) $gamemode;
		$this->name = $name;
		$this->motd = "Welcome to ".$name;
		$this->serverID = false;
		$this->seed = $seed;
		$this->serverip = $serverip;
		$this->load();
	}

	public function getTPS(){
		$v = array_values($this->tickMeasure);
		$tps = 40 / ($v[39] - $v[0]);
		return round($tps, 4);
	}
	
	public function titleTick(){
		$time = microtime(true);
		if(defined("DEBUG") and DEBUG >= 0 and ENABLE_ANSI === true){
			echo "\x1b]0;PocketMine-MP ".MAJOR_VERSION." | Online ". count($this->clients)."/".$this->maxClients." | RAM ".round((memory_get_usage() / 1024) / 1024, 2)."MB | U ".round(($this->interface->bandwidth[1] / max(1, $time - $this->interface->bandwidth[2])) / 1024, 2)." D ".round(($this->interface->bandwidth[0] / max(1, $time - $this->interface->bandwidth[2])) / 1024, 2)." kB/s | TPS ".$this->getTPS()."\x07";
		}
		$this->interface->bandwidth = array(0, 0, $time);
	}

	public function loadEvents(){
		if(ENABLE_ANSI === true){
			$this->schedule(30, array($this, "titleTick"), array(), true);
		}
		$this->schedule(20 * 15, array($this, "checkTicks"), array(), true);
		$this->schedule(20 * 60, array($this, "checkMemory"), array(), true);
		$this->schedule(20, array($this, "asyncOperationChecker"), array(), true);
	}
	
	public function checkTicks(){
		if($this->getTPS() < 12){
			console("[WARNING] Can't keep up! Is the server overloaded?");
		}
	}
	
	public function checkMemory(){
		$info = $this->debugInfo();
		$data = $info["memory_usage"].",".$info["players"].",".$info["entities"];
		$i = count($this->memoryStats) - 1;
		if($i < 0 or $this->memoryStats[$i] !== $data){
			$this->memoryStats[] = $data;
		}
	}

	public function startDatabase(){
		$this->preparedSQL = new stdClass();
		$this->preparedSQL->entity = new stdClass();
		$this->database = new SQLite3(":memory:");
		$this->query("PRAGMA journal_mode = OFF;");
		$this->query("PRAGMA encoding = \"UTF-8\";");
		$this->query("PRAGMA secure_delete = OFF;");
		$this->query("CREATE TABLE players (CID INTEGER PRIMARY KEY, EID NUMERIC, ip TEXT, port NUMERIC, name TEXT UNIQUE COLLATE NOCASE);");
		$this->query("CREATE TABLE entities (EID INTEGER PRIMARY KEY, level TEXT, type NUMERIC, class NUMERIC, hasUpdate NUMERIC, name TEXT, x NUMERIC, y NUMERIC, z NUMERIC, yaw NUMERIC, pitch NUMERIC, health NUMERIC);");
		$this->query("CREATE TABLE tiles (ID INTEGER PRIMARY KEY, level TEXT, class TEXT, x NUMERIC, y NUMERIC, z NUMERIC, spawnable NUMERIC);");
		$this->query("CREATE TABLE actions (ID INTEGER PRIMARY KEY, interval NUMERIC, last NUMERIC, code TEXT, repeat NUMERIC);");
		$this->query("CREATE TABLE handlers (ID INTEGER PRIMARY KEY, name TEXT, priority NUMERIC);");
		$this->query("CREATE TABLE blockUpdates (level TEXT, x INTEGER, y INTEGER, z INTEGER, type INTEGER, delay NUMERIC);");
		$this->query("CREATE TABLE recipes (id INTEGER PRIMARY KEY, type NUMERIC, recipe TEXT);");
		$this->query("PRAGMA synchronous = OFF;");
		$this->preparedSQL->selectHandlers = $this->database->prepare("SELECT DISTINCT ID FROM handlers WHERE name = :name ORDER BY priority DESC;");
		$this->preparedSQL->selectActions = $this->database->prepare("SELECT ID,code,repeat FROM actions WHERE last <= (:time - interval);");
		$this->preparedSQL->updateAction = $this->database->prepare("UPDATE actions SET last = :time WHERE ID = :id;");
		$this->preparedSQL->entity->setPosition = $this->database->prepare("UPDATE entities SET x = :x, y = :y, z = :z, pitch = :pitch, yaw = :yaw WHERE EID = :eid ;");
		$this->preparedSQL->entity->setLevel = $this->database->prepare("UPDATE entities SET level = :level WHERE EID = :eid ;");
	}

	public function query($sql, $fetch = false){
		$result = $this->database->query($sql) or console("[ERROR] [SQL Error] ".$this->database->lastErrorMsg().". Query: ".$sql, true, true, 0);
		if($fetch === true and ($result instanceof SQLite3Result)){
			$result = $result->fetchArray(SQLITE3_ASSOC);
		}
		return $result;
	}

	public function reloadConfig(){

	}

	public function debugInfo($console = false){
		$info = array();
		$info["tps"] = $this->getTPS();
		$info["memory_usage"] = round((memory_get_usage() / 1024) / 1024, 2)."MB";
		$info["memory_peak_usage"] = round((memory_get_peak_usage() / 1024) / 1024, 2)."MB";
		$info["entities"] = $this->query("SELECT count(EID) as count FROM entities;", true);
		$info["entities"] = $info["entities"]["count"];
		$info["players"] = $this->query("SELECT count(CID) as count FROM players;", true);
		$info["players"] = $info["players"]["count"];
		$info["events"] = count($this->eventsID);
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

	public function close($reason = "server stop"){
		if($this->stop !== true){
			if(is_int($reason)){
				$reason = "signal stop";
			}
			if(($this->api instanceof ServerAPI) === true){
				if(($this->api->chat instanceof ChatAPI) === true){
					$this->api->chat->broadcast("Stopping server...");
				}
			}
			$this->stop = true;
			$this->trigger("server.close", $reason);
			$this->interface->close();
			
			if(!defined("NO_THREADS")){
				@$this->asyncThread->stop = true;
			}
		}
	}

	public function setType($type = "normal"){
		switch(trim(strtolower($type))){
			case "normal":
			case "demo":
				$this->serverType = "MCCPP;Demo;";
				break;
			case "minecon":
				$this->serverType = "MCCPP;MINECON;";
				break;
		}

	}
	
	public function asyncOperation($type, array $data, callable $callable = null){
		if(defined("NO_THREADS")){
			return false;
		}
		$d = "";
		$type = (int) $type;
		switch($type){
			case ASYNC_CURL_GET:
				$d .= Utils::writeShort(strlen($data["url"])).$data["url"].(isset($data["timeout"]) ? Utils::writeShort($data["timeout"]) : Utils::writeShort(10));
				break;
			case ASYNC_CURL_POST:
				$d .= Utils::writeShort(strlen($data["url"])).$data["url"].(isset($data["timeout"]) ? Utils::writeShort($data["timeout"]) : Utils::writeShort(10));
				$d .= Utils::writeShort(count($data["data"]));
				foreach($data["data"] as $key => $value){
					$d .= Utils::writeShort(strlen($key)).$key . Utils::writeInt(strlen($value)).$value;
				}
				break;
			default:
				return false;
		}
		$ID = $this->asyncID++;
		$this->async[$ID] = $callable;
		$this->asyncThread->input .= Utils::writeInt($ID).Utils::writeShort($type).$d;
		return $ID;
	}
	
	public function asyncOperationChecker(){
		if(defined("NO_THREADS")){
			return false;
		}
		if(isset($this->asyncThread->output{5})){
			$offset = 0;
			$ID = Utils::readInt(substr($this->asyncThread->output, $offset, 4));
			$offset += 4;
			$type = Utils::readShort(substr($this->asyncThread->output, $offset, 2));
			$offset += 2;
			$data = array();
			switch($type){
				case ASYNC_CURL_GET:
				case ASYNC_CURL_POST:
					$len = Utils::readInt(substr($this->asyncThread->output, $offset, 4));
					$offset += 4;
					$data["result"] = substr($this->asyncThread->output, $offset, $len);
					$offset += $len;
					break;
			}
			$this->asyncThread->output = substr($this->asyncThread->output, $offset);
			if(isset($this->async[$ID]) and $this->async[$ID] !== null and is_callable($this->async[$ID])){
				if(is_array($this->async[$ID])){
					$method = $this->async[$ID][1];
					$result = $this->async[$ID][0]->$method($data, $type, $ID);
				}else{
					$result = $this->async[$ID]($data, $type, $ID);
				}
			}
			unset($this->async[$ID]);
		}
	}

	public function addHandler($event,callable $callable, $priority = 5){
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
	
	public function dhandle($e, $d){
		return $this->handle($e, $d);
	}

	public function handle($event, &$data){
		$this->preparedSQL->selectHandlers->reset();
		$this->preparedSQL->selectHandlers->clear();
		$this->preparedSQL->selectHandlers->bindValue(":name", $event, SQLITE3_TEXT);
		$handlers = $this->preparedSQL->selectHandlers->execute();
		$result = null;
		if($handlers instanceof SQLite3Result){
			$call = array();
			while(($hn = $handlers->fetchArray(SQLITE3_ASSOC)) !== false){
				$call[(int) $hn["ID"]] = true;
			}
			$handlers->finalize();
			foreach($call as $hnid => $boolean){
				if($result !== false and $result !== true){
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



	public function init(){		
		register_tick_function(array($this, "tick"));
		console("[DEBUG] Starting internal ticker calculation", true, true, 2);
		$t = 0;
		while(true){
			switch($t){
				case 0:
					declare(ticks=100);
					break;
				case 1:
					declare(ticks=60);
					break;
				case 2:
					declare(ticks=40);
					break;
				case 3:
					declare(ticks=30);
					break;
				case 4:
					declare(ticks=20);
					break;
				case 5:
					declare(ticks=15);
					break;
				default:
					declare(ticks=10);
					break;
			}
			if($t > 5){
				break;
			}
			$this->ticks = 0;
			while($this->ticks < 20){
				usleep(1);
			}
			
			if($this->getTPS() < 19.5){
				++$t;
			}else{
				break;
			}
		}

		$this->loadEvents();
		register_shutdown_function(array($this, "dumpError"));
		register_shutdown_function(array($this, "close"));
		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, array($this, "close"));
			pcntl_signal(SIGINT, array($this, "close"));
			pcntl_signal(SIGHUP, array($this, "close"));
		}
		console("[INFO] Default game type: ".strtoupper($this->getGamemode()));
		$this->trigger("server.start", microtime(true));
		console('[INFO] Done ('.round(microtime(true) - START_TIME, 3).'s)! For help, type "help" or "?"');
		$this->process();
	}

	public function dumpError(){
		if($this->stop === true){
			return;
		}
		console("[ERROR] An Unrecovereable has ocurred and the server has Crashed. Creating an Error Dump");
		$dump = "```\r\n# PocketMine-MP Error Dump ".date("D M j H:i:s T Y")."\r\n";
		$er = error_get_last();
		$dump .= "Error: ".var_export($er, true)."\r\n\r\n";
		$dump .= "Code: \r\n";
		$file = @file($er["file"], FILE_IGNORE_NEW_LINES);
		for($l = max(0, $er["line"] - 10); $l < $er["line"] + 10; ++$l){
			$dump .= "[".($l + 1)."] ".@$file[$l]."\r\n";
		}
		$dump .= "\r\n\r\n";
		$version = new VersionString();
		$dump .= "PM Version: ".$version." #".$version->getNumber()." [Protocol ".CURRENT_PROTOCOL."]\r\n";
		$dump .= "Commit: ".GIT_COMMIT."\r\n";
		$dump .= "uname -a: ".php_uname("a")."\r\n";
		$dump .= "PHP Version: " .phpversion()."\r\n";
		$dump .= "Zend version: ".zend_version()."\r\n";
		$dump .= "OS : " .PHP_OS.", ".Utils::getOS()."\r\n";
		$dump .= "Debug Info: ".var_export($this->debugInfo(false), true)."\r\n\r\n\r\n";
		global $arguments;
		$dump .= "Parameters: ".var_export($arguments, true)."\r\n\r\n\r\n";
		$p = $this->api->getProperties();
		if($p["rcon.password"] != ""){
			$p["rcon.password"] = "******";
		}
		$dump .= "server.properties: ".var_export($p, true)."\r\n\r\n\r\n";
		if($this->api->plugin instanceof PluginAPI){
			$plist = $this->api->plugin->getList();
			$dump .= "Loaded plugins:\r\n";
			foreach($plist as $p){
				$dump .= $p["name"]." ".$p["version"]." by ".$p["author"]."\r\n";
			}
			$dump .= "\r\n\r\n";
		}
		$dump .= "Loaded Modules: ".var_export(get_loaded_extensions(), true)."\r\n";
		$dump .= "Memory Usage Tracking: \r\n".chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9)))."\r\n";
		ob_start();
		phpinfo();
		$dump .= "\r\nphpinfo(): \r\n".chunk_split(base64_encode(gzdeflate(ob_get_contents(), 9)))."\r\n";
		ob_end_clean();
		$dump .= "\r\n```";
		$name = "Error_Dump_".date("D_M_j-H.i.s-T_Y");
		logg($dump, $name, true, 0, true);
		console("[ERROR] Please submit the \"{$name}.log\" file to the Bug Reporting page. Give as much info as you can.", true, true, 0);
	}

	public function tick(){
		$time = microtime(true);
		if($this->lastTick <= ($time - 0.05)){
			$this->tickMeasure[] = $this->lastTick = $time;
			unset($this->tickMeasure[key($this->tickMeasure)]);
			++$this->ticks;
			$this->tickerFunction($time);
		}
	}

	public static function clientID($ip, $port){
		//faster than string indexes in PHP
		return crc32($ip . $port) ^ crc32($port . $ip . BOOTUP_RANDOM);
	}

	public function packetHandler($packet){
		$data =& $packet["data"];
		$CID = PocketMinecraftServer::clientID($packet["ip"], $packet["port"]);
		if(isset($this->clients[$CID])){
			$this->clients[$CID]->handlePacket($packet["pid"], $data);
		}else{
			if($this->handle("server.noauthpacket", $packet) === false){
				return;
			}
			switch($packet["pid"]){
				case 0x01:
				case 0x02:
					if($this->invisible === true){
						$this->send(0x1c, array(
							$data[0],
							$this->serverID,
							RAKNET_MAGIC,
							$this->serverType,
						), false, $packet["ip"], $packet["port"]);
						break;
					}
					if(!isset($this->custom["times_".$CID])){
						$this->custom["times_".$CID] = 0;
					}
					$ln = 15;
					if($this->description == "" or substr($this->description, -1) != " "){						
						$this->description .= " ";
					}
					$txt = substr($this->description, $this->custom["times_".$CID], $ln);
					$txt .= substr($this->description, 0, $ln - strlen($txt));
					$this->send(0x1c, array(
						$data[0],
						$this->serverID,
						RAKNET_MAGIC,
						$this->serverType. $this->name . " [".count($this->clients)."/".$this->maxClients."] ".$txt,
					), false, $packet["ip"], $packet["port"]);
					$this->custom["times_".$CID] = ($this->custom["times_".$CID] + 1) % strlen($this->description);
					break;
				case 0x05:
					$version = $data[1];
					$size = strlen($data[2]);
					if($version !== CURRENT_STRUCTURE){
						console("[DEBUG] Incorrect structure #$version from ".$packet["ip"].":".$packet["port"], true, true, 2);
						$this->send(0x1a, array(
							CURRENT_STRUCTURE,
							RAKNET_MAGIC,
							$this->serverID,
						), false, $packet["ip"], $packet["port"]);
					}else{
						$this->send(0x06, array(
							RAKNET_MAGIC,
							$this->serverID,
							0,
							strlen($packet["raw"]),
						), false, $packet["ip"], $packet["port"]);
					}
					break;
				case 0x07:
					if($this->invisible === true){
						break;
					}
					$port = $data[2];
					$MTU = $data[3];
					$clientID = $data[4];
					if(count($this->clients) < $this->maxClients){
						$this->clients[$CID] = new Player($clientID, $packet["ip"], $packet["port"], $MTU); //New Session!
						$this->send(0x08, array(
							RAKNET_MAGIC,
							$this->serverID,
							$this->port,
							$data[3],
							0,
						), false, $packet["ip"], $packet["port"]);
					}
					break;
			}
		}
	}

	public function send($pid, $data = array(), $raw = false, $dest = false, $port = false){
		return $this->interface->writePacket($pid, $data, $raw, $dest, $port);
	}

	public function process(){
		$lastLoop = 0;
		while($this->stop === false){
			$packet = $this->interface->readPacket();
			if($packet !== false){
				$this->packetHandler($packet);
				$lastLoop = 0;
			}else{
				++$lastLoop;
				if($lastLoop < 16){
					usleep(1);
				}elseif($lastLoop < 128){
					usleep(100);
				}elseif($lastLoop < 256){
					usleep(512);
				}else{
					usleep(10000);
				}
			}
		}
	}

	public function trigger($event, $data = ""){
		if(isset($this->events[$event])){
			foreach($this->events[$event] as $evid => $ev){
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
	}

	public function schedule($ticks, callable $callback, $data = array(), $repeat = false, $eventName = "server.schedule"){
		if(!is_callable($callback)){
			return false;
		}
		$chcnt = $this->scheduleCnt++;
		$this->schedule[$chcnt] = array($callback, $data, $eventName);
		$this->query("INSERT INTO actions (ID, interval, last, repeat) VALUES(".$chcnt.", ".($ticks / 20).", ".microtime(true).", ".(((bool) $repeat) === true ? 1:0).");");
		return $chcnt;
	}

	public function tickerFunction($time){
		//actions that repeat every x time will go here
		$this->preparedSQL->selectActions->reset();
		$this->preparedSQL->selectActions->bindValue(":time", $time, SQLITE3_FLOAT);
		$actions = $this->preparedSQL->selectActions->execute();

		if($actions instanceof SQLite3Result){
			while(($action = $actions->fetchArray(SQLITE3_ASSOC)) !== false){
				$cid = $action["ID"];
				$this->preparedSQL->updateAction->reset();
				$this->preparedSQL->updateAction->bindValue(":time", $time, SQLITE3_FLOAT);
				$this->preparedSQL->updateAction->bindValue(":id", $cid, SQLITE3_INTEGER);
				$this->preparedSQL->updateAction->execute();
				$schedule = $this->schedule[$cid];
				if(!is_callable($schedule[0])){
					$return = false;
				}else{
					$return = call_user_func($schedule[0], $schedule[1], $schedule[2]);
				}

				if($action["repeat"] == 0 or $return === false){
					$this->query("DELETE FROM actions WHERE ID = ".$action["ID"].";");
					$this->schedule[$cid] = null;
					unset($this->schedule[$cid]);
				}
			}
			$actions->finalize();
		}
	}

	public function event($event,callable $func){
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
		if(!isset($this->events[$event])){
			$this->events[$event] = array();
		}
		$this->events[$event][$evid] = $func;
		$this->eventsID[$evid] = $event;
		console("[INTERNAL] Attached ".(is_array($func) ? get_class($func[0])."::".$func[1]:$func)." to event ".$event." (ID ".$evid.")", true, true, 3);
		return $evid;
	}

	public function deleteEvent($id){
		$id = (int) $id;
		if(isset($this->eventsID[$id])){
			$ev = $this->eventsID[$id];
			$this->eventsID[$id] = null;
			unset($this->eventsID[$id]);
			$this->events[$ev][$id] = null;
			unset($this->events[$ev][$id]);
			if(count($this->events[$ev]) === 0){
				unset($this->events[$ev]);
			}
		}
	}

}
