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

class Container{
	private $payload = "", $whitelist = false, $blacklist = false;
	public function __construct($payload = "", $whitelist = false, $blacklist = false){
		$this->payload = $payload;
		if(is_array($whitelist)){
			$this->whitelist = $whitelist;
		}
		if(is_array($blacklist)){
			$this->blacklist = $blacklist;
		}
	}
	
	public function get(){
		return $this->payload;
	}
	
	public function check($target){
		$w = true;
		$b = false;
		if($this->whitelist !== false){
			$w = false;
			if(in_array($target, $this->whitelist, true)){
				$w = true;
			}
		}else{
			$w = true;
		}
		if($this->blacklist !== false){
			$b = false;
			if(in_array($target, $this->blacklist, true)){
				$b = true;
			}
		}else{
			$b = false;
		}
		if($w === false or $b === true){
			return false;
		}
		return true;
	}
	
	
	public function __toString(){
		return $this->payload;
	}
}