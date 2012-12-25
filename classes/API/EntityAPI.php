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

class EntityAPI{
	private $server;
	function __construct($server){
		$this->server = $server;
		$this->server->addHandler("player.death", array($this, "handle"), 1);
	}
	
	public function init(){
		$this->server->api->console->register("give", "Give items to a player", array($this, "commandHandler"));
	}
	
	public function handle($data, $event){
		switch($event){
			case "player.death":
				$message = $data["name"];
				if(is_numeric($data["cause"]) and isset($this->entities[$data["cause"]])){
					$e = $this->api->entity->get($data["cause"]);
					switch($e->class){
						case ENTITY_PLAYER:
							$message .= " was killed by ".$e->name;
							break;
						default:
							$message .= " was killed";
							break;
					}
				}else{
					switch($data["cause"]){
						default:
							$message .= " was killed";
							break;
					}
				}
				$this->server->chat(false, $message);
				break;
		}	
	}
	
	public function commandHandler($cmd, $params){
		switch($cmd){
			case "give":
				break;
		}
	}

	public function get($eid){
		if(isset($this->server->entities[$eid])){
			return $this->server->entities[$eid];
		}
		return false;
	}
	
	public function getAll(){
		return $this->server->entities;
	}
	
	public function heal($eid, $heal = 1, $cause){
		$this->harm($eid, -$heal, $cause);
	}
	
	public function harm($eid, $attack = 1, $cause){
		$e = $this->get($eid);
		if($e === false or $e->dead === true){
			return false;
		}
		$e->setHealth($e->getHealth()-$attack, $cause);
	}
	
	public function add($class, $type = 0, $data = array()){
		$eid = $this->server->eidCnt++;
		$this->server->entities[$eid] = new Entity($this->server, $eid, $class, $type, $data);
		return $this->server->entities[$eid];
	}
	
	public function spawnTo($eid, $player){
		$e = $this->get($eid);
		if($e === false){
			return false;
		}
		$e->spawn($player);
	}
	
	public function spawnToAll($eid){
		$e = $this->get($eid);
		if($e === false){
			return false;
		}
		foreach($this->server->api->player->getAll() as $player){
			if($player->eid !== false){
				$e->spawn($player);
			}
		}
	}
	
	public function spawnAll($player){
		foreach($this->getAll() as $e){
			$e->spawn($player);
		}
	}
	
	public function remove($eid){
		if(isset($this->server->entities[$eid])){
			$this->server->entities[$eid]->close();
			unset($this->server->entities[$eid]);
		}
	}	
}