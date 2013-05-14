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
	define("OBJECT_PAINTING", 83);

define("ENTITY_ITEM", 3);

class Entity extends Position{
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
	public $last = array(0, 0, 0, 0, 0, 0);
	public $yaw;
	public $pitch;
	public $dead;
	public $data;
	public $class;
	public $attach;
	public $closed;
	public $player;
	public $fallY;
	public $fallStart;
	private $tickCounter;
	private $speedMeasure = array(0, 0, 0, 0, 0);
	private $server;
	public $level;
	function __construct(Level $level, $eid, $class, $type = 0, $data = array()){
		$this->level = $level;
		$this->fallY = false;
		$this->fallStart = false;
		$this->server = ServerAPI::request();
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
		$this->tickCounter = 0;
		$this->server->query("INSERT OR REPLACE INTO entities (EID, level, type, class, health) VALUES (".$this->eid.", '".$this->level->getName()."', ".$this->type.", ".$this->class.", ".$this->health.");");
		$this->x = isset($this->data["x"]) ? $this->data["x"]:0;
		$this->y = isset($this->data["y"]) ? $this->data["y"]:0;
		$this->z = isset($this->data["z"]) ? $this->data["z"]:0;
		$this->speedX = isset($this->data["speedX"]) ? $this->data["speedX"]:0;
		$this->speedY = isset($this->data["speedY"]) ? $this->data["speedY"]:0;
		$this->speedZ = isset($this->data["speedZ"]) ? $this->data["speedZ"]:0;
		$this->speed = 0;
		$this->yaw = isset($this->data["yaw"]) ? $this->data["yaw"]:0;
		$this->pitch = isset($this->data["pitch"]) ? $this->data["pitch"]:0;
		$this->position = array("level" => $this->level, "x" => &$this->x, "y" => &$this->y, "z" => &$this->z, "yaw" => &$this->yaw, "pitch" => &$this->pitch);
		switch($this->class){
			case ENTITY_PLAYER:
				$this->player = $this->data["player"];
				$this->setHealth($this->health, "generic");
				break;
			case ENTITY_ITEM:
				if($data["item"] instanceof Item){
					$this->meta = $this->data["item"]->getMetadata();
					$this->stack = $this->data["item"]->count;			
				}else{
					$this->meta = (int) $this->data["meta"];
					$this->stack = (int) $this->data["stack"];
				}
				$this->setHealth(5, "generic");
				$this->server->schedule(5, array($this, "update"), array(), true);
				break;
			case ENTITY_MOB:
				$this->setHealth($this->data["Health"], "generic");
				$this->server->schedule(5, array($this, "update"), array(), true);
				//$this->setName((isset($mobs[$this->type]) ? $mobs[$this->type]:$this->type));
				break;
			case ENTITY_OBJECT:
				$this->x = isset($this->data["TileX"]) ? $this->data["TileX"]:$this->x;
				$this->y = isset($this->data["TileY"]) ? $this->data["TileY"]:$this->y;
				$this->z = isset($this->data["TileZ"]) ? $this->data["TileZ"]:$this->z;
				$this->setHealth(1, "generic");
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				break;
		}
	}
	
	public function getDrops(){
		if($this->class === ENTITY_OBJECT){
			switch($this->type){
				case OBJECT_PAINTING:
					return array(
						array(PAINTING, 0, 1),
					);
			}
		}elseif($this->class === ENTITY_MOB){
			switch($this->type){
				case MOB_CHICKEN:
					return array(
						array(FEATHER, 0, mt_rand(0,2)),
						array(($this->fire > 0 ? COOKED_CHICKEN:RAW_CHICKEN), 0, 1),
					);
				case MOB_COW:
					return array(
						array(LEATHER, 0, mt_rand(0,2)),
						array(($this->fire > 0 ? STEAK:RAW_BEEF), 0, 1),
					);
				case MOB_PIG:
					return array(
						array(LEATHER, 0, mt_rand(0,2)),
						array(($this->fire > 0 ? COOKED_PORKCHOP:RAW_PORKCHOP), 0, 1),
					);
				case MOB_SHEEP:
					return array(
						array(WOOL, $this->data["Color"] & 0x0F, 1),
					);
			}
		}
		return array();
	}
	
	private function spawnDrops(){
		foreach($this->getDrops() as $drop){
			$this->server->api->block->drop($this, BlockAPI::getItem($drop[0] & 0xFFFF, $drop[1] & 0xFFFF, $drop[2] & 0xFF), true);
		}
	}
	
	public function environmentUpdate(){
		if($this->class === ENTITY_ITEM){
			$time = microtime(true);
			if(($time - $this->spawntime) >= 300){
				$this->close(); //Despawn timer
				return false;
			}
			if(($time - $this->spawntime) >= 0.6){ //TODO: set this on Player class, updates things when it moves
				$player = $this->server->query("SELECT EID FROM entities WHERE level = '".$this->level->getName()."' AND class = ".ENTITY_PLAYER." AND abs(x - {$this->x}) <= 1.5 AND abs(y - {$this->y}) <= 1.5 AND abs(z - {$this->z}) <= 1.5 LIMIT 1;", true);
				$player = $this->server->api->entity->get($player["EID"]);
				if($player instanceof Entity){
					$player = $player->player;
				}else{
					return false;
				}
				if(($player instanceof Player) and ($player->gamemode & 0x01) === 0x00 and $player->spawned === true){
					if($this->server->api->dhandle("player.pickup", array(
						"eid" => $player->eid,
						"player" => $player,
						"entity" => $this,
						"block" => $this->type,
						"meta" => $this->meta,
						"target" => $this->eid
					)) !== false){
						$this->close();
						return false;
					}
					return true;
				}
			}
		}
		
		if($this->dead === true){
			$this->fire = 0;
			$this->air = 300;
			return;
		}
		
		if($this->y < -16){
			$this->harm(8, "void", true);
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
		
		$startX = (int) (round($this->x - 0.5) - 1);
		$startY = (int) (round($this->y) - 1);
		$startZ = (int) (round($this->z - 0.5) - 1);
		$endX = $startX + 2;
		$endY = $startY + 2;
		$endZ = $startZ + 2;
		$waterDone = false;
		for($y = $startY; $y <= $endY; ++$y){
			for($x = $startX; $x <= $endX; ++$x){
				for($z = $startZ; $z <= $endZ; ++$z){
					$b = $this->level->getBlock(new Vector3($x, $y, $z));
					switch($b->getID()){
						case WATER:
						case STILL_WATER: //Drowing
							if($this->fire > 0 and $this->inBlock(new Vector3($x, $y, $z))){
								$this->fire = 0;
								$this->updateMetadata();
							}
							if($this->air <= 0){
								$this->harm(2, "water");
							}elseif($x == ($endX - 1) and $y == $endY and $z == ($endZ - 1) and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER) and $waterDone === false){
								$this->air -= 10;
								$waterDone = true;
								$this->updateMetadata();
							}
							break;
						case LAVA: //Lava damage
						case STILL_LAVA:
							if($this->inBlock(new Vector3($x, $y, $z))){
								$this->harm(5, "lava");
								$this->fire = 300;
								$this->updateMetadata();
							}
							break;
						case FIRE: //Fire block damage
							if($this->inBlock(new Vector3($x, $y, $z))){
								$this->harm(1, "fire");
								$this->fire = 300;
								$this->updateMetadata();
							}
							break;
						case CACTUS: //Cactus damage
							if($this->touchingBlock(new Vector3($x, $y, $z))){
								$this->harm(1, "cactus");
							}
							break;
						default:
							if($this->inBlock(new Vector3($x, $y, $z), 0.7) and $y == $endY and $b->isTransparent === false and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER)){
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
		
		if($this->tickCounter === 0){
			$this->tickCounter = 1;
			$this->environmentUpdate();
		}else{
			$this->tickCounter = 0;
		}
		
		if($this->class === ENTITY_ITEM or $this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER){
			$startX = ((int) round($this->x - 0.5)) - 1;
			$y = (int) round($this->y - 1);
			$startZ = ((int) round($this->z - 0.5)) - 1;
			$endX = $startX + 2;
			$endZ = $startZ + 2;
			$support = false;
			for($z = $startZ; $z <= $endZ; ++$z){
				for($x = $startX; $x <= $endX; ++$x){
					$v = new Vector3($x, $y, $z);
					if($this->isSupport($v)){
						$b = $this->level->getBlock($v);
						if($b->isFlowable !== true){
							$support = true;
							break;
						}
					}
				}
				if($support === true){
					break;
				}
			}
			if($this->class === ENTITY_ITEM or $this->class === ENTITY_MOB){
				if($this->speedX != 0){
					$this->x += $this->speedX * 5;
				}
				if($this->speedY != 0){
					$this->y += $this->speedY * 5;
				}
				if($this->speedZ != 0){
					$this->z += $this->speedZ * 5;
				}
				if($support === false){
					$this->speedY -= 0.04 * 5;
					//$this->server->api->handle("entity.motion", $this);
				}elseif($this->speedY < 0){
					$this->y = $y + 1;
					$this->speedX = 0;
					$this->speedY = 0;
					$this->speedZ = 0;
					//$this->server->api->handle("entity.motion", $this);
				}
			}elseif($this->class === ENTITY_PLAYER){
				if($support === false and ($this->player->gamemode & 0x01) === 0x00){
					if($this->fallY === false or $this->fallStart === false){
						$this->fallY = $y;
						$this->fallStart = microtime(true);
					}elseif($this->class === ENTITY_PLAYER and ($this->fallStart + 5) < microtime(true)){
						if($this->server->api->getProperty("allow-flight") !== true and $this->server->handle("player.flying", $this->player) !== true){
							$this->player->close("flying");
							return;
						}
					}elseif($y > $this->fallY){
						$this->fallY = $y;
					}					
				}elseif($this->fallY !== false){ //Fall damage!
					if($y < $this->fallY){
						$d = $this->level->getBlock(new Vector3($x, $y + 1, $z));
						$d2 = $this->level->getBlock(new Vector3($x, $y + 2, $z));
						$dmg = ($this->fallY - $y) - 3;
						if($dmg > 0 and !($d instanceof LiquidBlock) and $d->getID() !== LADDER and !($d2 instanceof LiquidBlock) and $d2->getID() !== LADDER){
							$this->harm($dmg, "fall");
						}
					}
					$this->fallY = false;
					$this->fallStart = false;
					
				}
			}
			
			if($this->class === ENTITY_PLAYER){
				$this->calculateVelocity();
				if($this->speed <= 5 or ($this->speed <= 12 and ($this->player->gamemode & 0x01) === 0x01)){
					$this->player->lastCorrect = new Vector3($this->last[0], $this->last[1], $this->last[2]);
				}
			}
		}
		
		if($this->class !== ENTITY_OBJECT and ($this->last[0] != $this->x or $this->last[1] != $this->y or $this->last[2] != $this->z or $this->last[3] != $this->yaw or $this->last[4] != $this->pitch)){
			if($this->server->api->handle("entity.move", $this) === false){
				if($this->class === ENTITY_PLAYER){
					$this->player->teleport(new Vector3($this->last[0], $this->last[1], $this->last[2]), $this->last[3], $this->last[4]);
				}else{
					$this->setPosition($this->last[0], $this->last[1], $this->last[2], $this->last[3], $this->last[4]);
				}
				if($this->class === ENTITY_PLAYER){
					$this->calculateVelocity();
				}
				return;
			}
			$this->updateLast();
		}
	}

	public function getDirection(){
		$rotation = ($this->yaw - 90) % 360;
		if ($rotation < 0) {
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2; //North
		}elseif(45 <= $rotation and $rotation < 135){
			return 3; //East
		}elseif(135 <= $rotation and $rotation < 225){
			return 0; //South
		}elseif(225 <= $rotation and $rotation < 315){
			return 1; //West
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
		if(!($player instanceof Player)){
			$player = $this->server->api->player->get($player);
		}
		if($player->eid === $this->eid or $this->closed !== false or $player->level !== $this->level){
			return false;
		}
		switch($this->class){
			case ENTITY_PLAYER:
				if($this->player->connected !== true){
					return false;
				}
				$player->dataPacket(MC_ADD_PLAYER, array(
					"clientID" => 0,/*$this->player->clientID,*/
					"username" => $this->player->username,
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"metadata" => $this->getMetadata(),
				));
				$player->dataPacket(MC_PLAYER_EQUIPMENT, array(
					"eid" => $this->eid,
					"block" => $this->player->equipment->getID(),
					"meta" => $this->player->equipment->getMetadata(),
				));
				break;
			case ENTITY_ITEM:
				$player->dataPacket(MC_ADD_ITEM_ENTITY, array(
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"yaw" => $this->yaw,
					"pitch" => $this->pitch,
					"roll" => 0,
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
				if($this->type === OBJECT_PAINTING){
					$player->dataPacket(MC_ADD_PAINTING, array(
						"eid" => $this->eid,
						"x" => (int) $this->x,
						"y" => (int) $this->y,
						"z" => (int) $this->z,
						"direction" => $this->getDirection(),
						"title" => $this->data["Motive"],
					));
				}
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

	public function setCoords(Vector3 $pos){
		if($pos instanceof Position){
			$this->level = $pos->level;
			$this->server->query("UPDATE entities SET level = '".$this->level->getName()."' WHERE EID = ".$this->eid.";");
		}
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z." WHERE EID = ".$this->eid.";");
	}

	public function move(Vector3 $pos, $yaw = 0, $pitch = 0){
		$this->x += $pos->x;
		$this->y += $pos->y;
		$this->z += $pos->z;
		$this->yaw += $yaw;
		$this->yaw %= 360;
		$this->pitch += $pitch;
		$this->pitch %= 90;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z.", pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");
	}

	public function setPosition(Vector3 $pos, $yaw, $pitch){
		if($pos instanceof Position){
			$this->level = $pos->level;
			$this->server->query("UPDATE entities SET level = '".$this->level->getName()."' WHERE EID = ".$this->eid.";");
		}
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z.", pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");
	}
	
	public function inBlock(Vector3 $block, $radius = 0.8){
		$me = new Vector3($this->x - 0.5, $this->y, $this->z - 0.5);
		if(($block->y == ((int) $this->y) or $block->y == (((int) $this->y) + 1)) and $block->maxPlainDistance($me) < $radius){
			return true;
		}
		return false;
	}
	
	public function touchingBlock(Vector3 $block, $radius = 0.9){
		$me = new Vector3($this->x - 0.5, $this->y, $this->z - 0.5);
		if(($block->y == (((int) $this->y) - 1) or $block->y == ((int) $this->y) or $block->y == (((int) $this->y) + 1)) and $block->maxPlainDistance($me) < $radius){
			return true;
		}
		return false;
	}
	
	public function isSupport(Vector3 $pos, $radius = 1){
		$me = new Vector3($this->x - 0.5, $pos->y, $this->z - 0.5);
		$diff = $this->y - $pos->y;
		if($me->distance($pos) < $radius and $diff > 0 and $diff < 1.2){
			return true;
		}
		return false;
	}

	public function resetSpeed(){
		$this->speedMeasure = array(0, 0, 0, 0, 0);	
	}

	public function getSpeed(){
		return array_sum($this->speedMeasure) / count($this->speedMeasure);		
	}
	
	public function calculateVelocity(){
		$diffTime = max(0.05, abs(microtime(true) - $this->last[5]));
		$origin = new Vector3($this->last[0], $this->last[2]);
		$final = new Vector3($this->x, $this->z);
		$speedX = ($this->last[0] - $this->x) / $diffTime;
		$speedY = ($this->last[1] - $this->y) / $diffTime;
		$speedZ = ($this->last[2] - $this->z) / $diffTime;
		$this->speedX = $speedX;
		$this->speedY = $speedY;
		$this->speedZ = $speedZ;		
		$this->speed = $origin->distance($final) / $diffTime;
		array_shift($this->speedMeasure);
		$this->speedMeasure[] = $this->speed;
	}
	
	public function updateLast(){		
		$this->last[0] = $this->x;
		$this->last[1] = $this->y;
		$this->last[2] = $this->z;
		$this->last[3] = $this->yaw;
		$this->last[4] = $this->pitch;
		$this->last[5] = microtime(true);
	}

	public function getPosition($round = false){
		return !isset($this->position) ? false:($round === true ? array_map("floor", $this->position):$this->position);
	}
	
	public function harm($dmg, $cause = "generic", $force = false){
		return $this->setHealth($this->getHealth() - ((int) $dmg), $cause, $force);
	}

	public function heal($health, $cause = "generic"){
		return $this->setHealth(min(20, $this->getHealth() + ((int) $health)), $cause);
	}

	public function setHealth($health, $cause = "generic", $force = false){
		$health = (int) $health;
		$harm = false;
		if($health < $this->health){
			$harm = true;
			$dmg = $this->health - $health;
			if($this->class === ENTITY_PLAYER and ($this->player instanceof Player)){
				$points = 0;
				$values = array(
					LEATHER_CAP => 1,
					LEATHER_TUNIC => 3,
					LEATHER_PANTS => 2,
					LEATHER_BOOTS => 1,
					CHAIN_HELMET => 1,
					CHAIN_CHESTPLATE => 5,
					CHAIN_LEGGINS => 4,
					CHAIN_BOOTS => 1,
					GOLD_HELMET => 1,
					GOLD_CHESTPLATE => 5,
					GOLD_LEGGINS => 3,
					GOLD_BOOTS => 1,
					IRON_HELMET => 2,
					IRON_CHESTPLATE => 6,
					IRON_LEGGINS => 5,
					IRON_BOOTS => 2,
					DIAMOND_HELMET => 3,
					DIAMOND_CHESTPLATE => 8,
					DIAMOND_LEGGINS => 6,
					DIAMOND_BOOTS => 3,
				);
				foreach($this->player->armor as $part){
					if(isset($values[$part[0]])){
						$points += $values[$part[0]];
					}
				}
				$dmg = (int) ($dmg - ($dmg * $points * 0.04));
				$health = $this->health - $dmg;
			}
			if(($this->class !== ENTITY_PLAYER or (($this->player instanceof Player) and (($this->player->gamemode & 0x01) === 0x00 or $force === true))) and ($this->dmgcounter[0] < microtime(true) or $this->dmgcounter[1] < $dmg) and !$this->dead){
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
				$this->spawnDrops();
				$this->air = 300;
				$this->fire = 0;
				$this->crouched = false;
				$this->fallY = false;
				$this->fallStart = false;
				$this->updateMetadata();
				$this->dead = true;
				if($this->player instanceof Player){
					$x = $this->x;
					$y = $this->y;
					$z = $this->z;
					$this->x = 0;
					$this->y = -10;
					$this->z = 0;
					$this->server->api->dhandle("entity.move", $this);
					$this->x = $x;
					$this->y = $y;
					$this->z = $z;
				}else{
					$this->server->api->dhandle("entity.event", array("entity" => $this, "event" => 3)); //Entity dead
				}
				if($this->player instanceof Player){
					$this->player->blocked = true;
					$this->server->api->dhandle("player.death", array("player" => $this->player, "cause" => $cause));
				}else{
					$this->close();
				}
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
