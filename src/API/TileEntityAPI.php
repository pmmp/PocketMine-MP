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

class TileEntityAPI{
	private $server;
	private $tileEntities;
	private $tCnt = 1;
	function __construct(){
		$this->tileEntities = array();
		$this->server = ServerAPI::request();
	}
	
	public function get(Position $pos){
		$tiles = $this->server->query("SELECT * FROM tileentities WHERE level = '".$pos->level->getName()."' AND x = {$pos->x} AND y = {$pos->y} AND z = {$pos->z};");
		$ret = array();
		if($tiles !== false and $tiles !== true){
			while(($t = $tiles->fetchArray(SQLITE3_ASSOC)) !== false){
				if(($tile = $this->getByID($t["ID"])) !== false){
					if($tile->normal === true){					
						$ret[] = $tile;
					}
				}
			}
		}
		if(count($ret) === 0){
			return false;
		}
		return $ret;
	}

	public function getByID($id){
		if($id instanceof TileEntity){
			return $id;
		}elseif(isset($this->tileEntities[$id])){
			return $this->tileEntities[$id];
		}
		return false;
	}
	
	public function init(){
	
	}

	public function getAll($level = null){
		if($level instanceof Level){
			$tileEntities = array();
			$l = $this->server->query("SELECT ID FROM tileentities WHERE level = '".$level->getName()."';");
			if($l !== false and $l !== true){
				while(($t = $l->fetchArray(SQLITE3_ASSOC)) !== false){
					$t = $this->get($e["ID"]);
					if($t instanceof TileEntity){
						$tileEntities[$t->id] = $t;
					}
				}
			}
			return $tileEntities;
		}
		return $this->tileEntities;
	}

	public function add(Level $level, $class, $x, $y, $z, $data = array()){
		$id = $this->tCnt++;
		$this->tileEntities[$id] = new TileEntity($level, $id, $class, $x, $y, $z, $data);
		$this->spawnToAll($level, $id);
		return $this->tileEntities[$id];
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

	public function spawnTo($id, $player, $queue = false){
		$t = $this->getByID($id);
		if($t === false){
			return false;
		}
		$t->spawn($player, $queue);
	}

	public function spawnToAll(Level $level, $id){
		$t = $this->getByID($id);
		if($t === false){
			return false;
		}
		foreach($this->server->api->player->getAll($level) as $player){
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
		if(isset($this->tileEntities[$id])){
			$t = $this->tileEntities[$id];
			$this->tileEntities[$id] = null;
			unset($this->tileEntities[$id]);
			$t->closed = true;
			$t->close();
			$this->server->query("DELETE FROM tileentities WHERE ID = ".$id.";");
			$t = null;
			unset($t);			
		}
	}
}