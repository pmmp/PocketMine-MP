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


define("ENTITY_PLAYER", 0);
define("ENTITY_MOB", 1);
define("ENTITY_OBJECT", 2);
define("ENTITY_ITEM", 3);
define("ENTITY_PAINTING", 4);

class Entity extends stdClass{
	var $eid, $type, $name, $x, $y, $z, $yaw, $pitch, $dead, $data, $class, $attach, $metadata, $closed, $player;
	
	function __construct($server, $eid, $class, $type = 0, $data = array()){
		$this->server = $server;
		$this->eid = (int) $eid;
		$this->type = (int) $type;
		$this->class = (int) $class;
		$this->player = false;
		$this->attach = false;
		$this->data = $data;
		$this->status = 0;
		$this->health = 20;
		$this->dead = false;
		$this->closed = false;
		$this->name = "";
		$this->server->query("INSERT OR REPLACE INTO entities (EID, type, class, health) VALUES (".$this->eid.", ".$this->type.", ".$this->class.", ".$this->health.");");
		$this->metadata = array();
		$this->x = isset($this->data["x"]) ? $this->data["x"]:0;
		$this->y = isset($this->data["y"]) ? $this->data["y"]:0;
		$this->z = isset($this->data["z"]) ? $this->data["z"]:0;
		$this->yaw = isset($this->data["yaw"]) ? $this->data["yaw"]:0;
		$this->pitch = isset($this->data["pitch"]) ? $this->data["pitch"]:0;
		$this->position = array("x" => &$this->x, "y" => &$this->y, "z" => &$this->z, "yaw" => &$this->yaw, "pitch" => &$this->pitch);
		switch($this->class){
			case ENTITY_PLAYER:
				$this->player = $this->data["player"];
				$this->health = &$this->player->data["health"];
				break;
			case ENTITY_ITEM:
				$this->meta = (int) $this->data["meta"];
				$this->stack = (int) $this->data["stack"];
				break;
			case ENTITY_MOB:
				//$this->setName((isset($mobs[$this->type]) ? $mobs[$this->type]:$this->type));
				break;
			case ENTITY_OBJECT:
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
		}
	}
	
	public function spawn($player){
		if(!is_object($player)){
			$player = $this->server->api->player->get($player);
		}
		if($player->eid === $this->eid){
			return false;
		}
		switch($this->class){
			case ENTITY_PLAYER:
				$player->dataPacket(MC_ADD_PLAYER, array(
					"clientID" => $this->player->clientID,
					"username" => $this->player->username,
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
				));
				break;
			case ENTITY_ITEM:
				$player->dataPacket(MC_ADD_ITEM_ENTITY, array(
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"block" => $this->type,
					"meta" => $this->meta,
					"stack" => $this->stack,
				));
				break;				
			case ENTITY_MOB:
				$player->dataPacket(MC_ADD_MOB, array(
					"type" => $this->type,
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
				));
				break;
			case ENTITY_OBJECT:
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
		}
	}
	
	public function close(){
		if($this->closed === false){
			$this->server->query("DELETE FROM entities WHERE EID = ".$this->eid.";");
			$this->server->trigger("entity.remove", $this->eid);
			$this->closed = true;
		}
	}
	
	public function __destruct(){
		$this->close();
	}
	
	public function getEID(){
		return $this->eid;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
		$this->server->query("UPDATE entities SET name = '".str_replace("'", "", $this->name)."' WHERE EID = ".$this->eid.";");
	}
	
	public function look($pos2){
		$pos = $this->getPosition();
		$angle = Utils::angle3D($pos2, $pos);
		$this->yaw = $angle["yaw"];
		$this->pitch = $angle["pitch"];
		$this->server->query("UPDATE entities SET pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");
	}
	
	public function setCoords($x, $y, $z){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z." WHERE EID = ".$this->eid.";");
	}
	
	public function move($x, $y, $z, $yaw = 0, $pitch = 0){
		$this->x += $x;
		$this->y += $y;
		$this->z += $z;
		$this->yaw += $yaw;
		$this->yaw %= 360;
		$this->pitch += $pitch;
		$this->pitch %= 90;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z.", pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");
	}
	
	public function setPosition($x, $y, $z, $yaw, $pitch){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z.", pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");		
		return true;
	}
	
	public function getPosition($round = false){
		return !isset($this->position) ? false:($round === true ? array_map("floor", $this->position):$this->position);
	}
	
	public function setHealth($health, $cause = ""){				
		$this->health = (int) $health;
		$this->server->query("UPDATE entities SET health = ".$this->health." WHERE EID = ".$this->eid.";");
		$this->server->trigger("entity.health.change", array("eid" => $this->eid, "health" => $health, "cause" => $cause));
		if($this->player !== false){
			$this->player->dataPacket(MC_SET_HEALTH, array(
				"health" => $this->health,
			));
		}
		if($this->health <= 0 and $this->dead === false){
			$this->dead = true;
			if($this->player !== false){
				$this->server->handle("onPlayerDeath", array("name" => $this->name, "cause" => $cause));
			}
		}elseif($this->health > 0){
			$this->dead = false;
		}
	}
	
	public function getHealth(){
		return $this->health;
	}

}

?>