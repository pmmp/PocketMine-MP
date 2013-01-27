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
	define("MOB_CHICKEN", 10);
	define("MOB_COW", 11);
	define("MOB_PIG", 12);
	define("MOB_SHEEP", 13);

	define("MOB_ZOMBIE", 32);
	define("MOB_CREEPER", 33);
	define("MOB_SKELETON", 34);
	define("MOB_SPIDER", 35);
	define("MOB_PIGMAN", 36);

define("ENTITY_OBJECT", 2);

define("ENTITY_ITEM", 3);

define("ENTITY_PAINTING", 4);

class Entity extends stdClass{
	public $invincible;
	public $age;
	public $air;
	public $spawntime;
	public $dmgcounter;
	public $eid;
	public $type;
	public $name;
	public $x;
	public $y;
	public $z;
	public $speedX;
	public $speedY;
	public $speedZ;
	public $speed;
	public $last = array(0, 0, 0, 0);
	public $yaw;
	public $pitch;
	public $dead;
	public $data;
	public $class;
	public $attach;
	public $closed;
	public $player;
	private $server;
	function __construct(PocketMinecraftServer $server, $eid, $class, $type = 0, $data = array()){
		$this->server = $server;
		$this->eid = (int) $eid;
		$this->type = (int) $type;
		$this->class = (int) $class;
		$this->player = false;
		$this->attach = false;
		$this->data = $data;
		$this->status = 0;
		$this->health = 20;
		$this->dmgcounter = array(0, 0, 0);
		$this->air = 300;
		$this->onground = true;
		$this->fire = 0;
		$this->crouched = false;
		$this->invincible = false;
		$this->spawntime = microtime(true);
		$this->dead = false;
		$this->closed = false;
		$this->name = "";
		$this->server->query("INSERT OR REPLACE INTO entities (EID, type, class, health) VALUES (".$this->eid.", ".$this->type.", ".$this->class.", ".$this->health.");");
		$this->server->schedule(4, array($this, "update"), array(), true);
		$this->server->schedule(10, array($this, "environmentUpdate"), array(), true);
		$this->x = isset($this->data["x"]) ? $this->data["x"]:0;
		$this->y = isset($this->data["y"]) ? $this->data["y"]:0;
		$this->z = isset($this->data["z"]) ? $this->data["z"]:0;
		$this->speedX = isset($this->data["speedX"]) ? $this->data["speedX"]:0;
		$this->speedY = isset($this->data["speedY"]) ? $this->data["speedY"]:0;
		$this->speedZ = isset($this->data["speedZ"]) ? $this->data["speedZ"]:0;
		$this->speed = 0;
		$this->yaw = isset($this->data["yaw"]) ? $this->data["yaw"]:0;
		$this->pitch = isset($this->data["pitch"]) ? $this->data["pitch"]:0;
		$this->position = array("x" => &$this->x, "y" => &$this->y, "z" => &$this->z, "yaw" => &$this->yaw, "pitch" => &$this->pitch);
		switch($this->class){
			case ENTITY_PLAYER:
				$this->player = $this->data["player"];
				$this->health = &$this->player->data["health"];
				$this->setHealth($this->health, "generic");
				break;
			case ENTITY_ITEM:
				$this->meta = (int) $this->data["meta"];
				$this->stack = (int) $this->data["stack"];
				$this->setHealth(5, "generic");
				break;
			case ENTITY_MOB:
				$this->setHealth($this->data["Health"], "generic");
				//$this->setName((isset($mobs[$this->type]) ? $mobs[$this->type]:$this->type));
				break;
			case ENTITY_OBJECT:
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
			case ENTITY_PAINTING:
			
				break;
		}
	}
	
	public function environmentUpdate(){
		if($this->closed === true){
			return false;
		}
		if($this->class === ENTITY_ITEM){
			if((microtime(true) - $this->spawntime) >= 300){
				$this->close(); //Despawn timer
				return false;
			}
			if($this->server->gamemode === 0){
				$player = $this->server->query("SELECT EID FROM entities WHERE class == ".ENTITY_PLAYER." AND abs(x - {$this->x}) <= 1.5 AND abs(y - {$this->y}) <= 1.5 AND abs(z - {$this->z}) <= 1.5 LIMIT 1;", true);
				if($player !== true and $player !== false){
					if($this->server->api->dhandle("player.pickup", array(
						"eid" => $player["EID"],
						"entity" => $this,
						"block" => $this->type,
						"meta" => $this->meta,
						"target" => $this->eid
					)) !== false){
						$this->close();
						return false;
					}
				}
			}
		}
		if($this->fire > 0){
			if(($this->fire % 20) === 0){
				$this->harm(1, "burning");
			}
			$this->fire -= 10;
			if($this->fire <= 0){
				$this->fire = 0;
				$this->updateMetadata();
			}
		}
		
		if($this->dead === true){
			return;
		}
		
		$startX = (int) (round($this->x - 0.5) - 1);
		$startY = (int) (round($this->y) - 1);
		$startZ = (int) (round($this->z - 0.5) - 1);
		$endX = $startX + 2;
		$endY = $startY + 2;
		$endZ = $startZ + 2;
		for($y = $startY; $y <= $endY; ++$y){
			for($x = $startX; $x <= $endX; ++$x){
				for($z = $startZ; $z <= $endZ; ++$z){
					$b = $this->server->api->level->getBlock($x, $y, $z);
					switch($b[0]){
						case 8:
						case 9: //Drowing
							if($this->fire > 0 and $this->inBlock($x, $y, $z)){
								$this->fire = 0;
								$this->updateMetadata();
							}
							if($this->air <= 0){
								$this->harm(2, "water");
							}elseif($x == ($endX - 1) and $y == $endY and $z == ($endZ - 1) and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER)){
								$this->air -= 10;
								$this->updateMetadata();
							}
							break;
						case 10: //Lava damage
						case 11:
							if($this->inBlock($x, $y, $z)){
								$this->harm(5, "lava");
								$this->fire = 300;
								$this->updateMetadata();
							}
							break;
						case 51: //Fire block damage
							if($this->inBlock($x, $y, $z)){
								$this->harm(1, "fire");
								$this->fire = 300;
								$this->updateMetadata();
							}
							break;
						case 81: //Cactus damage
							if($this->touchingBlock($x, $y, $z)){
								$this->harm(1, "cactus");
							}
							break;
						default:
							if($this->inBlock($x, $y, $z, 0.7) and $y == $endY and !isset(Material::$transparent[$b[0]]) and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER)){
								$this->harm(1, "suffocation"); //Suffocation
							}elseif($x == ($endX - 1) and $y == $endY and $z == ($endZ - 1)){
								$this->air = 300; //Breathing
							}
							break;
					}
				}
			}		
		}
	}

	public function update(){
		if($this->closed === true){
			return false;
		}
		if($this->last[0] !== $this->x or $this->last[1] !== $this->y or $this->last[2] !== $this->z){
			$this->server->api->dhandle("entity.move", $this);
			$this->calculateVelocity();
		}
	}

	public function getDirection(){
		$rotation = ($this->yaw - 90) % 360;
		if ($rotation < 0) {
			$rotation += 360.0;
		}
		if(0 <= $rotation && $rotation < 45) {
			return 2;
		}elseif(45 <= $rotation && $rotation < 135) {
			return 3;
		}elseif(135 <= $rotation && $rotation < 225) {
			return 0;
		}elseif(225 <= $rotation && $rotation < 315) {
			return 1;
		}elseif(315 <= $rotation && $rotation < 360) {
			return 2;
		}else{
			return null;
		}
	}
	
	public function getMetadata(){
		$flags = 0;
		$flags |= $this->fire > 0 ? 1:0;
		$flags |= ($this->crouched === true ? 1:0) << 1;
		$d = array(
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => $this->air),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		);
		if($this->class === ENTITY_MOB and $this->type === MOB_SHEEP){
			$d[16]["value"] = (($this->data["Sheared"] == 1 ? 1:0) << 5) | ($this->data["Color"] & 0x0F);
		}
		return $d;
	}
	
	public function updateMetadata(){
		$this->server->api->dhandle("entity.metadata", $this);
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
					"metadata" => $this->getMetadata(),
				));
				$player->dataPacket(MC_PLAYER_EQUIPMENT, array(
					"eid" => $this->eid,
					"block" => $this->player->equipment[0],
					"meta" => $this->player->equipment[1],
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
					"metadata" => $this->getMetadata(),
				));
				break;
			case ENTITY_OBJECT:
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
		}
	}

	public function close(){
		if($this->closed === false){
			$this->closed = true;
			$this->server->api->entity->remove($this->eid);
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
	}
	
	public function inBlock($x, $y, $z, $radius = 0.8){
		$block = new Vector3($x, $y, $z);
		$me = new Vector3($this->x - 0.5, $this->y, $this->z - 0.5);
		if(($y == ((int) $this->y) or $y == (((int) $this->y) + 1)) and $block->maxPlainDistance($me) < $radius){
			return true;
		}
		return false;
	}
	
	public function touchingBlock($x, $y, $z, $radius = 0.9){
		$block = new Vector3($x, $y, $z);
		$me = new Vector3($this->x - 0.5, $this->y, $this->z - 0.5);
		if(($y == (((int) $this->y) - 1) or $y == ((int) $this->y) or $y == (((int) $this->y) + 1)) and $block->maxPlainDistance($me) < $radius){
			return true;
		}
		return false;
	}
	
	public function calculateVelocity(){
		$diffTime = microtime(true) - $this->last[3];
		$this->last[3] = microtime(true);
		$origin = new Vector3($this->last[0], $this->last[1], $this->last[2]);
		$final = new Vector3($this->x, $this->y, $this->z);
		$speedX = abs($this->x - $this->last[0]) / $diffTime;
		$this->last[0] = $this->x;
		$speedY = ($this->y - $this->last[1]) / $diffTime;
		$this->last[1] = $this->y;
		$speedZ = abs($this->z - $this->last[2]) / $diffTime;
		$this->last[2] = $this->z;
		$this->speedX = $speedX;
		$this->speedY = $speedY;
		$this->speedZ = $speedZ;		
		$this->speed = $origin->distance($final) / $diffTime;
	}

	public function getPosition($round = false){
		return !isset($this->position) ? false:($round === true ? array_map("floor", $this->position):$this->position);
	}
	
	public function harm($dmg, $cause = "generic"){
		return $this->setHealth($this->getHealth() - ((int) $dmg), $cause);
	}

	public function setHealth($health, $cause = "generic", $force = false){
		$health = (int) $health;
		$harm = false;
		if($health < $this->health){
			$harm = true;
			$dmg = $this->health - $health;
			if(($this->server->gamemode === 0 or $force === true) and ($this->dmgcounter[0] < microtime(true) or $this->dmgcounter[1] < $dmg) and !$this->dead){
				$this->dmgcounter[0] = microtime(true) + 0.5;
				$this->dmgcounter[1] = $dmg;
			}else{
				return false; //Entity inmunity
			}
		}elseif($health === $this->health){
			return false;
		}
		if($this->server->api->dhandle("entity.health.change", array("entity" => $this, "eid" => $this->eid, "health" => $health, "cause" => $cause)) !== false){
			$this->health = min(127, max(-127, $health));
			$this->server->query("UPDATE entities SET health = ".$this->health." WHERE EID = ".$this->eid.";");
			if($harm === true){
				$this->server->api->dhandle("entity.event", array("entity" => $this, "event" => 2)); //Ouch! sound
			}
			if($this->player instanceof Player){
				$this->player->dataPacket(MC_SET_HEALTH, array(
					"health" => $this->health,
				));
			}
			if($this->health <= 0 and $this->dead === false){
				$this->air = 300;
				$this->fire = 0;
				$this->crouched = false;
				$this->updateMetadata();
				$this->dead = true;
				if($this->player instanceof Player){
					$this->server->api->dhandle("player.death", array("name" => $this->name, "cause" => $cause));
				}
				$this->server->api->dhandle("entity.event", array("entity" => $this, "event" => 3)); //Entity dead
			}elseif($this->health > 0){
				$this->dead = false;
			}
			return true;
		}
		return false;
	}

	public function getHealth(){
		return $this->health;
	}

}
