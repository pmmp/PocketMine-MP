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

class TimeAPI{
	public static $phases = array(
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
				$level = false;
				if($issuer instanceof Player){
					$level = $issuer->level;
				}
				$p = strtolower(array_shift($params));
				switch($p){
					case "check":
						$output .= "Time: ".$this->getDate($level).", ".$this->getPhase($level)." (".$this->get(true, $level).")\n";
						break;
					case "add":
						$output .= "Set the time to ".$this->add(array_shift($params), $level)."\n";
						break;
					case "set":
						$output .= "Set the time to ".$this->set(array_shift($params), $level)."\n";
						break;
					case "sunrise":
					case "day":
					case "sunset":
					case "night":
						$output .= "Set the time to ".$this->set($p, $level)."\n";
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

	public function get($raw = false, $level = false){
		if(!($level instanceof Level)){
			$level = $this->server->api->level->getDefault();
		}
		return $raw === true ? $level->getTime():abs($level->getTime()) % 19200;
	}

	public function add($time, $level = false){
		if(!($level instanceof Level)){
			$level = $this->server->api->level->getDefault();
		}
		$level->setTime($level->getTime() + (int) $time);
	}

	public function getDate($time = false){
		$time = !is_integer($time) ? $this->get(false, $time):$time;
		return str_pad(strval((floor($time /800) + 6) % 24), 2, "0", STR_PAD_LEFT).":".str_pad(strval(floor(($time % 800) / 13.33)), 2, "0", STR_PAD_LEFT);
	}

	public function getPhase($time = false){
		$time = !is_integer($time) ? $this->get(false, $time):$time;
		if($time < TimeAPI::$phases["sunset"]){
			$time = "day";
		}elseif($time < TimeAPI::$phases["night"]){
			$time = "sunset";
		}elseif($time < TimeAPI::$phases["sunrise"]){
			$time = "night";
		}else{
			$time = "sunrise";
		}
		return $time;
	}

	public function set($time, $level = false){
		if(!($level instanceof Level)){
			$level = $this->server->api->level->getDefault();
		}
		if(is_string($time) and isset(TimeAPI::$phases[$time])){
			$level->setTime(TimeAPI::$phases[$time]);
		}else{
			$level->setTime((int) $time);
		}
		return $level->getTime();
	}


}
