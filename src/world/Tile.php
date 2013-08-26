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

class Tile extends Position{
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
	function __construct(Level $level, $id, $class, $x, $y, $z, $data = array()){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->normal = true;
		$this->class = $class;
		$this->data = $data;
		$this->closed = false;
		if($class === false){
			$this->closed = true;
		}
		$this->name = "";
		$this->lastUpdate = microtime(true);
		$this->scheduledUpdate = false;
		$this->id = (int) $id;
		$this->x = (int) $x;
		$this->y = (int) $y;
		$this->z = (int) $z;
		$this->server->query("INSERT OR REPLACE INTO tiles (ID, level, class, x, y, z) VALUES (".$this->id.", '".$this->level->getName()."', '".$this->class."', ".$this->x.", ".$this->y.", ".$this->z.");");
		switch($this->class){
			case TILE_SIGN:
				$this->server->query("UPDATE tiles SET spawnable = 1 WHERE ID = ".$this->id.";");
				break;
			case TILE_FURNACE:
				if(!isset($this->data["BurnTime"]) or $this->data["BurnTime"] < 0){
					$this->data["BurnTime"] = 0;
				}
				if(!isset($this->data["CookTime"]) or $this->data["CookTime"] < 0 or ($this->data["BurnTime"] === 0 and $this->data["CookTime"] > 0)){
					$this->data["CookTime"] = 0;
				}
				if(!isset($this->data["MaxTime"])){
					$this->data["MaxTime"] = $this->data["BurnTime"];
					$this->data["BurnTicks"] = 0;
				}
				if($this->data["BurnTime"] > 0){
					$this->update();
				}
				break;
		}
	}

	public function update(){
		if($this->closed === true){
			return false;
		}	
		
		if($this->class === TILE_FURNACE){
			$fuel = $this->getSlot(1);
			$raw = $this->getSlot(0);
			$product = $this->getSlot(2);
			$smelt = $raw->getSmeltItem();
			$canSmelt = $smelt !== false and $raw->count > 0 and (($product->getID() === $smelt->getID() and $product->getMetadata() === $smelt->getMetadata() and $product->count < $product->getMaxStackSize()) or $product->getID() === AIR);
			if($this->data["BurnTime"] <= 0 and $canSmelt and $fuel->getFuelTime() !== false and $fuel->count > 0){
				$this->lastUpdate = microtime(true);
				$this->data["MaxTime"] = $this->data["BurnTime"] = floor($fuel->getFuelTime() * 20);
				$this->data["BurnTicks"] = 0;
				--$fuel->count;
				if($fuel->count === 0){
					$fuel = BlockAPI::getItem(AIR, 0, 0);
				}
				$this->setSlot(1, $fuel, false);
				$current = $this->level->getBlock($this);
				if($current->getID() === FURNACE){
					$this->level->setBlock($this, BlockAPI::get(BURNING_FURNACE, $current->getMetadata()));
				}
			}
			if($this->data["BurnTime"] > 0){
				$ticks = (microtime(true) - $this->lastUpdate) * 20;
				$this->data["BurnTime"] -= $ticks;
				$this->data["BurnTicks"] = ceil(($this->data["BurnTime"] / $this->data["MaxTime"]) * 200);
				if($smelt !== false and $canSmelt){
					$this->data["CookTime"] += $ticks;
					if($this->data["CookTime"] >= 200){ //10 seconds
						$product = BlockAPI::getItem($smelt->getID(), $smelt->getMetadata(), $product->count + 1);
						$this->setSlot(2, $product, false);
						--$raw->count;
						if($raw->count === 0){
							$raw = BlockAPI::getItem(AIR, 0, 0);
						}
						$this->setSlot(0, $raw, false);
						$this->data["CookTime"] -= 200;
					}
				}elseif($this->data["BurnTime"] <= 0){
					$this->data["BurnTime"] = 0;
					$this->data["CookTime"] = 0;
					$this->data["BurnTicks"] = 0;
				}else{
					$this->data["CookTime"] = 0;
				}
				
				$this->server->schedule(2, array($this, "update"));
				$this->scheduledUpdate = true;
			}else{
				$current = $this->level->getBlock($this);
				if($current->getID() === BURNING_FURNACE){
					$this->level->setBlock($this, BlockAPI::get(FURNACE, $current->getMetadata()));
				}
				$this->data["CookTime"] = 0;
				$this->data["BurnTime"] = 0;
				$this->data["BurnTicks"] = 0;
				$this->scheduledUpdate = false;
			}
		}
		$this->server->handle("tile.update", $this);
		$this->lastUpdate = microtime(true);
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
	
	public function setSlot($s, Item $item, $update = true){
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

		if($update === true and $this->scheduledUpdate === false){
			$this->update();
		}
		return true;
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
					"level" => $this->level,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"line0" => $this->data["Text1"],
					"line1" => $this->data["Text2"],
					"line2" => $this->data["Text3"],
					"line3" => $this->data["Text4"],
				));
				break;
		}
	}
	
	public function setText($line1 = "", $line2 = "", $line3 = "", $line4 = ""){
		if($this->class !== TILE_SIGN){
			return false;
		}
		$this->data["Text1"] = $line1;
		$this->data["Text2"] = $line2;
		$this->data["Text3"] = $line3;
		$this->data["Text4"] = $line4;
		$this->server->handle("tile.update", $this);
		return true;
	}
	
	public function getText(){
		return array(
			$this->data["Text1"],
			$this->data["Text2"],
			$this->data["Text3"],
			$this->data["Text4"]
		);
	}

	public function close(){
		if($this->closed === false){
			$this->closed = true;
			$this->server->api->tile->remove($this->id);
		}
	}

	public function __destruct(){
		$this->close();
	}

	public function getName(){
		return $this->name;
	}


	public function setPosition(Vector3 $pos){
		if($pos instanceof Position){
			$this->level = $pos->level;
			$this->server->query("UPDATE tiles SET level = '".$this->level->getName()."' WHERE ID = ".$this->id.";");
		}
		$this->x = (int) $pos->x;
		$this->y = (int) $pos->y;
		$this->z = (int) $pos->z;
		$this->server->query("UPDATE tiles SET x = ".$this->x.", y = ".$this->y.", z = ".$this->z." WHERE ID = ".$this->id.";");
	}

}
