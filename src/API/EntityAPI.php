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
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}

	public function get($eid){
		if(isset($this->server->entities[$eid])){
			return $this->server->entities[$eid];
		}
		return false;
	}
	
	public function init(){
	
	}

	public function getAll(){
		return $this->server->entities;
	}

	public function heal($eid, $heal = 1, $cause){
		$this->harm($eid, -$heal, $cause);
	}

	public function harm($eid, $attack = 1, $cause, $force = false){
		$e = $this->get($eid);
		if($e === false or $e->dead === true){
			return false;
		}
		$e->setHealth($e->getHealth() - $attack, $cause, $force);
	}

	public function add($class, $type = 0, $data = array()){
		$eid = $this->server->eidCnt++;
		$this->server->entities[$eid] = new Entity($this->server, $eid, $class, $type, $data);
		$this->server->handle("entity.add", $this->server->entities[$eid]);
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
			if($player->eid !== false and $player->eid !== $eid){
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
			$entity = $this->server->entities[$eid];
			$this->server->entities[$eid] = null;
			unset($this->server->entities[$eid]);
			$entity->closed = true;
			$this->server->query("DELETE FROM entities WHERE EID = ".$entity->eid.";");
			$this->server->api->dhandle("entity.remove", $entity);
			$entity = null;
			unset($entity);			
		}
	}
}