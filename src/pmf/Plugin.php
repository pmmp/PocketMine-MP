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

/***REM_START***/
require_once(FILE_PATH."/src/pmf/PMF.php");
/***REM_END***/

define("PMF_CURRENT_PLUGIN_VERSION", 0x02);

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
		if($this->pluginData["fversion"] >= 0x02){
			$data = explode(";", gzinflate($this->read(Utils::readInt($this->read(4)))));
			$this->pluginData["extra"] = array();
			foreach($data as $v){
				$v = trim($v);
				if($v != ""){
					$v = base64_decode($v);
					$kl = strpos($v, ":");
					$this->pluginData["extra"][substr($v, 0, $kl)] = substr($v, $kl + 1);
				}
			}
			
		}else{
			$this->pluginData["extra"] = gzinflate($this->read(Utils::readShort($this->read(2), false)));
		}
		$this->pluginData["code"] = "";
		while(!feof($this->fp)){
			$this->pluginData["code"] .= $this->read(4096);
		}
		$this->pluginData["code"] = gzinflate($this->pluginData["code"]);
	}

}