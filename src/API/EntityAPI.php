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
		$this->server->schedule(25, array($this, "updateEntities"), array(), true);
	}
	
	public function updateEntities(){
		$l = $this->server->query("SELECT EID FROM entities WHERE hasUpdate = 1;");
		
		if($l !== false and $l !== true){
			while(($e = $l->fetchArray(SQLITE3_ASSOC)) !== false){
				$e = $this->get($e["EID"]);
				if($e instanceof Entity){
					$e->update();
					$this->server->query("UPDATE entities SET hasUpdate = 0 WHERE EID = ".$e->eid.";");
				}
			}
		}
	}
	
	public function updateRadius(Position $center, $radius = 15, $class = false){
		$this->server->query("UPDATE entities SET hasUpdate = 1 WHERE level = '".$center->level->getName()."' ".($class !== false ? "AND class = $class ":"")."AND abs(x - {$center->x}) <= $radius AND abs(y - {$center->y}) <= $radius AND abs(z - {$center->z}) <= $radius;");
	}

	public function getRadius(Position $center, $radius = 15, $class = false){
		$entities = array();
		$l = $this->server->query("SELECT EID FROM entities WHERE level = '".$center->level->getName()."' ".($class !== false ? "AND class = $class ":"")."AND abs(x - {$center->x}) <= $radius AND abs(y - {$center->y}) <= $radius AND abs(z - {$center->z}) <= $radius;");
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

	public function spawnToAll(Entity $e){
		foreach($this->server->api->player->getAll($e->level) as $player){
			if($player->eid !== false and $player->eid !== $e->eid and $e->class !== ENTITY_PLAYER){
				$e->spawn($player);
			}
		}
	}
	
	public function drop(Position $pos, Item $item){
		if($item->getID() === AIR or $item->count <= 0){
			return;
		}
		$data = array(
			"x" => $pos->x + mt_rand(-10, 10) / 50,
			"y" => $pos->y + 0.19,
			"z" => $pos->z + mt_rand(-10, 10) / 50,
			//"speedX" => mt_rand(-3, 3) / 8,
			"speedY" => mt_rand(5, 8) / 2,
			//"speedZ" => mt_rand(-3, 3) / 8,
			"item" => $item,
		);
		if($this->server->api->handle("item.drop", $data) !== false){
			for($count = $item->count; $count > 0; ){
				$item->count = min($item->getMaxStackSize(), $count);
				$count -= $item->count;
				$e = $this->add($pos->level, ENTITY_ITEM, $item->getID(), $data);
				$this->spawnToAll($e);
				$this->server->api->handle("entity.motion", $e);
			}
		}
	}

	public function spawnAll(Player $player){
		foreach($this->getAll($player->level) as $e){
			if($e->class !== ENTITY_PLAYER){
				$e->spawn($player);
			}
		}
	}

	public function remove($eid){
		if(isset($this->entities[$eid])){
			$entity = $this->entities[$eid];
			$this->entities[$eid] = null;
			unset($this->entities[$eid]);
			$entity->closed = true;
			$this->server->query("DELETE FROM entities WHERE EID = ".$eid.";");
			if($entity->class === ENTITY_PLAYER){
				$this->server->api->player->broadcastPacket($this->server->api->player->getAll(), MC_REMOVE_PLAYER, array(
					"clientID" => 0,
					"eid" => $entity->eid,
				));
			}else{
				$this->server->api->player->broadcastPacket($this->server->api->player->getAll($entity->level), MC_REMOVE_ENTITY, array(
					"eid" => $entity->eid,
				));
			}
			$this->server->api->dhandle("entity.remove", $entity);
			$entity = null;
			unset($entity);			
		}
	}
}