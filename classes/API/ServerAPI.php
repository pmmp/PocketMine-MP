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

class ServerAPI extends stdClass{ //Yay! I can add anything to this class in runtime!
	var $server;
	private $config;
	function __construct(){
		console("[INFO] Starting ServerAPI server handler...");
		file_put_contents("packets.log", "");
		file_put_contents("console.in", "");
		if(!file_exists(FILE_PATH."white-list.txt")){
			console("[NOTICE] No white-list.txt found, creating blank file");
			file_put_contents(FILE_PATH."white-list.txt", "");
		}

		if(!file_exists(FILE_PATH."banned-ips.txt")){
			console("[NOTICE] No banned-ips.txt found, creating blank file");
			file_put_contents(FILE_PATH."banned-ips.txt", "");
		}

		if(!file_exists(FILE_PATH."server.properties")){
			console("[NOTICE] No server.properties found, using default settings");
			copy(FILE_PATH."common/default.properties", FILE_PATH."server.properties");
		}
		
		console("[DEBUG] Checking data folders...", true, true, 2);
		@mkdir(FILE_PATH."data/players/", 0777, true);
		@mkdir(FILE_PATH."data/maps/", 0777);
		@mkdir(FILE_PATH."data/plugins/", 0777);
		
		console("[DEBUG] Loading server.properties...", true, true, 2);
		$this->parseProperties();
		define("DEBUG", $this->config["debug"]);
		$this->server = new PocketMinecraftServer($this->getProperty("server-name"), $this->getProperty("gamemode"), $this->getProperty("seed"), $this->getProperty("protocol"), $this->getProperty("port"), $this->getProperty("server-id"));
		$this->server->api = $this;
		if(file_exists(FILE_PATH."data/maps/level.dat")){
			console("[NOTICE] Detected unimported map data. Importing...");
			$this->importMap(FILE_PATH."data/maps/", true);
		}
		$this->server->mapName = $this->getProperty("level-name");
		$this->server->mapDir = FILE_PATH."data/maps/".$this->getProperty("level-name")."/";
		$this->loadProperties();
		//Autoload all default APIs
		console("[INFO] Loading default APIs");
		$dir = dir(FILE_PATH."classes/API/");
		while(false !== ($file = $dir->read())){
			if($file !== "." and $file !== ".."){
				$API = basename($file, ".php");
				if(strtolower($API) !== "serverapi"){
					$name = strtolower(substr($API, 0, -3));
					$this->loadAPI($name, $API);
				}
			}
		}
	}
	
	public function loadAPI($name, $class, $dir = false){
		if($dir === false){
			$dir = FILE_PATH."classes/API/";
		}
		$file = $dir.$class.".php";
		if(!file_exists($file)){
			console("[ERROR] API ".$name." [".$class."] in ".$dir." doesn't exist", true, true, 0);
			return false;
		}
		require_once($file);
		$this->$name = new $class($this->server);
		
		console("[INFO] API ".$name." [".$class."] loaded");
	
	}
	
	public function importMap($dir, $remove = false){
		if(file_exists($dir."level.dat")){
			$nbt = new NBT();
			$level = parseNBTData($nbt->loadFile($dir."level.dat"));
			console("[DEBUG] Importing map \"".$level["LevelName"]."\" gamemode ".$level["GameType"]." with seed ".$level["RandomSeed"], true, true, 2);
			unset($level["Player"]);
			$lvName = $level["LevelName"]."/";
			@mkdir(FILE_PATH."data/maps/".$lvName, 0777);	
			file_put_contents(FILE_PATH."data/maps/".$lvName."level.dat", serialize($level));
			$entities = parseNBTData($nbt->loadFile($dir."entities.dat"));
			file_put_contents(FILE_PATH."data/maps/".$lvName."entities.dat", serialize($entities["Entities"]));
			if(!isset($entities["TileEntities"])){
				$entities["TileEntities"] = array();
			}
			file_put_contents(FILE_PATH."data/maps/".$lvName."tileEntities.dat", serialize($entities["TileEntities"]));
			console("[DEBUG] Imported ".count($entities["Entities"])." Entities and ".count($entities["TileEntities"])." TileEntities", true, true, 2);
			
			if($remove === true){
				rename($dir."chunks.dat", FILE_PATH."data/maps/".$lvName."chunks.dat");
				unlink($dir."level.dat");
				@unlink($dir."level.dat_old");
				@unlink($dir."player.dat");
				unlink($dir."entities.dat");
			}else{
				copy($dir."chunks.dat", FILE_PATH."data/maps/".$lvName."chunks.dat");
			}
			if($this->getProperty("level-name") === false){
				console("[INFO] Setting default level to \"".$level["LevelName"]."\"");
				$this->setProperty("level-name", $level["LevelName"]);
				$this->setProperty("gamemode", $level["GameType"]);
				$this->server->seed = $level["RandomSeed"];
				$this->setProperty("spawn", array("x" => $level["SpawnX"], "y" => $level["SpawnY"], "z" => $level["SpawnZ"]));
				$this->config["spawn"] = array("x" => $level["SpawnX"], "y" => $level["SpawnY"], "z" => $level["SpawnZ"]);
				$this->writeProperties();
			}
			console("[INFO] Map \"".$level["LevelName"]."\" importing done!");
			unset($level, $entities, $nbt);		
			return true;
		}
		return false;
	}
	
	public function getProperty($name){
		if(isset($this->config[$name])){
			return $this->config[$name];
		}
		return false;
	}
	
	public function setProperty($name, $value){
		$this->config[$name] = $value;
		$this->writeProperties();
		$this->loadProperties();
	}
	
	private function loadProperties(){
		if(is_object($this->server)){
			$this->server->setType($this->config["server-type"]);
			$this->server->timePerSecond = $this->config["time-per-second"];
			$this->server->maxClients = $this->config["max-players"];
			$this->server->description = $this->config["description"];
			$this->server->motd = $this->config["motd"];
			$this->server->gamemode = $this->config["gamemode"];
			$this->server->difficulty = $this->config["difficulty"];
			$this->server->spawn = $this->config["spawn"];
			$this->server->whitelist = $this->config["white-list"];
			$this->server->reloadConfig();
		}
	}
	
	private function writeProperties(){
		if(is_object($this->server)){
			$this->config["seed"] = $this->server->seed;
			$this->config["server-id"] = $this->server->serverID;
		}
		$this->config["regenerate-config"] = "false";
		$this->config["white-list"] = $this->config["white-list"] === true ? "true":"false";
		$prop = "#Pocket Minecraft PHP server properties\r\n#".date("D M j H:i:s T Y")."\r\n";
		foreach($this->config as $n => $v){
			if($n == "spawn"){
				$v = implode(";", $v);
			}
			$prop .= $n."=".$v."\r\n";
		}
		file_put_contents(FILE_PATH."server.properties", $prop);
	}
	
	private function parseProperties(){
		$prop = file_get_contents(FILE_PATH."server.properties");
		$prop = explode("\n", str_replace("\r", "", $prop));
		$this->config = array();
		foreach($prop as $line){
			if(trim($line) == "" or $line{0} == "#"){
				continue;
			}
			$d = explode("=", $line);
			$n = strtolower(array_shift($d));
			$v = implode("=", $d);
			switch($n){
				case "protocol":
					if(trim($v) == "CURRENT"){
						$v = CURRENT_PROTOCOL;
						break;
					}
				case "gamemode":
				case "max-players":
				case "port":
				case "debug":
				case "difficulty":
				case "time-per-second":
					$v = (int) $v;
					break;
				case "seed":
				case "server-id":
					$v = trim($v);
					$v = $v == "false" ? false:(preg_match("/[^0-9\-]/", $v) > 0 ? Utils::readInt(substr(md5($v, true), 0, 4)):$v);
					break;
				case "level-name":
					$v = trim($v);
					$v = $v == "false" ? false:$v;
					break;
				case "spawn":
					$v = explode(";", $v);
					$v = array("x" => floatval($v[0]), "y" => floatval($v[1]), "z" => floatval($v[2]));
					break;
				case "white-list":
				case "regenerate-config":
					$v = trim($v) == "true" ? true:false;
					break;
			}
			$this->config[$n] = $v;
		}
	}
	
	public function start(){
		$this->server->start();
	}


}