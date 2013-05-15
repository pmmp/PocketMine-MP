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
	private $level, $time, $startCheck, $startTime, $server, $name, $usedChunks;
	
	public function __construct(PMFLevel $level, Config $entities, Config $tileEntities, $name){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->entities = $entities;
		$this->tileEntities = $tileEntities;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->lastSave = $this->startCheck = microtime(true);
		$this->nextSave += 30;
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

		foreach($this->usedChunks as $i => $c){
			if(count($c) === 0){
				unset($this->usedChunks[$i]);
				$X = explode(".", $i);
				$Z = array_pop($X);
				$this->level->unloadChunk((int) $X, (int) $Z);
			}
		}
		
		if($this->lastSave < $now){
			$this->save();
			$this->lastSave = $now + 30;
		}
	}
	
	public function __destruct(){
		$this->save();
		unset($this->level);
	}
	
	public function save(){
		$this->level->setData("time", (int) $this->time);
		$this->level->doSaveRound();
		$this->level->saveData();
	}
	
	public function getBlock(Vector3 $pos){
		if(($pos instanceof Position) and $pos->level !== $this){
			return false;
		}
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);
		return BlockAPI::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}
	
	public function setBlock(Vector3 $pos, Block $block, $update = true, $tiles = false){
		if((($pos instanceof Position) and $pos->level !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}elseif(!($pos instanceof Position)){
			$pos = new Position($pos->x, $pos->y, $pos->z, $this);
		}
		
		if($this->server->api->dhandle("block.change", array(
			"position" => $pos,
			"block" => $block,
		)) !== false){
			$ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata());
			if($update === true){
				$this->server->api->block->blockUpdate($pos, BLOCK_UPDATE_NORMAL); //????? water?
				$this->server->api->block->blockUpdateAround($pos, BLOCK_UPDATE_NORMAL);
			}
			if($tiles === true){
				if(($t = $this->server->api->tileentity->get($pos)) !== false){
					$t[0]->close();
				}
			}
			return $ret;
		}
		return false;
	}
	
	public function getMiniChunk($X, $Z){
		return $this->level->getMiniChunk($X, $Z);
	}
	
	public function setMiniChunk($X, $Z, $data){
		return $this->level->setMiniChunk($X, $Z, $data);
	}
	
	public function loadChunk($X, $Z){
		return $this->level->loadChunk($X, $Z);
	}
	
	public function unloadChunk($X, $Z){
		return $this->level->unloadChunk($X, $Z);
	}
	
	public function getOrderedMiniChunk($X, $Z, $Y, $MTU){
		$raw = $this->level->getMiniChunk($X, $Z, $Y);
		$ordered = array();
		$i = 0;
		$ordered[$i] = "";
		$cnt = 0;
		$flag = chr(1 << $Y);
		for($j = 0; $j < 256; ++$j){
			if((strlen($ordered[$i]) + 16 + 8 + 1) > $MTU){
				++$i;
				$ordered[$i] = str_repeat("\x00", $cnt);
			}
			$index = $j << 5;
			$ordered[$i] .= $flag;
			$ordered[$i] .= substr($raw, $index, 16);
			$ordered[$i] .= substr($raw, $index + 16, 8);
			++$cnt;
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
}