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

class PluginAPI extends stdClass{
	private $server;
	private $plugins = array();
	public function __construct(){
		$this->server = ServerAPI::request();
	}

	public function getList(){
		$list = array();
		foreach($this->plugins as $p){
			$list[] = $p[1];
		}
		return $list;
	}

	public function getInfo($className){
		$className = strtolower($className);
		if(!isset($this->plugins[$className])){
			return false;
		}
		$plugin = $this->plugins[$className];
		return array($plugin[1], get_class_methods($plugin[0]));
	}

	public function load($file){
		if(strtolower(substr($file, -3)) === "pmf"){
			$pmf = new PMFPlugin($file);
			$info = $pmf->getPluginInfo();
		}else{
			$content = file_get_contents($file);
			$info = strstr($content, "*/", true);
			$content = str_repeat(PHP_EOL, substr_count($info, "\n")).substr(strstr($content, "*/"),2);
			if(preg_match_all('#([a-zA-Z0-9\-_]*)=([^\r\n]*)#u', $info, $matches) == 0){ //false or 0 matches
				console("[ERROR] Failed parsing of ".basename($file));
				return false;
			}
			$info = array();
			foreach($matches[1] as $k => $i){
				$v = $matches[2][$k];
				switch(strtolower($v)){
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
				$info[$i] = $v;
			}
			$info["code"] = $content;
			$info["class"] = trim(strtolower($info["class"]));
		}
		if(!isset($info["name"]) or !isset($info["version"]) or !isset($info["class"]) or !isset($info["author"])){
			console("[ERROR] Failed parsing of ".basename($file));
			return false;
		}
		console("[INFO] Loading plugin \"".FORMAT_GREEN.$info["name"].FORMAT_RESET."\" ".FORMAT_AQUA.$info["version"].FORMAT_RESET." by ".FORMAT_AQUA.$info["author"].FORMAT_RESET);
		if($info["class"] !== "none" and class_exists($info["class"])){
			console("[ERROR] Failed loading plugin: class already exists");
			return false;
		}
		if(eval($info["code"]) === false or ($info["class"] !== "none" and !class_exists($info["class"]))){
			console("[ERROR] Failed loading {$info['name']}: evaluation error");
			return false;
		}
		
		$className = $info["class"];
		$apiversion = array_map("intval", explode(",", (string) $info["apiversion"]));
		if(!in_array((string) CURRENT_API_VERSION, $apiversion)){
			console("[WARNING] Plugin \"".$info["name"]."\" may not be compatible with the API (".$info["apiversion"]." != ".CURRENT_API_VERSION.")! It can crash or corrupt the server!");
		}
		
		if($info["class"] !== "none"){			
			$object = new $className($this->server->api, false);
			if(!($object instanceof Plugin)){
				console("[ERROR] Plugin \"".$info["name"]."\" doesn't use the Plugin Interface");
				if(method_exists($object, "__destruct")){
					$object->__destruct();
				}
				$object = null;
				unset($object);
			}else{
				$this->plugins[$className] = array($object, $info);
			}
		}else{
			$this->plugins[md5($info["name"])] = array(new DummyPlugin($this->server->api, false), $info);
		}
	}

	public function get(Plugin $plugin){
		foreach($this->plugins as &$p){
			if($p[0] === $plugin){
				return $p;
			}
		}
		return false;
	}
	
	public function pluginsPath(){
		$path = join(DIRECTORY_SEPARATOR, array(DATA_PATH."plugins", ""));
		@mkdir($path);
		return $path;
	}
	
	
	public function configPath(Plugin $plugin){
		$p = $this->get($plugin);
		if($p === false){
			return false;
		}
		$path = $this->pluginsPath() . $p[1]["name"] . DIRECTORY_SEPARATOR;
		$this->plugins[$p[1]["class"]][1]["path"] = $path;
		@mkdir($path);
		return $path;
	}

	public function createConfig(Plugin $plugin, $default = array()){
		$p = $this->get($plugin);
		if($p === false){
			return false;
		}
		$path = $this->configPath($plugin);
		$cnf = new Config($path."config.yml", CONFIG_YAML, $default);
		$cnf->save();
		return $path;
	}

	private function fillDefaults($default, &$yaml){
		foreach($default as $k => $v){
			if(is_array($v)){
				if(!isset($yaml[$k]) or !is_array($yaml[$k])){
					$yaml[$k] = array();
				}
				$this->fillDefaults($v, $yaml[$k]);
			}elseif(!isset($yaml[$k])){
				$yaml[$k] = $v;
			}
		}
	}

	public function readYAML($file){
		return Spyc::YAMLLoad(file_get_contents($file));
	}

	public function writeYAML($file, $data){
		return file_put_contents($file, Spyc::YAMLDump($data));
	}

	public function init(){
		$this->server->event("server.start", array($this, "initAll"));
		$this->loadAll();
	}

	public function loadAll(){
		$dir = dir($this->pluginsPath());
		while(false !== ($file = $dir->read())){
			if($file{0} !== "."){
				$ext = strtolower(substr($file, -3));
				if($ext === "php" or $ext === "pmf"){
					$this->load($dir->path . $file);
				}
			}
		}
	}
	
	public function initAll(){
		console("[INFO] Starting plugins...");
		foreach($this->plugins as $p){
			$p[0]->init(); //ARGHHH!!! Plugin loading randomly fails!!
		}
	}
}


interface Plugin{
	public function __construct(ServerAPI $api, $server = false);
	public function init();
	public function __destruct();
}

class DummyPlugin implements Plugin{
	public function __construct(ServerAPI $api, $server = false){	
	}
	
	public function init(){
	}
	
	public function __destruct(){
	}
}
