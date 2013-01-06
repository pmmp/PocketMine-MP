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

class ServerAPI extends stdClass{ //Yay! I can add anything to this class in runtime!
	var $restart = false;
	private $server, $config, $apiList = array();
	function __construct(){
		@mkdir(FILE_PATH."logs/", 0777, true);
		@mkdir(FILE_PATH."players/", 0777);
		@mkdir(FILE_PATH."worlds/", 0777);
		@mkdir(FILE_PATH."plugins/", 0777);
		console("[INFO] Starting ServerAPI server handler...");
		file_put_contents(FILE_PATH."logs/packets.log", "");
		if(!file_exists(FILE_PATH."logs/test.bin.log") or md5_file(FILE_PATH."logs/test.bin.log") !== TEST_MD5){
			console("[NOTICE] Executing integrity tests...");
			console("[INFO] OS: ".PHP_OS.", ".Utils::getOS());
			console("[INFO] uname -a: ".php_uname("a"));
			console("[INFO] PHP Version: ".phpversion());
			console("[INFO] Endianness: ".ENDIANNESS);
			$test = b"";
			$test .= Utils::writeLong("5567381823242127440");
			$test .= Utils::writeLong("2338608908624488819");
			$test .= Utils::writeLong("2333181766244987936");
			$test .= Utils::writeLong("2334669371112169504");
			$test .= Utils::writeShort(Utils::readShort("\xff\xff\xff\xff"));
			$test .= Utils::writeShort(Utils::readShort("\xef\xff\xff\xff"));
			$test .= Utils::writeInt(Utils::readInt("\xff\xff\xff\xff"));
			$test .= Utils::writeInt(1);
			$test .= Utils::writeInt(-1);
			$test .= Utils::writeFloat(Utils::readfloat("\xff\xff\xff\xff"));
			$test .= Utils::writeFloat(-1.584563155838E+29);
			$test .= Utils::writeFloat(1);
			$test .= Utils::writeLDouble(Utils::readLDouble("\xff\xff\xff\xff\xff\xff\xff\xff"));
			$test .= Utils::writeLong("-1152921504606846977");
			$test .= Utils::writeLong("-1152921504606846976");
			$test .= Utils::writeTriad(16777215);
			$test .= Utils::writeTriad(16777216);
			$str = new Java_String("THIS_IS_ a TEsT_SEED1_123456789^.,.,\xff\x00\x15");
			$test .= Utils::writeLong($str->hashCode());
			$test .= Utils::writeDataArray(array("a", "b", "c", "\xff\xff\xff\xff"));
			$test .= Utils::hexToStr("012334567890");
			file_put_contents(FILE_PATH."logs/test.bin.log", $test);
			if(md5($test) !== TEST_MD5){
				console("[ERROR] Test error, please send your console.log + test.bin.log to the Github repo");
				die();
			}
		}

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
			copy(FILE_PATH."src/common/default.properties", FILE_PATH."server.properties");
		}

		console("[DEBUG] Loading server.properties...", true, true, 2);
		$this->parseProperties();
		define("DEBUG", $this->config["debug"]);
		$this->server = new PocketMinecraftServer($this->getProperty("server-name"), $this->getProperty("gamemode"), false, $this->getProperty("port"), $this->getProperty("server-id"));
		$this->server->api = $this;
		if($this->getProperty("upnp-forwading") === true ){
			console("[INFO] [UPnP] Trying to port forward...");
			UPnP_PortForward($this->getProperty("port"));
		}
		if($this->getProperty("last-update") === false or ($this->getProperty("last-update") + 3600) < time()){
			console("[INFO] Checking for new server version");
			console("[INFO] Last check: ".date("Y-m-d H:i:s", $this->getProperty("last-update")));
			$channel = "stable";
			if($this->getProperty("update-channel") == "dev" or $this->getProperty("update-channel") == "development"){
				$channel = "dev";
			}
			$this->setProperty("update-channel", $channel);

			if($channel === "dev"){
				$info = json_decode(Utils::curl_get("https://api.github.com/repos/shoghicp/PocketMine-MP"), true);
				if($info === false or !isset($info["updated_at"])){
					console("[ERROR] GitHub API Error");
				}else{
					$last = new DateTime($info["updated_at"]);
					$last = $last->getTimestamp();
					if($last >= $this->getProperty("last-update") and $this->getProperty("last-update") !== false){
						console("[NOTICE] A new DEVELOPMENT version of PocketMine-MP has been released");
						console("[NOTICE] If you want to update, get the latest version at https://github.com/shoghicp/PocketMine-MP/archive/master.zip");
						console("[NOTICE] This message will dissapear when you issue the command \"/update-done\"");
						sleep(3);
					}else{
						$this->setProperty("last-update", time());
						console("[INFO] This is the latest DEVELOPMENT version");
					}
				}
			}else{
				$info = json_decode(Utils::curl_get("https://api.github.com/repos/shoghicp/PocketMine-MP/tags"), true);
				if($info === false or !isset($info[0])){
					console("[ERROR] GitHub API Error");
				}else{

					$newest = new VersionString(MAJOR_VERSION);
					$newest = array(-1, $newest->getNumber());
					foreach($info as $i => $tag){
						$update = new VersionString($tag["name"]);
						$update = $update->getNumber();
						if($update > $newest[1]){
							$newest = array($i, $update);
						}
					}

					if($newest[0] !== -1){
						$target = $info[$newest[0]];
						console("[NOTICE] A new STABLE version of PocketMine-MP has been released");
						console("[NOTICE] Version \"".(new VersionString($newest[1]))."\" #".$newest[1]." [".substr($target["commit"]["sha"], 0, 10)."]");
						console("[NOTICE] Download it at ".$target["zipball_url"]);
						console("[NOTICE] This message will dissapear as soon as you update");
						sleep(5);
					}else{
						$this->setProperty("last-update", time());
						console("[INFO] This is the latest STABLE version");
					}
				}

			}
		}
		if(file_exists(FILE_PATH."worlds/level.dat")){
			console("[NOTICE] Detected unimported map data. Importing...");
			$this->importMap(FILE_PATH."worlds/", true);
		}
		$this->server->mapName = $this->getProperty("level-name");
		$this->server->mapDir = FILE_PATH."worlds/".$this->server->mapName."/";
		if($this->server->mapName === false or trim($this->server->mapName) === "" or (!file_exists($this->server->mapDir."chunks.dat") and !file_exists($this->server->mapDir."chunks.dat.gz"))){
			if($this->server->mapName === false or trim($this->server->mapName) === ""){
				$this->server->mapName = "world";
			}
			$this->server->mapDir = FILE_PATH."worlds/".$this->server->mapName."/";
			$generator = "SuperflatGenerator";
			if($this->getProperty("generator") !== false and class_exists($this->getProperty("generator"))){
				$generator = $this->getProperty("generator");
			}
			$this->gen = new WorldGenerator($generator, $this->server->seed);
			if($this->getProperty("generator-settings") !== false and trim($this->getProperty("generator-settings")) != ""){
				$this->gen->set("preset", $this->getProperty("generator-settings"));
			}
			$this->gen->init();
			$this->gen->generate();
			$this->gen->save($this->server->mapDir, $this->server->mapName);
			$this->setProperty("level-name", $this->server->mapName);
			$this->setProperty("gamemode", 1);
		}
		$this->loadProperties();
		$this->server->loadMap();

		//Autoload all default APIs
		console("[INFO] Loading default APIs");
		$dir = dir(FILE_PATH."src/API/");
		while(false !== ($file = $dir->read())){
			if($file{0} !== "."){ //Hidden and upwards folders
				$API = basename($file, ".php");
				if(strtolower($API) !== "serverapi"){
					$name = strtolower(substr($API, 0, -3));
					$this->loadAPI($name, $API);
				}
			}
		}
		foreach($this->apiList as $ob){
			if(is_callable(array($ob, "init"))){
				$ob->init();
			}
		}

		$this->server->loadEntities();
	}

	public function __destruct(){
		foreach($this->apiList as $ob){
			if(is_callable($ob, "__destruct")){
				$ob->__destruct();
				unset($this->apiList[$ob]);
			}
		}
	}


	private function loadProperties(){
		if(isset($this->config["memory-limit"])){
			@ini_set("memory_limit", $this->config["memory-limit"]);
		}else{
			$this->config["memory-limit"] = "256M";
		}
		if(!isset($this->config["invisible"])){
			$this->config["invisible"] = false;
		}
		if(is_object($this->server)){
			$this->server->setType($this->config["server-type"]);
			$this->server->timePerSecond = $this->config["time-per-second"];
			$this->server->invisible = $this->config["invisible"];
			$this->server->maxClients = $this->config["max-players"];
			$this->server->description = $this->config["description"];
			$this->server->motd = $this->config["motd"];
			$this->server->gamemode = $this->config["gamemode"];
			$this->server->difficulty = $this->config["difficulty"];
			$this->server->whitelist = $this->config["white-list"];
			$this->server->reloadConfig();
		}
	}

	private function writeProperties(){
		if(is_object($this->server)){
			$this->config["server-id"] = $this->server->serverID;
		}
		$config = $this->config;
		$config["white-list"] = $config["white-list"] === true ? "true":"false";
		$config["invisible"] = $config["invisible"] === true ? "true":"false";
		$config["upnp-forwading"] = $config["upnp-forwading"] === true ? "true":"false";
		$prop = "#Pocket Minecraft PHP server properties\r\n#".date("D M j H:i:s T Y")."\r\n";
		foreach($config as $n => $v){
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
				case "port":
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
			$this->config[$n] = $v;
		}
	}

	public function start(){
		$this->server->init();
		unregister_tick_function(array($this->server, "tick"));
		$this->__destruct();
		unset($this->server);
		if($this->getProperty("upnp-forwading") === true ){
			console("[INFO] [UPnP] Removing port forward...");
			UPnP_RemovePortForward($this->getProperty("port"));
		}
		return $this->restart;
	}

	/*-------------------------------------------------------------*/

	public function addHandler($e, $c, $p = 5){
		return $this->server->addHandler($e, $c, $p);
	}

	public function dhandle($e, $d){
		return $this->server->handle($e, $d);
	}

	public function handle($e, &$d){
		return $this->server->handle($e, $d);
	}

	public function action($t, $c, $r = true){
		return $this->server->action($t, $c, $r);
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

	public function importMap($dir, $remove = false){
		if(file_exists($dir."level.dat")){
			$nbt = new NBT();
			$level = parseNBTData($nbt->loadFile($dir."level.dat"));
			console("[DEBUG] Importing map \"".$level["LevelName"]."\" gamemode ".$level["GameType"]." with seed ".$level["RandomSeed"], true, true, 2);
			unset($level["Player"]);
			$lvName = $level["LevelName"]."/";
			@mkdir(FILE_PATH."worlds/".$lvName, 0777);
			file_put_contents(FILE_PATH."worlds/".$lvName."level.dat", serialize($level));
			$entities = parseNBTData($nbt->loadFile($dir."entities.dat"));
			file_put_contents(FILE_PATH."worlds/".$lvName."entities.dat", serialize($entities["Entities"]));
			if(!isset($entities["TileEntities"])){
				$entities["TileEntities"] = array();
			}
			file_put_contents(FILE_PATH."worlds/".$lvName."tileEntities.dat", serialize($entities["TileEntities"]));
			console("[DEBUG] Imported ".count($entities["Entities"])." Entities and ".count($entities["TileEntities"])." TileEntities", true, true, 2);

			if($remove === true){
				rename($dir."chunks.dat", FILE_PATH."worlds/".$lvName."chunks.dat");
				unlink($dir."level.dat");
				@unlink($dir."level.dat_old");
				@unlink($dir."player.dat");
				unlink($dir."entities.dat");
			}else{
				copy($dir."chunks.dat", FILE_PATH."worlds/".$lvName."chunks.dat");
			}
			if($this->getProperty("level-name") === false){
				console("[INFO] Setting default level to \"".$level["LevelName"]."\"");
				$this->setProperty("level-name", $level["LevelName"]);
				$this->setProperty("gamemode", $level["GameType"]);
				$this->server->seed = $level["RandomSeed"];
				$this->server->spawn = array("x" => $level["SpawnX"], "y" => $level["SpawnY"], "z" => $level["SpawnZ"]);
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

	public function getList(){
		return $this->apiList;
	}

	public function loadAPI($name, $class, $dir = false){
		if($dir === false){
			$dir = FILE_PATH."src/API/";
		}
		$file = $dir.$class.".php";
		if(!file_exists($file)){
			console("[ERROR] API ".$name." [".$class."] in ".$dir." doesn't exist", true, true, 0);
			return false;
		}
		require_once($file);
		$this->$name = new $class($this->server);
		$this->apiList[] = $this->$name;
		console("[INFO] API ".$name." [".$class."] loaded");
	}

}