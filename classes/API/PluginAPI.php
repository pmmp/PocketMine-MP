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

class PluginAPI extends stdClass{
	private $server, $plugins;
	public function __construct($server){
		$this->server = $server;
		$this->plugins = array();
	}
	
	private function load($file){
		$content = file_get_contents($file);
		$info = strstr($content, "*/", true);
		$content = substr(strstr($content, "*/"),2);
		if(preg_match_all('#([a-zA-Z0-9\-_]*)=([^\r\n]*)#u', $info, $matches) == 0){ //false or 0 matches
			console("[ERROR] [PluginAPI] Failed parsing of ".basename($file));
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
		if(!isset($info["name"]) or !isset($info["version"]) or !isset($info["class"]) or !isset($info["author"])){
			console("[ERROR] [PluginAPI] Failed parsing of ".basename($file));
		}
		console("[INFO] [PluginAPI] Loading plugin \"".$info["name"]."\" ".$info["version"]." by ".$info["author"]);
		if(eval($content) === false or !class_exists($info["class"])){
			console("[ERROR] [PluginAPI] Failed loading plugin");
		}
		$className = trim($info["class"]);
		if(isset($info["api"]) and $info["api"] !== true){
			console("[NOTICE] [PluginAPI] Plugin \"".$info["name"]."\" got raw access to Server methods");
		}
		$object = new $className($this->server->api, ((isset($info["api"]) and $info["api"] !== true) ? $this->server:false));
		$this->plugins[$className] = array($object, $info);
	}
	
	public function init(){
		console("[INFO] Loading Plugins...");
		$dir = dir(FILE_PATH."data/plugins/");
		while(false !== ($file = $dir->read())){
			if($file !== "." and $file !== ".."){
				if(strtolower(substr($file, -3)) === "php"){
					$this->load(FILE_PATH."data/plugins/" . $file);
				}
			}
		}
		foreach($this->plugins as $p){
			if(method_exists($p[0], "init")){
				$p[0]->init();
			}
		}
	}
}