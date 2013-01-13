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


class NormalGenerator{
	private $config, $spawn, $structure;
	public function __construct($seed){
		$this->config = array(
			"seed" => (int) $seed,
		);
	}

	public function set($name, $value){
		$this->config[$name] = $value;
	}

	public function init(){
		$this->spawn = array(128, 128, 128);
	}

	public function getSpawn(){
		return $this->spawn;
	}

	public function getColumn($x, $z){
		$x = (int) $x;
		$z = (int) $z;
		$column = $this->structure;
		if(floor(sqrt(pow($x - $this->spawn[0], 2) + pow($z - $this->spawn[2], 2))) <= $this->config["spawn-radius"]){
			$column[0]{strlen($column[0])-1} = chr($this->config["spawn-surface"]);
		}
		if(($x % 8) === 0 and ($z % 8) === 0 and $this->config["torches"] == "1"){
			$column[0] .= chr(50);
		}
		$column[0] .= str_repeat(chr(0), 128 - strlen($column[0]));
		$column[1] .= str_repeat(chr(0), 64 - strlen($column[1]));
		$column[2] .= str_repeat(chr(0), 64 - strlen($column[2]));
		$column[3] .= str_repeat(chr(0), 64 - strlen($column[3]));
		return $column;
	}

}