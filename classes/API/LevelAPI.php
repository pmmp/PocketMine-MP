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

class LevelAPI{
	private $server, $map, $active = false;
	function __construct($server){
		$this->server = $server;
		$this->map = $this->server->map;
		if($this->map !== false){
			$this->active = true;
		}
	}
	
	private function check(){
		if($this->active === false and $this->server->map === false){
			return false;
		}
		$this->active = true;
		return true;
	}

	public function getChunk($X, $Z){
		if($this->check() and isset($this->map->map[$X][$Z])){
			return $this->map->map[$X][$Z];		
		}
		return false;
	}
	
	public function getBlock($x, $y, $z){
		if($this->check()){
			return $this->map->getBlock($x, $y, $z);		
		}
		return array(0,0);
	}
	
	public function getOrderedChunk($X, $Z, $columnsPerPacket = 2){
		$columnsPerPacket = max(1, (int) $columnsPerPacket);
		$c = $this->getChunk($X, $Z);
		if($c === false){
			return array(str_repeat("\x00", 256));
		}
		$ordered = array();
		for($i = 0;$i < 0xff; ){
			$ordered[$i] = str_repeat("\x00", $i);
			for($j = 0; $j < $columnsPerPacket; ++$j){
				if(($i + $j) > 0xff){
					break;
				}
				$ordered[$i] .= "\xff";
				for($k = 0; $k < 8; ++$k){
					$ordered[$i] .= substr($c[0][$i+$j], $k << 4, 16); //Block data
					$ordered[$i] .= substr($c[1][$i+$j], $k << 3, 8); //Meta data
				}
			}
			$i += $columnsPerPacket;
		}
		return $ordered;
	}
}