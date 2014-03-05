<?php

/*
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

/***REM_START***/
require_once("SpawnableTile.php");
require_once("ContainerTileTrait.php");
/***REM_END***/

class ChestTile extends SpawnableTile{
	use ContainerTileTrait;
	
	const SLOTS = 27;
	
	public function __construct(Level $level, NBTTag_Compound $nbt){
		$nbt->id = Tile::CHEST;
		parent::__construct($level, $nbt);
	}
	
	public function isPaired(){
		if(!isset($this->namedtag->pairx) or !isset($this->namedtag->pairz)){
			return false;
		}
		return true;
	}
	
	public function getPair(){
		if($this->isPaired()){
			return $this->level->getTile(new Vector3((int) $this->namedtag->pairx, $this->y, (int) $this->namedtag->pairz));
		}
		return false;
	}
	
	public function pairWith(Tile $tile){
		if($this->isPaired()or $tile->isPaired()){
			return false;
		}
		
		$this->namedtag->pairx = $tile->x;
		$this->namedtag->pairz = $tile->z;
		
		$tile->namedtag->pairx = $this->x;
		$tile->namedtag->pairz = $this->z;
		
		$this->spawnToAll();
		$tile->spawnToAll();
		$this->server->handle("tile.update", $this);
		$this->server->handle("tile.update", $tile);
	}
	
	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}
		
		$tile = $this->getPair();
		unset($this->namedtag->pairx, $this->namedtag->pairz, $tile->namedtag->pairx, $tile->namedtag->pairz);
		
		$this->spawnToAll();
		$this->server->handle("tile.update", $this);
		if($tile instanceof ChestTile){
			$tile->spawnToAll();
			$this->server->handle("tile.update", $tile);
		}
	}

	public function spawnTo(Player $player){
		if($this->closed){
			return false;
		}

		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		if($this->isPaired()){
			$nbt->setData(new NBTTag_Compound("", array(
				new NBTTag_String("id", Tile::CHEST),
				new NBTTag_Int("x", (int) $this->x),
				new NBTTag_Int("y", (int) $this->y),
				new NBTTag_Int("z", (int) $this->z),	
				new NBTTag_Int("pairx", (int) $this->namedtag->pairx),
				new NBTTag_Int("pairz", (int) $this->namedtag->pairz)
			)));
		}else{
			$nbt->setData(new NBTTag_Compound("", array(
				new NBTTag_String("id", Tile::CHEST),
				new NBTTag_Int("x", (int) $this->x),
				new NBTTag_Int("y", (int) $this->y),
				new NBTTag_Int("z", (int) $this->z)
			)));
		}
			
		$pk = new EntityDataPacket;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->namedtag = $nbt->write();
		$player->dataPacket($pk);

	}
}