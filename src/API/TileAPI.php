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

class TileAPI{
	private $server;
	private $tiles;
	private $tCnt = 1;
	function __construct(){
		$this->tiles = array();
		$this->server = ServerAPI::request();
	}
	
	public function get(Position $pos){
		$tile = $this->server->query("SELECT * FROM tiles WHERE level = '".$pos->level->getName()."' AND x = {$pos->x} AND y = {$pos->y} AND z = {$pos->z};", true);
		if($tile !== false and $tile !== true and ($tile = $this->getByID($tile["ID"])) !== false){				
			return $tile;
		}
		return false;
	}

	public function getByID($id){
		if($id instanceof Tile){
			return $id;
		}elseif(isset($this->tiles[$id])){
			return $this->tiles[$id];
		}
		return false;
	}
	
	public function init(){
	
	}

	public function getAll($level = null){
		if($level instanceof Level){
			$tiles = array();
			$l = $this->server->query("SELECT ID FROM tiles WHERE level = '".$level->getName()."';");
			if($l !== false and $l !== true){
				while(($t = $l->fetchArray(SQLITE3_ASSOC)) !== false){
					$t = $this->getByID($t["ID"]);
					if($t instanceof Tile){
						$tiles[$t->id] = $t;
					}
				}
			}
			return $tiles;
		}
		return $this->tiles;
	}

	public function add(Level $level, $class, $x, $y, $z, $data = array()){
		$id = $this->tCnt++;
		$this->tiles[$id] = new Tile($level, $id, $class, $x, $y, $z, $data);
		$this->spawnToAll($this->tiles[$id]);
		return $this->tiles[$id];
	}
	
	public function addSign(Level $level, $x, $y, $z, $lines = array("", "", "", "")){
		return $this->add($level, TILE_SIGN, $x, $y, $z, $data = array(
			"id" => "Sign",
			"x" => $x,
			"y" => $y,
			"z" => $z,
			"Text1" => $lines[0],
			"Text2" => $lines[1],
			"Text3" => $lines[2],
			"Text4" => $lines[3],
		));
	}

	public function spawnToAll(Tile $t){
		foreach($this->server->api->player->getAll($t->level) as $player){
			if($player->eid !== false){
				$t->spawn($player);
			}
		}
	}

	public function spawnAll(Player $player){
		foreach($this->getAll($player->level) as $t){
			$t->spawn($player);
		}
	}

	public function remove($id){
		if(isset($this->tiles[$id])){
			$t = $this->tiles[$id];
			$this->tiles[$id] = null;
			unset($this->tiles[$id]);
			$t->closed = true;
			$t->close();
			$this->server->query("DELETE FROM tiles WHERE ID = ".$id.";");
			$this->server->api->dhandle("tile.remove", $t);
			$t = null;
			unset($t);			
		}
	}
}