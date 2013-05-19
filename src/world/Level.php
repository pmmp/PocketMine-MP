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

class Level{
	public $entities, $tileEntities;
	private $level, $time, $startCheck, $startTime, $server, $name, $usedChunks, $nextSave;
	
	public function __construct(PMFLevel $level, Config $entities, Config $tileEntities, $name){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->entities = $entities;
		$this->tileEntities = $tileEntities;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->nextSave = $this->startCheck = microtime(true);
		$this->nextSave += 90;
		$this->server->schedule(15, array($this, "checkThings"), array(), true);
		$this->server->event("server.close", array($this, "save"));
		$this->name = $name;
		$this->usedChunks = array();
	}
	
	public function useChunk($X, $Z, Player $player){
		if(!isset($this->usedChunks[$X.".".$Z])){
			$this->usedChunks[$X.".".$Z] = array();
		}
		$this->usedChunks[$X.".".$Z][$player->CID] = true;
		$this->level->loadChunk($X, $Z);
	}
	
	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][$player->CID]);
		}
	}
	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[$X.".".$Z][$player->CID]);
	}
	
	public function checkThings(){
		$now = microtime(true);
		$time = $this->startTime + ($now - $this->startCheck) * 20;
		if($this->server->api->dhandle("time.change", array("level" => $this, "time" => $time)) !== false){
			$this->time = $time;
		}
		
		if($this->nextSave < $now and $this->server->saveEnabled === true){
			foreach($this->usedChunks as $i => $c){
				if(count($c) === 0){
					unset($this->usedChunks[$i]);
					$X = explode(".", $i);
					$Z = array_pop($X);
					$this->level->unloadChunk((int) array_pop($X), (int) $Z);
				}
			}
			$this->save();
		}
	}
	
	public function __destruct(){
		$this->save();
		unset($this->level);
	}
	
	public function save($force = false){
		if($this->server->saveEnabled === false and $force === false){
			return;
		}
		$entities = array();
		foreach($this->server->api->entity->getAll($this) as $entity){
			if($entity->class === ENTITY_MOB){
				$entities[] = array(
					"id" => $entity->type,
					"Color" => @$entity->data["Color"],
					"Sheared" => @$entity->data["Sheared"],
					"Health" => $entity->health,
					"Pos" => array(
						0 => $entity->x,
						1 => $entity->y,
						2 => $entity->z,
					),
					"Rotation" => array(
						0 => $entity->yaw,
						1 => $entity->pitch,
					),
				);
			}elseif($entity->class === ENTITY_OBJECT){
				$entities[] = array(
					"id" => $entity->type,
					"TileX" => $entity->x,
					"TileX" => $entity->y,
					"TileX" => $entity->z,
					"Health" => $entity->health,
					"Motive" => $entity->data["Motive"],
					"Pos" => array(
						0 => $entity->x,
						1 => $entity->y,
						2 => $entity->z,
					),
					"Rotation" => array(
						0 => $entity->yaw,
						1 => $entity->pitch,
					),
				);
			}elseif($entity->class === ENTITY_ITEM){
				$entities[] = array(
					"id" => 64,
					"Item" => array(
						"id" => $entity->type,
						"Damage" => $entity->meta,
						"Count" => $entity->stack,
					),
					"Health" => $entity->health,
					"Pos" => array(
						0 => $entity->x,
						1 => $entity->y,
						2 => $entity->z,
					),
					"Rotation" => array(
						0 => 0,
						1 => 0,
					),
				);
			}
		}
		$this->entities->setAll($entities);
		$this->entities->save();
		$tiles = array();
		foreach($this->server->api->tileentity->getAll($this) as $tile){		
			$tiles[] = $tile->data;
		}
		$this->tileEntities->setAll($tiles);
		$this->tileEntities->save();
		
		$this->level->setData("time", (int) $this->time);
		$this->level->doSaveRound();
		$this->level->saveData();
		$this->nextSave = microtime(true) + 90;
	}
	
	public function getBlock(Vector3 $pos){
		if(($pos instanceof Position) and $pos->level !== $this){
			return false;
		}
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);
		return BlockAPI::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}
	
	public function setBlockRaw(Vector3 $pos, Block $block){
		return $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata());
	}
	
	public function setBlock(Vector3 $pos, Block $block, $update = true, $tiles = false){
		if((($pos instanceof Position) and $pos->level !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}
		
		$ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata());
		if($ret === true){
			if(!($pos instanceof Position)){
				$pos = new Position($pos->x, $pos->y, $pos->z, $this);
			}
			$this->server->trigger("block.change", array(
				"position" => $pos,
				"block" => $block,
			));
			if($update === true){
				$this->server->api->block->blockUpdate($this->getBlock($pos), BLOCK_UPDATE_NORMAL); //????? water?
				$this->server->api->block->blockUpdateAround($pos, BLOCK_UPDATE_NORMAL);
			}
			if($tiles === true){
				if(($t = $this->server->api->tileentity->get($pos)) !== false){
					$t[0]->close();
				}
			}
		}
		return $ret;
	}
	
	public function getMiniChunk($X, $Y, $Z){
		return $this->level->getMiniChunk($X, $Z);
	}
	
	public function setMiniChunk($X, $Y, $Z, $data){
		return $this->level->setMiniChunk($X, $Y, $Z, $data);
	}
	
	public function loadChunk($X, $Z){
		return $this->level->loadChunk($X, $Z);
	}
	
	public function unloadChunk($X, $Z){
		return $this->level->unloadChunk($X, $Z);
	}
	
	public function getOrderedMiniChunk($X, $Z, $Y){
		$raw = $this->level->getMiniChunk($X, $Z, $Y);
		$ordered = "";
		$flag = chr(1 << $Y);
		for($j = 0; $j < 256; ++$j){
			$index = $j << 5;
			$ordered .= $flag;
			$ordered .= substr($raw, $index, 16);
			$ordered .= substr($raw, $index + 16, 8);
		}
		return $ordered;
	}
	
	public function getSpawn(){
		return new Position($this->level->getData("spawnX"), $this->level->getData("spawnY"), $this->level->getData("spawnZ"), $this);
	}
	
	public function setSpawn(Vector3 $pos){
		$this->level->setData("spawnX", $pos->x);
		$this->level->setData("spawnY", $pos->y);
		$this->level->setData("spawnZ", $pos->z);
	}
	
	public function getTime(){
		return (int) ($this->time);
	}
	
	public function getName(){
		return $this->name;//return $this->level->getData("name");
	}
	
	public function setTime($time){
		$this->startTime = $this->time = (int) $time;
		$this->startCheck = microtime(true);
	}
	
	public function getSeed(){
		return (int) $this->level->getData("seed");
	}
	
	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
		return $this->server->api->block->scheduleBlockUpdate($pos, $delay, $type);
	}
}