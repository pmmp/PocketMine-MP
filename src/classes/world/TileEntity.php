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


define("TILE_SIGN", 0);

class TileEntity extends stdClass{
	public $name;
	public $normal;
	public $id;
	public $x;
	public $y;
	public $z;
	public $data;
	public $class;
	public $attach;
	public $metadata;
	public $closed;
	private $server;
	function __construct(PocketMinecraftServer $server, $id, $class, $x, $y, $z, $data = array()){
		$this->server = $server;
		$this->normal = true;
		$this->class = (int) $class;
		$this->data = $data;
		$this->closed = false;
		if($class === false){
			$this->closed = true;
		}
		$this->name = "";
		$this->id = (int) $id;
		$this->x = (int) $x;
		$this->y = (int) $y;
		$this->z = (int) $z;
		$this->server->query("INSERT OR REPLACE INTO tileentities (ID, class, x, y, z) VALUES (".$this->id.", ".$this->class.", ".$this->x.", ".$this->y.", ".$this->z.");");
		switch($this->class){
			case TILE_SIGN:
				$this->server->query("UPDATE tileentities SET spawnable = 1 WHERE ID = ".$this->id.";");
				
				break;
		}
		//$this->server->schedule(40, array($this, "update"), array(), true);
	}

	public function update(){
		if($this->closed === true){
			return false;
		}
	}

	public function spawn($player){
		if($this->closed){
			return false;
		}
		if(!($player instanceof Player)){
			$player = $this->server->api->player->get($player);
		}
		switch($this->class){
			case TILE_SIGN:
				$player->dataPacket(MC_SIGN_UPDATE, array(
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"line0" => $this->data["Text1"],
					"line1" => $this->data["Text2"],
					"line2" => $this->data["Text3"],
					"line3" => $this->data["Text4"],
				), true);
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

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
		$this->server->query("UPDATE entities SET name = '".str_replace("'", "", $this->name)."' WHERE EID = ".$this->eid.";");
	}


	public function setPosition($x, $y, $z){
		$this->x = (int) $x;
		$this->y = (int) $y;
		$this->z = (int) $z;
		$this->server->query("UPDATE entities SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z." WHERE EID = ".$this->eid.";");
	}

}
