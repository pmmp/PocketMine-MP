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

/***REM_START***/
require_once(FILE_PATH."/src/pmf/PMF.php");
/***REM_END***/

define("PMF_CURRENT_PLUGIN_VERSION", 0x01);

class PMFPlugin extends PMF{
	private $pluginData = array();
	public function __construct($file){
		$this->load($file);
		$this->parseInfo();
		$this->parsePlugin();
	}
	
	public function getPluginInfo(){
		return $this->pluginData;
	}
	
	protected function parsePlugin(){
		if($this->getType() !== 0x01){
			return false;
		}
		$this->seek(5);
		$this->pluginData["fversion"] = ord($this->read(1));
		if($this->pluginData["fversion"] > PMF_CURRENT_PLUGIN_VERSION){
			return false;
		}
		$this->pluginData["name"] = $this->read(Utils::readShort($this->read(2), false));
		$this->pluginData["version"] = $this->read(Utils::readShort($this->read(2), false));
		$this->pluginData["author"] = $this->read(Utils::readShort($this->read(2), false));
		if($this->pluginData["fversion"] >= 0x01){
			$this->pluginData["apiversion"] = $this->read(Utils::readShort($this->read(2), false));
		}else{
			$this->pluginData["apiversion"] = Utils::readShort($this->read(2), false);
		}
		$this->pluginData["class"] = $this->read(Utils::readShort($this->read(2), false));
		$this->pluginData["identifier"] = $this->read(Utils::readShort($this->read(2), false)); //Will be used to check for updates
		$this->pluginData["extra"] = gzinflate($this->read(Utils::readShort($this->read(2), false))); //Additional custom plugin data
		$this->pluginData["code"] = "";
		while(!feof($this->fp)){
			$this->pluginData["code"] .= $this->read(4096);
		}
		$this->pluginData["code"] = gzinflate($this->pluginData["code"]);
	}

}