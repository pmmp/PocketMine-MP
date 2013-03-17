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


define("TILE_SIGN", "Sign");
define("TILE_CHEST", "Chest");
	define("CHEST_SLOTS", 27);
define("TILE_FURNACE", "Furnace");
	define("FURNACE_SLOTS", 3);

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
		$this->class = $class;
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
		$this->server->query("INSERT OR REPLACE INTO tileentities (ID, class, x, y, z) VALUES (".$this->id.", '".$this->class."', ".$this->x.", ".$this->y.", ".$this->z.");");
		switch($this->class){
			case TILE_SIGN:
				$this->server->query("UPDATE tileentities SET spawnable = 1 WHERE ID = ".$this->id.";");
				
				break;
		}
	}

	public function update(){
		if($this->closed === true){
			return false;
		}
	}
	
	public function getSlotIndex($s){
		if($this->class !== TILE_CHEST and $this->class !== TILE_FURNACE){
			return false;
		}
		foreach($this->data["Items"] as $i => $slot){
			if($slot["Slot"] === $s){
				return $i;
			}
		}
		return -1;
	}
	
	public function getSlot($s){
		$i = $this->getSlotIndex($s);
		if($i === false or $i < 0){
			return BlockAPI::getItem(AIR, 0, 0);
		}else{
			return BlockAPI::getItem($this->data["Items"][$i]["id"], $this->data["Items"][$i]["Damage"], $this->data["Items"][$i]["Count"]);
		}
	}
	
	public function setSlot($s, Item $item){
		$i = $this->getSlotIndex($s);
		$d = array(
			"Count" => $item->count,
			"Slot" => $s,
			"id" => $item->getID(),
			"Damage" => $item->getMetadata(),
		);
		if($i === false){
			return false;
		}elseif($item->getID() === AIR or $item->count <= 0){
			if($i >= 0){
				unset($this->data["Items"][$i]);
			}
		}elseif($i < 0){
			$this->data["Items"][] = $d;
		}else{
			$this->data["Items"][$i] = $d;
		}
		$this->server->api->dhandle("tile.container.slot", array(
			"tile" => $this,
			"slot" => $s,
			"slotdata" => $item,
		));
		$this->server->handle("tile.update", $this);
		return true;
	}

	public function spawn($player, $queue = false){
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
				), $queue);
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
