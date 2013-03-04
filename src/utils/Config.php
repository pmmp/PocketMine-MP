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

define("CONFIG_DETECT", -1); //Detect by file extension
define("CONFIG_PROPERTIES", 0); // .properties
define("CONFIG_CNF", CONFIG_PROPERTIES); // .cnf
define("CONFIG_JSON", 1); // .js, .json
define("CONFIG_YAML", 2); // .yml, .yaml
//define("CONFIG_EXPORT", 3); // .export, .xport
define("CONFIG_SERIALIZED", 4); // .sl
define("CONFIG_LIST", 5); // .txt, .list

class Config{
	private $config;
	private $file;
	private $correct;
	private $type = CONFIG_DETECT;
	public static $formats = array(
		"properties" => CONFIG_PROPERTIES,
		"cnf" => CONFIG_CNF,
		"conf" => CONFIG_CNF,
		"config" => CONFIG_CNF,
		"json" => CONFIG_JSON,
		"js" => CONFIG_JSON,
		"yml" => CONFIG_YAML,
		"yaml" => CONFIG_YAML,
		//"export" => CONFIG_EXPORT,
		//"xport" => CONFIG_EXPORT,
		"sl" => CONFIG_SERIALIZED,
		"serialize" => CONFIG_SERIALIZED,
		"txt" => CONFIG_LIST,
		"list" => CONFIG_LIST,	
	);
	public function __construct($file, $type = CONFIG_DETECT, $default = array(), &$correct = null){
		$this->load($file, $type, $default);
		$correct = $this->check();
	}
	
	public function load($file, $type = CONFIG_DETECT, $default = array()){
		$this->correct = true;
		$this->type = (int) $type;
		$this->file = $file;
		if(!is_array($default)){
			$default = array();
		}
		if(!file_exists($file)){
			$this->config = $default;
		}else{			
			if($this->type === CONFIG_DETECT){
				$extension = explode(".", basename($this->file));
				$extension = strtolower(trim(array_pop($extension)));
				if(isset(Config::$formats[$extension])){
					$this->type = Config::$formats[$extension];
				}else{
					$this->correct = false;
				}
			}
			if($this->correct === true){
				$content = @file_get_contents($this->file);
				switch($this->type){
					case CONFIG_PROPERTIES:
					case CONFIG_CNF:
						$this->parseProperties($content);
						break;
					case CONFIG_JSON:
						$this->config = @json_decode($content, true);
						break;
					case CONFIG_YAML:
						$this->config = Spyc::YAMLLoad($content);
						break;
					case CONFIG_SERIALIZED:
						$this->config = @unserialize($content);
						break;
					case CONFIG_LIST:
						$this->parseList($content);
						break;
					default:
						$this->correct = false;
						return false;
						break;
				}
				if(!is_array($this->config)){
					$this->config = $default;
				}
				$this->fillDefaults($default, $this->config);
			}else{
				return false;
			}
		}
		return true;
	}
	
	public function check(){
		return $this->correct === true;
	}
	
	public function save(){
		if($this->correct === true){
			switch($this->type){
				case CONFIG_PROPERTIES:
				case CONFIG_CNF:
					$content = $this->writeProperties();
					break;
				case CONFIG_JSON:
					$content = json_encode($this->config, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
					break;
				case CONFIG_YAML:
					$content = Spyc::YAMLDump($this->config);
					break;
				case CONFIG_SERIALIZED:
					$content = @serialize($this->config);
					break;
				case CONFIG_LIST:
					$content = implode("\r\n", array_keys($this->config));
					break;
				}
				@file_put_contents($this->file, $content, LOCK_EX);
				return true;
		}else{
			return false;
		}
	}
	
	public function get($k){
		if($this->correct === false or !isset($this->config[$k])){
			return false;
		}
		return ($this->config[$k]);
	}
	
	public function set($k, $v = true){
		$this->config[$k] = $v;
	}
	
	public function exists($k){
		return isset($this->config[$k]);
	}
	
	public function remove($k){
		unset($this->config[$k]);
	}
	
	public function getAll($keys = false){
		return ($keys === true ? array_keys($this->config):$this->config);
	}
	
	private function fillDefaults($default, &$data){
		foreach($default as $k => $v){
			if(is_array($v)){
				if(!isset($data[$k]) or !is_array($data[$k])){
					$data[$k] = array();
				}
				$this->fillDefaults($v, $data[$k]);
			}elseif(!isset($data[$k])){
				$data[$k] = $v;
			}
		}
	}

	private function parseList($content){
		foreach(explode("\n", trim(str_replace("\r\n", "\n", $content))) as $v){
			$v = trim($v);
			if($v == ""){
				continue;
			}
			$this->config[$v] = true;
		}
	}
	
	private function writeProperties(){
		$content = "#Properties Config file\r\n#".date("D M j H:i:s T Y")."\r\n";
		foreach($this->config as $k => $v){
			if(is_bool($v) === true){
				$v = $v === true ? "on":"off";
			}elseif(is_array($v)){
				$v = implode(";", $v); 
			}
			$content .= $k."=".$v."\r\n";
		}
		return $content;
	}
	
	private function parseProperties($content){
		if(preg_match_all('/([a-zA-Z0-9\-_]*)=([^\r\n]*)/u', $content, $matches) > 0){ //false or 0 matches
			foreach($matches[1] as $i => $k){
				$v = trim($matches[2][$i]);
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
				if(isset($this->config[$k])){
					console("[NOTICE] [Config] Repeated property ".$k." on file ".$this->file, true, true, 2);
				}
				$this->config[$k] = $v;
			}
		}
	}

}