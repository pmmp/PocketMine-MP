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
			$b = true;
			if(in_array($target, $this->blacklist, true)){
				$b = false;
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