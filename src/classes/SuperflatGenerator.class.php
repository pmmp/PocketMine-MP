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


class SuperflatGenerator{
	private $config, $spawn, $structure;
	public function __construct($seed){
		$this->config = array(
			"preset" => "7;20x1;3x3;2",
			"spawn-surface" => 24,
			"spawn-radius" => 10,
			"torches" => 0,
			"seed" => (int) $seed,
		);
		$this->parsePreset();
	}

	public function set($name, $value){
		$this->config[$name] = $value;
		if($name === "preset"){
			$this->parsePreset();
		}
	}

	private function parsePreset(){
		$this->structure = array(
			0 => "",
			1 => "",
			2 => str_repeat("\x00", 64),
			3 => str_repeat("\x00", 64),
		);
		$preset = explode(";", trim($this->config["preset"]));
		foreach($preset as $i => $data){
			$num = 1;
			if(preg_match('#([a-zA-Z\-_]*)\((.*)\)#', $data, $matches) > 0){ //Property
				$this->config[$matches[1]] = $matches[2];
				continue;
			}elseif(preg_match('#([0-9]*)x([0-9:]*)#', $data, $matches) > 0){
				$num = (int) $matches[1];
				$d = explode(":", $matches[2]);
			}else{
				$d = explode(":", $data);
			}
			$block = (int) array_shift($d);
			$meta = (int) @array_shift($d);
			for($j = 0; $j < $num; ++$j){
				$this->structure[0] .= chr($block & 0xFF);
				$this->structure[1] .= substr(dechex($meta & 0x0F), -1);
			}
		}
		$this->structure[1] = pack("h*", str_pad($this->structure[1], (strlen($this->structure[1])&0xFE) + 2, "0", STR_PAD_RIGHT)); //invert nibbles
		$this->structure[0] = substr($this->structure[0], 0, 128);
		$this->structure[1] = substr($this->structure[1], 0, 64);
		$this->structure[2] = substr($this->structure[2], 0, 64);
		$this->structure[3] = substr($this->structure[3], 0, 64);
	}

	public function init(){
		$this->spawn = array(128, strlen($this->structure[0]), 128);
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