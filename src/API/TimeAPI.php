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

class TimeAPI{
	var $phases = array(
		"day" => 0,
		"sunset" => 9500,
		"night" => 10900,
		"sunrise" => 17800,
	);
	private $server;
	function __construct(){
		$this->server = ServerAPI::request();
	}

	public function init(){
		$this->server->api->console->register("time", "<check|set|add> [time]", array($this, "commandHandler"));
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "time":
				$p = strtolower(array_shift($params));
				switch($p){
					case "check":
						$output .= "Time: ".$this->getDate().", ".$this->getPhase()." (".$this->get(true).")\n";
						break;
					case "add":
						$output .= "Set the time to ".$this->add(array_shift($params))."\n";
						break;
					case "set":
						$output .= "Set the time to ".$this->set(array_shift($params))."\n";
						break;
					case "sunrise":
					case "day":
					case "sunset":
					case "night":
						$output .= "Set the time to ".$this->set($p)."\n";
						break;
					default:
						$output .= "Usage: /time <check|set|add> [time]\n";
						break;
				}
				break;
		}
		return $output;
	}

	public function night(){
		return $this->set("night");
	}
	public function day(){
		return $this->set("day");
	}
	public function sunrise(){
		return $this->set("sunrise");
	}
	public function sunset(){
		return $this->set("sunset");
	}

	public function get($raw = false){
		return $raw === true ? $this->server->time:abs($this->server->time) % 19200;
	}

	public function add($time){
		$this->server->time += (int) $time;
		return $this->server->time;
	}

	public function getDate($time = false){
		$time = $time === false ? $this->get():$time;
		return str_pad(strval((floor($time /800) + 6) % 24), 2, "0", STR_PAD_LEFT).":".str_pad(strval(floor(($time % 800) / 13.33)), 2, "0", STR_PAD_LEFT);
	}

	public function getPhase($time = false){
		$time = $time === false ? $this->get():$time;
		if($time < $this->phase["sunset"]){
			$time = "day";
		}elseif($time < $this->phase["night"]){
			$time = "sunset";
		}elseif($time < $this->phase["sunrise"]){
			$time = "night";
		}else{
			$time = "sunrise";
		}
		return $time;
	}

	public function set($time){
		if(is_string($time) and isset($this->phases[$time])){
			$this->server->time = $this->phases[$time];
		}else{
			$this->server->time = (int) $time;
		}
		return $this->server->time;
	}


}