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
define("CONFIG_EXPORT", 3); // .export, .xport
define("CONFIG_SERIALIZE", 4); // .sl
define("CONFIG_LIST", 5); // .txt, .list

class Config{
	private $config;
	private $file;
	private $type = CONFIG_DETECT;
	private $correct = true;
	public static $formats = array(
		"properties" => CONFIG_PROPERTIES,
		"cnf" => CONFIG_CNF,
		"conf" => CONFIG_CNF,
		"config" => CONFIG_CNF,
		"json" => CONFIG_JSON,
		"js" => CONFIG_JSON,
		"yml" => CONFIG_YAML,
		"yaml" => CONFIG_YAML,
		"export" => CONFIG_EXPORT,
		"xport" => CONFIG_EXPORT,
		"sl" => CONFIG_SERIALIZE,
		"serialize" => CONFIG_SERIALIZE,
		"txt" => CONFIG_LIST,
		"list" => CONFIG_LIST,	
	);
	public function __construct($file, $type = CONFIG_DETECT, $default = array()){
		$this->type = (int) $type;
		$this->file = $file;
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
				}
				var_dump($this->config);
				die();
			}
		}
	}
	
	private function parseProperties($content){
		if(preg_match_all('/([a-zA-Z0-9\-_]*)=([^\r\n]*)/u', $content, $matches) == 0){ //false or 0 matches
			foreach($matches[1] as $k => $i){
				$v = trim($matches[2][$k]);
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