<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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

class Entity{
	var $eid, $type, $name, $position, $dead, $metadata, $class, $attach, $data, $closed;
	protected $health, $client;
	
	function __construct($eid, $class, $type = 0, $server){ //$type = 0 ---> player
		$this->server = $server;
		$this->eid = (int) $eid;
		$this->type = (int) $type;
		$this->class = (int) $class;
		$this->attach = false;
		$this->data = array();
		$this->status = 0;
		$this->health = 20;
		$this->dead = false;
		$this->closed = false;
		$this->name = "";
		$this->server->query("INSERT OR REPLACE INTO entities (EID, type, class, health) VALUES (".$this->eid.", ".$this->type.", ".$this->class.", ".$this->health.");");
		$this->metadata = array();
		/*include("misc/entities.php");
		switch($this->class){
			case ENTITY_PLAYER:
			case ENTITY_ITEM:
				break;
				
			case ENTITY_MOB:
				$this->setName((isset($mobs[$this->type]) ? $mobs[$this->type]:$this->type));
				break;
			case ENTITY_OBJECT:
				$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
		}*/
	}
	
	public function close(){
		if($this->closed === false){
			$this->server->query("DELETE FROM entities WHERE EID = ".$this->eid.";");
			$this->server->trigger("onEntityRemove", $this->eid);
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
		$this->position["yaw"] = $angle["yaw"];
		$this->position["pitch"] = $angle["pitch"];
		$this->server->query("UPDATE entities SET pitch = ".$this->position["pitch"].", yaw = ".$this->position["yaw"]." WHERE EID = ".$this->eid.";");
	}
	
	public function setCoords($x, $y, $z){
		if(!isset($this->position)){
			$this->position = array(
				"x" => 0,
				"y" => 0,
				"z" => 0,
				"yaw" => 0,
				"pitch" => 0,
				"ground" => 0,
			);		
		}
		$this->position["x"] = $x;
		$this->position["y"] = $y;
		$this->position["z"] = $z;
		$this->server->query("UPDATE entities SET x = ".$this->position["x"].", y = ".$this->position["y"].", z = ".$this->position["z"]." WHERE EID = ".$this->eid.";");
	}
	
	public function move($x, $y, $z, $yaw = 0, $pitch = 0){
		if(!isset($this->position)){
			$this->position = array(
				"x" => 0,
				"y" => 0,
				"z" => 0,
				"yaw" => 0,
				"pitch" => 0,
				"ground" => 0,
			);		
		}
		$this->position["x"] += $x;
		$this->position["y"] += $y;
		$this->position["z"] += $z;
		$this->position["yaw"] += $yaw;
		$this->position["yaw"] %= 360;
		$this->position["pitch"] += $pitch;
		$this->position["pitch"] %= 90;
		$this->server->query("UPDATE entities SET x = ".$this->position["x"].", y = ".$this->position["y"].", z = ".$this->position["z"].", pitch = ".$this->position["pitch"].", yaw = ".$this->position["yaw"]." WHERE EID = ".$this->eid.";");
	}
	
	public function setPosition($x, $y, $z, $yaw, $pitch){
		$this->position = array(
			"x" => $x,
			"y" => $y,
			"z" => $z,
			"yaw" => $yaw,
			"pitch" => $pitch,
			"ground" => $ground,
		);
		$this->server->query("UPDATE entities SET x = ".$this->position["x"].", y = ".$this->position["y"].", z = ".$this->position["z"].", pitch = ".$this->position["pitch"].", yaw = ".$this->position["yaw"]." WHERE EID = ".$this->eid.";");		
		return true;
	}
	
	public function getPosition($round = false){
		return !isset($this->position) ? false:($round === true ? array_map("floor", $this->position):$this->position);
	}
	
	public function setHealth($health){				
		$this->health = (int) $health;
		$this->server->query("UPDATE entities SET health = ".$this->health." WHERE EID = ".$this->eid.";");
	}
	
	public function getHealth(){
		return $this->health;
	}

}

?>