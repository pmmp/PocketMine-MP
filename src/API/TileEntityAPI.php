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
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}
	
	public function get($x, $y = false, $z = false){
		if(($x instanceof Vector3) or ($x instanceof Block)){
			$z = (int) $x->z;	
			$y = (int) $x->y;	
			$x = (int) $x->x;			
		}else{
			$x = (int) $x;
			$y = (int) $y;
			$z = (int) $z;
		}
		$tiles = $this->server->query("SELECT * FROM tileentities WHERE x = $x AND y = $y AND z = $z;");
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
		}elseif(isset($this->server->tileEntities[$id])){
			return $this->server->tileEntities[$id];
		}
		return false;
	}
	
	public function init(){
	
	}

	public function getAll(){
		return $this->server->tileEntities;
	}

	public function add($class, $x, $y, $z, $data = array()){
		$id = $this->tCnt++;
		$this->server->tileEntities[$id] = new TileEntity($this->server, $id, $class, $x, $y, $z, $data);
		$this->spawnToAll($id);
		return $this->server->tileEntities[$id];
	}
	
	public function addSign($x, $y, $z, $lines = array("", "", "", "")){
		return $this->add(TILE_SIGN, $x, $y, $z, $data = array(
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

	public function spawnToAll($id){
		$t = $this->getByID($id);
		if($t === false){
			return false;
		}
		foreach($this->server->api->player->getAll() as $player){
			if($player->eid !== false){
				$t->spawn($player);
			}
		}
	}

	public function spawnAll($player){
		foreach($this->getAll() as $t){
			$t->spawn($player);
		}
	}

	public function remove($id){
		if(isset($this->server->tileEntities[$id])){
			$t = $this->server->tileEntities[$id];
			$this->server->tileEntities[$id] = null;
			unset($this->server->tileEntities[$id]);
			$t->closed = true;
			$t->close();
			$this->server->query("DELETE FROM tileentities WHERE ID = ".$id.";");
			$t = null;
			unset($t);			
		}
	}
}