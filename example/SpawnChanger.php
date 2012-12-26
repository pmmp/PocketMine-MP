<?php

/*
__PocketMine Plugin__
name=SpawnChanger
description=Change the spawn coordinates, or make it default for all players!
version=0.0.1
author=shoghicp
class=SpawnChanger
*/


class SpawnChanger implements Plugin{
	private $api, $config, $path;
	public function __construct($api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$spawn = $this->api->level->getSpawn();
		$this->path = $this->api->plugin->createConfig($this, array(
			"spawnX" => $spawn["x"],
			"spawnY" => $spawn["y"],
			"spawnZ" => $spawn["z"],
			"custom-spawn" => false,
			"force-spawn" => false,
		));
		$this->config = $this->api->plugin->readYAML($this->path."config.yml");
		if($this->config["custom-spawn"] === false){
			$this->config["spawnX"] = $spawn["x"];
			$this->config["spawnY"] = $spawn["y"];
			$this->config["spawnZ"] = $spawn["z"];
			$this->api->plugin->writeYAML($this->path."config.yml", $this->config);
		}
		$this->api->addHandler("api.player.offline.get", array($this, "handle"), 15);
		$this->api->console->register("spawnchanger", "SpawnChanger init point managing", array($this, "command"));
		$this->api->console->register("spawn", "Teleports to spawn", array($this, "command"));
	}
	
	public function __destruct(){
	
	}	

	public function command($cmd, $args){
		switch($cmd){
			case "spawnchanger":
					switch(strtolower(array_shift($args))){
						case "force":
							$l = array_shift($args);
							if($l != "0" and $l != "1"){
								console("[SpawnChanger] Usage: /spawnchanger force <1 | 0>");
							}else{
								$this->config["force-spawn"] = $l == "0" ? false:true;
								if($this->config["force-spawn"] === true){
									console("[SpawnChanger] Forced spawn point");
								}else{
									console("[SpawnChanger] Freed pawn point");
								}
								$this->api->plugin->writeYAML($this->path."config.yml", $this->config);
							}						
							break;
						case "set":
							$z = array_pop($args);
							$y = array_pop($args);
							$x = array_pop($args);
							if($x === null or $y === null or $z === null){
								console("[SpawnChanger] Usage: /spawnchanger set <x> <y> <z>");
							}else{
								$this->config["custom-spawn"] = true;
								$this->config["spawnX"] = (float) $x;
								$this->config["spawnY"] = (float) $y;
								$this->config["spawnZ"] = (float) $z;
								console("[SpawnChanger] Spawn point set at X ".$this->config["spawnX"]." Y ".$this->config["spawnY"]." Z ".$this->config["spawnZ"]);
								$this->api->plugin->writeYAML($this->path."config.yml", $this->config);
							}
							break;
						default:
							console("[SpawnChanger] Always spawn player in spawn point: /spawnchanger force <1 | 0>");
							console("[SpawnChanger] Set the spawn point: /spawnchanger set <x> <y> <z>");
							break;
					}
				break;
			case "spawn":
				if($this->api->player->tppos(implode(" ", $args), $this->config["spawnX"], $this->config["spawnY"], $this->config["spawnZ"]) !== false){
					console("[SpawnChanger] Teleported to spawn!");
				}else{
					console("[SpawnChanger] Usage: /spawn <player>");
				}
				break;
		}
	}
	
	public function handle(&$data, $event){
		switch($event){
			case "api.player.offline.get":
				if($this->config["force-spawn"] === true){
					$data["spawn"]["x"] = $this->config["spawnX"];
					$data["spawn"]["y"] = $this->config["spawnY"];
					$data["spawn"]["z"] = $this->config["spawnZ"];
				}
				break;
		}
	}

}