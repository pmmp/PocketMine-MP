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
	private $level, $time, $startCheck, $startTime, $server;
	
	public function __construct(PMFLevel $level, Config $entities, Config $tileEntities){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->entities = $entities;
		$this->tileEntities = $tileEntities;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->startCheck = microtime(true);
		$this->server->schedule(15, array($this, "checkThings"));
	}
	
	public function __destruct(){
		$this->save();
		unset($this->level);
	}
	
	public function save(){
		$this->level->setData("time", $this->time);
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
	
	public function setBlock(Position $pos, Block $block, $update = true, $tiles = false){
		if((($pos instanceof Position) and $pos->level !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}elseif($this->server->api->dhandle("block.change", array(
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
		return ($this->time);
	}
	
	public function getName(){
		return $this->level->getData("name");
	}
	
	public function setTime($time){
		$this->startTime = $this->time = (int) $time;
		$this->startCheck = microtime(true);
	}
	
	public function checkThings(){
		$now = microtime(true);
		$this->time = $this->startTime + ($now - $this->startCheck) * 20;
	}
	
	public function getSeed(){
		return (int) $this->level->getData("seed");
	}
}