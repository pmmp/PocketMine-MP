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
	private $entities;
	private $eCnt = 1;

	function __construct(){
		$this->entities = array();
		$this->server = ServerAPI::request();
	}

	public function get($eid){
		if(isset($this->entities[$eid])){
			return $this->entities[$eid];
		}
		return false;
	}
	
	public function init(){
	
	}

	public function getAll($level = null){
		if($level instanceof Level){
			$entities = array();
			$l = $this->server->query("SELECT EID FROM entities WHERE level = '".$level->getName()."';");
			if($l !== false and $l !== true){
				while(($e = $l->fetchArray(SQLITE3_ASSOC)) !== false){
					$e = $this->get($e["EID"]);
					if($e instanceof Entity){
						$entities[$e->eid] = $e;
					}
				}
			}
			return $entities;
		}
		return $this->entities;
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

	public function add(Level $level, $class, $type = 0, $data = array()){
		$eid = $this->eCnt++;
		$this->entities[$eid] = new Entity($level, $eid, $class, $type, $data);
		$this->server->handle("entity.add", $this->entities[$eid]);
		return $this->entities[$eid];
	}

	public function spawnTo($eid, $player){
		$e = $this->get($eid);
		if($e === false){
			return false;
		}
		$e->spawn($player);
	}

	public function spawnToAll(Level $level, $eid){
		$e = $this->get($eid);
		if($e === false){
			return false;
		}
		foreach($this->server->api->player->getAll($level) as $player){
			if($player->eid !== false and $player->eid !== $eid){
				$e->spawn($player);
			}
		}
	}
	
	public function drop(Position $pos, Item $item){
		if($item->getID() === AIR or $item->count <= 0){
			return;
		}
		$data = array(
			"x" => $pos->x,
			"y" => $pos->y + 0.19,
			"z" => $pos->z,
			"speedX" => mt_rand(-3, 3) / 8,
			"speedY" => mt_rand(5, 8) / 1.5,
			"speedZ" => mt_rand(-3, 3) / 8,
			"item" => $item,
		);
		if($this->server->api->handle("item.drop", $data) !== false){
			for($count = $item->count; $count > 0; ){
				$item->count = min($item->getMaxStackSize(), $count);
				$count -= $item->count;
				$e = $this->add($pos->level, ENTITY_ITEM, $item->getID(), $data);
				$this->spawnToAll($pos->level, $e->eid);
				$this->server->api->handle("entity.motion", $e);
			}
		}
	}

	public function spawnAll(Player $player){
		foreach($this->getAll($player->level) as $e){
			$e->spawn($player);
		}
	}

	public function remove($eid){
		if(isset($this->entities[$eid])){
			$entity = $this->entities[$eid];
			$this->entities[$eid] = null;
			unset($this->entities[$eid]);
			$entity->closed = true;
			$this->server->query("DELETE FROM entities WHERE EID = ".$eid.";");
			$this->server->api->dhandle("entity.remove", $entity);
			$entity = null;
			unset($entity);			
		}
	}
}