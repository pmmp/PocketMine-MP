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

class ServerAPI{
	public $restart = false;
	private static $serverRequest = false;
	private $asyncCalls = array();
	private $server;
	private $config;
	private $apiList = array();
	private $asyncCnt = 0;
	private $rcon;
	private $query;

    //TODO: Instead of hard-coding functions, use PHPDoc-compatible methods to load APIs.

	/**
	 * @var ConsoleAPI
	 */
	public $console;

	/**
	 * @var LevelAPI
	 */
	public $level;

	/**
	 * @var BlockAPI
	 */
	public $block;

	/**
	 * @var ChatAPI
	 */
	public $chat;

	/**
	 * @var BanAPI
	 */
	public $ban;

	/**
	 * @var EntityAPI
	 */
	public $entity;

	/**
	 * @var TimeAPI
	 */
	public $time;

	/**
	 * @var PlayerAPI
	 */
	public $player;

	/**
	 * @var TileAPI
	 */
	public $tile;

	/**
	 * @return PocketMinecraftServer
	 */
	public static function request(){
		return self::$serverRequest;
	}
	
	public function start(){
		return $this->run();
	}
	
	public function run(){
		$this->load();
		return $this->init();
	}
	
	public function load(){
		@mkdir(DATA_PATH."players/", 0755);
		@mkdir(DATA_PATH."worlds/", 0755);
		@mkdir(DATA_PATH."plugins/", 0755);
		$version = new VersionString();
		console("[INFO] Starting Minecraft PE server version ".FORMAT_AQUA.CURRENT_MINECRAFT_VERSION);
		
		console("[INFO] Loading properties...");
		$this->config = new Config(DATA_PATH . "server.properties", CONFIG_PROPERTIES, array(
			"server-name" => "Minecraft: PE Server",
			"description" => "Server made using PocketMine-MP",
			"motd" => "Welcome @player to this server!",
			"server-ip" => "",
			"server-port" => 19132,
			"server-type" => "normal",
			"memory-limit" => "128M",
			"last-update" => false,
			"white-list" => false,
			"announce-player-achievements" => true,
			"spawn-protection" => 16,
			"view-distance" => 10,
			"max-players" => 20,
			"allow-flight" => false,
			"spawn-animals" => true,
			"spawn-mobs" => true,
			"gamemode" => SURVIVAL,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => true,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(Utils::getRandomBytes(20, false)), 3, 10),
			"send-usage" => true,
			"auto-save" => true,
		));
		
		$this->parseProperties();
		define("DEBUG", $this->getProperty("debug", 1));
		if($this->getProperty("port") !== false){
			$this->setProperty("server-port", $this->getProperty("port"));
			$this->config->remove("port");
			$this->config->remove("invisible");
		}
		$this->server = new PocketMinecraftServer($this->getProperty("server-name"), $this->getProperty("gamemode"), ($seed = $this->getProperty("level-seed")) != "" ? (int) $seed:false, $this->getProperty("server-port"), ($ip = $this->getProperty("server-ip")) != "" ? $ip:"0.0.0.0");
		$this->server->api = $this;
		self::$serverRequest = $this->server;
		console("[INFO] This server is running PocketMine-MP version ".($version->isDev() ? FORMAT_YELLOW:"").MAJOR_VERSION.FORMAT_RESET." (MCPE: ".CURRENT_MINECRAFT_VERSION.") (API ".CURRENT_API_VERSION.")", true, true, 0);
		console("[INFO] PocketMine-MP is distibuted under the LGPL License", true, true, 0);

		if($this->getProperty("upnp-forwarding") === true){
			console("[INFO] [UPnP] Trying to port forward...");
			UPnP_PortForward($this->getProperty("server-port"));
		}

		if($this->getProperty("last-update") === false or ($this->getProperty("last-update") + 3600) < time()){
			console("[INFO] Checking for new server version");
			console("[INFO] Last check: ".FORMAT_AQUA.date("Y-m-d H:i:s", $this->getProperty("last-update"))."\x1b[0m");
			if($this->server->version->isDev()){
				$info = json_decode(Utils::curl_get("https://api.github.com/repos/PocketMine/PocketMine-MP/commits"), true);
				if($info === false or !isset($info[0])){
					console("[ERROR] Github API error");
				}else{
					$last = new DateTime($info[0]["commit"]["committer"]["date"]);
					$last = $last->getTimestamp();
					if($last >= $this->getProperty("last-update") and $this->getProperty("last-update") !== false and GIT_COMMIT != $info[0]["sha"]){
						console("[NOTICE] ".FORMAT_YELLOW."A new DEVELOPMENT version of PocketMine-MP has been released!");
						console("[NOTICE] ".FORMAT_YELLOW."Commit \"".$info[0]["commit"]["message"]."\" [".substr($info[0]["sha"], 0, 10)."] by ".$info[0]["commit"]["committer"]["name"]);
						console("[NOTICE] ".FORMAT_YELLOW."Get it at PocketMine.net or at https://github.com/PocketMine/PocketMine-MP/archive/".$info[0]["sha"].".zip");
						console("[NOTICE] This message will dissapear after issuing the command \"/update-done\"");
					}else{
						$this->setProperty("last-update", time());
						console("[INFO] ".FORMAT_AQUA."This is the latest DEVELOPMENT version");
					}
				}
			}else{
				$info = json_decode(Utils::curl_get("https://api.github.com/repos/PocketMine/PocketMine-MP/tags"), true);
				if($info === false or !isset($info[0])){
					console("[ERROR] Github API error");
				}else{
					$newest = new VersionString(MAJOR_VERSION);
					$newestN = $newest->getNumber();
					$update = new VersionString($info[0]["name"]);
					$updateN = $update->getNumber();
					if($updateN > $newestN){
						console("[NOTICE] ".FORMAT_GREEN."A new STABLE version of PocketMine-MP has been released!");
						console("[NOTICE] ".FORMAT_GREEN."Version \"".$info[0]["name"]."\" #".$updateN);
						console("[NOTICE] Get it at PocketMine.net or at ".$info[0]["zipball_url"]);
						console("[NOTICE] This message will dissapear as soon as you update");
					}else{
						$this->setProperty("last-update", time());
						console("[INFO] ".FORMAT_AQUA."This is the latest STABLE version");
					}
				}
			}
		}

		$this->loadProperties();
		
		
		$this->loadAPI("console", "ConsoleAPI");
		$this->loadAPI("level", "LevelAPI");
		$this->loadAPI("block", "BlockAPI");
		$this->loadAPI("chat", "ChatAPI");
		$this->loadAPI("ban", "BanAPI");		
		$this->loadAPI("entity", "EntityAPI");		
		$this->loadAPI("tile", "TileAPI");
		$this->loadAPI("player", "PlayerAPI");
		$this->loadAPI("time", "TimeAPI");
		
		foreach($this->apiList as $ob){
			if(is_callable(array($ob, "init"))){
				$ob->init(); //Fails sometimes!!!
			}
		}
		$this->loadAPI("plugin", "PluginAPI"); //fix :(
		$this->plugin->init();
	}
	
	public function async(callable $callable, $params = array(), $remove = false){
		$cnt = $this->asyncCnt++;
		$this->asyncCalls[$cnt] = new Async($callable, $params);
		return $remove === true ? $this->getAsync($cnt):$cnt;
	}
	
	public function getAsync($id){
		if(!isset($this->asyncCalls[$id])){
			return false;
		}
		$ob = $this->asyncCalls[$id];
		unset($this->asyncCalls[$id]);
		return $ob;
	}
	public function autoSave(){
		console("[DEBUG] Saving....", true, true, 2);
		$this->server->api->level->saveAll();
	}
		
	public function sendUsage(){
		console("[DEBUG] Sending usage data...", true, true, 2);
		$plist = "";
		foreach($this->plugin->getList() as $p){
			$plist .= str_replace(array(";", ":"), "", $p["name"]).":".str_replace(array(";", ":"), "", $p["version"]).";";
		}
		
		$this->asyncOperation(ASYNC_CURL_POST, array(
			"url" => "http://stats.pocketmine.net/usage.php",
			"data" => array(
				"serverid" => $this->server->serverID,
				"port" => $this->server->port,
				"os" => Utils::getOS(),
				"memory_total" => $this->getProperty("memory-limit"),
				"memory_usage" => memory_get_usage(true),
				"php_version" => PHP_VERSION,
				"version" => MAJOR_VERSION,
				"mc_version" => CURRENT_MINECRAFT_VERSION,
				"protocol" => CURRENT_PROTOCOL,
				"online" => count($this->server->clients),
				"max" => $this->server->maxClients,
				"plugins" => $plist,
			),
		), NULL);
	}

	public function __destruct(){
		foreach($this->apiList as $i => $ob){
			if(method_exists($ob, "__destruct")){
				$ob->__destruct();
				unset($this->apiList[$i]);
			}
		}
	}


	private function loadProperties(){
		if(($memory = str_replace("B", "", strtoupper($this->getProperty("memory-limit")))) !== false){
			$value = array("M" => 1, "G" => 1024);
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 128){
				console("[WARNING] PocketMine-MP may not work right with less than 128MB of RAM", true, true, 0);
			}
			@ini_set("memory_limit", $memory);
		}else{
			$this->setProperty("memory-limit", "128M");
		}

		if($this->server instanceof PocketMinecraftServer){
			$this->server->setType($this->getProperty("server-type"));
			$this->server->maxClients = $this->getProperty("max-players");
			$this->server->description = $this->getProperty("description");
			$this->server->motd = $this->getProperty("motd");
			$this->server->gamemode = $this->getProperty("gamemode");
			$this->server->difficulty = $this->getProperty("difficulty");
			$this->server->whitelist = $this->getProperty("white-list");
			$this->server->reloadConfig();
		}
	}

	private function writeProperties(){
		$this->config->save();
	}

	private function parseProperties(){
		foreach($this->config->getAll() as $n => $v){
			switch($n){
				case "last-update":
					if($v === false){
						$v = time();
					}else{
						$v = (int) $v;
					}
					break;
				case "gamemode":
				case "max-players":
				case "server-port":
				case "debug":
				case "difficulty":
					$v = (int) $v;
					break;
				case "server-id":
					if($v !== false){
						$v = preg_match("/[^0-9\-]/", $v) > 0 ? Utils::readInt(substr(md5($v, true), 0, 4)):$v;
					}
					break;
			}
			$this->config->set($n, $v);
		}
		if($this->getProperty("hardcore") == 1 and $this->getProperty("difficulty") < 3){
			$this->setProperty("difficulty", 3);
		}
	}

	public function init(){
		if(!(self::$serverRequest instanceof PocketMinecraftServer)){
			self::$serverRequest = $this->server;
		}

		if($this->getProperty("send-usage") !== false){
			$this->server->schedule(6000, array($this, "sendUsage"), array(), true); //Send the info after 5 minutes have passed
			$this->sendUsage();
		}
		if($this->getProperty("auto-save") === true){
			$this->server->schedule(18000, array($this, "autoSave"), array(), true);	
		}
		if(!defined("NO_THREADS") and $this->getProperty("enable-rcon") === true){
			$this->rcon = new RCON($this->getProperty("rcon.password", ""), $this->getProperty("rcon.port", $this->getProperty("server-port")), ($ip = $this->getProperty("server-ip")) != "" ? $ip:"0.0.0.0", $this->getProperty("rcon.threads", 1), $this->getProperty("rcon.clients-per-thread", 50));
		}

		if($this->getProperty("enable-query") === true){
			$this->query = new Query();
		}
		CraftingRecipes::init();
		$this->server->init();
		unregister_tick_function(array($this->server, "tick"));
		$this->console->__destruct();
		if($this->rcon instanceof RCON){
			$this->rcon->stop();
		}
		$this->__destruct();
		if($this->getProperty("upnp-forwarding") === true ){
			console("[INFO] [UPnP] Removing port forward...");
			UPnP_RemovePortForward($this->getProperty("server-port"));
		}
		return $this->restart;
	}

	/*-------------------------------------------------------------*/

	public function asyncOperation($t, $d, $c = null){
		return $this->server->asyncOperation($t, $d, $c);
	}
	
	public function addHandler($e, $c, $p = 5){
		return $this->server->addHandler($e, $c, $p);
	}

	public function dhandle($e, $d){
		return $this->server->handle($e, $d);
	}

	public function handle($e, &$d){
		return $this->server->handle($e, $d);
	}

	public function schedule($t, $c, $d, $r = false, $e = "server.schedule"){
		return $this->server->schedule($t, $c, $d, $r, $e);
	}

	public function event($e, $d){
		return $this->server->event($e, $d);
	}

	public function trigger($e, $d){
		return $this->server->trigger($e, $d);
	}

	public function deleteEvent($id){
		return $this->server->deleteEvent($id);
	}
	
	public function getProperties(){
		return $this->config->getAll();
	}

	public function getProperty($name, $default = false){
		if(($v = arg($name)) !== false){ //Allow for command-line arguments
			switch(strtolower(trim($v))){
				case "on":
				case "true":
				case "yes":
					$v = true;
					break;
				case "off":
				case "false":
				case "no":
					$v = false;
					break;
			}
			switch($name){
				case "last-update":
					if($v === false){
						$v = time();
					}else{
						$v = (int) $v;
					}
					break;
				case "gamemode":
				case "max-players":
				case "server-port":
				case "debug":
				case "difficulty":
				case "time-per-second":
					$v = (int) $v;
					break;
				case "server-id":
					if($v !== false){
						$v = preg_match("/[^0-9\-]/", $v) > 0 ? Utils::readInt(substr(md5($v, true), 0, 4)):$v;
					}
					break;
			}
			return $v;
		}
		return ($this->config->exists($name) ? $this->config->get($name):$default);
	}

	public function setProperty($name, $value){
		$this->config->set($name, $value);
		$this->writeProperties();
		$this->loadProperties();
	}

	public function getList(){
		return $this->apiList;
	}

	public function loadAPI($name, $class, $dir = false){
		if(isset($this->$name)){
			return false;
		}elseif(!class_exists($class)){
			$internal = false;
			if($dir === false){
				$internal = true;
				$dir = FILE_PATH."src/API/";
			}
			$file = $dir.$class.".php";
			if(!file_exists($file)){
				console("[ERROR] API ".$name." [".$class."] in ".$dir." doesn't exist", true, true, 0);
				return false;
			}
			require_once($file);
		}else{
			$internal = true;
		}
		$this->$name = new $class();
		$this->apiList[] = $this->$name;
		console("[".($internal === true ? "INTERNAL":"DEBUG")."] API \x1b[36m".$name."\x1b[0m [\x1b[30;1m".$class."\x1b[0m] loaded", true, true, ($internal === true ? 3:2));
	}

}
