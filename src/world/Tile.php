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
			case TILE_CHEST:
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
	
	public function isPaired(){
		if($this->class !== TILE_CHEST){
			return false;
		}
		if(!isset($this->data["pairx"]) or !isset($this->data["pairz"])){
			return false;
		}
		return true;
	}
	
	public function getPair(){
		if($this->isPaired()){
			return $this->server->api->tile->get(new Position((int) $this->data["pairx"], $this->y, (int) $this->data["pairz"], $this->level));
		}
		return false;
	}
	
	public function pairWith(Tile $tile){
		if($this->isPaired()or $tile->isPaired()){
			return false;
		}
		
		$this->data["pairx"] = $tile->x;
		$this->data["pairz"] = $tile->z;
		
		$tile->data["pairx"] = $this->x;
		$tile->data["pairz"] = $this->z;
		
		$this->server->api->tile->spawnToAll($this);
		$this->server->api->tile->spawnToAll($tile);
		$this->server->handle("tile.update", $this);
		$this->server->handle("tile.update", $tile);
	}
	
	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}
		
		$tile = $this->getPair();
		unset($this->data["pairx"], $this->data["pairz"], $tile->data["pairx"], $tile->data["pairz"]);
		
		$this->server->api->tile->spawnToAll($this);
		$this->server->handle("tile.update", $this);
		if($tile instanceof Tile){
			$this->server->api->tile->spawnToAll($tile);
			$this->server->handle("tile.update", $tile);
		}
	}
	
	public function openInventory(Player $player){
		if($this->class === TILE_CHEST){
			$player->windowCnt++;
			$player->windowCnt = $id = max(2, $player->windowCnt % 99);
			if(($pair = $this->getPair()) !== false){				
				if(($pair->x + ($pair->z << 13)) > ($this->x + ($this->z << 13))){ //Order them correctly
					$player->windows[$id] = array(
						$pair,
						$this
					);
				}else{
					$player->windows[$id] = array(
						$this,
						$pair
					);
				}
			}else{
				$player->windows[$id] = $this;
			}
			
			$pk = new ContainerOpenPacket;
			$pk->windowid = $id;
			$pk->type = WINDOW_CHEST;
			$pk->slots = is_array($player->windows[$id]) ? CHEST_SLOTS << 1:CHEST_SLOTS;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$player->dataPacket($pk);
			$slots = array();
			
			if(is_array($player->windows[$id])){
				$all = $this->server->api->player->getAll($this->level);
				foreach($player->windows[$id] as $ob){				
					$pk = new TileEventPacket;
					$pk->x = $ob->x;
					$pk->y = $ob->y;
					$pk->z = $ob->z;
					$pk->case1 = 1;
					$pk->case2 = 2;
					$this->server->api->player->broadcastPacket($all, $pk);
					for($s = 0; $s < CHEST_SLOTS; ++$s){
						$slot = $ob->getSlot($s);
						if($slot->getID() > AIR and $slot->count > 0){
							$slots[] = $slot;
						}else{
							$slots[] = BlockAPI::getItem(AIR, 0, 0);
						}
					}
				}
			}else{
				$pk = new TileEventPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->case1 = 1;
				$pk->case2 = 2;
				$this->server->api->player->broadcastPacket($this->server->api->player->getAll($this->level), $pk);
				for($s = 0; $s < CHEST_SLOTS; ++$s){
					$slot = $this->getSlot($s);
					if($slot->getID() > AIR and $slot->count > 0){
						$slots[] = $slot;
					}else{
						$slots[] = BlockAPI::getItem(AIR, 0, 0);
					}
				}
			}
			
			$pk = new ContainerSetContentPacket;
			$pk->windowid = $id;
			$pk->slots = $slots;
			$player->dataPacket($pk);
			return true;
		}elseif($this->class === TILE_FURNACE){
			$player->windowCnt++;
			$player->windowCnt = $id = max(2, $player->windowCnt % 99);
			$player->windows[$id] = $this;
			
			$pk = new ContainerOpenPacket;
			$pk->windowid = $id;
			$pk->type = WINDOW_FURNACE;
			$pk->slots = FURNACE_SLOTS;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$player->dataPacket($pk);
			
			$slots = array();
			for($s = 0; $s < FURNACE_SLOTS; ++$s){
				$slot = $this->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$slots[] = $slot;
				}else{
					$slots[] = BlockAPI::getItem(AIR, 0, 0);
				}
			}
			$pk = new ContainerSetContentPacket;
			$pk->windowid = $id;
			$pk->slots = $slots;
			$player->dataPacket($pk);
			return true;
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
			$canSmelt = ($smelt !== false and $raw->count > 0 and (($product->getID() === $smelt->getID() and $product->getMetadata() === $smelt->getMetadata() and $product->count < $product->getMaxStackSize()) or $product->getID() === AIR));
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
					$this->level->setBlock($this, BlockAPI::get(BURNING_FURNACE, $current->getMetadata()), true, false, true);
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
					$this->level->setBlock($this, BlockAPI::get(FURNACE, $current->getMetadata()), true, false, true);
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
	
	public function setSlot($s, Item $item, $update = true, $offset = 0){
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
			"offset" => $offset,
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
			case TILE_CHEST:
				$nbt = new NBT();
				$nbt->write(chr(NBT::TAG_COMPOUND)."\x00\x00");
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("id");
				$nbt->writeTAG_String($this->class);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("x");
				$nbt->writeTAG_Int((int) $this->x);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("y");
				$nbt->writeTAG_Int((int) $this->y);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("z");
				$nbt->writeTAG_Int((int) $this->z);
				
				if($this->isPaired()){
					$nbt->write(chr(NBT::TAG_INT));
					$nbt->writeTAG_String("pairx");
					$nbt->writeTAG_Int((int) $this->data["pairx"]);
					
					$nbt->write(chr(NBT::TAG_INT));
					$nbt->writeTAG_String("pairz");
					$nbt->writeTAG_Int((int) $this->data["pairz"]);
				}
				
				$nbt->write(chr(NBT::TAG_END));				
				
				$pk = new EntityDataPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->namedtag = $nbt->binary;
				$player->dataPacket($pk);
				break;
			case TILE_SIGN:
				$nbt = new NBT();
				$nbt->write(chr(NBT::TAG_COMPOUND)."\x00\x00");
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("Text1");
				$nbt->writeTAG_String($this->data["Text1"]);
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("Text2");
				$nbt->writeTAG_String($this->data["Text2"]);
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("Text3");
				$nbt->writeTAG_String($this->data["Text3"]);
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("Text4");
				$nbt->writeTAG_String($this->data["Text4"]);
				
				$nbt->write(chr(NBT::TAG_STRING));
				$nbt->writeTAG_String("id");
				$nbt->writeTAG_String($this->class);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("x");
				$nbt->writeTAG_Int((int) $this->x);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("y");
				$nbt->writeTAG_Int((int) $this->y);
				
				$nbt->write(chr(NBT::TAG_INT));
				$nbt->writeTAG_String("z");
				$nbt->writeTAG_Int((int) $this->z);
				
				$nbt->write(chr(NBT::TAG_END));				
				
				$pk = new EntityDataPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->namedtag = $nbt->binary;
				$player->dataPacket($pk);
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
		$this->server->api->tile->spawnToAll($this);	
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
