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
	private $config, $spawn, $seed, $structure;
	public function __construct($seed){
		$this->seed = (int) $seed;
		$this->config = array(
			"preset" => "7;20x1;3x3;2;spawn-surface(24);spawn-radius(10)",
			"spawn-surface" => 24,
			"spawn-radius" => 10,
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
		$preset = explode(";", $this->config["preset"]);
		$this->structure = "";
		foreach($preset as $i => $data){
			if(preg_match('#([a-zA-Z\-_]*)\((.*)\)#', $data, $matches) > 0){ //Property
				$this->config[$matches[1]] = $matches[2];
			}elseif(preg_match('#([0-9]*)x([0-9]*)#', $data, $matches) > 0){
				$num = (int) $matches[1];
				$block = (int) $matches[2];
				for($j = 0; $j < $num; ++$j){
					$this->structure .= chr($block%256);
				}
			}else{
				$block = (int) $data;
				$this->structure .= chr($block%256);
			}
		}
		$this->structure = substr($this->structure, 0, 128);
	}
	
	public function init(){
		$this->spawn = array(128, strlen($this->structure), 128);
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
		$column[0] = $this->structure;
		if(floor(sqrt(pow($x - $this->spawn[0], 2) + pow($z - $this->spawn[2], 2))) <= $this->config["spawn-radius"]){
			$column[0]{strlen($column[0])-1} = chr($this->config["spawn-surface"]);
		}
		$column[0] .= str_repeat(chr(0), 128 - strlen($column[0]));
		$column[1] = str_repeat(chr(0), 64);
		$column[2] = str_repeat(chr(0), 64);
		$column[3] = str_repeat(chr(0), 64);
		return $column;
	}

}