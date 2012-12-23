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


class DefaultGenerator{
	private $config, $spawn, $seed;
	public function __construct($seed){
		$this->seed = (int) $seed;
		$this->config = array(
			"base-height" => 30,
			"surface" => 2,
			"spawn-surface" => 24,
			"spawn-radius" => 10,
			"fill" => 3,
			"floor" => 7,
		);
		$this->spawn = array(128, $this->config["base-height"] + 1, 128);
	}
	
	public function set($name, $value){
		$this->config[$name] = $value;
	}
	
	public function init(){
		$this->config["base-height"] = max(2, $this->config["base-height"]);
		$this->spawn = array(128, $this->config["base-height"] + 1, 128);
	}
	
	public function getSpawn(){
		return $this->spawn;
	}
	
	public function getColumn($x, $z){
		$x = (int) $x;
		$z = (int) $z;
		$column = array(
			0 => "",
			1 => "",
			2 => "",
			3 => "",
		);
		$column[0] = chr($this->config["floor"]) . str_repeat(chr($this->config["fill"]), $this->config["base-height"] - 2);
		if(floor(sqrt(pow($x - $this->spawn[0], 2) + pow($z - $this->spawn[2], 2))) <= $this->config["spawn-radius"]){
			$column[0] .= chr($this->config["spawn-surface"]);
		}else{
			$column[0] .= chr($this->config["surface"]);
		}
		$column[0] .= str_repeat(chr(0), 128 - strlen($column[0]));
		$column[1] = str_repeat(chr(0), 64);
		$column[2] = str_repeat(chr(0), 64);
		$column[3] = str_repeat(chr(0), 64);
		return $column;
	}

}