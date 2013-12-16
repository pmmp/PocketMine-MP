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
	private $speedMeasure = array(0, 0, 0, 0, 0, 0, 0);
	private $server;
	private $isStatic;
	public $level;
	public $lastUpdate;
	public $check = true;
	public $size = 1;
	public $inAction = false;

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
		$this->fire = 0;
		$this->crouched = false;
		$this->invincible = false;
		$this->lastUpdate = $this->spawntime = microtime(true);
		$this->dead = false;
		$this->closed = false;
		$this->isStatic = false;
		$this->name = "";
		$this->tickCounter = 0;
		$this->server->query("INSERT OR REPLACE INTO entities (EID, level, type, class, health, hasUpdate) VALUES (".$this->eid.", '".$this->level->getName()."', ".$this->type.", ".$this->class.", ".$this->health.", 0);");
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
				$this->size = 1.2;
				break;
			case ENTITY_ITEM:
				if(isset($data["item"]) and ($data["item"] instanceof Item)){
					$this->meta = $this->data["item"]->getMetadata();
					$this->stack = $this->data["item"]->count;			
				}else{
					$this->meta = (int) $this->data["meta"];
					$this->stack = (int) $this->data["stack"];
				}
				$this->setHealth(5, "generic");
				$this->server->schedule(6010, array($this, "update")); //Despawn
				$this->update();
				$this->size = 0.75;
				break;
			case ENTITY_MOB:
				$this->setHealth(isset($this->data["Health"]) ? $this->data["Health"]:10, "generic");
				$this->update();
				//$this->setName((isset($mobs[$this->type]) ? $mobs[$this->type]:$this->type));
				$this->size = 1;
				break;
			case ENTITY_FALLING:
				$this->setHealth(PHP_INT_MAX, "generic");
				$this->update();
				$this->size = 0.98;
				break;
			case ENTITY_OBJECT:
				$this->x = isset($this->data["TileX"]) ? $this->data["TileX"]:$this->x;
				$this->y = isset($this->data["TileY"]) ? $this->data["TileY"]:$this->y;
				$this->z = isset($this->data["TileZ"]) ? $this->data["TileZ"]:$this->z;
				$this->setHealth(1, "generic");
				//$this->setName((isset($objects[$this->type]) ? $objects[$this->type]:$this->type));
				$this->size = 1;
				if($this->type === OBJECT_PAINTING){
					$this->isStatic = true;
				}elseif($this->type === OBJECT_PRIMEDTNT){
					$this->setHealth(10000000, "generic");
					$this->server->schedule(5, array($this, "updateFuse"), array(), true);
					$this->update();
				}elseif($this->type === OBJECT_ARROW){
					$this->server->schedule(1210, array($this, "update")); //Despawn
					$this->update();
				}
				break;
		}
		$this->updateLast();
		$this->updatePosition();
	}
	
	public function updateFuse(){
		if($this->closed === true){
			return false;
		}
		if($this->type === OBJECT_PRIMEDTNT){
			$this->updateMetadata();
			if(((microtime(true) - $this->spawntime) * 20) >= $this->data["fuse"]){
				$this->close();
				$explosion = new Explosion($this, $this->data["power"]);
				$explosion->explode();
			}
		}
	}
	
	public function getDrops(){
		if($this->class === ENTITY_PLAYER and ($this->player->gamemode & 0x01) === 0){
			$inv = array();
			for($i = 0; $i < PLAYER_SURVIVAL_SLOTS; ++$i){
				$slot = $this->player->getSlot($i);
				$this->player->setSlot($i, BlockAPI::getItem(AIR, 0, 0));
				if($slot->getID() !== AIR and $slot->count > 0){
					$inv[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
			for($re = 0; $re < 4; $re++){
				$slot = $this->player->getArmor($re);
				$this->player->setArmor($re, BlockAPI::getItem(AIR, 0, 0));
				if($slot->getID() !== AIR and $slot->count > 0){
					$inv[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
			return $inv;
		}elseif($this->class === ENTITY_OBJECT){
			switch($this->type){
				case OBJECT_PAINTING:
					return array(
						array(PAINTING, 0, 1),
					);
			}
		}elseif($this->class === ENTITY_MOB){
			switch($this->type){
				case MOB_ZOMBIE:
					return array(
						array(FEATHER, 0, mt_rand(0,2)),
					);
				case MOB_SPIDER:
					return array(
						array(STRING, 0, mt_rand(0,2)),
					);
				case MOB_PIGMAN:
					return array(
						array(COOKED_PORKCHOP, 0, mt_rand(0,2)),
					);
				case MOB_CREEPER:
					return array(
						array(GUNPOWDER, 0, mt_rand(0,2)),
					);
				case MOB_SKELETON:
					return array(
						array(ARROW, 0, mt_rand(0,2)),
						array(BONE, 0, mt_rand(0,2)),
					);
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
			$this->server->api->entity->drop($this, BlockAPI::getItem($drop[0] & 0xFFFF, $drop[1] & 0xFFFF, $drop[2] & 0xFF), true);
		}
	}
	
	public function environmentUpdate(){
		$hasUpdate = false;
		$time = microtime(true);
		if($this->class === ENTITY_PLAYER and ($this->player instanceof Player) and $this->player->spawned === true and $this->player->blocked !== true){
			foreach($this->server->api->entity->getRadius($this, 1.5, ENTITY_ITEM) as $item){
				if($item->spawntime > 0 and ($time - $item->spawntime) >= 0.6){
					if((($this->player->gamemode & 0x01) === 1 or $this->player->hasSpace($item->type, $item->meta, $item->stack) === true) and $this->server->api->dhandle("player.pickup", array(
						"eid" => $this->player->eid,
						"player" => $this->player,
						"entity" => $item,
						"block" => $item->type,
						"meta" => $item->meta,
						"target" => $item->eid
					)) !== false){
						$item->close();
						//$item->spawntime = 0;
						//$this->server->schedule(15, array($item, "close"));
					}
				}
			}
		}elseif($this->class === ENTITY_ITEM){
			if(($time - $this->spawntime) >= 300){
				$this->close(); //Despawn timer
				return false;
			}
		}elseif($this->class === ENTITY_OBJECT and $this->type === OBJECT_ARROW){
			if(($time - $this->spawntime) >= 60){
				$this->close(); //Despawn timer
				return false;
			}
		}
		
		if($this->class === ENTITY_MOB){
			switch($this->type){
				case MOB_CHICKEN:
				case MOB_SHEEP:
				case MOB_COW:
				case MOB_PIG:
					if($this->server->api->getProperty("spawn-animals") !== true){
						$this->close();
						return false;
					}
					break;
				case MOB_ZOMBIE:
				case MOB_CREEPER:
				case MOB_PIGMAN:
				case MOB_SKELETON:
				case MOB_SPIDER:
					if($this->server->api->getProperty("spawn-mobs") !== true){
						$this->close();
						return false;
					}
					break;
			}
		}
	
		if($this->class !== ENTITY_PLAYER and ($this->x <= 0 or $this->z <= 0 or $this->x >= 256 or $this->z >= 256 or $this->y >= 128 or $this->y <= 0)){
			$this->close();
			return false;
		}
		
		if($this->dead === true){
			$this->fire = 0;
			$this->air = 300;
			return false;
		}
		
		if($this->y < -16){
			$this->harm(8, "void", true);
			$hasUpdate = true;
		}
		
		if($this->fire > 0){
			if(($this->fire % 20) === 0){
				$this->harm(1, "burning");
			}
			$this->fire -= 10;
			if($this->fire <= 0){
				$this->fire = 0;
				$this->updateMetadata();
			}else{
				$hasUpdate = true;
			}
			if(($this->player instanceof Player) and ($this->player->gamemode & 0x01) === CREATIVE){ //Remove fire effects in next tick
				$this->fire = 1;
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
								$hasUpdate = true;
							}elseif($x == ($endX - 1) and $y == $endY and $z == ($endZ - 1) and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER) and $waterDone === false){
								$this->air -= 10;
								$waterDone = true;
								$this->updateMetadata();
								$hasUpdate = true;
							}
							break;
						case LAVA: //Lava damage
						case STILL_LAVA:
							if($this->inBlock(new Vector3($x, $y, $z))){
								$this->harm(5, "lava");
								$this->fire = 300;
								$this->updateMetadata();
								$hasUpdate = true;
							}
							break;
						case FIRE: //Fire block damage
							if($this->inBlock(new Vector3($x, $y, $z))){
								$this->harm(1, "fire");
								$this->fire = 300;
								$this->updateMetadata();
								$hasUpdate = true;
							}
							break;
						case CACTUS: //Cactus damage
							if($this->touchingBlock(new Vector3($x, $y, $z))){
								$this->harm(1, "cactus");
								$hasUpdate = true;
							}
							break;
						default:
							if($this->inBlock(new Vector3($x, $y, $z), 0.7) and $y == $endY and $b->isTransparent === false and ($this->class === ENTITY_MOB or $this->class === ENTITY_PLAYER)){
								$this->harm(1, "suffocation"); //Suffocation
								$hasUpdate = true;
							}elseif($x == ($endX - 1) and $y == $endY and $z == ($endZ - 1)){
								$this->air = 300; //Breathing
							}
							break;
					}
				}
			}		
		}
		return $hasUpdate;
	}

	public function update(){
		if($this->closed === true){
			return false;
		}
		$now = microtime(true);
		if($this->check === false){
			$this->lastUpdate = $now;
			return;
		}
		$tdiff = $now - $this->lastUpdate;

		if($this->tickCounter === 0){
			$this->tickCounter = 1;
			$hasUpdate = $this->environmentUpdate();
		}else{
			$hasUpdate = true;
			$this->tickCounter = 0;
		}
		
		if($this->closed === true){
			return false;
		}
		
		if($this->isStatic === false){
			$startX = floor($this->x - 0.5 - $this->size - 1);
			//prefix for flying when player on fence
			$y = (int) floor($this->y - 1);
			$startZ = floor($this->z - 0.5 - $this->size - 1);
			$endX = ceil($this->x - 0.5 + $this->size + 1);
			$endZ = ceil($this->z - 0.5 + $this->size + 1);
			$support = false;
			$isFlying = true;
			for($z = $startZ; $z <= $endZ; ++$z){
				for($x = $startX; $x <= $endX; ++$x){
					$v = new Vector3($x, $y, $z);
					if($this->isSupport($v, $this->size)){
						$b = $this->level->getBlock($v);
						if($b->isSolid === true){
							$support = true;
							$isFlying = false;
							break;
						}elseif(($b instanceof LiquidBlock) or $b->getID() === COBWEB or $b->getID() === LADDER or $b->getID() === FENCE or $b->getID() === STONE_WALL){
							$isFlying = false;
						}
					}
				}
				if($support === true){
					break;
				}
			}
			if($this->class !== ENTITY_PLAYER){
				$update = false;
				if(($this->class !== ENTITY_OBJECT and $this->type !== OBJECT_PRIMEDTNT) or $support === false){
					$drag = 0.4 * $tdiff;
					if($this->speedX != 0){
						$this->speedX -= $this->speedX * $drag;
						$this->x += $this->speedX * $tdiff;
						$update = true;
					}
					if($this->speedZ != 0){
						$this->speedZ -= $this->speedZ * $drag;
						$this->z += $this->speedZ * $tdiff;
						$update = true;
					}
					if($this->speedY != 0){
						$this->speedY -= $this->speedY * $drag;
						$ny = $this->y + $this->speedY * $tdiff;
						if($ny <= $this->y){
							$x = (int) ($this->x - 0.5);
							$z = (int) ($this->z - 0.5);
							$lim = (int) floor($ny);
							for($y = (int) ceil($this->y) - 1; $y >= $lim; --$y){
								if($this->level->getBlock(new Vector3($x, $y, $z))->isSolid === true){
									$ny = $y + 1;
									$this->speedY = 0;
									$support = true;
									if($this->class === ENTITY_FALLING){
										$this->y = $ny;
										$fall = $this->level->getBlock(new Vector3(intval($this->x - 0.5), intval(ceil($this->y)), intval($this->z - 0.5)));
										$down = $this->level->getBlock(new Vector3(intval($this->x - 0.5), intval(ceil($this->y) - 1), intval($this->z - 0.5)));
										if($fall->isFullBlock === false or $down->isFullBlock === false){
											$this->server->api->entity->drop($this, BlockAPI::getItem($this->data["Tile"] & 0xFFFF, 0, 1), true);
										}else{
											$this->level->setBlock($fall, BlockAPI::get($this->data["Tile"]), true, false, true);
										}
										$this->server->api->handle("entity.motion", $this);
										$this->close();
										return false;
									}
									break;
								}
							}
						}
						$this->y = $ny;
						$update = true;
					}
				}
				
				if($support === false){
					$this->speedY -= ($this->class === ENTITY_FALLING ? 18:32) * $tdiff;
					$update = true;
				}else{
					$this->speedX = 0;
					$this->speedY = 0;
					$this->speedZ = 0;
					$this->server->api->handle("entity.move", $this);
					if(($this->class === ENTITY_OBJECT and $this->type !== OBJECT_PRIMEDTNT) or $this->speedY <= 0.1){
						$update = false;						
						$this->server->api->handle("entity.motion", $this);
					}
				}
				
				if($update === true){
					$hasUpdate = true;
					$this->server->api->handle("entity.motion", $this);
				}
				
			}elseif($this->player instanceof Player){
				if($isFlying === true and ($this->player->gamemode & 0x01) === 0x00){
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
						if($dmg > 0 and !($d instanceof LiquidBlock) and $d->getID() !== LADDER and $d->getID() !== COBWEB and !($d2 instanceof LiquidBlock) and $d2->getID() !== LADDER and $d2->getID() !== COBWEB){
							$this->harm($dmg, "fall");
						}
					}
					$this->fallY = false;
					$this->fallStart = false;
					
				}
				$this->calculateVelocity();
				if($this->speed <= 9 or ($this->speed <= 20 and ($this->player->gamemode & 0x01) === 0x01)){
					$this->player->lastCorrect = new Vector3($this->last[0], $this->last[1], $this->last[2]);
				}
			}
		}
		
		
		if($this->class !== ENTITY_PLAYER){
			$this->updateMovement();
			if($hasUpdate === true){
				$this->server->schedule(5, array($this, "update"));
			}
		}
		$this->lastUpdate = $now;
	}
	
	public function updateMovement(){
		if($this->closed === true){
			return false;
		}
		$now = microtime(true);
		if($this->isStatic === false and ($this->last[0] != $this->x or $this->last[1] != $this->y or $this->last[2] != $this->z or $this->last[3] != $this->yaw or $this->last[4] != $this->pitch)){
			if($this->class === ENTITY_PLAYER or ($this->last[5] + 8) < $now){
				if($this->server->api->handle("entity.move", $this) === false){
					if($this->class === ENTITY_PLAYER){
						$this->player->teleport(new Vector3($this->last[0], $this->last[1], $this->last[2]), $this->last[3], $this->last[4]);
					}else{
						$this->setPosition($this->last[0], $this->last[1], $this->last[2], $this->last[3], $this->last[4]);
					}
				}else{
					$this->updateLast();
					$players = $this->server->api->player->getAll($this->level);
					if($this->player instanceof Player){
						unset($players[$this->player->CID]);
						$this->server->api->player->broadcastPacket($players, MC_MOVE_PLAYER, array(
							"eid" => $this->eid,
							"x" => $this->x,
							"y" => $this->y,
							"z" => $this->z,
							"yaw" => $this->yaw,
							"pitch" => $this->pitch,
							"bodyYaw" => $this->yaw,
						));
					}else{
						$this->server->api->player->broadcastPacket($players, MC_MOVE_ENTITY_POSROT, array(
							"eid" => $this->eid,
							"x" => $this->x,
							"y" => $this->y,
							"z" => $this->z,
							"yaw" => $this->yaw,
							"pitch" => $this->pitch,
						));
					}
				}
			}else{
				$this->updatePosition($this->x, $this->y, $this->z, $this->yaw, $this->pitch);
			}
		}
		$this->lastUpdate = $now;
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
		$flags |= ($this->crouched === true ? 0b10:0) << 1;
		$flags |= ($this->inAction === true ? 0b10000:0);
		$d = array(
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => $this->air),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		);
		if($this->class === ENTITY_MOB and $this->type === MOB_SHEEP){
			if(!isset($this->data["Sheared"])){
				$this->data["Sheared"] = 0;
				$this->data["Color"] = mt_rand(0,15);
			}
			$d[16]["value"] = (($this->data["Sheared"] == 1 ? 1:0) << 4) | ($this->data["Color"] & 0x0F);
		}elseif($this->type === OBJECT_PRIMEDTNT){
			$d[16]["value"] = (int) max(0, $this->data["fuse"] - (microtime(true) - $this->spawntime) * 20);
		}elseif($this->class === ENTITY_PLAYER){
			if($this->player->isSleeping !== false){
				$d[16]["value"] = 2;
				$d[17]["value"] = array($this->player->isSleeping->x, $this->player->isSleeping->y, $this->player->isSleeping->z);
			}
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
		if($player->eid === $this->eid or $this->closed !== false or ($player->level !== $this->level and $this->class !== ENTITY_PLAYER)){
			return false;
		}
		switch($this->class){
			case ENTITY_PLAYER:
				if($this->player->connected !== true or $this->player->spawned === false){
					return false;
				}
				$player->dataPacket(MC_ADD_PLAYER, array(
					"clientID" => 0,/*$this->player->clientID,*/
					"username" => $this->player->username,
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"yaw" => 0,
					"pitch" => 0,
					"unknown1" => 0,
					"unknown2" => 0,
					"metadata" => $this->getMetadata(),
				));
				$player->dataPacket(MC_PLAYER_EQUIPMENT, array(
					"eid" => $this->eid,
					"block" => $this->player->getSlot($this->player->slot)->getID(),
					"meta" => $this->player->getSlot($this->player->slot)->getMetadata(),
					"slot" => 0,
				));
				$this->player->sendArmor($player);
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
				$player->dataPacket(MC_SET_ENTITY_MOTION, array(
					"eid" => $this->eid,
					"speedX" => (int) ($this->speedX * 400),
					"speedY" => (int) ($this->speedY * 400),
					"speedZ" => (int) ($this->speedZ * 400),
				));
				break;
			case ENTITY_MOB:
				$player->dataPacket(MC_ADD_MOB, array(
					"type" => $this->type,
					"eid" => $this->eid,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"yaw" => 0,
					"pitch" => 0,
					"metadata" => $this->getMetadata(),
				));
				$player->dataPacket(MC_SET_ENTITY_MOTION, array(
					"eid" => $this->eid,
					"speedX" => (int) ($this->speedX * 400),
					"speedY" => (int) ($this->speedY * 400),
					"speedZ" => (int) ($this->speedZ * 400),
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
				}elseif($this->type === OBJECT_PRIMEDTNT){
					$player->dataPacket(MC_ADD_ENTITY, array(
						"eid" => $this->eid,
						"type" => $this->type,
						"x" => $this->x,
						"y" => $this->y,
						"z" => $this->z,
						"did" => 0,
					));
				}elseif($this->type === OBJECT_ARROW){
					$player->dataPacket(MC_ADD_ENTITY, array(
						"eid" => $this->eid,
						"type" => $this->type,
						"x" => $this->x,
						"y" => $this->y,
						"z" => $this->z,
						"did" => 0,
					));
					$player->dataPacket(MC_SET_ENTITY_MOTION, array(
						"eid" => $this->eid,
						"speedX" => (int) ($this->speedX * 400),
						"speedY" => (int) ($this->speedY * 400),
						"speedZ" => (int) ($this->speedZ * 400),
					));
				}
				break;
			case ENTITY_FALLING:
				$player->dataPacket(MC_ADD_ENTITY, array(
					"eid" => $this->eid,
					"type" => $this->type,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"did" => -$this->data["Tile"],
				));
				$player->dataPacket(MC_SET_ENTITY_MOTION, array(
					"eid" => $this->eid,
					"speedX" => (int) ($this->speedX * 400),
					"speedY" => (int) ($this->speedY * 400),
					"speedZ" => (int) ($this->speedZ * 400),
				));
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
	
	public function updatePosition(){
		$this->server->query("UPDATE entities SET level = '".$this->level->getName()."', x = ".$this->x.", y = ".$this->y.", z = ".$this->z.", pitch = ".$this->pitch.", yaw = ".$this->yaw." WHERE EID = ".$this->eid.";");
	}

	public function setPosition(Vector3 $pos, $yaw = false, $pitch = false){
		if($pos instanceof Position and $this->level !== $pos->level){
			$this->level = $pos->level;
			$this->server->preparedSQL->entity->setLevel->reset();
			$this->server->preparedSQL->entity->setLevel->clear();
			$this->server->preparedSQL->entity->setLevel->bindValue(":level", $this->level->getName(), SQLITE3_TEXT);
			$this->server->preparedSQL->entity->setLevel->bindValue(":eid", $this->eid, SQLITE3_TEXT);
			$this->server->preparedSQL->entity->setLevel->execute();
		}
		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;
		if($yaw !== false){
			$this->yaw = $yaw;
		}
		if($pitch !== false){
			$this->pitch = $pitch;
		}
		$this->server->preparedSQL->entity->setPosition->reset();
		$this->server->preparedSQL->entity->setPosition->clear();
		$this->server->preparedSQL->entity->setPosition->bindValue(":x", $this->x, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->bindValue(":y", $this->y, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->bindValue(":z", $this->z, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->bindValue(":pitch", $this->pitch, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->bindValue(":yaw", $this->yaw, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->bindValue(":eid", $this->eid, SQLITE3_TEXT);
		$this->server->preparedSQL->entity->setPosition->execute();
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
		$me = new Vector2($this->x - 0.5, $this->z - 0.5);
		$diff = $this->y - $pos->y;
		if($me->distance(new Vector2($pos->x, $pos->z)) < $radius and $diff > -0.7 and $diff < 1.6){
			return true;
		}
		return false;
	}

	public function resetSpeed(){
		$this->speedMeasure = array(0, 0, 0, 0, 0, 0, 0);	
	}

	public function getSpeed(){
		return $this->speed;		
	}
	
	public function getSpeedMeasure(){
		return array_sum($this->speedMeasure) / count($this->speedMeasure);		
	}
	
	public function calculateVelocity(){
		$diffTime = max(0.05, abs(microtime(true) - $this->last[5]));
		$origin = new Vector2($this->last[0], $this->last[2]);
		$final = new Vector2($this->x, $this->z);
		$speedX = ($this->last[0] - $this->x) / $diffTime;
		$speedY = ($this->last[1] - $this->y) / $diffTime;
		$speedZ = ($this->last[2] - $this->z) / $diffTime;
		if($this->speedX != $speedX or $this->speedY != $speedY or $this->speedZ != $speedZ){
			$this->speedX = $speedX;
			$this->speedY = $speedY;
			$this->speedZ = $speedZ;
			$this->server->api->handle("entity.motion", $this);
		}
		$this->speed = $origin->distance($final) / $diffTime;
		unset($this->speedMeasure[key($this->speedMeasure)]);
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
		return $this->setHealth(max(-128, $this->getHealth() - ((int) $dmg)), $cause, $force);
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
					CHAIN_LEGGINGS => 4,
					CHAIN_BOOTS => 1,
					GOLD_HELMET => 1,
					GOLD_CHESTPLATE => 5,
					GOLD_LEGGINGS => 3,
					GOLD_BOOTS => 1,
					IRON_HELMET => 2,
					IRON_CHESTPLATE => 6,
					IRON_LEGGINGS => 5,
					IRON_BOOTS => 2,
					DIAMOND_HELMET => 3,
					DIAMOND_CHESTPLATE => 8,
					DIAMOND_LEGGINGS => 6,
					DIAMOND_BOOTS => 3,
				);
				foreach($this->player->armor as $part){
					if($part instanceof Item and isset($values[$part->getID()])){
						$points += $values[$part->getID()];
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
		if($this->server->api->dhandle("entity.health.change", array("entity" => $this, "eid" => $this->eid, "health" => $health, "cause" => $cause)) !== false or $force === true){
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
					$this->server->api->player->broadcastPacket($this->server->api->player->getAll($this->level), MC_MOVE_ENTITY_POSROT, array(
						"eid" => $this->eid,
						"x" => -256,
						"y" => 128,
						"z" => -256,
						"yaw" => 0,
						"pitch" => 0,
					));
				}else{
					$this->server->api->dhandle("entity.event", array("entity" => $this, "event" => 3)); //Entity dead
				}
				if($this->player instanceof Player){
					$this->player->blocked = true;
					$this->server->api->dhandle("player.death", array("player" => $this->player, "cause" => $cause));
					if($this->server->api->getProperty("hardcore") == 1){
						$this->server->api->ban->ban($this->player->username);
					}
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
	
	public function __toString(){
		return "Entity(x={$this->x},y={$this->y},z={$this->z},level=".$this->level->getName().",class={$this->class},type={$this->type})";
	}

}
